<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generate_uuid_v4(): string
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function ensure_authenticated_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }

    if (empty($_SESSION['auth_uuid'])) {
        $_SESSION['auth_uuid'] = generate_uuid_v4();
    }
}

function get_role_name(mysqli $conn, int $roleId): string
{
    if ($roleId <= 0) {
        return 'Unknown';
    }

    $query = 'SELECT Role FROM userroletbl WHERE UserRoleId = ' . intval($roleId) . ' LIMIT 1';
    $result = mysqli_query($conn, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (string) ($row['Role'] ?? 'Unknown');
    }

    return 'Unknown';
}

function resolve_user_context(mysqli $conn, ?string $email = null): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $email = $email ?? ($_SESSION['user_email'] ?? '');
    $roleId = 0;
    $allowedAreas = [];

    if ($email !== '') {
        $escapedEmail = mysqli_real_escape_string($conn, $email);
        $userSql = "SELECT id, email, UserRoleId FROM users WHERE email = '$escapedEmail' LIMIT 1";
        $userResult = mysqli_query($conn, $userSql);
        if ($userResult && $userRow = mysqli_fetch_assoc($userResult)) {
            $roleId = intval($userRow['UserRoleId'] ?? 0);
        }

        $infoSql = "SELECT UID, UserRoleId FROM userinformationtbl WHERE LOWER(Email) = LOWER('$escapedEmail') OR UserName = '$escapedEmail' LIMIT 1";
        $infoResult = mysqli_query($conn, $infoSql);
        if ($infoResult && $infoRow = mysqli_fetch_assoc($infoResult)) {
            if ($roleId === 0) {
                $roleId = intval($infoRow['UserRoleId'] ?? 0);
            }
            $uid = intval($infoRow['UID'] ?? 0);
            if ($uid > 0) {
                $accessSql = 'SELECT UsrAccArea FROM useraccessdatatbl WHERE UsrAccUID = ' . $uid;
                $accessResult = mysqli_query($conn, $accessSql);
                while ($accessResult && $accessRow = mysqli_fetch_assoc($accessResult)) {
                    $area = intval($accessRow['UsrAccArea'] ?? 0);
                    if ($area > 0) {
                        $allowedAreas[] = $area;
                    }
                }
            }
        }
    }

    if ($roleId === 0 && !empty($_SESSION['user_role_id'])) {
        $roleId = intval($_SESSION['user_role_id']);
    }

    $roleName = get_role_name($conn, $roleId);
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role_id'] = $roleId;
    $_SESSION['user_role_name'] = $roleName;
    $_SESSION['allowed_areas'] = $allowedAreas;

    return [
        'role_id' => $roleId,
        'role_name' => $roleName,
        'allowed_areas' => $allowedAreas,
    ];
}

function load_user_access_context(mysqli $conn): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user'])) {
        return ['role_id' => 0, 'role_name' => 'Guest', 'allowed_areas' => []];
    }

    if (!empty($_SESSION['user_role_id']) && isset($_SESSION['allowed_areas'])) {
        return [
            'role_id' => intval($_SESSION['user_role_id']),
            'role_name' => $_SESSION['user_role_name'] ?? get_role_name($conn, intval($_SESSION['user_role_id'])),
            'allowed_areas' => array_map('intval', (array) ($_SESSION['allowed_areas'] ?? [])),
        ];
    }

    return resolve_user_context($conn, $_SESSION['user_email'] ?? null);
}

function user_can_perform(mysqli $conn, string $action): bool
{
    $context = load_user_access_context($conn);
    $roleId = intval($context['role_id'] ?? 0);

    if ($roleId === 2 || $roleId === 6) {
        return true;
    }

    switch ($action) {
        case 'insert':
            return $roleId === 1;
        case 'modify':
            return $roleId === 3;
        case 'verify':
            return $roleId === 4;
        case 'manage_users':
            return $roleId === 2;
        default:
            return false;
    }
}

function can_access_employee(mysqli $conn, $circleOfficeId): bool
{
    $context = load_user_access_context($conn);
    $roleId = intval($context['role_id'] ?? 0);
    if ($roleId === 2 || $roleId === 6) {
        return true;
    }

    $circleOfficeId = intval($circleOfficeId);
    if ($circleOfficeId <= 0) {
        return false;
    }

    $allowedAreas = array_map('intval', $context['allowed_areas'] ?? []);
    return in_array($circleOfficeId, $allowedAreas, true);
}

function apply_employee_area_access(mysqli $conn, array $conditions): array
{
    $context = load_user_access_context($conn);
    $roleId = intval($context['role_id'] ?? 0);
    if ($roleId === 2 || $roleId === 6) {
        return $conditions;
    }

    $allowedAreas = array_map('intval', $context['allowed_areas'] ?? []);
    if (empty($allowedAreas)) {
        $conditions[] = '1 = 0';
        return $conditions;
    }

    $areas = implode(',', $allowedAreas);
    $conditions[] = "eCircOffi IN ($areas)";
    return $conditions;
}

function require_url_authorization(): void
{
    ensure_authenticated_session();

    $token = $_GET['auth_token'] ?? '';
    if ($token !== ($_SESSION['auth_uuid'] ?? '')) {
        $queryParams = $_GET;
        $queryParams['auth_token'] = $_SESSION['auth_uuid'];
        $target = basename($_SERVER['PHP_SELF']) . '?' . http_build_query($queryParams);
        header('Location: ' . $target);
        exit;
    }
}

function require_post_authorization(): void
{
    ensure_authenticated_session();

    if (empty($_POST['auth_token']) || $_POST['auth_token'] !== ($_SESSION['auth_uuid'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }
}

function require_role_access(mysqli $conn, string $action): void
{
    if (!user_can_perform($conn, $action)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have permission for this action']);
        exit;
    }
}

function require_employee_access(mysqli $conn, $circleOfficeId): void
{
    if (!can_access_employee($conn, $circleOfficeId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You are not authorized to access this employee record']);
        exit;
    }
}

function append_auth_token(string $url): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $token = $_SESSION['auth_uuid'] ?? '';
    if ($token === '') {
        $token = generate_uuid_v4();
        $_SESSION['auth_uuid'] = $token;
    }

    $separator = strpos($url, '?') === false ? '?' : '&';

    return $url . $separator . 'auth_token=' . urlencode($token);
}
