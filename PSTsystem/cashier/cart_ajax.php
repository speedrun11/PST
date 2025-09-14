<?php
session_start();
include('config/config.php');
include('config/checklogin.php');

check_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_to_cart':
            $prod_id = $_POST['prod_id'] ?? '';
            $prod_name = $_POST['prod_name'] ?? '';
            $prod_price = $_POST['prod_price'] ?? '';
            $prod_qty = $_POST['prod_qty'] ?? 1;
            
            if (empty($prod_id) || empty($prod_name) || empty($prod_price)) {
                echo json_encode(['success' => false, 'message' => 'Missing product information']);
                exit;
            }
            
            // Initialize cart in session if not exists
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            // Check if product already exists in cart
            $item_exists = false;
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['prod_id'] === $prod_id) {
                    $_SESSION['cart'][$key]['prod_qty'] += $prod_qty;
                    $item_exists = true;
                    break;
                }
            }
            
            // Add new item if not exists
            if (!$item_exists) {
                $_SESSION['cart'][] = [
                    'prod_id' => $prod_id,
                    'prod_name' => $prod_name,
                    'prod_price' => $prod_price,
                    'prod_qty' => $prod_qty
                ];
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Item added to cart',
                'cart_count' => count($_SESSION['cart'])
            ]);
            break;
            
        case 'update_cart':
            $prod_id = $_POST['prod_id'] ?? '';
            $prod_qty = $_POST['prod_qty'] ?? 1;
            
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $key => $item) {
                    if ($item['prod_id'] === $prod_id) {
                        if ($prod_qty <= 0) {
                            unset($_SESSION['cart'][$key]);
                        } else {
                            $_SESSION['cart'][$key]['prod_qty'] = $prod_qty;
                        }
                        break;
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated',
                'cart_count' => count($_SESSION['cart'] ?? [])
            ]);
            break;
            
        case 'remove_from_cart':
            $prod_id = $_POST['prod_id'] ?? '';
            
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $key => $item) {
                    if ($item['prod_id'] === $prod_id) {
                        unset($_SESSION['cart'][$key]);
                        break;
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_count' => count($_SESSION['cart'] ?? [])
            ]);
            break;
            
        case 'clear_cart':
            $_SESSION['cart'] = [];
            echo json_encode([
                'success' => true,
                'message' => 'Cart cleared',
                'cart_count' => 0
            ]);
            break;
            
        case 'get_cart':
            $cart = $_SESSION['cart'] ?? [];
            $total = 0;
            
            foreach ($cart as $item) {
                $total += $item['prod_price'] * $item['prod_qty'];
            }
            
            echo json_encode([
                'success' => true,
                'cart' => $cart,
                'total' => $total,
                'cart_count' => count($cart)
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
