<?php
require_once "auth.php";
ensure_authenticated_session();
require_url_authorization();
include "database.php";

$employee = null;
$error = '';
$success = '';

// Get employee data if editing
if (isset($_GET['id'])) {
    $eid = intval($_GET['id']);
    $query = "SELECT * FROM employees WHERE eid = $eid";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $employee = mysqli_fetch_assoc($result);
        require_employee_access($conn, $employee['eCircOffi'] ?? null);
    } else {
        $error = "Employee not found";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eid'])) {
    $eid = isset($_POST['eid']) ? intval($_POST['eid']) : 0;
    $eName = isset($_POST['eName']) ? mysqli_real_escape_string($conn, $_POST['eName']) : '';
    $EmailAdd = isset($_POST['EmailAdd']) ? mysqli_real_escape_string($conn, $_POST['EmailAdd']) : '';
    $ePhonCell = isset($_POST['ePhonCell']) ? mysqli_real_escape_string($conn, $_POST['ePhonCell']) : '';
    $ePhonOffi = isset($_POST['ePhonOffi']) ? mysqli_real_escape_string($conn, $_POST['ePhonOffi']) : '';
    $ePhonResi = isset($_POST['ePhonResi']) ? mysqli_real_escape_string($conn, $_POST['ePhonResi']) : '';
    // Support both form name `eDesignID` and legacy `eDesigID`
    if (isset($_POST['eDesignID'])) {
        $eDesigID = intval($_POST['eDesignID']);
    } else {
        $eDesigID = isset($_POST['eDesigID']) ? intval($_POST['eDesigID']) : 0;
    }
    $Gender = isset($_POST['Gender']) ? mysqli_real_escape_string($conn, $_POST['Gender']) : '';
    $CNIC = isset($_POST['CNIC']) ? mysqli_real_escape_string($conn, $_POST['CNIC']) : '';
    $eFHName = isset($_POST['eFHName']) ? mysqli_real_escape_string($conn, $_POST['eFHName']) : '';
    $PNO = isset($_POST['PNO']) ? mysqli_real_escape_string($conn, $_POST['PNO']) : '';
    $eServiceNature = isset($_POST['eServiceNature']) ? intval($_POST['eServiceNature']) : 0;
    $eBPS = isset($_POST['eBPS']) ? intval($_POST['eBPS']) : 0;
    $eDoB = (isset($_POST['eDoB']) && $_POST['eDoB'] !== '') ? mysqli_real_escape_string($conn, $_POST['eDoB']) : null;
    $eDoR = (isset($_POST['eDoR']) && $_POST['eDoR'] !== '') ? mysqli_real_escape_string($conn, $_POST['eDoR']) : null;
    $eCircOffi = (isset($_POST['eCircOffi']) && $_POST['eCircOffi'] !== '') ? intval($_POST['eCircOffi']) : null;
    $eCircOffiWork = (isset($_POST['eCircOffiWork']) && $_POST['eCircOffiWork'] !== '') ? intval($_POST['eCircOffiWork']) : null;
    $Domicile = (isset($_POST['Domicile']) && $_POST['Domicile'] !== '') ? intval($_POST['Domicile']) : null;
    $Present_Add = isset($_POST['Present_Add']) ? mysqli_real_escape_string($conn, $_POST['Present_Add']) : '';
    $Present_Add_City = (isset($_POST['Present_Add_City']) && $_POST['Present_Add_City'] !== '') ? intval($_POST['Present_Add_City']) : null;
    $Permanent_Add = isset($_POST['Permanent_Add']) ? mysqli_real_escape_string($conn, $_POST['Permanent_Add']) : '';
    $Permanent_Add_City = (isset($_POST['Permanent_Add_City']) && $_POST['Permanent_Add_City'] !== '') ? intval($_POST['Permanent_Add_City']) : null;
    $eRecLoc = isset($_POST['eRecLoc']) ? 1 : 0;
    $BMSID = (isset($_POST['BMSID']) && $_POST['BMSID'] !== '') ? intval($_POST['BMSID']) : 0;
    $eDesigBMS = isset($_POST['eDesigBMS']) ? mysqli_real_escape_string($conn, $_POST['eDesigBMS']) : '';
    $eBPSBMS = (isset($_POST['eBPSBMS']) && $_POST['eBPSBMS'] !== '') ? intval($_POST['eBPSBMS']) : 0;
    $eCircOffiBMS = (isset($_POST['eCircOffiBMS']) && $_POST['eCircOffiBMS'] !== '') ? intval($_POST['eCircOffiBMS']) : null;
    // Accept retirement year from `eRetired` or `eDoRet` form field
    if (isset($_POST['eRetired']) && $_POST['eRetired'] !== '') {
        $eRetired = intval($_POST['eRetired']);
    } elseif (isset($_POST['eDoRet']) && $_POST['eDoRet'] !== '') {
        $eRetired = intval($_POST['eDoRet']);
    } else {
        $eRetired = null;
    }

    require_role_access($conn, 'modify');

    $updateQuery = "UPDATE employees SET 
        PNO = '$PNO',
        CNIC = '$CNIC',
        eName = '$eName',
        eFHName = '$eFHName',
        eServiceNature = $eServiceNature,
        eDesigID = $eDesigID,
        eBPS = $eBPS,
        eDoR = " . ($eDoR ? "'$eDoR'" : "NULL") . ",
        eCircOffi = " . ($eCircOffi ? $eCircOffi : "NULL") . ",
        eCircOffiWork = " . ($eCircOffiWork ? $eCircOffiWork : "NULL") . ",
        eDoB = " . ($eDoB ? "'$eDoB'" : "NULL") . ",
        Gender = '$Gender',
        Domicile = " . ($Domicile ? $Domicile : "NULL") . ",
        Present_Add = '$Present_Add',
        Present_Add_City = " . ($Present_Add_City ? $Present_Add_City : "NULL") . ",
        Permanent_Add = '$Permanent_Add',
        Permanent_Add_City = " . ($Permanent_Add_City ? $Permanent_Add_City : "NULL") . ",
        EmailAdd = '$EmailAdd',
        ePhonOffi = '$ePhonOffi',
        ePhonResi = '$ePhonResi',
        ePhonCell = '$ePhonCell',
        eRecLoc = $eRecLoc,
        BMSID = $BMSID,
        eDesigBMS = '$eDesigBMS',
        eBPSBMS = $eBPSBMS,
        eCircOffiBMS = " . ($eCircOffiBMS ? $eCircOffiBMS : "NULL") . ",
        eRetired = " . ($eRetired ? $eRetired : "NULL") . "
        WHERE eid = $eid";

    // Temporary debug: if ?debug=1 is present, output and log the generated SQL
    if (isset($_GET['debug']) && $_GET['debug'] == '1') {
        echo '<div class="alert alert-info"><strong>DEBUG:</strong> Generated UPDATE query for eid=' . htmlspecialchars($eid) . ':</div>';
        echo '<pre>' . htmlspecialchars($updateQuery) . '</pre>';
        @file_put_contents(__DIR__ . '/update_debug.log', date('c') . " - eid={$eid}\n" . $updateQuery . "\n\n", FILE_APPEND);
    }

    if (mysqli_query($conn, $updateQuery)) {
        $affected = mysqli_affected_rows($conn);
        if ($affected > 0) {
            $success = "Employee updated successfully!";
        } else {
            $success = "No changes were made to the employee record.";
        }
        // Refresh employee data
        $result = mysqli_query($conn, "SELECT * FROM employees WHERE eid = $eid");
        $employee = mysqli_fetch_assoc($result);
    } else {
        $error = "Error updating employee: " . mysqli_error($conn);
    }
}

// Fetch designations for dropdown

// Fetch designations for dropdown
$designationsQuery = "SELECT DID, DesigName FROM designations WHERE dpo=1 ORDER BY DesigName";
$designationsResult = mysqli_query($conn, $designationsQuery);
$designations = [];
if ($designationsResult && mysqli_num_rows($designationsResult) > 0) {
    while ($row = mysqli_fetch_assoc($designationsResult)) {
        $designations[] = $row;
    }
}

// Fetch cities for dropdowns
$citiesQuery = "SELECT CID, CName FROM cities ORDER BY CName";
$citiesResult = mysqli_query($conn, $citiesQuery);
$cities = [];
if ($citiesResult && mysqli_num_rows($citiesResult) > 0) {
    while ($row = mysqli_fetch_assoc($citiesResult)) {
        $cities[] = $row;
    }
}

$domicilesQuery = "SELECT d.CID, d.CName, d.CoName AS CoID, c.CoName AS CountryName FROM domiciles d LEFT JOIN countries c ON d.CoName = c.CoID ORDER BY d.CName";
$domicilesResult = mysqli_query($conn, $domicilesQuery);
$domiciles = [];
if ($domicilesResult && mysqli_num_rows($domicilesResult) > 0) {
    while ($row = mysqli_fetch_assoc($domicilesResult)) {
        $domiciles[] = $row;
    }
}

// Fetch circles/offices for dropdowns
$circlesQuery = "SELECT COID, COName FROM circlesoffices ORDER BY COName";
$circlesResult = mysqli_query($conn, $circlesQuery);
$circles = [];
if ($circlesResult && mysqli_num_rows($circlesResult) > 0) {
    while ($row = mysqli_fetch_assoc($circlesResult)) {
        $circles[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Data Modification - IRD-HRMS</title>
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
                            <a class="nav-link active" href="employee_list.php"><i class="fas fa-users"></i> Employees</a>
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
                    <h1>Employee Data</h1>
                    <p>Update employee information</p>
                </div>
            </div>

            <!-- Back Button -->
            <div class="row g-4 mb-3">
                <div class="col-lg-12">
                    <a href="employee_list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Employees</a>
                </div>
            </div>

            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Edit Form -->
            <?php if ($employee): ?>
                <div class="row g-4">
                    <div class="col-lg-12">
                        <div class="content-card">
                            <div class="card-header">
                                <h3><i class="fas fa-edit"></i> Employee Information (ID: <?php echo $employee['eid']; ?>)</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="employee-form">
                                    <input type="hidden" name="eid" value="<?php echo $employee['eid']; ?>">

                                    <!-- Personal Information Section -->
                                    <h5 class="section-title mt-4"><i class="fas fa-user"></i> Personal Information</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="eName" class="form-label">Full Name *</label>
                                            <input type="text" class="form-control" id="eName" name="eName" value="<?php echo htmlspecialchars($employee['eName']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="Gender" class="form-label">Gender *</label>
                                            <select class="form-select" id="Gender" name="Gender" required>
                                                <option value="">-- Select --</option>
                                                <option value="m" <?php echo ($employee['Gender'] == 'm') ? 'selected' : ''; ?>>Male</option>
                                                <option value="f" <?php echo ($employee['Gender'] == 'f') ? 'selected' : ''; ?>>Female</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="CNIC" class="form-label">CNIC</label>
                                            <input type="text" class="form-control" id="CNIC" name="CNIC" value="<?php echo htmlspecialchars($employee['CNIC']); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="eDoB" class="form-label">Date of Birth</label>
                                            <input type="datetime-local" class="form-control" id="eDoB" name="eDoB" value="<?php echo $employee['eDoB'] ? str_replace(' ', 'T', $employee['eDoB']) : ''; ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="eFHName" class="form-label">Father's Name</label>
                                            <input type="text" class="form-control" id="eFHName" name="eFHName" value="<?php echo htmlspecialchars($employee['eFHName']); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="PNO" class="form-label">PNO</label>
                                            <input type="text" class="form-control" id="PNO" name="PNO" value="<?php echo htmlspecialchars($employee['PNO']); ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="eDoR" class="form-label">Date of Recruitment</label>
                                            <input type="datetime-local" class="form-control" id="eDoR" name="eDoR" value="<?php echo $employee['eDoR'] ? str_replace(' ', 'T', $employee['eDoR']) : ''; ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="Domicile" class="form-label">Domicile</label>
                                            <select class="form-select" id="Domicile" name="Domicile">
                                                <option value="">-- Select Domicile --</option>
                                                <?php foreach ($domiciles as $dom): ?>
                                                    <option value="<?php echo $dom['CID']; ?>" <?php echo (isset($employee['Domicile']) && $employee['Domicile'] == $dom['CID']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($dom['CName'] . (isset($dom['CountryName']) ? ' (' . $dom['CountryName'] . ')' : '')); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <label for="ePhonCell" class="form-label">Cell Phone</label>
                                    <input type="text" class="form-control" id="ePhonCell" name="ePhonCell" value="<?php echo htmlspecialchars($employee['ePhonCell']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="ePhonOffi" class="form-label">Office Phone</label>
                                <input type="text" class="form-control" id="ePhonOffi" name="ePhonOffi" value="<?php echo htmlspecialchars($employee['ePhonOffi']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="ePhonResi" class="form-label">Residence Phone</label>
                                <input type="text" class="form-control" id="ePhonResi" name="ePhonResi" value="<?php echo htmlspecialchars($employee['ePhonResi']); ?>">
                            </div>
                        </div>

                        <!-- Professional Information Section -->
                        <h5 class="section-title mt-4"><i class="fas fa-briefcase"></i> Professional Information</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eDesignID" class="form-label">Designation *</label>
                                <select class="form-select" id="eDesignID" name="eDesignID" required>
                                    <option value="">-- Select Designation --</option>
                                    <?php foreach ($designations as $desig): ?>
                                        <option value="<?php echo $desig['DID']; ?>" <?php echo (isset($employee['eDesigID']) && $employee['eDesigID'] == $desig['DID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($desig['DesigName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="eBPS" class="form-label">BPS</label>
                                <input type="number" class="form-control" id="eBPS" name="eBPS" value="<?php echo htmlspecialchars($employee['eBPS']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="eServiceNature" class="form-label">Service Status *</label>
                                <select class="form-select" id="eServiceNature" name="eServiceNature" required>
                                    <option value="1" <?php echo ($employee['eServiceNature'] == 1) ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo ($employee['eServiceNature'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eCircOffi" class="form-label">Circle/Office</label>
                                <select class="form-select" id="eCircOffi" name="eCircOffi">
                                    <option value="">-- Select Circle/Office --</option>
                                    <?php foreach ($circles as $circle): ?>
                                        <option value="<?php echo $circle['COID']; ?>" <?php echo (isset($employee['eCircOffi']) && $employee['eCircOffi'] == $circle['COID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($circle['COName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="eCircOffiWork" class="form-label">Circle/Office (Work)</label>
                                <select class="form-select" id="eCircOffiWork" name="eCircOffiWork">
                                    <option value="">-- Select Circle/Office --</option>
                                    <?php foreach ($circles as $circle): ?>
                                        <option value="<?php echo $circle['COID']; ?>" <?php echo (isset($employee['eCircOffiWork']) && $employee['eCircOffiWork'] == $circle['COID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($circle['COName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eDoRet" class="form-label">Date of Retirement</label>
                                <input type="number" class="form-control" id="eDoRet" name="eDoRet" value="<?php echo isset($employee['eDoRet']) ? htmlspecialchars($employee['eDoRet']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="BMSID" class="form-label">BMS ID</label>
                                <input type="number" class="form-control" id="BMSID" name="BMSID" value="<?php echo htmlspecialchars($employee['BMSID']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eDesigBMS" class="form-label">Designation (BMS)</label>
                                <input type="text" class="form-control" id="eDesigBMS" name="eDesigBMS" value="<?php echo htmlspecialchars($employee['eDesigBMS']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="eCircOffiBMS" class="form-label">Circle/Office (BMS)</label>
                                <select class="form-select" id="eCircOffiBMS" name="eCircOffiBMS">
                                    <option value="">-- Select Circle/Office --</option>
                                    <?php foreach ($circles as $circle): ?>
                                        <option value="<?php echo $circle['COID']; ?>" <?php echo (isset($employee['eCircOffiBMS']) && $employee['eCircOffiBMS'] == $circle['COID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($circle['COName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eRetired" class="form-label">Retired (Year)</label>
                                <input type="number" class="form-control" id="eRetired" name="eRetired" value="<?php echo htmlspecialchars($employee['eRetired']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input type="checkbox" class="form-check-input" id="eRecLoc" name="eRecLoc" <?php echo ($employee['eRecLoc'] == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="eRecLoc">
                                        Record Locked
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information Section -->
                        <h5 class="section-title mt-4"><i class="fas fa-map-marker-alt"></i> Address Information</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="Permanent_Add" class="form-label">Permanent Address</label>
                                <textarea class="form-control" id="Permanent_Add" name="Permanent_Add" rows="2"><?php echo htmlspecialchars($employee['Permanent_Add']); ?></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="Permanent_Add_City" class="form-label">Permanent Address City</label>
                                <select class="form-select" id="Permanent_Add_City" name="Permanent_Add_City">
                                    <option value="">-- Select City --</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?php echo $city['CID']; ?>" <?php echo (isset($employee['Permanent_Add_City']) && $employee['Permanent_Add_City'] == $city['CID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($city['CName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="Present_Add" class="form-label">Present Address</label>
                                <textarea class="form-control" id="Present_Add" name="Present_Add" rows="2"><?php echo htmlspecialchars($employee['Present_Add']); ?></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="Present_Add_City" class="form-label">Present Address City</label>
                                <select class="form-select" id="Present_Add_City" name="Present_Add_City">
                                    <option value="">-- Select City --</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?php echo $city['CID']; ?>" <?php echo (isset($employee['Present_Add_City']) && $employee['Present_Add_City'] == $city['CID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($city['CName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-5">
                            <div class="col-md-12">
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Changes</button>
                                    <a href="employee_list.php" class="btn btn-secondary btn-lg"><i class="fas fa-times"></i> Cancel</a>
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
        </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle"></i> No employee data found. Please select an employee to edit.
        </div>
    <?php endif; ?>
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
</body>

</html>