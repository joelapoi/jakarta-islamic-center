<nav class="col-md-2 d-md-block sidebar">
    <div class="position-sticky pt-3">
        <!-- <div class="text-center mb-4">
            <h5 class="text-white">Islamic Center HRM</h5>
            <small class="text-white-50" id="user-name"></small>
        </div> -->
        <div class="text-center mb-4">
            <img src="{{ asset('image/logo.png') }}" alt="Logo Islamic Center HRM" class="img-fluid" style="max-height: 50px;">

            <small class="text-white-50" id="user-name"></small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item" id="menu-users">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="{{ route('anggaran-kegiatan.index') }}">
                    <i class="fas fa-money-bill"></i> Anggaran Kegiatan
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="{{ route('pencairan-dana.index') }}">
                    <i class="fas fa-hand-holding-usd"></i> Pencairan Dana
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="{{ route('rekap-pengajuan.index') }}">
                    <i class="fas fa-file-invoice"></i> Rekap Pengajuan
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="{{ route('buku-cek.index') }}">
                    <i class="fas fa-check-square"></i> Buku Cek
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="{{ route('lpj-kegiatan.index') }}">
                    <i class="fas fa-clipboard-check"></i> LPJ Kegiatan
                </a>
            </li>
        </ul>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const user = getUser();
        if (user) {
            document.getElementById('user-name').textContent = user.name;
            
            // Hide users menu if not admin
            const roles = user.roles || [];
            if (!roles.includes('super_admin') && !roles.includes('admin')) {
                const usersMenu = document.getElementById('menu-users');
                if (usersMenu) usersMenu.style.display = 'none';
            }
        }
        
        // Set active menu
        const currentPath = window.location.pathname;
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
    });
</script>