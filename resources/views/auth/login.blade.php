<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Islamic Center HRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2d8659 0%, #48a578 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 420px;
            width: 100%;
        }
        .login-card .card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        .login-card h3 {
            color: #2d8659;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2d8659 0%, #48a578 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1f5d3d 0%, #2d8659 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 134, 89, 0.3);
        }
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            border-color: #2d8659;
            box-shadow: 0 0 0 0.2rem rgba(45, 134, 89, 0.25);
        }
        .form-check-input:checked {
            background-color: #2d8659;
            border-color: #2d8659;
        }
        .islamic-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            background-image: 
                repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255,255,255,.1) 35px, rgba(255,255,255,.1) 70px),
                repeating-linear-gradient(-45deg, transparent, transparent 35px, rgba(255,255,255,.1) 35px, rgba(255,255,255,.1) 70px);
            pointer-events: none;
        }
        .logo-container {
            background: white;
            padding: 15px;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Islamic Pattern Background -->
    <div class="islamic-pattern"></div>
    
    <div class="login-card">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <!-- Header -->
                <div class="text-center mb-4">
                    <div class="mb-3">
                        @if(file_exists(public_path('image/logo.png')))
                            <div class="logo-container">
                                <img src="{{ asset('image/logo.png') }}" 
                                    alt="Logo Islamic Center HRM" 
                                    class="img-fluid" 
                                    style="height: 50px; width: auto;">
                            </div>
                        @else
                            <i class="fas fa-mosque fa-3x" style="color: #2d8659;"></i>
                        @endif
                    </div>
                    <h4 class="fw-bold text-success">Islamic Center HRM</h4>
                    <p class="text-muted mb-0">Assalamualaikum</p>
                    <small class="text-muted">Sign in to continue</small>
                </div>

                <!-- Success Message -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Error Message -->
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    
                    <!-- Email Field -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0" style="border-radius: 8px 0 0 8px;">
                                <i class="fas fa-envelope text-muted"></i>
                            </span>
                            <input type="email" 
                                   name="email" 
                                   id="email"
                                   class="form-control border-start-0 @error('email') is-invalid @enderror" 
                                   placeholder="Enter your email"
                                   value="{{ old('email') }}"
                                   required 
                                   autofocus
                                   style="border-radius: 0 8px 8px 0;">
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0" style="border-radius: 8px 0 0 8px;">
                                <i class="fas fa-lock text-muted"></i>
                            </span>
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   class="form-control border-start-0 @error('password') is-invalid @enderror" 
                                   placeholder="Enter your password"
                                   required
                                   style="border-radius: 0 8px 8px 0;">
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-4 form-check">
                        <input type="checkbox" 
                               name="remember" 
                               id="remember" 
                               class="form-check-input">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>

                <!-- Divider -->
                <hr class="my-4">

                <!-- Additional Info -->
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Secure login with Laravel
                    </small>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="text-center mt-3">
            <small class="text-white">
                &copy; {{ date('Y') }} Islamic Center HRM. All rights reserved.
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto dismiss alerts after 5 seconds -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            // Focus on email if error on password
            @if($errors->has('password') && !$errors->has('email'))
                document.getElementById('password').focus();
            @endif
        });
    </script>
</body>
</html>