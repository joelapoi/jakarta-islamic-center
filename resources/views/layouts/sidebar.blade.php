<nav class="col-md-2 d-md-block sidebar">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <img src="{{ asset('image/logo.png') }}" alt="Logo Islamic Center HRM" class="img-fluid" style="max-height: 50px;">
            <small class="text-white-50 d-block mt-2">{{ auth()->user()->name ?? '' }}</small>
        </div>
        
        @php
            // Ambil roles dari session yang disimpan saat login
            $userRoles = Session::get('user_roles', []);
            // Cek apakah admin (super_admin atau admin)
            $isAdmin = in_array('super_admin', $userRoles) || in_array('admin', $userRoles);
            // Cek apakah staff
            $isStaff = in_array('staff', $userRoles);
        @endphp
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            
            {{-- Menu Users - Hanya untuk Super Admin dan Admin --}}
            @if($isAdmin)
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
            @endif
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('anggaran-kegiatan.*') ? 'active' : '' }}" href="{{ route('anggaran-kegiatan.index') }}">
                    <i class="fas fa-money-bill"></i> Anggaran Kegiatan
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('pencairan-dana.*') ? 'active' : '' }}" href="{{ route('pencairan-dana.index') }}">
                    <i class="fas fa-hand-holding-usd"></i> Pencairan Dana
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rekap-pengajuan.*') ? 'active' : '' }}" href="{{ route('rekap-pengajuan.index') }}">
                    <i class="fas fa-file-invoice"></i> Rekap Pengajuan
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('buku-cek.*') ? 'active' : '' }}" href="{{ route('buku-cek.index') }}">
                    <i class="fas fa-check-square"></i> Buku Cek
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('lpj-kegiatan.*') ? 'active' : '' }}" href="{{ route('lpj-kegiatan.index') }}">
                    <i class="fas fa-clipboard-check"></i> LPJ Kegiatan
                </a>
            </li>
        </ul>
    </div>
</nav>