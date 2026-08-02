<?php
require_once "auth.php";
ensure_authenticated_session();
require_url_authorization();
include "database.php";

if (!in_array(intval($_SESSION['user_role_id'] ?? 0), [2, 6], true)) {
    http_response_code(403);
    echo "You do not have permission to access this interface.";
    exit;
}

$successMessage = '';
$errorMessage = '';

$userContext = load_user_access_context($conn);
$allowedOfficeAreas = array_map('intval', $userContext['allowed_areas'] ?? []);
$canAccessAllOffices = in_array(intval($userContext['role_id'] ?? 0), [2, 6], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_token']) && $_POST['auth_token'] === ($_SESSION['auth_uuid'] ?? '')) {
    if (isset($_POST['action']) && $_POST['action'] === 'insert_employee') {
        $eName = trim($_POST['eName'] ?? '');
        $PNO = trim($_POST['PNO'] ?? '');
        $eFHName = trim($_POST['eFHName'] ?? '');
        $EmailAdd = trim($_POST['EmailAdd'] ?? '');
        $ePhonCell = trim($_POST['ePhonCell'] ?? '');
        $CNIC = trim($_POST['CNIC'] ?? '');
        $eDesigID = intval($_POST['eDesigID'] ?? 0);
        $eCircOffi = intval($_POST['eCircOffi'] ?? 0);
        $Gender = trim($_POST['Gender'] ?? '');
        $eServiceNature = intval($_POST['eServiceNature'] ?? 0);

        if (!$canAccessAllOffices && !in_array($eCircOffi, $allowedOfficeAreas, true)) {
            $eCircOffi = 0;
        }

        if ($eName === '') {
            $errorMessage = 'Employee name is required.';
        } else {
            $insertSql = "INSERT INTO employees (PNO, CNIC, eName, eFHName, eServiceNature, eDesigID, eCircOffi, Gender, EmailAdd, ePhonCell) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ssssiiisss', $PNO, $CNIC, $eName, $eFHName, $eServiceNature, $eDesigID, $eCircOffi, $Gender, $EmailAdd, $ePhonCell);
                if (mysqli_stmt_execute($stmt)) {
                    $successMessage = 'Employee record created successfully.';
                } else {
                    $errorMessage = 'Failed to create employee record.';
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

$designations = [];
$designationsResult = mysqli_query($conn, "SELECT DID, DesigName, DesigBS FROM designations WHERE DPO = 1 ORDER BY DesigName");
if ($designationsResult) {
    while ($row = mysqli_fetch_assoc($designationsResult)) {
        $designations[] = $row;
    }
}

$circles = [];
if ($canAccessAllOffices) {
    $circlesResult = mysqli_query($conn, "SELECT COID, COName FROM circlesoffices WHERE COPO = 1 ORDER BY COName");
} elseif (!empty($allowedOfficeAreas)) {
    $areaList = implode(',', $allowedOfficeAreas);
    $circlesResult = mysqli_query($conn, "SELECT COID, COName FROM circlesoffices WHERE COPO = 1 AND COID IN ($areaList) ORDER BY COName");
} else {
    $circlesResult = false;
}
if ($circlesResult) {
    while ($row = mysqli_fetch_assoc($circlesResult)) {
        $circles[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee - IRD-HRMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-user-plus"></i> Add New Employee</h2>
                <p class="text-muted mb-0">Create a new employee record from this dedicated page.</p>
            </div>
            <a href="<?php echo append_auth_token('admin_interface.php'); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Admin Menu</a>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="insert_employee">
                    <input type="hidden" name="auth_token" value="<?php echo htmlspecialchars($_SESSION['auth_uuid'] ?? ''); ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee Name</label>
                            <input type="text" class="form-control" name="eName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PID / PNO</label>
                            <input type="text" class="form-control" name="PNO">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Father/Husband Name</label>
                            <input type="text" class="form-control" name="eFHName">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CNIC</label>
                            <input type="text" class="form-control" name="CNIC">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="EmailAdd">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cell No</label>
                            <input type="text" class="form-control" name="ePhonCell">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Designation</label>
                            <select class="form-select" name="eDesigID">
                                <option value="0">-- Select --</option>
                                <?php foreach ($designations as $designation): ?>
                                    <option value="<?php echo intval($designation['DID']); ?>"><?php echo htmlspecialchars($designation['DesigName']); ?> (BS-<?php echo htmlspecialchars($designation['DesigBS']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Circle / Office</label>
                            <select class="form-select" name="eCircOffi">
                                <option value="0">-- Select --</option>
                                <?php foreach ($circles as $circle): ?>
                                    <option value="<?php echo intval($circle['COID']); ?>"><?php echo htmlspecialchars($circle['COName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="Gender">
                                <option value="">-- Select --</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="eServiceNature">
                                <option value="0">Inactive</option>
                                <option value="1">Active</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save"></i> Create Employee</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>