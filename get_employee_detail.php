<?php
require_once "auth.php";
ensure_authenticated_session();
require_url_authorization();
include "database.php";

if (isset($_GET['id'])) {
    $eid = intval($_GET['id']);
    $query = "SELECT * FROM employees WHERE eid = $eid";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $employee = mysqli_fetch_assoc($result);
        require_employee_access($conn, $employee['eCircOffi'] ?? null);
?>
        <style>
            .employee-details {
                font-size: 14px;
            }

            .employee-details .table {
                font-size: 12px;
                margin-bottom: 0;
            }

            .employee-details .table thead th {
                padding: 6px 8px;
                font-weight: 600;
                background-color: #2c3e50 !important;
                color: #ffffff !important;
                border: 1px solid #000 !important;
            }

            .employee-details table thead th {
                background-color: #2c3e50 !important;
                color: #ffffff !important;
            }

            .employee-details thead th {
                background-color: #2c3e50 !important;
                color: #ffffff !important;
            }

            .employee-details .table tbody td {
                padding: 5px 8px;
                white-space: nowrap;
            }

            .employee-details .table-responsive {
                max-width: 100%;
                overflow-x: auto;
            }

            .employee-details h6 {
                font-size: 13px;
                font-weight: 600;
                margin-top: 15px;
                margin-bottom: 10px;
                color: #ffffff !important;
                background-color: #2c3e50 !important;
                padding: 8px 10px !important;
                border-radius: 4px;
            }

            .employee-details .row {
                margin-bottom: 10px;
            }

            .print-button-container {
                margin-bottom: 15px;
                display: flex;
                gap: 10px;
            }

            @media print {
                .print-button-container {
                    display: none !important;
                }

                .employee-details {
                    font-size: 11px;
                }

                .employee-details .table {
                    font-size: 10px;
                    border: 1px solid #000;
                }

                .employee-details .table thead th,
                .employee-details .table tbody td {
                    border: 1px solid #000;
                    padding: 4px 6px;
                }

                .employee-details .table thead th {
                    background-color: #333 !important;
                    color: #ffffff !important;
                }

                .employee-details hr {
                    page-break-inside: avoid;
                }

                .employee-details .row {
                    page-break-inside: avoid;
                }

                .employee-details h6 {
                    color: #000 !important;
                    background-color: #e0e0e0 !important;
                    padding: 6px 8px !important;
                }

                body {
                    margin: 0;
                    padding: 10mm;
                }

                .badge {
                    border: 1px solid #000 !important;
                }
            }
        </style>
        <div class="print-button-container" id="printButtonContainer">
            <button class="btn btn-primary btn-sm" onclick="printEmployeeData()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn btn-secondary btn-sm" onclick="printEmployeeDataPDF()">
                <i class="fas fa-file-pdf"></i> Print as PDF
            </button>
        </div>
        <script>
            function printEmployeeData() {
                var printContents = document.querySelector('.employee-details').innerHTML;
                var printWindow = window.open('', '', 'height=800,width=1200');
                printWindow.document.write('<html><head><title>Employee Details - IRD-HRMS</title>');
                printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">');
                printWindow.document.write('<style>');
                printWindow.document.write('body { font-size: 11px; margin: 20px; }');
                printWindow.document.write('.table { border: 1px solid #000; }');
                printWindow.document.write('.table thead th { background-color: #2c3e50 !important; color: #ffffff !important; border: 1px solid #000; padding: 6px; font-weight: bold; }');
                printWindow.document.write('.table tbody td { border: 1px solid #000; padding: 4px; }');
                printWindow.document.write('h6 { font-size: 13px; font-weight: 600; margin-top: 15px; color: #ffffff; background-color: #2c3e50; padding: 6px 8px; }');
                printWindow.document.write('.table-responsive { overflow-x: auto; }');
                printWindow.document.write('</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write(printContents);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.print();
            }

            function printEmployeeDataPDF() {
                alert('PDF export feature coming soon. Please use Print option and select "Save as PDF" from your browser print dialog.');
                printEmployeeData();
            }

            function printEmployeeRecord() {
                var printContents = document.querySelector('.employee-details').innerHTML;
                var printWindow = window.open('', 'PrintWindow', 'height=900,width=1200,left=100,top=100');

                if (!printWindow) {
                    alert('Please disable your browser pop-up blocker to print this record.');
                    return;
                }

                var htmlContent = '<!DOCTYPE html>';
                htmlContent += '<html>';
                htmlContent += '<head>';
                htmlContent += '<meta charset="UTF-8">';
                htmlContent += '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
                htmlContent += '<title>Employee Record - IRD-HRMS</title>';
                htmlContent += '<style>';
                // A4 Page Setup
                htmlContent += '@page { size: A4; margin: 10mm; }';
                htmlContent += '@media print {';
                htmlContent += '  * { margin: 0; padding: 0; }';
                htmlContent += '  body { margin: 0; padding: 10mm; font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; }';
                htmlContent += '  .page-break { page-break-before: always; }';
                htmlContent += '  .no-break { page-break-inside: avoid; }';
                htmlContent += '  h6 { page-break-after: avoid; }';
                htmlContent += '  table { page-break-inside: avoid; }';
                htmlContent += '  tr { page-break-inside: avoid; }';
                htmlContent += '  .header { page-break-after: avoid; }';
                htmlContent += '}';
                // Screen View
                htmlContent += 'body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.4; background-color: #f5f5f5; padding: 20px; }';
                htmlContent += '.print-page { background-color: white; margin: 0 auto; padding: 15mm; width: 210mm; height: 297mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }';
                htmlContent += '.header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px; page-break-after: avoid; }';
                htmlContent += '.header h2 { margin: 0; font-size: 16px; font-weight: bold; }';
                htmlContent += '.header p { margin: 3px 0; font-size: 10px; }';
                htmlContent += '.header h3 { margin: 8px 0 0 0; font-size: 13px; font-weight: bold; }';
                htmlContent += 'h6 { font-weight: bold; margin: 12px 0 8px 0; padding: 5px 8px; background-color: #2c3e50; color: #fff; font-size: 12px; border-radius: 3px; page-break-after: avoid; }';
                htmlContent += 'p { margin: 2px 0; font-size: 10px; }';
                htmlContent += 'strong { font-weight: bold; }';
                htmlContent += '.info-section { margin-bottom: 10px; page-break-inside: avoid; }';
                htmlContent += '.table { margin: 8px 0; border-collapse: collapse; width: 100%; font-size: 10px; }';
                htmlContent += '.table thead th { background-color: #2c3e50 !important; color: #ffffff !important; border: 1px solid #000; padding: 5px 4px; font-weight: bold; text-align: left; }';
                htmlContent += '.table tbody td { border: 1px solid #ccc; padding: 4px; }';
                htmlContent += '.table tbody tr:nth-child(even) { background-color: #f9f9f9; }';
                htmlContent += 'hr { margin: 12px 0; border: none; border-top: 1px solid #ccc; page-break-after: avoid; }';
                htmlContent += '.row { margin-bottom: 10px; page-break-inside: avoid; }';
                htmlContent += '.col-md-6 { width: 50%; display: inline-block; vertical-align: top; }';
                htmlContent += '.table-responsive { width: 100%; overflow-x: visible; }';
                htmlContent += '.badge { border: 1px solid #666; padding: 2px 5px; font-size: 9px; }';
                htmlContent += '.print-footer { text-align: center; font-size: 9px; margin-top: 15px; padding-top: 8px; border-top: 1px solid #ccc; }';
                htmlContent += '</style>';
                htmlContent += '</head>';
                htmlContent += '<body>';
                htmlContent += '<div class="print-page">';
                htmlContent += '<div class="header">';
                htmlContent += '<h2>INLAND REVENUE DEPARTMENT, AJ&K</h2>';
                htmlContent += '<p>Human Resource Management System (IRD-HRMS)</p>';
                htmlContent += '<h3>Employee Record</h3>';
                htmlContent += '</div>';
                htmlContent += '<div class="info-section">';
                htmlContent += printContents;
                htmlContent += '</div>';
                htmlContent += '<div class="print-footer">';
                htmlContent += '<p>This is a computer-generated record. Printed on: ' + new Date().toLocaleString() + '</p>';
                htmlContent += '</div>';
                htmlContent += '</div>';
                htmlContent += '</body>';
                htmlContent += '</html>';

                printWindow.document.open();
                printWindow.document.write(htmlContent);
                printWindow.document.close();

                setTimeout(function() {
                    printWindow.focus();
                    printWindow.print();
                }, 250);
            }
        </script>
        <div class="employee-details">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($employee['eid']); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($employee['eName']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['EmailAdd']); ?></p>
                    <p><strong>Cell Phone:</strong> <?php echo htmlspecialchars($employee['ePhonCell']); ?></p>
                    <p><strong>Office Phone:</strong> <?php echo htmlspecialchars($employee['ePhonOffi']); ?></p>
                    <p><strong>Residence Phone:</strong> <?php echo htmlspecialchars($employee['ePhonResi']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Gender:</strong> <?php echo ($employee['Gender'] == 'M') ? 'Male' : 'Female'; ?></p>
                    <p><strong>CNIC:</strong> <?php echo htmlspecialchars($employee['CNIC']); ?></p>
                    <p><strong>Designation:</strong> <?php echo htmlspecialchars($employee['eDesigBMS']); ?></p>
                    <p><strong>Service Status:</strong> <?php echo ($employee['eServiceNature'] == 1) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?></p>
                    <p><strong>Date of Birth:</strong> <?php echo $employee['eDoB'] ? date('Y-m-d', strtotime($employee['eDoB'])) : 'N/A'; ?></p>
                    <p><strong>BPS:</strong> <?php echo htmlspecialchars($employee['eBPS']); ?></p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h6>Address Information</h6>
                </div>
                <div class="col-md-6">
                    <p><strong>Permanent Address:</strong> <?php echo htmlspecialchars($employee['Permanent_Add']); ?></p>
                    <p><strong>Present Address:</strong> <?php echo htmlspecialchars($employee['Present_Add']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Father's Name:</strong> <?php echo htmlspecialchars($employee['eFHName']); ?></p>
                    <p><strong>PNO:</strong> <?php echo htmlspecialchars($employee['PNO']); ?></p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h6>Qualifications</h6>
                    <?php
                    // Fetch employee qualifications with qualification details
                    $qual_query = "SELECT eq.EQID, eq.EQ_EMP_ID, eq.Institute, eq.CGPA_Grade_Div, eq.`Year of Passing` AS YearOfPassing, eq.`Major Subjects` AS MajorSubjects, q.Q_FName 
                                   FROM empqual eq 
                                   LEFT JOIN qualifications q ON eq.Emp_Qual = q.QID 
                                   WHERE eq.EQ_EMP_ID = $eid 
                                   ORDER BY eq.`Year of Passing` DESC";
                    $qual_result = mysqli_query($conn, $qual_query);

                    if ($qual_result && mysqli_num_rows($qual_result) > 0) {
                    ?>
                        <table class="table table-sm table-striped">
                            <thead class="table-light">
                                <tr bgcolor="#1d2e3f">
                                    <th>Qualification</th>
                                    <th>Institute</th>
                                    <th>CGPA/Grade/Division</th>
                                    <th>Year of Passing</th>
                                    <th>Major Subjects</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($qual = mysqli_fetch_assoc($qual_result)) {
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($qual['Q_FName'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($qual['Institute'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($qual['CGPA_Grade_Div'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($qual['YearOfPassing'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($qual['MajorSubjects'] ?? ''); ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    <?php
                    } else {
                        echo "<p class='text-muted'><em>No qualifications recorded</em></p>";
                    }
                    ?>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h6>Posting & Transfer History</h6>
                    <?php
                    // Fetch employee posting transfer history
                    $trans_query = "SELECT apt.APT_ID, apt.APT_EMP_ID, apt.PUNotiNo, apt.PUNotiDate, apt.APT_DateofJoin, 
                                           d1.DesigName AS OldDesignation, d2.DesigName AS NewDesignation, 
                                           apt.APT_Emp_BPS, apt.APT_NewEmp_BPS, 
                                           co1.COName AS OldCircle, co2.COName AS NewCircle,
                                           apt.APT_Count
                                    FROM appointposttrans apt
                                    LEFT JOIN designations d1 ON apt.APT_DesigID = d1.DID
                                    LEFT JOIN designations d2 ON apt.APT_NewDesigID = d2.DID
                                    LEFT JOIN circlesoffices co1 ON apt.APT_CircOffi = co1.COID
                                    LEFT JOIN circlesoffices co2 ON apt.APT_NewCircOffi = co2.COID
                                    WHERE apt.APT_EMP_ID = $eid
                                    ORDER BY apt.APT_Count ASC";
                    $trans_result = mysqli_query($conn, $trans_query);

                    if ($trans_result && mysqli_num_rows($trans_result) > 0) {
                    ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Old Circle/Office</th>
                                        <th>Old Designation</th>
                                        <th>Old BPS</th>
                                        <th>Notification No.</th>
                                        <th>Notification Date</th>
                                        <th>New Circle/Office</th>
                                        <th>New Designation</th>
                                        <th>Date of Join</th>
                                        <th>Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($trans = mysqli_fetch_assoc($trans_result)) {
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($trans['OldCircle'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($trans['OldDesignation'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($trans['APT_Emp_BPS'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($trans['PUNotiNo'] ?? 'N/A'); ?></td>
                                            <td><?php echo $trans['PUNotiDate'] ? date('Y-m-d', strtotime($trans['PUNotiDate'])) : 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($trans['NewCircle'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($trans['NewDesignation'] ?? 'N/A'); ?></td>
                                            <td><?php echo $trans['APT_DateofJoin'] ? date('Y-m-d', strtotime($trans['APT_DateofJoin'])) : 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($trans['APT_Count'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php
                    } else {
                        echo "<p class='text-muted'><em>No posting/transfer records found</em></p>";
                    }
                    ?>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h6>Promotion & Upgradation History</h6>
                    <?php
                    // Fetch employee promotion and upgradation history
                    $prom_query = "SELECT pu.PUID, pu.PU_EMP_ID, pu.PU_PUNotiNo, pu.PU_PUNotiDate, pu.PU_PUJoinDate,
                                          d1.DesigName AS PromotedDesignation, d1.DesigBS AS PromotedBPS,
                                          d2.DesigName AS AssumedDesignation, d2.DesigBS AS AssumedBPS,
                                          pu.PU_Count
                                    FROM promotupgrade pu
                                    LEFT JOIN designations d1 ON pu.PU_PromDesigID = d1.DID
                                    LEFT JOIN designations d2 ON pu.PU_PromDesigIDAs = d2.DID
                                    WHERE pu.PU_EMP_ID = $eid
                                    ORDER BY pu.PU_Count ASC";
                    $prom_result = mysqli_query($conn, $prom_query);

                    if ($prom_result && mysqli_num_rows($prom_result) > 0) {
                    ?>
                        <table class="table table-sm table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Present (Designation)</th>
                                    <th>Present BPS</th>
                                    <th>Assumed As (Designation)</th>
                                    <th>Assumed BPS</th>
                                    <th>Notification No.</th>
                                    <th>Notification Date</th>
                                    <th>Date of Join</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($prom = mysqli_fetch_assoc($prom_result)) {
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($prom['PromotedDesignation'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($prom['PromotedBPS'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($prom['AssumedDesignation'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($prom['AssumedBPS'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($prom['PU_PUNotiNo'] ?? 'N/A'); ?></td>
                                        <td><?php echo $prom['PU_PUNotiDate'] ? date('Y-m-d', strtotime($prom['PU_PUNotiDate'])) : 'N/A'; ?></td>
                                        <td><?php echo $prom['PU_PUJoinDate'] ? date('Y-m-d', strtotime($prom['PU_PUJoinDate'])) : 'N/A'; ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    <?php
                    } else {
                        echo "<p class='text-muted'><em>No promotion/upgradation records found</em></p>";
                    }
                    ?>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <!--                <div style="text-align: right; margin-top: 20px;">
                    <button class="btn btn-lg btn-primary" onclick="printEmployeeRecord()" style="padding: 10px 30px;">
                        <i class="fas fa-print"></i> Print Record
                    </button>
                </div> -->
            </div>
        </div> -->
<?php
    } else {
        echo "<div class='alert alert-warning'>Employee not found</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Invalid employee ID</div>";
}
?>