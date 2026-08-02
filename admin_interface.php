<?php
require_once "auth.php";
ensure_authenticated_session();
require_url_authorization();

if (!in_array(intval($_SESSION['user_role_id'] ?? 0), [2, 6], true)) {
    http_response_code(403);
    echo "You do not have permission to access this interface.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Interface - IRD-HRMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-user-shield"></i> Admin Interface</h2>
                <p class="text-muted mb-0">Choose a dedicated admin task below.</p>
            </div>
            <a href="<?php echo append_auth_token('index.php'); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-user-plus text-primary"></i> Add New Employee</h5>
                        <p class="card-text text-muted">Create a new employee record in a dedicated form.</p>
                        <a href="<?php echo append_auth_token('admin_add_employee.php'); ?>" class="btn btn-primary"><i class="fas fa-arrow-right"></i> Open Page</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-edit text-warning"></i> Update Employee</h5>
                        <p class="card-text text-muted">Load and edit an existing employee record.</p>
                        <a href="<?php echo append_auth_token('admin_update_employee.php'); ?>" class="btn btn-warning text-dark"><i class="fas fa-arrow-right"></i> Open Page</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-lock-open text-success"></i> Unlock Employee Data</h5>
                        <p class="card-text text-muted">Control the eRecLoc flag for employee records.</p>
                        <a href="<?php echo append_auth_token('admin_unlock_employee_data.php'); ?>" class="btn btn-success"><i class="fas fa-arrow-right"></i> Open Page</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-users-cog text-info"></i> Manage Users</h5>
                        <p class="card-text text-muted">Add or modify users and grant access areas.</p>
                        <a href="<?php echo append_auth_token('admin_manage_users.php'); ?>" class="btn btn-info text-white"><i class="fas fa-arrow-right"></i> Open Page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>