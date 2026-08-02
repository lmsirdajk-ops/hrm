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
$selectedUser = null;
$selectedAccessAreas = [];

$userContext = load_user_access_context($conn);
$allowedOfficeAreas = array_map('intval', $userContext['allowed_areas'] ?? []);
$canAccessAllOffices = in_array(intval($userContext['role_id'] ?? 0), [2, 6], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_token']) && $_POST['auth_token'] === ($_SESSION['auth_uuid'] ?? '')) {
    if (isset($_POST['action']) && $_POST['action'] === 'create_user') {
        $firstName = trim($_POST['FirstName'] ?? '');
        $lastName = trim($_POST['LastName'] ?? '');
        $email = trim($_POST['Email'] ?? '');
        $cell = trim($_POST['Cell'] ?? '');
        $userName = trim($_POST['UserName'] ?? '');
        $password = trim($_POST['Password'] ?? '');
        $userRoleId = intval($_POST['UserRoleId'] ?? 0);
        $circOffi = intval($_POST['CircOffi'] ?? 0);
        $accessAreas = is_array($_POST['access_areas'] ?? null) ? array_values(array_unique(array_map('intval', $_POST['access_areas']))) : [];

        if (!$canAccessAllOffices && !in_array($circOffi, $allowedOfficeAreas, true)) {
            $circOffi = 0;
        }
        if (!$canAccessAllOffices) {
            $accessAreas = array_values(array_filter($accessAreas, function ($areaId) use ($allowedOfficeAreas) {
                return $areaId > 0 && in_array($areaId, $allowedOfficeAreas, true);
            }));
        }

        if ($email === '' || $password === '' || $userName === '') {
            $errorMessage = 'Email, username and password are required.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $checkUser = mysqli_query($conn, "SELECT UID FROM userinformationtbl WHERE Email = '" . mysqli_real_escape_string($conn, $email) . "' LIMIT 1");
            if ($checkUser && mysqli_num_rows($checkUser) > 0) {
                $errorMessage = 'A user with this email already exists.';
            } else {
                $userStmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password, UserName, UserRoleId, StatusId, LastAccessDate, CircOffi, Cell) VALUES (?, ?, ?, ?, ?, 1, NOW(), ?, ?)");
                if ($userStmt) {
                    $fullName = trim($firstName . ' ' . $lastName);
                    mysqli_stmt_bind_param($userStmt, 'ssssiis', $fullName, $email, $passwordHash, $userName, $userRoleId, $circOffi, $cell);
                    if (mysqli_stmt_execute($userStmt)) {
                        $infoStmt = mysqli_prepare($conn, "INSERT INTO userinformationtbl (FirstName, LastName, Email, Cell, UserName, Password, UserRoleId, StatusId, CircOffi) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)");
                        if ($infoStmt) {
                            mysqli_stmt_bind_param($infoStmt, 'sssssssi', $firstName, $lastName, $email, $cell, $userName, $password, $userRoleId, $circOffi);
                            if (mysqli_stmt_execute($infoStmt)) {
                                $uid = mysqli_insert_id($conn);
                                if ($uid > 0) {
                                    $deleteAccess = mysqli_prepare($conn, 'DELETE FROM useraccessdatatbl WHERE UsrAccUID = ?');
                                    mysqli_stmt_bind_param($deleteAccess, 'i', $uid);
                                    mysqli_stmt_execute($deleteAccess);
                                    mysqli_stmt_close($deleteAccess);

                                    $accessStmt = mysqli_prepare($conn, 'INSERT INTO useraccessdatatbl (UsrAccUID, UsrAccArea) VALUES (?, ?)');
                                    foreach ($accessAreas as $area) {
                                        $areaId = intval($area);
                                        if ($areaId > 0) {
                                            mysqli_stmt_bind_param($accessStmt, 'ii', $uid, $areaId);
                                            mysqli_stmt_execute($accessStmt);
                                        }
                                    }
                                    mysqli_stmt_close($accessStmt);
                                }
                            }
                            mysqli_stmt_close($infoStmt);
                        }
                        $successMessage = 'User account created successfully.';
                    } else {
                        $errorMessage = 'Failed to create user account.';
                    }
                    mysqli_stmt_close($userStmt);
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'load_user') {
        $userId = intval($_POST['user_id'] ?? 0);
        $userQuery = mysqli_query($conn, "SELECT UID, FirstName, LastName, Email, Cell, UserName, UserRoleId, CircOffi FROM userinformationtbl WHERE UID = $userId LIMIT 1");
        if ($userQuery && mysqli_num_rows($userQuery) > 0) {
            $selectedUser = mysqli_fetch_assoc($userQuery);
            $selectedAccessAreas = [];
            $accessQuery = mysqli_query($conn, "SELECT UsrAccArea FROM useraccessdatatbl WHERE UsrAccUID = $userId");
            if ($accessQuery) {
                while ($accessRow = mysqli_fetch_assoc($accessQuery)) {
                    $areaId = intval($accessRow['UsrAccArea'] ?? 0);
                    if ($areaId > 0) {
                        $selectedAccessAreas[] = $areaId;
                    }
                }
                $selectedAccessAreas = array_values(array_unique($selectedAccessAreas));
                if (!$canAccessAllOffices) {
                    $selectedAccessAreas = array_values(array_filter($selectedAccessAreas, function ($areaId) use ($allowedOfficeAreas) {
                        return in_array($areaId, $allowedOfficeAreas, true);
                    }));
                }
            }
            $successMessage = 'User loaded for modification.';
        } else {
            $errorMessage = 'Selected user could not be loaded.';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_user') {
        $userId = intval($_POST['user_id'] ?? 0);
        $firstName = trim($_POST['FirstName'] ?? '');
        $lastName = trim($_POST['LastName'] ?? '');
        $email = trim($_POST['Email'] ?? '');
        $cell = trim($_POST['Cell'] ?? '');
        $userName = trim($_POST['UserName'] ?? '');
        $password = trim($_POST['Password'] ?? '');
        $userRoleId = intval($_POST['UserRoleId'] ?? 0);
        $circOffi = intval($_POST['CircOffi'] ?? 0);
        $accessAreas = is_array($_POST['access_areas'] ?? null) ? array_values(array_unique(array_map('intval', $_POST['access_areas']))) : [];

        if (!$canAccessAllOffices && !in_array($circOffi, $allowedOfficeAreas, true)) {
            $circOffi = 0;
        }
        if (!$canAccessAllOffices) {
            $accessAreas = array_values(array_filter($accessAreas, function ($areaId) use ($allowedOfficeAreas) {
                return $areaId > 0 && in_array($areaId, $allowedOfficeAreas, true);
            }));
        }

        if ($userId > 0 && $email !== '' && $userName !== '') {
            $existingQuery = mysqli_query($conn, "SELECT Email, UserName FROM userinformationtbl WHERE UID = $userId LIMIT 1");
            $existingUser = $existingQuery ? mysqli_fetch_assoc($existingQuery) : null;
            $lookupEmail = $existingUser['Email'] ?? $email;
            $lookupUserName = $existingUser['UserName'] ?? $userName;
            $passwordHash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;

            $userStmt = mysqli_prepare($conn, "UPDATE users SET full_name = ?, email = ?, UserName = ?, UserRoleId = ?, CircOffi = ?, Cell = ?" . ($passwordHash !== null ? ', password = ?' : '') . " WHERE email = ? OR UserName = ?");
            if ($userStmt) {
                $fullName = trim($firstName . ' ' . $lastName);
                $params = [$fullName, $email, $userName, $userRoleId, $circOffi, $cell];
                if ($passwordHash !== null) {
                    $params[] = $passwordHash;
                }
                $params[] = $lookupEmail;
                $params[] = $lookupUserName;
                $types = '';
                foreach ($params as $param) {
                    $types .= is_int($param) ? 'i' : 's';
                }
                mysqli_stmt_bind_param($userStmt, $types, ...$params);
                if (mysqli_stmt_execute($userStmt)) {
                    $infoStmt = mysqli_prepare($conn, "UPDATE userinformationtbl SET FirstName = ?, LastName = ?, Email = ?, Cell = ?, UserName = ?, UserRoleId = ?, CircOffi = ?" . ($password !== '' ? ', Password = ?' : '') . " WHERE UID = ?");
                    if ($infoStmt) {
                        $infoParams = [$firstName, $lastName, $email, $cell, $userName, $userRoleId, $circOffi];
                        if ($password !== '') {
                            $infoParams[] = $password;
                        }
                        $infoParams[] = $userId;
                        $infoTypes = '';
                        foreach ($infoParams as $param) {
                            $infoTypes .= is_int($param) ? 'i' : 's';
                        }
                        mysqli_stmt_bind_param($infoStmt, $infoTypes, ...$infoParams);
                        mysqli_stmt_execute($infoStmt);
                        mysqli_stmt_close($infoStmt);
                    }

                    $deleteAccess = mysqli_prepare($conn, 'DELETE FROM useraccessdatatbl WHERE UsrAccUID = ?');
                    mysqli_stmt_bind_param($deleteAccess, 'i', $userId);
                    mysqli_stmt_execute($deleteAccess);
                    mysqli_stmt_close($deleteAccess);

                    $accessStmt = mysqli_prepare($conn, 'INSERT INTO useraccessdatatbl (UsrAccUID, UsrAccArea) VALUES (?, ?)');
                    foreach ($accessAreas as $area) {
                        $areaId = intval($area);
                        if ($areaId > 0) {
                            mysqli_stmt_bind_param($accessStmt, 'ii', $userId, $areaId);
                            mysqli_stmt_execute($accessStmt);
                        }
                    }
                    mysqli_stmt_close($accessStmt);
                    $selectedAccessAreas = $accessAreas;
                    $selectedUser = [
                        'UID' => $userId,
                        'FirstName' => $firstName,
                        'LastName' => $lastName,
                        'Email' => $email,
                        'Cell' => $cell,
                        'UserName' => $userName,
                        'UserRoleId' => $userRoleId,
                        'CircOffi' => $circOffi,
                    ];
                    $successMessage = 'User account updated successfully.';
                } else {
                    $errorMessage = 'Failed to update user account.';
                }
                mysqli_stmt_close($userStmt);
            }
        }
    }
}

$roles = [];
$rolesResult = mysqli_query($conn, "SELECT UserRoleId, Role FROM userroletbl ORDER BY UserRoleId");
if ($rolesResult) {
    while ($row = mysqli_fetch_assoc($rolesResult)) {
        $roles[] = $row;
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

$users = [];
$usersResult = mysqli_query($conn, "SELECT UID, FirstName, LastName, Email, UserName, UserRoleId, CircOffi FROM userinformationtbl ORDER BY UID DESC");
if ($usersResult) {
    while ($row = mysqli_fetch_assoc($usersResult)) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - IRD-HRMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-users-cog"></i> Manage Users & Access</h2>
                <p class="text-muted mb-0">Create new users or update existing user and office access permissions.</p>
            </div>
            <a href="<?php echo append_auth_token('admin_interface.php'); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Admin Menu</a>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-plus"></i> Add New User</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="create_user">
                            <input type="hidden" name="auth_token" value="<?php echo htmlspecialchars($_SESSION['auth_uuid'] ?? ''); ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="FirstName" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="LastName" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="Email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" name="UserName" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" name="Password" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cell</label>
                                    <input type="text" class="form-control" name="Cell">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Role</label>
                                    <select class="form-select" name="UserRoleId" required>
                                        <option value="">-- Select --</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo intval($role['UserRoleId']); ?>"><?php echo htmlspecialchars($role['Role']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Primary Office</label>
                                    <select class="form-select" name="CircOffi">
                                        <option value="0">-- Select --</option>
                                        <?php foreach ($circles as $circle): ?>
                                            <option value="<?php echo intval($circle['COID']); ?>"><?php echo htmlspecialchars($circle['COName']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Grant Access to Offices</label>
                                    <select class="form-select" name="access_areas[]" multiple size="6">
                                        <?php foreach ($circles as $circle): ?>
                                            <option value="<?php echo intval($circle['COID']); ?>"><?php echo htmlspecialchars($circle['COName']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Hold Ctrl/Cmd to select multiple offices.</div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-user-plus"></i> Create User</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-edit"></i> Modify Existing User</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mb-4">
                            <input type="hidden" name="action" value="load_user">
                            <input type="hidden" name="auth_token" value="<?php echo htmlspecialchars($_SESSION['auth_uuid'] ?? ''); ?>">
                            <label class="form-label">Select User</label>
                            <select class="form-select" name="user_id">
                                <option value="">-- Select --</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo intval($user['UID']); ?>" <?php echo (isset($selectedUser['UID']) && $selectedUser['UID'] == $user['UID']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($user['Email'] . ' (' . $user['UserName'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-outline-warning mt-3"><i class="fas fa-arrow-right"></i> Load User</button>
                        </form>

                        <?php if ($selectedUser): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_user">
                                <input type="hidden" name="auth_token" value="<?php echo htmlspecialchars($_SESSION['auth_uuid'] ?? ''); ?>">
                                <input type="hidden" name="user_id" value="<?php echo intval($selectedUser['UID']); ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="FirstName" value="<?php echo htmlspecialchars($selectedUser['FirstName'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" name="LastName" value="<?php echo htmlspecialchars($selectedUser['LastName'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="Email" value="<?php echo htmlspecialchars($selectedUser['Email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" name="UserName" value="<?php echo htmlspecialchars($selectedUser['UserName'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control" name="Password">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Cell</label>
                                        <input type="text" class="form-control" name="Cell" value="<?php echo htmlspecialchars($selectedUser['Cell'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Role</label>
                                        <select class="form-select" name="UserRoleId" required>
                                            <option value="">-- Select --</option>
                                            <?php foreach ($roles as $role): $selected = (isset($selectedUser['UserRoleId']) && $selectedUser['UserRoleId'] == $role['UserRoleId']) ? 'selected' : ''; ?>
                                                <option value="<?php echo intval($role['UserRoleId']); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($role['Role']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Primary Office</label>
                                        <select class="form-select" name="CircOffi">
                                            <option value="0">-- Select --</option>
                                            <?php foreach ($circles as $circle): $selected = (isset($selectedUser['CircOffi']) && $selectedUser['CircOffi'] == $circle['COID']) ? 'selected' : ''; ?>
                                                <option value="<?php echo intval($circle['COID']); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($circle['COName']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Grant Access to Offices</label>
                                        <select class="form-select" name="access_areas[]" multiple size="6">
                                            <?php foreach ($circles as $circle): $selectedAccess = in_array(intval($circle['COID']), $selectedAccessAreas, true) ? 'selected' : ''; ?>
                                                <option value="<?php echo intval($circle['COID']); ?>" <?php echo $selectedAccess; ?>><?php echo htmlspecialchars($circle['COName']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Hold Ctrl/Cmd to select multiple offices.</div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-warning mt-3"><i class="fas fa-save"></i> Update User</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>