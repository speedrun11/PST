<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');
check_login();

header('Content-Type: application/json');

try {
	$raw = file_get_contents('php://input');
	$payload = json_decode($raw, true);
	if (!is_array($payload)) {
		echo json_encode(['success' => false, 'error' => 'Invalid payload']);
		exit;
	}
	$customer_name = trim($payload['customer_name'] ?? '');
	$customer_id = trim($payload['customer_id'] ?? '');
	$items = $payload['items'] ?? [];
	$order_type = isset($payload['order_type']) && in_array($payload['order_type'], ['dine-in','takeout']) ? $payload['order_type'] : 'dine-in';
	$order_code = $alpha . '-' . $beta;
	if ($customer_name === '') {
		echo json_encode(['success' => false, 'error' => 'Customer name is required']);
		exit;
	}
	if ($customer_id === '') {
		$customer_id = 'CUST-' . time();
	}
	if (!is_array($items) || count($items) === 0) {
		echo json_encode(['success' => false, 'error' => 'No items in order']);
		exit;
	}
	$order_group_id = $customer_id . '_' . $order_code . '_' . time();
	$success_count = 0;

	// Begin transaction for stock validation and deduction
	$mysqli->begin_transaction();

	// First pass: validate stock for all items
	foreach ($items as $item) {
		$prod_id = $item['id'] ?? $item['prod_id'] ?? '';
		$prod_name = $item['name'] ?? $item['prod_name'] ?? '';
		$prod_price = (float)($item['price'] ?? $item['prod_price'] ?? 0);
		$prod_qty = (int)($item['quantity'] ?? $item['prod_qty'] ?? 0);
		// Compute additional charge for takeout: +₱1 per Double/Combo/Regular + Spicy item
		$additional_charge = 0.0;
		if ($order_type === 'takeout') {
			$nm = strtolower($prod_name);
			if (strpos($nm, 'double') !== false || strpos($nm, 'combo') !== false || strpos($nm, 'regular + spicy') !== false) {
				$additional_charge = 1.0 * $prod_qty;
			}
		}
		$order_status = 'Pending';
		if ($prod_id === '' || $prod_name === '' || $prod_qty < 1) {
			continue;
		}
		// Verify product exists and compute available stock considering links
		$verifyQuery = "SELECT prod_id, prod_quantity FROM rpos_products WHERE prod_id = ? FOR UPDATE";
		$verifyStmt = $mysqli->prepare($verifyQuery);
		$verifyStmt->bind_param('s', $prod_id);
		$verifyStmt->execute();
		$verifyResult = $verifyStmt->get_result();
		if ($verifyResult->num_rows === 0) {
			$mysqli->rollback();
			echo json_encode(['success' => false, 'error' => 'Product not found: '.htmlspecialchars($prod_name)]);
			exit;
		}
		$rowP = $verifyResult->fetch_assoc();
		$available_qty = (int)$rowP['prod_quantity'];
		$badge = '';
		// Check links for mirror/combo
		$link_stmt = $mysqli->prepare("SELECT l.relation, l.base_product_id, bp.prod_name, bp.prod_quantity 
									 FROM rpos_product_links l 
									 JOIN rpos_products bp ON bp.prod_id = l.base_product_id 
									 WHERE l.linked_product_id = ?");
		if ($link_stmt) {
			$link_stmt->bind_param('s', $prod_id);
			$link_stmt->execute();
			$link_res = $link_stmt->get_result();
			$bases = [];
			$is_mirror = false;
			while ($r = $link_res->fetch_assoc()) {
				if ($r['relation'] === 'mirror') {
					$is_mirror = true;
					$available_qty = intdiv(max(0, (int)$r['prod_quantity']), 2);
				} else if ($r['relation'] === 'combo') {
					$bases[] = $r;
				}
			}
			if (!$is_mirror && count($bases) > 0) {
				$mins = array_map(function($r){ return (int)$r['prod_quantity']; }, $bases);
				$available_qty = count($mins) ? min($mins) : $available_qty;
			}
			$link_stmt->close();
		}
		if ($available_qty < $prod_qty) {
			$mysqli->rollback();
			echo json_encode(['success' => false, 'error' => 'Insufficient stock for '.htmlspecialchars($prod_name).'. Available: '.$available_qty]);
			exit;
		}
	}

	// Second pass: insert orders and deduct stock
	foreach ($items as $item) {
		$prod_id = $item['id'] ?? $item['prod_id'] ?? '';
		$prod_name = $item['name'] ?? $item['prod_name'] ?? '';
		$prod_price = (float)($item['price'] ?? $item['prod_price'] ?? 0);
		$prod_qty = (int)($item['quantity'] ?? $item['prod_qty'] ?? 0);
		if ($prod_id === '' || $prod_name === '' || $prod_qty < 1) {
			continue;
		}
		// Unique order row id
		try { $item_order_id = bin2hex(random_bytes(5)); } catch (Exception $e) { $item_order_id = substr(md5(uniqid((string)mt_rand(), true)), 0, 10); }
		$postQuery = "INSERT INTO rpos_orders (prod_qty, order_id, order_code, customer_id, customer_name, prod_id, prod_name, prod_price, order_status, order_type, additional_charge, order_group_id) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";
		$postStmt = $mysqli->prepare($postQuery);
		$postStmt->bind_param(
			'ssssssssssss',
			$prod_qty,
			$item_order_id,
			$order_code,
			$customer_id,
			$customer_name,
			$prod_id,
			$prod_name,
			$prod_price,
			$order_status,
			$order_type,
			$additional_charge,
			$order_group_id
		);
		if ($postStmt->execute()) {
			$success_count++;
		}

		// Deduct stock considering mirror/combo rules
		// Check if this product is a mirror of a base product
		$link_stmt2 = $mysqli->prepare("SELECT relation, base_product_id FROM rpos_product_links WHERE linked_product_id = ?");
		if ($link_stmt2) {
			$link_stmt2->bind_param('s', $prod_id);
			$link_stmt2->execute();
			$link_res2 = $link_stmt2->get_result();
			$bases = [];
			$mirror_base = null;
			while ($lr = $link_res2->fetch_assoc()) {
				if ($lr['relation'] === 'mirror') { $mirror_base = $lr['base_product_id']; }
				if ($lr['relation'] === 'combo') { $bases[] = $lr['base_product_id']; }
			}
			$link_stmt2->close();
			if ($mirror_base) {
				// Mirror: consumes 2 units of base per 1 sold
				$consumed = $prod_qty * 2;
				$upd = $mysqli->prepare("UPDATE rpos_products SET prod_quantity = prod_quantity - ? WHERE prod_id = ?");
				$upd->bind_param('is', $consumed, $mirror_base);
				$upd->execute();
			} elseif (count($bases) > 0) {
				// Combo: consumes 1 unit from each base per 1 sold
				foreach ($bases as $base_id) {
					$consumed = $prod_qty; 
					$upd = $mysqli->prepare("UPDATE rpos_products SET prod_quantity = prod_quantity - ? WHERE prod_id = ?");
					$upd->bind_param('is', $consumed, $base_id);
					$upd->execute();
				}
			} else {
				// Normal product: deduct from its own stock
				$upd = $mysqli->prepare("UPDATE rpos_products SET prod_quantity = prod_quantity - ? WHERE prod_id = ?");
				$upd->bind_param('is', $prod_qty, $prod_id);
				$upd->execute();
			}
		}
	}

	if ($success_count === 0) {
		echo json_encode(['success' => false, 'error' => 'Failed to insert order items']);
		$mysqli->rollback();
		exit;
	}

	$mysqli->commit();
	$_SESSION['order_success'] = "Order processed successfully! $success_count items added. Order Type: " . ucfirst($order_type);
	$_SESSION['order_code'] = $order_code;
	$_SESSION['customer_id'] = $customer_id;
	$_SESSION['order_type'] = $order_type;
	echo json_encode(['success' => true, 'redirect' => 'payments.php']);
	exit;
} catch (Exception $e) {
	if ($mysqli && $mysqli->errno === 0) { /* noop */ }
	if ($mysqli) { @$mysqli->rollback(); }
	echo json_encode(['success' => false, 'error' => 'Server error']);
	exit;
}
?>
