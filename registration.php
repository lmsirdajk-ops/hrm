<?php
session_start();
if (isset($_SESSION["user"])) {
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Azad Government of the State of Jammu and Kashmir</title>
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
                <div class="logo-section">
                    <img src="img/ird-Logo.png" alt="IRD Logo" class="org-logo">
                    <div>
                        <h2>Inland Revenue Department, AJ&K</h2>
                        <p>Human Resource Management System (IRD-HRMS)</p>
                    </div>
                </div>
                <div class="header-center">
                    <div class="govt-seal">Azad Government of the State of Jammu and Kashmir</div>
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

    <!-- Registration Container -->
    <div class="login-wrapper">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="login-info">
                        <div class="hero-pill"><i class="fas fa-user-plus"></i> Create your account</div>
                        <h1>Join the IRD-HRM ecosystem</h1>
                        <p>Register with a polished and secure experience designed for staff access and efficient administration.</p>
                        <ul class="features-list">
                            <li><i class="fas fa-check-circle"></i> Guided and secure registration</li>
                            <li><i class="fas fa-check-circle"></i> Straightforward account setup</li>
                            <li><i class="fas fa-check-circle"></i> Government-standard usability</li>
                            <li><i class="fas fa-check-circle"></i> Support-ready experience</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="login-card">
                        <div class="login-header">
                            <h3>New User Registration</h3>
                            <p>Fill in the details to create an account</p>
                        </div>

                        <?php
                        if (isset($_POST["submit"])) {
                            $fullName = $_POST["fullname"];
                            $email = $_POST["email"];
                            $password = $_POST["password"];
                            $passwordRepeat = $_POST["repeat_password"];

                            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                            $errors = array();

                            if (empty($fullName) or empty($email) or empty($password) or empty($passwordRepeat)) {
                                array_push($errors, "All fields are required");
                            }
                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                array_push($errors, "Email is not valid");
                            }
                            if (strlen($password) < 8) {
                                array_push($errors, "Password must be at least 8 characters long");
                            }
                            if ($password !== $passwordRepeat) {
                                array_push($errors, "Password does not match");
                            }
                            require_once "database.php";
                            $sql = "SELECT * FROM users WHERE email = '$email'";
                            $result = mysqli_query($conn, $sql);
                            $rowCount = mysqli_num_rows($result);
                            if ($rowCount > 0) {
                                array_push($errors, "Email already exists!");
                            }
                            if (count($errors) > 0) {
                                foreach ($errors as  $error) {
                                    echo "<div class='alert alert-danger alert-dismissible fade show'><i class='fas fa-exclamation-circle'></i> <strong>Error!</strong> $error <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
                                }
                            } else {

                                $sql = "INSERT INTO users (full_name, email, password) VALUES ( ?, ?, ? )";
                                $stmt = mysqli_stmt_init($conn);
                                $prepareStmt = mysqli_stmt_prepare($stmt, $sql);
                                if ($prepareStmt) {
                                    mysqli_stmt_bind_param($stmt, "sss", $fullName, $email, $passwordHash);
                                    mysqli_stmt_execute($stmt);
                                    echo "<div class='alert alert-success alert-dismissible fade show'><i class='fas fa-check-circle'></i> <strong>Success!</strong> Your account has been created. <a href='login.php'>Login now</a> <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
                                } else {
                                    echo "<div class='alert alert-danger alert-dismissible fade show'><i class='fas fa-exclamation-circle'></i> <strong>Error!</strong> Something went wrong. Please try again. <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
                                }
                            }
                        }
                        ?>

                        <form action="registration.php" method="post">
                            <div class="form-group mb-4">
                                <label for="fullname" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" placeholder="Enter your full name" name="fullname" id="fullname" class="form-control" required>
                                </div>
                            </div>

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
                                    <input type="password" placeholder="Enter password (min 8 characters)" name="password" id="password" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="repeat_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" placeholder="Confirm your password" name="repeat_password" id="repeat_password" class="form-control" required>
                                </div>
                            </div>

                            <button type="submit" name="submit" class="btn btn-login w-100 mb-3">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </form>

                        <div class="login-footer">
                            <p>Already have an account? <a href="login.php">Login here</a></p>
                            <p class="help-text"><i class="fas fa-info-circle"></i> Password must be at least 8 characters</p>
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
                    <p>&copy; 2026 Inland Revenue Department, Government of AJK. All rights reserved.</p>
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