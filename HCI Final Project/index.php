<?php
session_start();
// If user is already logged in, redirect to appropriate page
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'Administrator':
            header('Location: adminHome.php');
            break;
        case 'Resident':
            header('Location: residentHome.php');
            break;
        case 'Security Guard':
            header('Location: sgHome.php');
            break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gated Community Portal - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
</head>

<body>
    <!-- make me an image tag -->


    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <!-- Logo and Title -->
                <div class="text-center mb-4">
                    <i class="fas fa-building logo-icon mb-3"></i>
                    <h1 class="h3 fw-bold text-primary">Gated Community Portal</h1>
                    <p class="text-secondary">Welcome back! Please sign in to continue.</p>
                </div>

                <!-- Login Card -->
                <div class="login-card p-4 p-md-5">
                    <h2 class="h4 mb-1 fw-bold">Sign In</h2>
                    <?php if (isset($_SESSION['login_error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php
                            echo htmlspecialchars($_SESSION['login_error']);
                            unset($_SESSION['login_error']);
                            ?>
                        </div>
                    <?php endif; ?>
                    <p class="text-secondary mb-4">Enter your credentials to access your account</p>

                    <form id="loginForm" method="POST" action="process_login.php">
                        <!-- Username Input -->
                        <div class="mb-4">
                            <label class="form-label fw-medium">Username</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" name="username" class="form-control border-start-0"
                                    placeholder="Enter your username" required>
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="mb-4">
                            <label class="form-label fw-medium">Password</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" name="password" class="form-control border-start-0"
                                    placeholder="••••••••" required>
                            </div>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                                <label class="form-check-label text-secondary" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="forgot_password.php" class="text-decoration-none">Forgot password?</a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 mb-4">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                            Sign In
                        </button>

                        <!-- Register Link -->
                        <p class="text-center text-secondary mb-0">
                            Don't have an account?
                            <a href="#" class="text-decoration-none fw-medium">Contact your administrator</a>
                        </p>
                    </form>
                </div>

                <!-- Footer -->
                <p class="text-center text-secondary small mt-4">
                    © 2024 Gated Community Portal. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission handling with loading state
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const button = this.querySelector('button[type="submit"]');
            const spinner = button.querySelector('.spinner-border');
            button.disabled = true;
            spinner.classList.remove('d-none');
        });

        // Input group focus effect
        document.querySelectorAll('.input-group .form-control').forEach(input => {
            input.addEventListener('focus', () => {
                input.previousElementSibling.style.borderColor = '#2563eb';
            });
            input.addEventListener('blur', () => {
                input.previousElementSibling.style.borderColor = '#e2e8f0';
            });
        });
    </script>
</body>

</html>