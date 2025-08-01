<footer class="py-5">
  <div class="container">
    <div class="row align-items-center justify-content-xl-between">
      <div class="col-xl-6">
      </div>
      <div class="col-xl-6">
        <ul class="nav nav-footer justify-content-center justify-content-xl-end">
          <li class="nav-item">
            <a href="" class="nav-link" target="_blank">PST INVENTORY MANAGEMENT SYSTEM</a>
          </li>
          <li class="nav-item">
            <a href="inventory_dashboard.php" class="nav-link">Dashboard</a>
          </li>
          <li class="nav-item">
            <a href="products.php" class="nav-link">Products</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</footer>

<style>
.footer {
  background-color: rgba(26, 26, 46, 0.9);
  border-top: 1px solid rgba(192, 160, 98, 0.2);
}

.nav-footer .nav-link {
  color: var(--text-light);
  transition: all 0.3s ease;
  padding: 0.5rem 1rem;
}

.nav-footer .nav-link:hover {
  color: var(--accent-gold);
  transform: translateY(-2px);
}

@media (max-width: 768px) {
  .nav-footer {
    flex-direction: column;
    align-items: center;
  }
  
  .nav-footer .nav-item {
    margin-bottom: 0.5rem;
  }
}
</style>