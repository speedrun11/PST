<!-- Quick Order Modal -->
<div class="modal fade" id="quickOrderModal" tabindex="-1" role="dialog" aria-labelledby="quickOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="background: rgba(26, 26, 46, 0.95); border: 1px solid rgba(192, 160, 98, 0.3);">
            <div class="modal-header" style="border-bottom: 1px solid rgba(192, 160, 98, 0.3);">
                <h5 class="modal-title" id="quickOrderModalLabel" style="color: var(--accent-gold);">
                    <i class="fas fa-bolt"></i> Quick Order
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: var(--text-light);">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Search Bar -->
                <div class="form-group">
                    <input type="text" id="quickSearch" class="form-control" placeholder="Search products..." 
                           style="background: rgba(26, 26, 46, 0.8); border: 1px solid rgba(192, 160, 98, 0.3); color: var(--text-light);">
                </div>
                
                <!-- Quick Order Items -->
                <div id="quickOrderItems" style="max-height: 400px; overflow-y: auto;">
                    <!-- Items will be populated by JavaScript -->
                </div>
                
                <!-- Quick Order Summary -->
                <div class="quick-order-summary" style="background: rgba(192, 160, 98, 0.1); padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <div class="row">
                        <div class="col-6">
                            <strong style="color: var(--accent-gold);">Total Items:</strong>
                            <span id="quickItemCount">0</span>
                        </div>
                        <div class="col-6 text-right">
                            <strong style="color: var(--accent-gold);">Total Amount:</strong>
                            <span id="quickTotalAmount">₱ 0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(192, 160, 98, 0.3);">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="quickOrderBtn" disabled>
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.quick-order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border: 1px solid rgba(192, 160, 98, 0.2);
    border-radius: 5px;
    margin-bottom: 10px;
    background: rgba(26, 26, 46, 0.6);
    transition: all 0.3s ease;
}

.quick-order-item:hover {
    border-color: var(--accent-gold);
    background: rgba(26, 26, 46, 0.8);
}

.quick-item-info {
    flex: 1;
}

.quick-item-name {
    color: var(--accent-gold);
    font-weight: 600;
    margin-bottom: 5px;
}

.quick-item-price {
    color: var(--text-light);
    font-size: 0.9em;
}

.quick-item-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.quick-qty-btn {
    background: var(--accent-gold);
    border: none;
    color: var(--text-dark);
    width: 25px;
    height: 25px;
    border-radius: 50%;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.quick-qty-btn:hover {
    background: var(--accent-red);
    transform: scale(1.1);
}

.quick-qty-input {
    width: 40px;
    text-align: center;
    background: rgba(26, 26, 46, 0.8);
    border: 1px solid rgba(192, 160, 98, 0.3);
    border-radius: 3px;
    color: var(--text-light);
    padding: 2px;
}

.quick-add-btn {
    background: var(--accent-green);
    border: none;
    color: var(--text-light);
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.quick-add-btn:hover {
    background: var(--accent-blue);
    transform: translateY(-1px);
}
</style>

<script>
let quickOrderItems = [];

// Load products for quick order
function loadQuickOrderProducts() {
    fetch('search_products.php?q=')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayQuickOrderProducts(data.products);
            }
        })
        .catch(error => console.error('Error loading products:', error));
}

function displayQuickOrderProducts(products) {
    const container = document.getElementById('quickOrderItems');
    container.innerHTML = '';
    
    products.forEach(product => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'quick-order-item';
        itemDiv.innerHTML = `
            <div class="quick-item-info">
                <div class="quick-item-name">${product.name}</div>
                <div class="quick-item-price">₱ ${parseFloat(product.price).toFixed(2)}</div>
            </div>
            <div class="quick-item-controls">
                <button class="quick-qty-btn" onclick="updateQuickQty('${product.id}', -1)">-</button>
                <input type="number" class="quick-qty-input" id="qty_${product.id}" value="1" min="1">
                <button class="quick-qty-btn" onclick="updateQuickQty('${product.id}', 1)">+</button>
                <button class="quick-add-btn" onclick="addQuickItem('${product.id}', '${product.name}', ${product.price})">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        `;
        container.appendChild(itemDiv);
    });
}

function updateQuickQty(productId, change) {
    const input = document.getElementById(`qty_${productId}`);
    let qty = parseInt(input.value) + change;
    if (qty < 1) qty = 1;
    input.value = qty;
}

function addQuickItem(productId, productName, productPrice) {
    const qty = parseInt(document.getElementById(`qty_${productId}`).value);
    
    const existingItem = quickOrderItems.find(item => item.id === productId);
    if (existingItem) {
        existingItem.quantity += qty;
    } else {
        quickOrderItems.push({
            id: productId,
            name: productName,
            price: productPrice,
            quantity: qty
        });
    }
    
    updateQuickOrderSummary();
}

function updateQuickOrderSummary() {
    const itemCount = quickOrderItems.reduce((sum, item) => sum + item.quantity, 0);
    const totalAmount = quickOrderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    document.getElementById('quickItemCount').textContent = itemCount;
    document.getElementById('quickTotalAmount').textContent = `₱ ${totalAmount.toFixed(2)}`;
    
    const quickOrderBtn = document.getElementById('quickOrderBtn');
    quickOrderBtn.disabled = quickOrderItems.length === 0;
}

// Add to main cart
document.getElementById('quickOrderBtn').addEventListener('click', function() {
    quickOrderItems.forEach(item => {
        addToCart(item.id, item.name, item.price, 'default.jpg');
    });
    
    // Clear quick order
    quickOrderItems = [];
    updateQuickOrderSummary();
    
    // Close modal
    $('#quickOrderModal').modal('hide');
    
    // Show success message
    showNotification('Items added to cart successfully!', 'success');
});

// Search functionality
document.getElementById('quickSearch').addEventListener('input', function() {
    const searchTerm = this.value;
    fetch(`search_products.php?q=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayQuickOrderProducts(data.products);
            }
        })
        .catch(error => console.error('Error searching products:', error));
});

// Load products when modal is shown
$('#quickOrderModal').on('show.bs.modal', function() {
    loadQuickOrderProducts();
    quickOrderItems = [];
    updateQuickOrderSummary();
});

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        background: ${type === 'success' ? 'rgba(74, 107, 87, 0.9)' : 'rgba(158, 43, 43, 0.9)'};
        color: var(--text-light);
        padding: 15px 20px;
        border-radius: 5px;
        border: 1px solid ${type === 'success' ? 'rgba(74, 107, 87, 0.4)' : 'rgba(158, 43, 43, 0.4)'};
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    `;
    notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}`;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
