<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
}
include "database.php";

// Get filter type from query parameter
$filterType = isset($_GET['type']) ? $_GET['type'] : 'all';
$filterValue = isset($_GET['value']) ? $_GET['value'] : '';

// Get search filter parameters
$searchName = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$searchDesignation = isset($_GET['search_designation']) ? intval($_GET['search_designation']) : '';
$searchCircle = isset($_GET['search_circle']) ? intval($_GET['search_circle']) : '';
$searchGender = isset($_GET['search_gender']) ? $_GET['search_gender'] : '';
$searchStatus = isset($_GET['search_status']) ? $_GET['search_status'] : '';

// Build query based on filter
$query = "SELECT employees.eid, PNO, eName, eFHName, eDesigID, eCircOffi, circlesoffices.COName AS COName, CNIC, ePhonCell, Gender, eServiceStatus FROM employees LEFT JOIN circlesoffices ON employees.eCircOffi = circlesoffices.COID";

$conditions = array();

if ($filterType == 'male') {
    $conditions[] = "Gender = 'm'";
    $pageTitle = "Male Employees";
} elseif ($filterType == 'female') {
    $conditions[] = "Gender = 'f'";
    $pageTitle = "Female Employees";
} elseif ($filterType == 'In-service') {
    $conditions[] = "eServiceStatus = 1";
    $pageTitle = "In-service Employees";
} elseif ($filterType == 'Retired') {
    $conditions[] = "eServiceStatus != 1";
    $pageTitle = "Inactive Employees";
} else {
    $pageTitle = "All Employees";
}

// Add search filter conditions
if (!empty($searchName)) {
    $searchName = mysqli_real_escape_string($conn, $searchName);
    $conditions[] = "(eName LIKE '%$searchName%' OR PNO LIKE '%$searchName%')";
}

if (!empty($searchDesignation)) {
    // search_designation is an ID; match employee's eDesigID directly
    $searchDesignation = intval($searchDesignation);
    $conditions[] = "eDesigID = $searchDesignation";
}

if (!empty($searchCircle)) {
    $conditions[] = "eCircOffi = $searchCircle";
}

if (!empty($searchGender) && $searchGender != 'all') {
    $conditions[] = "Gender = '$searchGender'";
}

if (!empty($searchStatus) && $searchStatus != 'all') {
    if ($searchStatus == 'active') {
        $conditions[] = "eServiceStatus = 1";
    } else {
        $conditions[] = "eServiceStatus != 1";
    }
}

if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY eid DESC";
$result = mysqli_query($conn, $query);

// Get counts for display
$totalCount = mysqli_num_rows($result);
$activeQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM employees WHERE eServiceStatus = 1");
$activeResult = mysqli_fetch_assoc($activeQuery);
$activeCount = $activeResult['count'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - IRD-HRMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
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
                            <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="#"><i class="fas fa-users"></i> Employees</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><i class="fas fa-file-alt"></i> Reports</a>
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
            <!-- Page Header -->
            <div class="welcome-section">
                <div class="welcome-content">
                    <h1><?php echo $pageTitle; ?></h1>
                    <p>Manage and view employee information</p>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $totalCount; ?></h3>
                            <p>Total <?php echo $pageTitle; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $activeCount; ?></h3>
                            <p>Active <?php echo $pageTitle; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Table -->
            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-filter"></i> Search Filters</h3>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <input type="hidden" name="type" value="<?php echo htmlspecialchars($filterType); ?>">

                                <div class="col-md-3">
                                    <label for="search_name" class="form-label">Employee Name/PID</label>
                                    <input type="text" class="form-control" id="search_name" name="search_name" placeholder="Enter name or PID" value="<?php echo htmlspecialchars($searchName); ?>">
                                </div>

                                <div class="col-md-3">
                                    <label for="search_designation" class="form-label">Designation</label>
                                    <select class="form-select" id="search_designation" name="search_designation">
                                        <option value="">-- All Designations --</option>
                                        <?php
                                        $desigQuery = mysqli_query($conn, "SELECT DID, DesigName FROM designations where dpo=1 ORDER BY DesigName");
                                        while ($desig = mysqli_fetch_assoc($desigQuery)) {
                                            $selected = ($searchDesignation == $desig['DID']) ? 'selected' : '';
                                            echo "<option value='" . $desig['DID'] . "' $selected>" . htmlspecialchars($desig['DesigName']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="search_circle" class="form-label">Circle/Office</label>
                                    <select class="form-select" id="search_circle" name="search_circle">
                                        <option value="">-- All Circles/Offices --</option>
                                        <?php
                                        $circleQuery = mysqli_query($conn, "SELECT COID, COName FROM circlesoffices WHERE COPO = '1' ORDER BY COName");
                                        while ($circle = mysqli_fetch_assoc($circleQuery)) {
                                            $selected = ($searchCircle == $circle['COID']) ? 'selected' : '';
                                            echo "<option value='" . $circle['COID'] . "' $selected>" . htmlspecialchars($circle['COName']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="search_gender" class="form-label">Gender</label>
                                    <select class="form-select" id="search_gender" name="search_gender">
                                        <option value="">-- All --</option>
                                        <option value="m" <?php echo ($searchGender == 'm') ? 'selected' : ''; ?>>Male</option>
                                        <option value="f" <?php echo ($searchGender == 'f') ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="search_status" class="form-label">Service Status</label>
                                    <select class="form-select" id="search_status" name="search_status">
                                        <option value="">-- All --</option>
                                        <option value="active" <?php echo ($searchStatus == 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($searchStatus == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                                    <a href="employee_list.php" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Table -->
            <div class="row g-4 mt-4">
                <div class="col-lg-12">
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-table"></i> <?php echo $pageTitle; ?> Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>PID</th>
                                            <th>Emp. Name</th>
                                            <th>Parental Name</th>
                                            <th>Designation</th>
                                            <th>Circle/Office</th>
                                            <th>CNIC</th>
                                            <th>Cell No</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result && mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                // $status = ($row['eServiceStatus'] == 1) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                                                echo "<tr>";
                                                echo "<td><strong>" . htmlspecialchars($row['PNO']) . "</strong></td>";
                                                echo "<td>" . htmlspecialchars($row['eName']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['eFHName']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['eDesigID']) . "</td>";
                                                $circleDisplay = (isset($row['COName']) && $row['COName']) ? $row['COName'] : $row['eCircOffi'];
                                                echo "<td>" . htmlspecialchars($circleDisplay) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['CNIC']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['ePhonCell']) . "</td>";
                                                echo "<td>";
                                                echo "<div style='display: flex; gap: 4px; flex-wrap: nowrap; white-space: nowrap;'>";
                                                echo "<button class='btn btn-sm btn-info' onclick='viewEmployee(" . $row['eid'] . ")' title='View Details' style='flex-shrink: 0;'><i class='fas fa-eye'></i></button>";
                                                echo "<a href='edit_employee.php?id=" . $row['eid'] . "' class='btn btn-sm btn-warning' title='Edit' style='flex-shrink: 0;'><i class='fas fa-edit'></i></a>";
                                                echo "<button class='btn btn-sm btn-danger' onclick='deleteEmployee(" . $row['eid'] . ")' title='Delete' style='flex-shrink: 0;'><i class='fas fa-trash'></i></button>";
                                                echo "</div>";
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='8' class='text-center text-muted'>No employees found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back to Dashboard Button -->
            <div class="row g-4 mt-4">
                <div class="col-lg-12">
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Employee Detail Modal -->
    <div class="modal fade" id="employeeDetailModal" tabindex="-1" aria-labelledby="employeeDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="employeeDetailModalLabel">Employee Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="employeeDetailContent">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

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
                            <li><a href="index.php">Dashboard</a></li>
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
    <script>
        // View Employee Details
        function viewEmployee(employeeId) {
            const modal = new bootstrap.Modal(document.getElementById('employeeDetailModal'));
            const detailContent = document.getElementById('employeeDetailContent');

            // Fetch employee details via AJAX
            fetch('get_employee_detail.php?id=' + employeeId)
                .then(response => response.text())
                .then(data => {
                    detailContent.innerHTML = data;
                    modal.show();
                })
                .catch(error => {
                    detailContent.innerHTML = '<div class="alert alert-danger">Error loading employee details</div>';
                    console.error('Error:', error);
                });
        }

        function printEmployeeData() {
            const detailElement = document.querySelector('#employeeDetailContent .employee-details');
            if (!detailElement) {
                alert('Employee details are not available for printing.');
                return;
            }

            const printContents = detailElement.innerHTML;
            const printWindow = window.open('', '', 'height=800,width=1200');
            if (!printWindow) {
                alert('Please disable your browser pop-up blocker to print this record.');
                return;
            }

            printWindow.document.write('<html><head><title>Employee Details - IRD-HRMS</title>');
            printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">');
            printWindow.document.write('<style>body { font-size: 11px; margin: 20px; } .table { border: 1px solid #000; border-collapse: collapse; width: 100%; } .table th, .table td { border: 1px solid #000; padding: 6px; } .table thead th { background-color: #2c3e50 !important; color: #ffffff !important; } h6 { font-size: 13px; font-weight: 600; margin-top: 15px; color: #ffffff; background-color: #2c3e50; padding: 6px 8px; } .table-responsive { overflow-x: auto; }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContents);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }

        function printEmployeeDataPDF() {
            alert('PDF export feature coming soon. Please use Print option and select "Save as PDF" from your browser print dialog.');
            printEmployeeData();
        }

        function printEmployeeRecord() {
            const detailElement = document.querySelector('#employeeDetailContent .employee-details');
            if (!detailElement) {
                alert('Employee details are not available for printing.');
                return;
            }

            const printContents = detailElement.innerHTML;
            const printWindow = window.open('', 'PrintWindow', 'height=900,width=1200,left=100,top=100');
            if (!printWindow) {
                alert('Please disable your browser pop-up blocker to print this record.');
                return;
            }

            let htmlContent = '<!DOCTYPE html>';
            htmlContent += '<html><head><title>Employee Record - IRD-HRMS</title>';
            htmlContent += '<meta charset="UTF-8">';
            htmlContent += '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
            htmlContent += '<style>';
            htmlContent += '@page { size: A4; margin: 10mm; }';
            htmlContent += 'body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; margin: 0; padding: 10mm; background-color: #f5f5f5; }';
            htmlContent += '.print-page { background-color: white; margin: 0 auto; padding: 15mm; width: 210mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }';
            htmlContent += '.header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px; }';
            htmlContent += '.header h2 { margin: 0; font-size: 16px; }';
            htmlContent += '.header p { margin: 3px 0; font-size: 10px; }';
            htmlContent += 'h6 { font-weight: bold; margin: 12px 0 8px 0; padding: 5px 8px; background-color: #2c3e50; color: #fff; font-size: 12px; border-radius: 3px; }';
            htmlContent += '.table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 10px; }';
            htmlContent += '.table th, .table td { border: 1px solid #000; padding: 5px 4px; text-align: left; }';
            htmlContent += '.table thead th { background-color: #2c3e50; color: #ffffff; }';
            htmlContent += '.table tbody tr:nth-child(even) { background-color: #f9f9f9; }';
            htmlContent += '.print-footer { text-align: center; font-size: 9px; margin-top: 15px; padding-top: 8px; border-top: 1px solid #ccc; }';
            htmlContent += '</style></head><body>';
            htmlContent += '<div class="print-page">';
            htmlContent += '<div class="header">';
            htmlContent += '<h2>INLAND REVENUE DEPARTMENT, AJ&K</h2>';
            htmlContent += '<p>Human Resource Management System (IRD-HRMS)</p>';
            htmlContent += '<h3>Employee Record</h3>';
            htmlContent += '</div>';
            htmlContent += '<div class="info-section">';
            htmlContent += printContents;
            htmlContent += '</div>';
            htmlContent += '<div class="print-footer"><p>This is a computer-generated record. Printed on: ' + new Date().toLocaleString() + '</p></div>';
            htmlContent += '</div></body></html>';

            printWindow.document.open();
            printWindow.document.write(htmlContent);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function() {
                printWindow.print();
            }, 250);
        }

        // Toggle Employee Status (Active/Inactive)
        function toggleStatus(employeeId, currentStatus) {
            const newStatus = currentStatus == 1 ? 0 : 1;
            const actionText = currentStatus == 1 ? 'deactivate' : 'activate';

            if (confirm('Are you sure you want to ' + actionText + ' this employee?')) {
                fetch('toggle_employee_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + employeeId + '&status=' + newStatus
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Employee ' + actionText + 'd successfully');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error updating employee status');
                    });
            }
        }

        // Delete Employee
        function deleteEmployee(employeeId) {
            if (confirm('Are you sure you want to delete this employee record? This action cannot be undone.')) {
                fetch('delete_employee.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + employeeId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Employee deleted successfully');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting employee');
                    });
            }
        }
    </script>
</body>

</html>