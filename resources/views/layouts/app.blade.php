<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Islamic Center HRM')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-green: #2d8659;
            --primary-green-dark: #1f5d3d;
            --primary-green-light: #48a578;
            --secondary-green: #e8f5e9;
            --accent-gold: #d4af37;
            --text-dark: #1a1a1a;
            --sidebar-bg: linear-gradient(180deg, #2d8659 0%, #1f5d3d 100%);
        }
        
        body {
            background-color: #f8faf9;
            color: var(--text-dark);
        }
        
        .sidebar {
            min-height: 100vh;
            background: var(--sidebar-bg);
            box-shadow: 2px 0 10px rgba(45, 134, 89, 0.1);
        }
        
        .sidebar .nav-link {
            color: #ffffff;
            padding: 12px 20px;
            margin: 4px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: var(--accent-gold);
            color: var(--text-dark);
            font-weight: 600;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .navbar {
            background: #ffffff !important;
            border-bottom: 2px solid var(--secondary-green);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .btn-primary {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-green-dark);
            border-color: var(--primary-green-dark);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .badge-primary {
            background-color: var(--primary-green);
        }
        
        .text-primary {
            color: var(--primary-green) !important;
        }
        
        .bg-primary {
            background-color: var(--primary-green) !important;
        }
        
        .alert-success {
            background-color: var(--secondary-green);
            border-color: var(--primary-green-light);
            color: var(--primary-green-dark);
        }
        
        #loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(45, 134, 89, 0.15);
            backdrop-filter: blur(4px);
            z-index: 9999;
        }
        
        #loading .spinner-border {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--primary-green);
            width: 3rem;
            height: 3rem;
        }
        
        .table thead {
            background-color: var(--secondary-green);
            color: var(--primary-green-dark);
        }
        
        .page-link {
            color: var(--primary-green);
        }
        
        .page-link:hover {
            color: var(--primary-green-dark);
            background-color: var(--secondary-green);
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Loading Spinner -->
    <div id="loading">
        <div class="spinner-border text-light" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            @include('layouts.sidebar')
            
            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <!-- Navbar -->
                @include('layouts.navbar')
                
                <!-- Flash Messages -->
                <div id="flash-messages"></div>
                
                <!-- Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Helper Functions (hanya untuk backward compatibility jika masih ada yang pakai) -->
    <script>
        // API Base URL
        const API_BASE_URL = '{{ url('/api') }}';
        
        // Get JWT Token from localStorage
        function getToken() {
            return localStorage.getItem('jwt_token') || localStorage.getItem('token');
        }
        
        // Set JWT Token
        function setToken(token) {
            localStorage.setItem('jwt_token', token);
            localStorage.setItem('token', token);
        }
        
        // Remove JWT Token
        function removeToken() {
            localStorage.removeItem('jwt_token');
            localStorage.removeItem('token');
        }
        
        // Get User from localStorage (backward compatibility only)
        function getUser() {
            const user = localStorage.getItem('user');
            return user ? JSON.parse(user) : null;
        }
        
        // Set User
        function setUser(user) {
            localStorage.setItem('user', JSON.stringify(user));
        }
        
        // Remove User
        function removeUser() {
            localStorage.removeItem('user');
        }
        
        // Check if authenticated
        function isAuthenticated() {
            return getToken() !== null;
        }
        
        // Show loading
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }
        
        // Hide loading
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }
        
        // Show flash message
        function showFlash(message, type = 'success') {
            const container = document.getElementById('flash-messages');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;
            container.appendChild(alert);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
        
        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                document.getElementById('logout-form').submit();
            }
        }
    </script>
    
    @stack('scripts')
</body>
</html>