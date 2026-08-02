<?php
require_once "auth.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION["user"])) {
    header("Location: " . append_auth_token("index.php"));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Azad Government of the State of Jammu and Kashmir - Inland Revenue Department - HR MIS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="modern.css">
</head>

<body class="login-body">
    <!-- Header -->
    <header class="unified-header">
        <div class="container-fluid">
            <div class="header-content">
                <div class="header-center">
                    <div class="govt-seal">Azad Government of the State of Jammu and Kashmir</div>
                </div>

                <div class="logo-section">
                    <img src="img/ird-Logo.png" alt="IRD Logo" class="org-logo">
                    <div>
                        <h2>Inland Revenue Department, AJ&K</h2>
                        <p>Human Resource Management System (IRD-HRMS)</p>
                    </div>
                </div>
                <div class="social-media">
                    <a href="https://www.facebook.com" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.twitter.com" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.youtube.com" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="https://www.instagram.com" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </header>

    <!-- Login Container -->
    <div class="login-wrapper">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="login-info">
                        <div class="hero-pill"><i class="fas fa-shield-alt"></i> Modern portal access</div>
                        <h1>Welcome to the IRD-HRM Portal</h1>
                        <p>Access a modern, secure workspace for employee administration, reporting, and operational oversight.</p>
                        <ul class="features-list">
                            <li><i class="fas fa-check-circle"></i> Secure authentication</li>
                            <li><i class="fas fa-check-circle"></i> Centralized employee management</li>
                            <li><i class="fas fa-check-circle"></i> Reliable reporting workflows</li>
                            <li><i class="fas fa-check-circle"></i> Privacy-focused access control</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="login-card">
                        <div class="login-header">
                            <h3>User Login</h3>
                            <p>Enter your credentials to continue</p>
                        </div>

                        <?php
                        if (isset($_POST["login"])) {
                            $email = $_POST["email"];
                            $password = $_POST["password"];
                            require_once "database.php";
                            $sql = "SELECT * FROM users WHERE email = '$email'";
                            $result = mysqli_query($conn, $sql);
                            $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
                            if ($user) {
                                if (password_verify($password, $user["password"])) {
                                    if (session_status() === PHP_SESSION_NONE) {
                                        session_start();
                                    }
                                    $_SESSION["user"] = "yes";
                                    $_SESSION["user_email"] = $user["email"];
                                    $_SESSION["user_role_id"] = intval($user["UserRoleId"] ?? 0);
                                    $_SESSION["auth_uuid"] = generate_uuid_v4();
                                    resolve_user_context($conn, $user["email"]);
                                    header("Location: " . append_auth_token("index.php"));
                                    die();
                                } else {
                                    echo "<div class='alert alert-danger alert-dismissible fade show'><i class='fas fa-exclamation-circle'></i> <strong>Error!</strong> Password does not match. <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
                                }
                            } else {
                                echo "<div class='alert alert-danger alert-dismissible fade show'><i class='fas fa-exclamation-circle'></i> <strong>Error!</strong> Email does not exist. <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
                            }
                        }
                        ?>

                        <form action="login.php" method="post">
                            <div class="form-group mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" placeholder="Enter your email" name="email" id="email" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" placeholder="Enter your password" name="password" id="password" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>

                            <button type="submit" name="login" class="btn btn-login w-100 mb-3">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </form>

                        <div class="login-footer">
                            <!--<p>Don't have an account? <a href="registration.php">Register here</a></p>-->
                            <p class="help-text"><i class="fas fa-phone"></i> Need help? Contact support</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-login">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2026 Income Revenue Department, Government of AJK. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#">Privacy Policy</a> | <a href="#">Terms of Use</a> | <a href="#">Contact Us</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
</form>
<!-- <div>
    <p>Not registered yet <a href="registration.php">Register Here</a></p>
</div>
 --></div>
</body>

</html>