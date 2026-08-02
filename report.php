<?php
require_once "auth.php";
ensure_authenticated_session();
require_url_authorization();

include "database.php";

// Get circle/office filter
$filterCircle = isset($_GET['filter_circle']) && $_GET['filter_circle'] !== '' ? intval($_GET['filter_circle']) : '';

$accessConditions = [];
if ($filterCircle !== '') {
    $accessConditions[] = "eCircOffi = $filterCircle";
}
$accessConditions = apply_employee_area_access($conn, $accessConditions);
$accessConditionSql = '';
if (!empty($accessConditions)) {
    $accessConditionSql = implode(' AND ', $accessConditions);
}

$totalEmployeesQuery = "SELECT COUNT(*) as count FROM employees";
if ($accessConditionSql !== '') {
    $totalEmployeesQuery .= " WHERE $accessConditionSql";
}
$totalEmployees = mysqli_query($conn, $totalEmployeesQuery);
$totalEmpRow = mysqli_fetch_assoc($totalEmployees);
$totalCount = $totalEmpRow['count'];

$activeEmployeesQuery = "SELECT COUNT(*) as count FROM employees WHERE eServiceNature = 1";
if ($accessConditionSql !== '') {
    $activeEmployeesQuery .= " AND $accessConditionSql";
}
$activeEmployees = mysqli_query($conn, $activeEmployeesQuery);
$activeEmpRow = mysqli_fetch_assoc($activeEmployees);
$activeCount = $activeEmpRow['count'];

$maleEmployeesQuery = "SELECT COUNT(*) as count FROM employees WHERE Gender = 'M'";
if ($accessConditionSql !== '') {
    $maleEmployeesQuery .= " AND $accessConditionSql";
}
$maleEmployees = mysqli_query($conn, $maleEmployeesQuery);
$maleRow = mysqli_fetch_assoc($maleEmployees);
$maleCount = $maleRow['count'];

$femaleEmployeesQuery = "SELECT COUNT(*) as count FROM employees WHERE Gender = 'F'";
if ($accessConditionSql !== '') {
    $femaleEmployeesQuery .= " AND $accessConditionSql";
}
$femaleEmployees = mysqli_query($conn, $femaleEmployeesQuery);
$femaleRow = mysqli_fetch_assoc($femaleEmployees);
$femaleCount = $femaleRow['count'];

$inactiveEmployeesQuery = "SELECT COUNT(*) as count FROM employees WHERE eRetired = 2";
if ($accessConditionSql !== '') {
    $inactiveEmployeesQuery .= " AND $accessConditionSql";
}
$inactiveEmployees = mysqli_query($conn, $inactiveEmployeesQuery);
$inactiveRow = mysqli_fetch_assoc($inactiveEmployees);
$inactiveCount = $inactiveRow['count'];

// Designation-wise summary
$designationSummaryQuery = "SELECT COALESCE(designations.DesigName, 'Unassigned') AS DesigName, COUNT(*) AS cnt FROM employees LEFT JOIN designations ON employees.eDesigID = designations.DID";
if ($accessConditionSql !== '') {
    $designationSummaryQuery .= " WHERE $accessConditionSql";
}
$designationSummaryQuery .= " GROUP BY designations.DesigName ORDER BY cnt DESC";
$designationSummary = mysqli_query($conn, $designationSummaryQuery);

$employeeListQuery = "SELECT employees.eid, 
                 employees.PNO, 
                 employees.CNIC, 
                 employees.eName, 
                 employees.eFHName, 
                 employees.eServiceNature,
                 employees.eDesigID, 
                 CONCAT(designations.DesigName, ' ( BPS-', employees.eBPS, ')') AS DesigBPS, 
                 employees.eDoB, 
                 employees.ePhonCell, 
                 employees.EmailAdd 
          FROM designations     
          RIGHT JOIN employees ON designations.DID = employees.eDesigID WHERE employees.PNO IS NOT NULL AND employees.PNO != ''";

/* $Complete_employeeListQuery = "SELECT Employees.eid, Employees.PNO, Employees.CNIC, Employees.eName, Employees.eFHName, Employees.eServiceStatus, [DesigName] & IIf(IsEmpty([Employees].[eBPS]) Or IsNull([Employees].[eBPS]),'',' ( BPS-' & [Employees].[eBPS] & ')') AS DesigBPS, Employees.eDoB, IIf(IsNull([eDoB]) Or IsEmpty([eDoB]),'Nil',IIf(Day([eDoB])=1,DateSerial(Year([eDoB])+60,Month([eDoB]),0),DateSerial(Year([eDoB])+60,Month([eDoB])+1,0))) AS Dt_Retire, IIf(IsEmpty([Employees].[eCircOffi]) Or IsNull([employees].[eCircOffi]),'',[circlesoffices].[COName] & IIf(IsEmpty([RangesOffices].[RName]) Or IsNull([RangesOffices].[RName]),'',' (' & [RangesOffices].[RName] & " - " & [zones].[ZName] & ')')) AS CirOff, IIf(IsEmpty([Employees].[eCircOffiWork]) Or IsNull([Employees].[eCircOffiWork]),'',[CirclesOffices_1].[COName] & IIf(IsEmpty([RangesOffices_1].[RName]) Or IsNull([RangesOffices_1].[RName]),'',' (' & [RangesOffices_1].[RName] & '-' & [Zones_1].[ZName] & ')')) AS CircOffWrk, Employees.ePhonCell, Employees.ePhonOffi, [Employees].[Present_Add] & ' (' & [Cities].[CName] & ')' AS Pre_Add, [Employees].[Permanent_Add] & ' (' & [Cities_1].[cname] & '-' & [Countries].[CoName] & ')' AS Per_Add, Employees.EmailAdd FROM (((((Cities RIGHT JOIN (((Zones RIGHT JOIN RangesOffices ON Zones.[ZID] = RangesOffices.[Zid]) RIGHT JOIN CirclesOffices ON RangesOffices.[RID] = CirclesOffices.[CORangID]) RIGHT JOIN (Designations RIGHT JOIN Employees ON Designations.[DID] = Employees.[eDesigID]) ON CirclesOffices.[COID] = Employees.[eCircOffi]) ON Cities.[CID] = Employees.[Present_Add_City]) LEFT JOIN Cities AS Cities_1 ON Employees.Permanent_Add_City = Cities_1.CID) LEFT JOIN Countries ON Cities_1.CoID = Countries.CoID) LEFT JOIN CirclesOffices AS CirclesOffices_1 ON Employees.eCircOffiWork = CirclesOffices_1.COID) LEFT JOIN RangesOffices AS RangesOffices_1 ON CirclesOffices_1.CORangID = RangesOffices_1.RID) LEFT JOIN Zones AS Zones_1 ON RangesOffices_1.Zid = Zones_1.ZID";

$OldemployeeListQuery = "SELECT PNO, eName, eFHName, EmailAdd, ePhonCell, eDesigID, eServiceStatus FROM employees";
 */
if ($filterCircle !== '') {
    $employeeListQuery .= " AND employees.eCircOffi = $filterCircle";
}
if ($accessConditionSql !== '') {
    $employeeListQuery .= " AND $accessConditionSql";
}
$employeeListQuery .= " ORDER BY PNO ASC";
$employeeList = mysqli_query($conn, $employeeListQuery);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - IRD-HRM</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Compact print styles to reduce page space usage */
        @media print {
            :root {
                -webkit-print-color-adjust: exact;
            }

            html,
            body {
                margin: 0;
                padding: 0;
                font-size: 10px;
                color: #000;
            }

            .container {
                padding: 4mm;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            header,
            .card {
                border: none;
                box-shadow: none;
            }

            .card {
                margin: 0 0 4px 0;
            }

            .card-body {
                padding: 6px 8px;
            }

            h2,
            h5 {
                margin: 0 0 4px 0;
                font-weight: 600;
            }

            .table {
                font-size: 9px;
                border-collapse: collapse;
                width: 100%;
            }

            .table th,
            .table td {
                padding: 4px 6px;
                border: 1px solid #444;
            }

            .table thead th {
                background: #eee;
                color: #000;
            }

            .table-responsive {
                overflow: visible;
            }

            .card-header {
                padding: 6px 8px;
            }

            .badge {
                display: inline-block;
                padding: 2px 6px;
                font-size: 8px;
            }

            @page {
                margin: 8mm;
                size: A4 portrait;
            }
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h2 class="mb-1"><i class="fas fa-file-alt"></i> Employee Reports</h2>
                <p class="text-muted mb-0">HR summary and employee record overview</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>

        <!-- Circle/Office Filter -->
        <form method="GET" class="row g-3 mb-4 no-print">
            <div class="col-md-4">
                <label for="filter_circle" class="form-label">Circle/Office</label>
                <select class="form-select" id="filter_circle" name="filter_circle" onchange="this.form.submit()">
                    <option value="">-- All Circles/Offices --</option>
                    <?php
                    $circleQuery = mysqli_query($conn, "SELECT COID, COName FROM circlesoffices WHERE COPO = '1' ORDER BY COName");
                    while ($c = mysqli_fetch_assoc($circleQuery)) {
                        $sel = ($filterCircle == $c['COID']) ? 'selected' : '';
                        echo "<option value='" . $c['COID'] . "' $sel>" . htmlspecialchars($c['COName']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <a href="report.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Total Employees</h5>
                        <h2 class="mb-0"><?php echo (int)$totalCount; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title text-success">Active Employees</h5>
                        <h2 class="mb-0"><?php echo (int)$activeCount; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body">
                        <h5 class="card-title text-info">Male Employees</h5>
                        <h2 class="mb-0"><?php echo (int)$maleCount; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning">Female Employees</h5>
                        <h2 class="mb-0"><?php echo (int)$femaleCount; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-users"></i> Recent Employee Records</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Emp. PNO</th>
                                <th>Name (Father/Spouse)</th>
                                <th>Email</th>
                                <th>Cell No</th>
                                <th>Designation</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($employeeList && mysqli_num_rows($employeeList) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($employeeList)): ?>
                                    <?php $status = ($row['eServiceNature'] == 1) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['PNO']); ?></td>
                                        <td><?php echo htmlspecialchars($row['eName']); ?>/ <br> <?php echo htmlspecialchars($row['eFHName']); ?></td>
                                        <td><?php echo htmlspecialchars($row['EmailAdd']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ePhonCell']); ?></td>
                                        <td><?php echo htmlspecialchars($row['DesigBPS']); ?></td>
                                        <td><?php echo $status; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No employee records available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Designation-wise Summary -->
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="fas fa-list"></i> Summary by Designation</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" style="font-size:11px;">
                        <thead class="table-light">
                            <tr>
                                <th>Designation</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($designationSummary && mysqli_num_rows($designationSummary) > 0):
                                while ($d = mysqli_fetch_assoc($designationSummary)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($d['DesigName']); ?></td>
                                        <td class="text-end"><?php echo (int)$d['cnt']; ?></td>
                                    </tr>
                                <?php endwhile;
                            else: ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No designation data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th class="text-end"><?php echo (int)$totalCount; ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>