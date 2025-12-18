<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <i class="fas fa-bars me-3 text-muted" style="cursor: pointer;"></i>
            <span class="navbar-brand mb-0 fw-bold" style="color: #2d8659;" id="page-title">@yield('page-title', 'Dashboard')</span>
        </div>
        
        <div class="d-flex align-items-center">
            <div class="me-3 text-end">
                <div class="fw-semibold" style="color: #2d8659;" id="user-display-name"></div>
                <small class="text-muted" id="user-email"></small>
            </div>
            
            <!-- Form logout dengan method POST -->
            <form action="{{ route('logout') }}" method="POST" id="logout-form" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const user = getUser();
        if (user) {
            document.getElementById('user-display-name').textContent = user.name;
            document.getElementById('user-email').textContent = user.email;
        }
    });

    // Optional: Fungsi logout alternatif jika masih menggunakan onclick
    function logout() {
        // Clear localStorage/sessionStorage
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        sessionStorage.clear();
        
        // Submit form logout
        document.getElementById('logout-form').submit();
    }
</script>