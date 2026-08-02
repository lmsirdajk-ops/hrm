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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_token']) && $_POST['auth_token'] === ($_SESSION['auth_uuid'] ?? '')) {
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_unlock') {
        $employeeId = intval($_POST['employee_id'] ?? 0);
        $newValue = isset($_POST['unlock_value']) ? intval($_POST['unlock_value']) : 0;
        if ($employeeId > 0) {
            $stmt = mysqli_prepare($conn, 'UPDATE employees SET eRecLoc = ? WHERE eid = ?');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ii', $newValue, $employeeId);
                if (mysqli_stmt_execute($stmt)) {
                    $successMessage = 'Employee record lock status updated.';
                } else {
                    $errorMessage = 'Failed to update employee record lock status.';
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

$employees = [];
$employeeResult = mysqli_query($conn, "SELECT eid, PNO, eName, eRecLoc FROM employees ORDER BY eid DESC");
if ($employeeResult) {
    while ($row = mysqli_fetch_assoc($employeeResult)) {
        $employees[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unlock Employee Data - IRD-HRMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-lock-open"></i> Unlock Employee Data</h2>
                <p class="text-muted mb-0">Manage whether employee records are marked unlocked by the eRecLoc flag.</p>
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
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>PID</th>
                                <th>Employee Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($employee['PNO'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($employee['eName'] ?? ''); ?></td>
                                    <td>
                                        <?php if ((int)($employee['eRecLoc'] ?? 0) === 1): ?>
                                            <span class="badge bg-success">Unlocked</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Locked</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_unlock">
                                            <input type="hidden" name="auth_token" value="<?php echo htmlspecialchars($_SESSION['auth_uuid'] ?? ''); ?>">
                                            <input type="hidden" name="employee_id" value="<?php echo intval($employee['eid']); ?>">
                                            <?php if ((int)($employee['eRecLoc'] ?? 0) === 1): ?>
                                                <input type="hidden" name="unlock_value" value="0">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-lock"></i> Lock</button>
                                            <?php else: ?>
                                                <input type="hidden" name="unlock_value" value="1">
                                                <button type="submit" class="btn btn-sm btn-outline-success"><i class="fas fa-lock-open"></i> Unlock</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>