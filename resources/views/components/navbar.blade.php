<header class="navbar">
    <div class="navbar-container">
        <a href="{{ route('orders.create') }}" class="navbar-brand">
            <span class="brand-text">Order & Inventory</span>
        </a>

        <nav class="navbar-menu">
            <a href="{{ route('orders.create') }}" class="nav-link {{ request()->routeIs('orders.create') ? 'active' : '' }}">
                <span class="nav-icon">➕</span>
                <span>Create Order</span>
            </a>
        </nav>
    </div>
</header>
