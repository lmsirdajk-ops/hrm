<?php
require_once "auth.php";
ensure_authenticated_session();
require_url_authorization();
include "database.php";

$report_type = isset($_GET['type']) ? $_GET['type'] : 'circle_office';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - IRD-HRMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .report-container {
            margin: 20px 0;
        }

        .report-header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .report-header h3 {
            margin: 0;
        }

        .circle-section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background-color: #f9f9f9;
        }

        .circle-title {
            background-color: #34495e;
            color: white;
            padding: 10px 15px;
            border-radius: 3px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .report-table {
            font-size: 13px;
        }

        .report-table thead th {
            background-color: #2c3e50;
            color: white;
            border: 1px solid #000;
            padding: 8px;
        }

        .report-table tbody td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .report-table tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        .print-button {
            margin-bottom: 20px;
        }

        .employee-count {
            background-color: #e8f4f8;
            padding: 8px 12px;
            border-radius: 3px;
            display: inline-block;
            margin-bottom: 10px;
        }

        @media print {

            .print-button,
            .btn,
            nav,
            header {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 10mm;
            }

            .circle-section {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .report-table {
                page-break-inside: avoid;
            }
        }
    </style>
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
                            <a class="nav-link" href="employee_list.php"><i class="fas fa-users"></i> Employees</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="reports.php"><i class="fas fa-file-alt"></i> Reports</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="container-fluid">
            <div class="report-container">
                <!-- Report Navigation -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="btn-group" role="group">
                            <a href="reports.php?type=circle_office" class="btn btn-outline-primary <?php echo ($report_type == 'circle_office') ? 'active' : ''; ?>">
                                <i class="fas fa-map-marker-alt"></i> Circle/Office Wise
                            </a>
                        </div>
                        <button class="btn btn-success print-button" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>

                <!-- Circle/Office Wise Report -->
                <?php if ($report_type == 'circle_office') { ?>
                    <div class="report-header">
                        <h3><i class="fas fa-map-marker-alt"></i> Circle/Office Wise Employee Report</h3>
                        <p class="mb-0">Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
                    </div>

                    <?php
                    $accessConditions = [];
                    $accessConditions = apply_employee_area_access($conn, $accessConditions);
                    $accessConditionSql = '';
                    if (!empty($accessConditions)) {
                        $accessConditionSql = implode(' AND ', $accessConditions);
                    }

                    // Fetch all circles/offices where COPO = 1
                    $circle_query = "SELECT COID, COName FROM circlesoffices WHERE COPO = '1' ORDER BY COName ASC";
                    $circle_result = mysqli_query($conn, $circle_query);

                    if ($circle_result && mysqli_num_rows($circle_result) > 0) {
                        while ($circle = mysqli_fetch_assoc($circle_result)) {
                            $coid = (int) $circle['COID'];
                            $coname = $circle['COName'];

                            if (!in_array(intval($_SESSION['user_role_id'] ?? 0), [2, 6], true) && !can_access_employee($conn, $coid)) {
                                continue;
                            }

                            // Fetch employees for this circle
                            $emp_query = "SELECT e.eid, e.eName, e.eDesigBMS, e.eBPS, e.Gender, e.PNO, e.eServiceNature
                                         FROM employees e 
                                         WHERE e.eCircOffi = $coid";
                            if ($accessConditionSql !== '') {
                                $emp_query .= " AND $accessConditionSql";
                            }
                            $emp_query .= " ORDER BY e.eName ASC";
                            $emp_result = mysqli_query($conn, $emp_query);
                            $emp_count = mysqli_num_rows($emp_result);

                            if ($emp_count > 0) {
                    ?>
                                <div class="circle-section">
                                    <div class="circle-title">
                                        <i class="fas fa-building"></i> <?php echo htmlspecialchars($coname); ?>
                                    </div>
                                    <div class="employee-count">
                                        <i class="fas fa-users"></i> Total Employees: <strong><?php echo $emp_count; ?></strong>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm report-table">
                                            <thead>
                                                <tr>
                                                    <th>S.No</th>
                                                    <th>Employee ID</th>
                                                    <th>Name</th>
                                                    <th>Designation</th>
                                                    <th>BPS</th>
                                                    <th>Gender</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sno = 1;
                                                while ($emp = mysqli_fetch_assoc($emp_result)) {
                                                    $status = ($emp['eServiceNature'] == 1) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                                                ?>
                                                    <tr>
                                                        <td><?php echo $sno++; ?></td>
                                                        <td><?php echo htmlspecialchars($emp['eid']); ?></td>
                                                        <td><?php echo htmlspecialchars($emp['eName']); ?></td>
                                                        <td><?php echo htmlspecialchars($emp['eDesigBMS']); ?></td>
                                                        <td><?php echo htmlspecialchars($emp['eBPS']); ?></td>
                                                        <td><?php echo ($emp['Gender'] == 'M') ? 'Male' : 'Female'; ?></td>
                                                        <td><?php echo $status; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                    <?php
                            }
                        }
                    } else {
                        echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No circles/offices found.</div>';
                    }
                    ?>
                <?php } ?>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gj5UL5jXzYfx/0JqJ2TSXfeJeJlJ5UeJf5n3sFsDj3/SrP" crossorigin="anonymous"></script>
</body>

</html>