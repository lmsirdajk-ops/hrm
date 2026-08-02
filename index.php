<?php
require_once "auth.php";
ensure_authenticated_session();
require_url_authorization();
include "database.php";

// Fetch employee statistics
$totalEmployees = mysqli_query($conn, "SELECT COUNT(*) as count FROM employees");
$totalEmpRow = mysqli_fetch_assoc($totalEmployees);
$totalCount = $totalEmpRow['count'];

$activeEmployees = mysqli_query($conn, "SELECT COUNT(*) as count FROM employees WHERE eServiceNature = 1");
$activeEmpRow = mysqli_fetch_assoc($activeEmployees);
$activeCount = $activeEmpRow['count'];

$maleEmployees = mysqli_query($conn, "SELECT COUNT(*) as count FROM employees WHERE Gender = 'M'");
$maleRow = mysqli_fetch_assoc($maleEmployees);
$maleCount = $maleRow['count'];

$femaleEmployees = mysqli_query($conn, "SELECT COUNT(*) as count FROM employees WHERE Gender = 'F'");
$femaleRow = mysqli_fetch_assoc($femaleEmployees);
$femaleCount = $femaleRow['count'];

$inactiveEmployees = mysqli_query($conn, "SELECT COUNT(*) as count FROM employees WHERE eRetired = 2");
$inactiveRow = mysqli_fetch_assoc($inactiveEmployees);
$inactiveCount = $inactiveRow['count'];

// Fetch recent employees for display
$recentEmployees = mysqli_query($conn, "SELECT eid, eName, EmailAdd, ePhonCell, eDesigBMS, eServiceNature, eDesigID FROM employees ORDER BY eid DESC LIMIT 10");
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

<body class="dashboard-page">
    <!-- Header -->
    <header class="unified-header">
        <div class="container-fluid">
            <div class="header-content">
                <div class="logo-section">
                    <img src="img/ird-Logo.png" alt="IRD Logo" class="org-logo">
                    <div>
                        <h1>Inland Revenue Department, AJ&K</h1>
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
                <div class="user-menu">
                    <div class="dropdown">
                        <button class="btn btn-user dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> Profile
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="navbar navbar-expand-lg navbar-dark">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="<?php echo append_auth_token('index.php'); ?>"><i class="fas fa-home"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="fas fa-users"></i> Employees</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo append_auth_token('report.php'); ?>"><i class="fas fa-file-alt"></i> Reports</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="fas fa-bell"></i> Notifications</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="container-fluid">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <div class="hero-content">
                    <div>
                        <div class="hero-pill"><i class="fas fa-shield-alt"></i> Secure • Smart • Government-ready</div>
                        <h1>Welcome to the next-generation IRD-HRM Portal</h1>
                        <p>Manage employee records, monitor staffing, and access reporting tools through a faster, more intuitive experience designed for modern public-sector operations.</p>
                        <div class="hero-actions">
                            <a href="<?php echo append_auth_token('employee_list.php?type=all'); ?>" class="btn btn-light"><i class="fas fa-users"></i> View Employees</a>
                            <a href="<?php echo append_auth_token('report.php'); ?>" class="btn btn-outline-light"><i class="fas fa-chart-line"></i> Open Reports</a>
                            <a href="<?php echo append_auth_token('admin_interface.php'); ?>" class="btn btn-outline-light"><i class="fas fa-user-shield"></i> Admin Panel</a>
                        </div>
                    </div>
                    <div class="hero-side">
                        <div class="hero-metric">
                            <strong><?php echo $totalCount; ?></strong>
                            <span>Registered employees</span>
                        </div>
                        <div class="hero-metric">
                            <strong><?php echo $activeCount; ?></strong>
                            <span>Active workforce</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row g-4 mb-5">
                <div class="col-md-3 col-sm-6">
                    <a href="<?php echo append_auth_token('employee_list.php?type=all'); ?>" class="stat-card-link">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $totalCount; ?></h3>
                                <p>Total Employees</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="<?php echo append_auth_token('employee_list.php?type=active'); ?>" class="stat-card-link">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $activeCount; ?></h3>
                                <p>Active Employees</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="<?php echo append_auth_token('employee_list.php?type=male'); ?>" class="stat-card-link">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-male"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $maleCount; ?></h3>
                                <p>Male Employees</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="<?php echo append_auth_token('employee_list.php?type=female'); ?>" class="stat-card-link">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-female"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $femaleCount; ?></h3>
                                <p>Female Employees</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="<?php echo append_auth_token('employee_list.php?type=inactive'); ?>" class="stat-card-link">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-ban"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $inactiveCount; ?></h3>
                                <p>Inactive Employees</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Main Content Sections -->
            <div class="row g-4">
                <!-- Quick Access -->
                <div class="col-lg-6">
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-lightning-bolt"></i> Quick Access</h3>
                        </div>
                        <div class="card-body">
                            <div class="quick-links">
                                <a href="#" class="quick-link">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Add New Employee</span>
                                </a>
                                <a href="<?php echo append_auth_token('report.php'); ?>" class="quick-link">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>Download Reports</span>
                                </a>
                                <a href="#" class="quick-link">
                                    <i class="fas fa-envelope"></i>
                                    <span>Send Notifications</span>
                                </a>
                                <a href="#" class="quick-link">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>View Analytics</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="col-lg-6">
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-history"></i> Recent Activities</h3>
                        </div>
                        <div class="card-body">
                            <div class="activity-list">
                                <div class="activity-item">
                                    <i class="fas fa-user-check"></i>
                                    <div class="activity-text">
                                        <p>Employee registered</p>
                                        <small>2 hours ago</small>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <i class="fas fa-file-upload"></i>
                                    <div class="activity-text">
                                        <p>Report submitted</p>
                                        <small>5 hours ago</small>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <i class="fas fa-user-edit"></i>
                                    <div class="activity-text">
                                        <p>Employee profile updated</p>
                                        <small>1 day ago</small>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <i class="fas fa-calendar-check"></i>
                                    <div class="activity-text">
                                        <p>Leave approved</p>
                                        <small>2 days ago</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Section -->
            <div class="row g-4 mt-4">
                <div class="col-lg-12">
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-tools"></i> Available Services</h3>
                        </div>
                        <div class="card-body">
                            <div class="services-grid">
                                <div class="service-box">
                                    <i class="fas fa-id-card"></i>
                                    <h5>Employee Management</h5>
                                    <p>Manage employee records and details</p>
                                </div>
                                <div class="service-box">
                                    <i class="fas fa-calendar-alt"></i>
                                    <h5>Leave Management</h5>
                                    <p>Handle leave requests and approvals</p>
                                </div>
                                <div class="service-box">
                                    <i class="fas fa-chart-line"></i>
                                    <h5>Performance Reports</h5>
                                    <p>Generate performance analytics</p>
                                </div>
                                <div class="service-box">
                                    <i class="fas fa-graduation-cap"></i>
                                    <h5>Training Programs</h5>
                                    <p>Manage staff training initiatives</p>
                                </div>
                                <div class="service-box">
                                    <i class="fas fa-money-bill"></i>
                                    <h5>Payroll Management</h5>
                                    <p>Process salaries and benefits</p>
                                </div>
                                <div class="service-box">
                                    <i class="fas fa-clipboard-list"></i>
                                    <h5>Compliance</h5>
                                    <p>Ensure regulatory compliance</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Real-time Employee Records -->
            <div class="row g-4 mt-4">
                <div class="col-lg-12">
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-database"></i> Employee Records (Real-time)</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Designation</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($recentEmployees && mysqli_num_rows($recentEmployees) > 0) {
                                            while ($row = mysqli_fetch_assoc($recentEmployees)) {
                                                $status = ($row['eServiceNature'] == 1) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                                                echo "<tr>";
                                                echo "<td><strong>" . htmlspecialchars($row['eid']) . "</strong></td>";
                                                echo "<td>" . htmlspecialchars($row['eName']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['EmailAdd']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['ePhonCell']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['eDesigBMS']) . "</td>";
                                                echo "<td>" . $status . "</td>";
                                                echo "<td><a href='#' class='btn btn-sm btn-primary'><i class='fas fa-eye'></i> View</a></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='7' class='text-center text-muted'>No employees found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container-fluid">
            <div class="footer-content">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <h5>About IRD-HRM</h5>
                        <p>A comprehensive Human Resource Management System for the Income Revenue Department.</p>
                    </div>
                    <div class="col-md-4 mb-4">
                        <h5>Quick Links</h5>
                        <ul>
                            <li><a href="#">Home</a></li>
                            <li><a href="#">Contact Us</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms of Use</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4 mb-4">
                        <h5>Contact Information</h5>
                        <p><i class="fas fa-phone"></i> +92-5822-90252</p>
                        <p><i class="fas fa-envelope"></i> support@ird.gov.pk</p>
                        <p><i class="fas fa-map-marker-alt"></i> Muzaffarabad, Azad Jammu and Kashmir</p>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2026 Income Revenue Department, Government of AJK. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>