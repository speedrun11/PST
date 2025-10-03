<?php
session_start();
include('../cashier/config/config.php');
// Simple auth guard for KDS: require staff session; if missing, send to Cashier login
if (!isset($_SESSION['staff_id']) || strlen($_SESSION['staff_id']) == 0) {
    header('Location: ../cashier/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>PST - Enhanced Kitchen Display System</title>
	<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<style>
		:root {
			--primary-dark: #1a1a2e;
			--primary-light: #f8f5f2;
			--accent-gold: #c0a062;
			--accent-red: #9e2b2b;
			--accent-green: #4a6b57;
			--accent-blue: #3a5673;
			--text-light: #f8f5f2;
			--text-dark: #1a1a2e;
			--transition-speed: 0.3s;
		}
		
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}
		
		body {
			background: linear-gradient(135deg, #0f0f19 0%, #1a1a2e 100%);
			color: var(--text-light);
			font-family: 'Poppins', sans-serif;
			min-height: 100vh;
			overflow-x: hidden;
		}
		
		.header {
			padding: 20px 30px;
			background: rgba(23, 23, 42, 0.95);
			border-bottom: 2px solid rgba(192, 160, 98, 0.3);
			display: flex;
			align-items: center;
			justify-content: space-between;
			backdrop-filter: blur(10px);
			position: sticky;
			top: 0;
			z-index: 100;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
		}
		
		.header h1 {
			margin: 0;
			color: var(--accent-gold);
			font-weight: 700;
			font-size: 1.8rem;
			display: flex;
			align-items: center;
			gap: 12px;
		}
		
		.header-controls {
			display: flex;
			align-items: center;
			gap: 20px;
		}
		
		.status-indicator {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 8px 16px;
			background: rgba(192, 160, 98, 0.1);
			border-radius: 20px;
			border: 1px solid rgba(192, 160, 98, 0.3);
		}
		
		.status-dot {
			width: 8px;
			height: 8px;
			border-radius: 50%;
			background: var(--accent-green);
			animation: pulse 2s infinite;
		}
		
		@keyframes pulse {
			0%, 100% { opacity: 1; }
			50% { opacity: 0.5; }
		}
		
		.refresh-info {
			color: var(--accent-gold);
			font-size: 0.9rem;
			font-weight: 500;
		}
		
		.stats-bar {
			padding: 15px 30px;
			background: rgba(26, 26, 46, 0.8);
			border-bottom: 1px solid rgba(192, 160, 98, 0.2);
			display: flex;
			justify-content: space-around;
			flex-wrap: wrap;
			gap: 20px;
		}
		
		.stat-item {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 5px;
		}
		
		.stat-number {
			font-size: 1.5rem;
			font-weight: 700;
			color: var(--accent-gold);
		}
		
		.stat-label {
			font-size: 0.8rem;
			color: var(--text-light);
			opacity: 0.8;
		}
		
		.board {
			padding: 30px;
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
			gap: 20px;
			min-height: calc(100vh - 200px);
		}
		
		.order-card {
			background: rgba(28, 28, 51, 0.9);
			border: 2px solid rgba(192, 160, 98, 0.2);
			border-radius: 15px;
			padding: 20px;
			transition: all var(--transition-speed) ease;
			backdrop-filter: blur(10px);
			position: relative;
			overflow: hidden;
		}
		
		.order-card:hover {
			transform: translateY(-5px);
			border-color: var(--accent-gold);
			box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
		}
		
		.order-card.pending {
			border-left: 4px solid #ff9f43;
		}
		
		.order-card.preparing {
			border-left: 4px solid var(--accent-blue);
		}
		
		.order-card.ready {
			border-left: 4px solid var(--accent-green);
		}
		
		.order-header {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			margin-bottom: 15px;
		}
		
		.order-info h3 {
			color: var(--accent-gold);
			font-weight: 700;
			font-size: 1.3rem;
			margin-bottom: 5px;
		}
		
		.order-meta {
			display: flex;
			flex-direction: column;
			gap: 3px;
			font-size: 0.9rem;
			color: var(--text-light);
			opacity: 0.8;
		}
		
		.order-type {
			display: inline-flex;
			align-items: center;
			gap: 5px;
			padding: 4px 8px;
			border-radius: 12px;
			font-size: 0.8rem;
			font-weight: 600;
		}
		
		.order-type.dine-in {
			background: rgba(58, 86, 115, 0.3);
			color: var(--accent-blue);
		}
		
		.order-type.takeout {
			background: rgba(192, 160, 98, 0.3);
			color: var(--accent-gold);
		}
		
		.status-badge {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 8px 12px;
			border-radius: 20px;
			font-size: 0.85rem;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}
		
		.status-badge.pending {
			background: rgba(255, 159, 67, 0.2);
			color: #ff9f43;
			border: 1px solid rgba(255, 159, 67, 0.3);
		}
		
		.status-badge.preparing {
			background: rgba(58, 86, 115, 0.2);
			color: var(--accent-blue);
			border: 1px solid rgba(58, 86, 115, 0.3);
		}
		
		.status-badge.ready {
			background: rgba(74, 107, 87, 0.2);
			color: var(--accent-green);
			border: 1px solid rgba(74, 107, 87, 0.3);
		}
		
		.order-time {
			font-size: 0.8rem;
			color: var(--accent-gold);
			margin-top: 5px;
		}
		
		.items-list {
			margin: 15px 0;
			padding-left: 0;
			list-style: none;
		}
		
		.item {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 8px 0;
			border-bottom: 1px solid rgba(192, 160, 98, 0.1);
		}
		
		.item:last-child {
			border-bottom: none;
		}
		
		.item-name {
			color: var(--text-light);
			font-weight: 500;
		}
		
		.item-qty {
			background: rgba(192, 160, 98, 0.2);
			color: var(--accent-gold);
			padding: 4px 8px;
			border-radius: 12px;
			font-size: 0.8rem;
			font-weight: 600;
		}
		
		.order-actions {
			display: flex;
			gap: 10px;
			margin-top: 20px;
			flex-wrap: wrap;
		}
		
		.action-btn {
			border: none;
			padding: 10px 16px;
			border-radius: 8px;
			cursor: pointer;
			font-weight: 600;
			font-size: 0.9rem;
			transition: all var(--transition-speed) ease;
			display: flex;
			align-items: center;
			gap: 6px;
			flex: 1;
			justify-content: center;
			min-width: 100px;
		}
		
		.action-btn:hover:not(:disabled) {
			transform: translateY(-2px);
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
		}
		
		.action-btn:disabled {
			opacity: 0.5;
			cursor: not-allowed;
		}
		
		.btn-preparing {
			background: linear-gradient(135deg, var(--accent-blue), rgba(58, 86, 115, 0.8));
			color: white;
		}
		
		.btn-ready {
			background: linear-gradient(135deg, var(--accent-green), rgba(74, 107, 87, 0.8));
			color: white;
		}
		
		.btn-complete {
			background: linear-gradient(135deg, var(--accent-gold), rgba(192, 160, 98, 0.8));
			color: var(--text-dark);
		}
		
		.footer {
			padding: 15px 30px;
			background: rgba(23, 23, 42, 0.95);
			border-top: 1px solid rgba(192, 160, 98, 0.2);
			display: flex;
			justify-content: space-between;
			align-items: center;
			font-size: 0.9rem;
			color: var(--accent-gold);
			backdrop-filter: blur(10px);
		}
		
		.footer-info {
			display: flex;
			gap: 20px;
			align-items: center;
		}
		
		.no-orders {
			grid-column: 1 / -1;
			text-align: center;
			padding: 60px 20px;
			color: var(--text-light);
			opacity: 0.6;
		}
		
		.no-orders i {
			font-size: 3rem;
			margin-bottom: 20px;
			color: var(--accent-gold);
		}
		
		.no-orders h3 {
			font-size: 1.5rem;
			margin-bottom: 10px;
		}
		
		.loading {
			display: flex;
			justify-content: center;
			align-items: center;
			padding: 40px;
			color: var(--accent-gold);
		}
		
		.loading i {
			animation: spin 1s linear infinite;
			margin-right: 10px;
		}
		
		@keyframes spin {
			from { transform: rotate(0deg); }
			to { transform: rotate(360deg); }
		}
		
		.priority-high {
			animation: glow 2s ease-in-out infinite alternate;
		}
		
		@keyframes glow {
			from { box-shadow: 0 0 5px rgba(255, 159, 67, 0.5); }
			to { box-shadow: 0 0 20px rgba(255, 159, 67, 0.8); }
		}
		
		/* Responsive Design */
		@media (max-width: 768px) {
			.header {
				padding: 15px 20px;
				flex-direction: column;
				gap: 15px;
			}
			
			.header h1 {
				font-size: 1.5rem;
			}
			
			.stats-bar {
				padding: 10px 20px;
			}
			
			.board {
				padding: 20px;
				grid-template-columns: 1fr;
			}
			
			.order-card {
				padding: 15px;
			}
			
			.order-actions {
				flex-direction: column;
			}
			
			.action-btn {
				flex: none;
			}
			
			.footer {
				flex-direction: column;
				gap: 10px;
				text-align: center;
			}
		}
		
		@media (max-width: 480px) {
			.header h1 {
				font-size: 1.3rem;
			}
			
			.order-header {
				flex-direction: column;
				gap: 10px;
			}
			
			.order-info h3 {
				font-size: 1.1rem;
			}
		}
	</style>
</head>
<body>
	<div class="header">
		<h1>
			<i class="fas fa-utensils"></i>
			Enhanced Kitchen Display System
		</h1>
		<div class="header-controls">
			<div class="status-indicator">
				<div class="status-dot"></div>
				<span class="refresh-info">Live Updates</span>
			</div>
			<div class="refresh-info">
				<i class="fas fa-sync-alt"></i>
				Auto-refresh every 3 seconds
			</div>
		</div>
	</div>
	
	<div class="stats-bar" id="statsBar">
		<div class="stat-item">
			<div class="stat-number" id="pendingCount">0</div>
			<div class="stat-label">Pending</div>
		</div>
		<div class="stat-item">
			<div class="stat-number" id="preparingCount">0</div>
			<div class="stat-label">Preparing</div>
		</div>
		<div class="stat-item">
			<div class="stat-number" id="readyCount">0</div>
			<div class="stat-label">Ready</div>
		</div>
		<div class="stat-item">
			<div class="stat-number" id="totalCount">0</div>
			<div class="stat-label">Total Orders</div>
		</div>
	</div>
	
	<div class="board" id="ordersBoard">
		<div class="loading">
			<i class="fas fa-spinner"></i>
			Loading orders...
		</div>
	</div>
	
	<div class="footer">
		<div class="footer-info">
			<span><i class="fas fa-database"></i> Connected to Central Database</span>
			<span><i class="fas fa-clock"></i> Last updated: <span id="lastUpdate">--</span></span>
		</div>
		<div>
			<span>Status Flow: Pending → Preparing → Ready → Completed</span>
		</div>
	</div>
	<script>
		let orders = [];
		let lastUpdateTime = null;
		
	async function fetchOrders() {
			try {
		const res = await fetch('api/get_orders.php');
		const data = await res.json();
				
				if (!data.success) {
					console.error('Failed to fetch orders:', data.error);
					return;
				}
				
				orders = data.orders;
				lastUpdateTime = new Date();
				updateDisplay();
				updateStats();
				updateLastUpdateTime();
				
			} catch (error) {
				console.error('Error fetching orders:', error);
				showError('Connection error. Retrying...');
			}
		}
		
		function updateDisplay() {
		const board = document.getElementById('ordersBoard');
			
			if (orders.length === 0) {
				board.innerHTML = `
					<div class="no-orders">
						<i class="fas fa-clipboard-list"></i>
						<h3>No Active Orders</h3>
						<p>All caught up! New orders will appear here automatically.</p>
					</div>
				`;
				return;
			}
			
		board.innerHTML = '';
			
			orders.forEach(order => {
				const card = createOrderCard(order);
				board.appendChild(card);
			});
		}
		
		function createOrderCard(order) {
			const card = document.createElement('div');
			card.className = `order-card ${order.status.toLowerCase()}`;
			
			// Add priority class for old pending orders
			const orderTime = new Date(order.created_at);
			const now = new Date();
			const timeDiff = (now - orderTime) / (1000 * 60); // minutes
			
			if (order.status === 'Pending' && timeDiff > 10) {
				card.classList.add('priority-high');
			}
			
			const statusIcon = getStatusIcon(order.status);
			const orderTypeIcon = order.order_type === 'takeout' ? 'shopping-bag' : 'utensils';
			
			card.innerHTML = `
				<div class="order-header">
					<div class="order-info">
						<h3>${order.order_code}</h3>
						<div class="order-meta">
							<div><i class="fas fa-user"></i> ${order.customer_name}</div>
							<div class="order-type ${order.order_type}">
								<i class="fas fa-${orderTypeIcon}"></i>
								${order.order_type.charAt(0).toUpperCase() + order.order_type.slice(1)}
							</div>
						</div>
						<div class="order-time">
							<i class="fas fa-clock"></i>
							${formatTime(order.created_at)}
						</div>
					</div>
					<div class="status-badge ${order.status.toLowerCase()}">
						<i class="fas fa-${statusIcon}"></i>
						${order.status}
					</div>
				</div>
				
				<ul class="items-list">
					${order.items.map(item => `
						<li class="item">
							<span class="item-name">${item.prod_name}</span>
							<span class="item-qty">x${item.prod_qty}</span>
						</li>
					`).join('')}
				</ul>
				
				<div class="order-actions">
					${getActionButtons(order)}
				</div>
			`;
			
			return card;
		}
		
		function getStatusIcon(status) {
			switch(status) {
				case 'Pending': return 'clock';
				case 'Preparing': return 'fire';
				case 'Ready': return 'check-circle';
				default: return 'question';
			}
		}
		
		function getActionButtons(order) {
			const buttons = [];
			
			// Only allow starting preparation when order is Paid
			if (order.status === 'Paid') {
				buttons.push(`
					<button class="action-btn btn-preparing" onclick="updateStatus('${order.order_code}', 'Preparing')">
						<i class="fas fa-fire"></i>
						Start Preparing
					</button>
				`);
			} else if (order.status === 'Pending') {
				// Show disabled button for Pending orders
				buttons.push(`
					<button class="action-btn btn-preparing" disabled title="Awaiting payment/confirmation">
						<i class="fas fa-fire"></i>
						Start Preparing
					</button>
				`);
			}
			
			if (order.status === 'Preparing') {
				buttons.push(`
					<button class="action-btn btn-ready" onclick="updateStatus('${order.order_code}', 'Ready')">
						<i class="fas fa-check-circle"></i>
						Mark Ready
					</button>
				`);
			}
			
			if (order.status === 'Ready') {
				buttons.push(`
					<button class="action-btn btn-complete" onclick="updateStatus('${order.order_code}', 'Completed')">
						<i class="fas fa-check-double"></i>
						Complete Order
					</button>
				`);
			}
			
			return buttons.join('');
		}
		
		function updateStats() {
			const stats = {
				pending: 0,
				preparing: 0,
				ready: 0,
				total: orders.length
			};
			
			orders.forEach(order => {
				switch(order.status) {
					case 'Pending':
					case 'Paid':
						stats.pending++;
						break;
					case 'Preparing':
						stats.preparing++;
						break;
					case 'Ready':
						stats.ready++;
						break;
				}
			});
			
			document.getElementById('pendingCount').textContent = stats.pending;
			document.getElementById('preparingCount').textContent = stats.preparing;
			document.getElementById('readyCount').textContent = stats.ready;
			document.getElementById('totalCount').textContent = stats.total;
		}
		
		function updateLastUpdateTime() {
			if (lastUpdateTime) {
				const timeString = lastUpdateTime.toLocaleTimeString();
				document.getElementById('lastUpdate').textContent = timeString;
			}
		}
		
		function formatTime(dateString) {
			const date = new Date(dateString);
			const now = new Date();
			const diffMs = now - date;
			const diffMins = Math.floor(diffMs / (1000 * 60));
			
			if (diffMins < 1) return 'Just now';
			if (diffMins < 60) return `${diffMins}m ago`;
			
			const diffHours = Math.floor(diffMins / 60);
			if (diffHours < 24) return `${diffHours}h ago`;
			
			return date.toLocaleDateString();
		}
		
	async function updateStatus(orderCode, status) {
			try {
				const response = await fetch('api/update_order_status.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify({
						order_code: orderCode,
						status: status
					})
				});
				
				const result = await response.json();
				
				if (result.success) {
					// Show success feedback
					showSuccess(`Order ${orderCode} updated to ${status}`);
					// Refresh orders immediately
					await fetchOrders();
				} else {
					showError(`Failed to update order: ${result.error}`);
				}
				
			} catch (error) {
				console.error('Error updating status:', error);
				showError('Failed to update order status');
			}
		}
		
		function showSuccess(message) {
			// Create temporary success notification
			const notification = document.createElement('div');
			notification.style.cssText = `
				position: fixed;
				top: 20px;
				right: 20px;
				background: var(--accent-green);
				color: white;
				padding: 15px 20px;
				border-radius: 8px;
				z-index: 1000;
				font-weight: 600;
				box-shadow: 0 4px 12px rgba(0,0,0,0.3);
			`;
			notification.textContent = message;
			document.body.appendChild(notification);
			
			setTimeout(() => {
				notification.remove();
			}, 3000);
		}
		
		function showError(message) {
			// Create temporary error notification
			const notification = document.createElement('div');
			notification.style.cssText = `
				position: fixed;
				top: 20px;
				right: 20px;
				background: var(--accent-red);
				color: white;
				padding: 15px 20px;
				border-radius: 8px;
				z-index: 1000;
				font-weight: 600;
				box-shadow: 0 4px 12px rgba(0,0,0,0.3);
			`;
			notification.textContent = message;
			document.body.appendChild(notification);
			
			setTimeout(() => {
				notification.remove();
			}, 5000);
		}
		
		// Initialize and start auto-refresh
		fetchOrders();
		setInterval(fetchOrders, 3000); // Refresh every 3 seconds
		
		// Add keyboard shortcuts
		document.addEventListener('keydown', (e) => {
			if (e.ctrlKey || e.metaKey) {
				switch(e.key) {
					case 'r':
						e.preventDefault();
		fetchOrders();
						break;
				}
	}
		});
	</script>
</body>
</html>
