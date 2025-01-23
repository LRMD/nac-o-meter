<?php

// Configuration
define('MAILJET_API_KEY', 'your-api-key');
define('MAILJET_API_SECRET', 'your-api-secret');
define('RECIPIENT_EMAIL', 'lyac@qrz.lt');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $success = false;
    $logContent = '';
    $filename = '';
    
    // Count valid QSO records
    $validQsos = 0;
    if (!empty($_POST['qso_date'])) {
        foreach ($_POST['qso_date'] as $i => $date) {
            if (!empty($date) && !empty($_POST['qso_time'][$i]) && !empty($_POST['qso_call'][$i])) {
                $validQsos++;
            }
        }
    }

    // Add QSO validation to required fields check
    if ($validQsos === 0) {
        $errors[] = "At least one QSO record is required";
    }
    
    // Validate required fields
    $required = ['TName', 'TDate', 'PCall', 'PWWLo', 'PBand'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Field {$field} is required";
        }
    }
    
    if (empty($errors)) {
        // Generate REG1TEST file content
        $logContent = "[REG1TEST;1]\r\n";
        $logContent .= "TName=" . $_POST['TName'] . "\r\n";
        $logContent .= "TDate=" . date('Ymd', strtotime($_POST['TDate'])) . ";" . 
                       date('Ymd', strtotime($_POST['TDate'])) . "\r\n";
        $logContent .= "PCall=" . strtoupper($_POST['PCall']) . "\r\n";
        $logContent .= "PWWLo=" . strtoupper($_POST['PWWLo']) . "\r\n";
        $logContent .= "PBand=" . $_POST['PBand'] . "\r\n";
        $logContent .= "PSect=SINGLE\r\n";
        if (!empty($_POST['RCall'])) {
            $logContent .= "RCall=" . strtoupper($_POST['RCall']) . "\r\n";
        }
        $logContent .= "[Remarks]\r\n";
        $logContent .= $_POST['Remarks'] ?? '' . "\r\n";
        $logContent .= "\r\n[QSORecords;" . $validQsos . "]\r\n";

        // Add QSO records
        if (!empty($_POST['qso_date'])) {
            foreach ($_POST['qso_date'] as $i => $date) {
                if (!empty($date) && !empty($_POST['qso_time'][$i]) && !empty($_POST['qso_call'][$i])) {
                    $logContent .= sprintf("%s;%s;%s;%s;%s;;%s;;;%s;7;;;;\r\n",
                        date('ymd', strtotime($_POST['qso_date'][$i])),
                        str_replace(':', '', $_POST['qso_time'][$i]),
                        $_POST['qso_call'][$i],
                        $_POST['qso_mode'][$i] === 'CW' ? '2' : 
                            ($_POST['qso_mode'][$i] === 'SSB' ? '1' : 
                            ($_POST['qso_mode'][$i] === 'FM' ? '6' : '7')),
                        $_POST['qso_sent'][$i],
                        $_POST['qso_rcvd'][$i],
                        $_POST['qso_wwl'][$i]
                    );
                }
            }
        }

        // Generate filename
        $filename = sprintf('%s_%s_%s.edi', 
            strtoupper($_POST['PCall']),
            date('Ymd', strtotime($_POST['TDate'])),
            str_replace(' ', '', $_POST['PBand'])
        );

        // Attempt to send email
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "noreply@logs.cqcq.lt",
                        'Name' => $_POST['PCall']
                    ],
                    'To' => [
                        [
                            'Email' => RECIPIENT_EMAIL,
                            'Name' => "LYAC Robot"
                        ]
                    ],
                    'Subject' => $_POST['PCall'] . ' ' . $_POST['TDate'],
                    'TextPart' => $logContent,
                    'Attachments' => [
                        [
                            'ContentType' => 'text/plain',
                            'Filename' => $filename,
                            'Base64Content' => base64_encode($logContent)
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init('https://api.mailjet.com/v3.1/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, MAILJET_API_KEY . ':' . MAILJET_API_SECRET);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $success = ($httpCode === 200);
        
        if (!$success) {
            $errors[] = "Failed to send email. HTTP Code: " . $httpCode;
            if ($response) {
                $responseData = json_decode($response, true);
                if (isset($responseData['ErrorMessage'])) {
                    $errors[] = $responseData['ErrorMessage'];
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>REG1TEST Log Submission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { padding: 20px; }
        .qso-row { margin-bottom: 10px; }
        .language { margin-bottom: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 5px; }
        .optional-fields { display: none; }
        .qso-header { font-weight: bold; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="language">
            <div class="lt">
                <h4>🇱🇹 LYAC žurnalo pateikimo forma</h4>
                <p>Ši paslauga leidžia tiesiogiai pateikti LYAC žurnalą per naršyklę. Užpildykite reikiamus laukus ir sistema automatiškai sugeneruos REG1TEST formato failą bei išsiųs jį į LYAC robotą.</p>
            </div>
            <div class="en mt-3">
                <h4>🇬🇧 LYAC Log Submission Form</h4>
                <p>This service allows direct submission of LYAC logs through your browser. Fill in the required fields and the system will automatically generate a REG1TEST format file and send it to the LYAC robot.</p>
            </div>
            <div class="uk mt-3">
                <h4>🇺🇦 Форма подання журналу LYAC</h4>
                <p>Цей сервіс дозволяє безпосередньо подавати журнали LYAC через браузер. Заповніть необхідні поля, і система автоматично згенерує файл формату REG1TEST та надішле його роботу LYAC.</p>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                Log file was successfully sent to LYAC Robot as <?php echo htmlspecialchars($filename); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($logContent)): ?>
            <div class="card mt-3">
                <div class="card-header">Generated REG1TEST File Contents</div>
                <div class="card-body">
                    <pre class="font-monospace" style="white-space: pre-wrap;"><?php echo htmlspecialchars($logContent); ?></pre>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" id="logForm">
            <div class="row mb-3">
                <div class="col">
                    <label>TName</label>
                    <select name="TName" class="form-control" required>
                        <option value="NAC/LYAC 144 MHz">144 MHz</option>
                        <option value="NAC/LYAC 432 MHz">432 MHz</option>
                        <option value="NAC/LYAC 1296 MHz">1296 MHz</option>
                    </select>
                </div>
                <div class="col">
                    <label>TDate</label>
                    <input type="date" name="TDate" class="form-control" required 
                           value="<?php echo date('Y-m-d'); ?>"
                           placeholder="Contest date (Tuesday)">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label>PCall</label>
                    <input type="text" name="PCall" class="form-control" required 
                           pattern="[A-Za-z0-9/]+" 
                           placeholder="Your callsign"
                           title="Valid callsign required">
                </div>
                <div class="col">
                    <label>PWWLo</label>
                    <input type="text" name="PWWLo" class="form-control" required 
                           pattern="[A-Ra-r][A-Ra-r][0-9][0-9][A-Xa-x][A-Xa-x]" 
                           placeholder="Your WWL locator (e.g. KO24AA)"
                           title="Valid WWL locator required">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label>PBand</label>
                    <input type="text" name="PBand" class="form-control" readonly>
                </div>
            </div>

            <div class="mb-3">
                <button type="button" class="btn btn-secondary" id="toggleOptional">Show/Hide Optional Fields</button>
            </div>

            <div class="optional-fields">
                <div class="row mb-3">
                    <div class="col">
                        <label>RCall</label>
                        <input type="text" name="RCall" class="form-control" placeholder="Contest manager callsign">
                    </div>
                    <div class="col">
                        <label>PClub</label>
                        <input type="text" name="PClub" class="form-control" placeholder="Club name">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label>RAdr1</label>
                        <input type="text" name="RAdr1" class="form-control" placeholder="Address line 1">
                    </div>
                    <div class="col">
                        <label>RAdr2</label>
                        <input type="text" name="RAdr2" class="form-control" placeholder="Address line 2">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label>RPoCo</label>
                        <input type="text" name="RPoCo" class="form-control" placeholder="Postal code">
                    </div>
                    <div class="col">
                        <label>RCity</label>
                        <input type="text" name="RCity" class="form-control" placeholder="City">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label>RCoun</label>
                        <input type="text" name="RCoun" class="form-control" placeholder="Country">
                    </div>
                    <div class="col">
                        <label>RPhon</label>
                        <input type="text" name="RPhon" class="form-control" placeholder="Phone number">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label>RHBBS</label>
                        <input type="text" name="RHBBS" class="form-control" placeholder="BBS">
                    </div>
                    <div class="col">
                        <label>MOpe1</label>
                        <input type="text" name="MOpe1" class="form-control" placeholder="Main operator callsign">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label>MOpe2</label>
                        <input type="text" name="MOpe2" class="form-control" placeholder="Second operator callsign">
                    </div>
                    <div class="col">
                        <label>STXEq</label>
                        <input type="text" name="STXEq" class="form-control" placeholder="Transmitter equipment">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label>SPowe</label>
                        <input type="text" name="SPowe" class="form-control" placeholder="Power">
                    </div>
                    <div class="col">
                        <label>SRXEq</label>
                        <input type="text" name="SRXEq" class="form-control" placeholder="Receiver equipment">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label>SAnte</label>
                        <input type="text" name="SAnte" class="form-control" placeholder="Antenna (e.g. 35W/IC-7000/13x el DK7ZB)">
                    </div>
                    <div class="col">
                        <label>SAntH</label>
                        <input type="text" name="SAntH" class="form-control" placeholder="Antenna height">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label>Remarks</label>
                <textarea name="Remarks" class="form-control" placeholder="Additional comments"></textarea>
            </div>

            <h4>QSO Records</h4>
            <div id="qsoContainer">
                <div class="qso-header row">
                    <div class="col">Date</div>
                    <div class="col">Time</div>
                    <div class="col">Call</div>
                    <div class="col">Mode</div>
                    <div class="col">Sent</div>
                    <div class="col">Rcvd</div>
                    <div class="col">WWL</div>
                </div>
                <div class="qso-row row">
                    <div class="col">
                        <input type="date" name="qso_date[]" class="form-control" 
                               value="<?php echo date('Y-m-d'); ?>" readonly>
                    </div>
                    <div class="col">
                        <input type="time" name="qso_time[]" class="form-control" 
                               value="<?php echo gmdate('H:i'); ?>" step="60">
                    </div>
                    <div class="col"><input type="text" name="qso_call[]" class="form-control" placeholder="Callsign"></div>
                    <div class="col">
                        <select name="qso_mode[]" class="form-control">
                            <option value="CW">CW</option>
                            <option value="SSB">SSB</option>
                            <option value="FM">FM</option>
                        </select>
                    </div>
                    <div class="col"><input type="text" name="qso_sent[]" class="form-control" placeholder="599"></div>
                    <div class="col"><input type="text" name="qso_rcvd[]" class="form-control" placeholder="599"></div>
                    <div class="col"><input type="text" name="qso_wwl[]" class="form-control" 
                                          pattern="[A-Ra-r][A-Ra-r][0-9][0-9][A-Xa-x][A-Xa-x]"
                                          placeholder="KO24AA"></div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Submit Log</button>
        </form>
    </div>

    <footer class="container mt-5 mb-3 text-center text-muted">
        <hr>
        <p>Created by Simonas Kareiva LY2EN | Contact: <a href="mailto:ly2en@qrz.lt">ly2en@qrz.lt</a></p>
        <p>Automated email delivery is a paid service and is not guaranteed to work.</p>
    </footer>

    <script>
        $(document).ready(function() {
            // Load saved values from localStorage
            const savedPCall = localStorage.getItem('PCall');
            const savedPWWLo = localStorage.getItem('PWWLo');
            
            if (savedPCall) {
                $('input[name="PCall"]').val(savedPCall);
            }
            if (savedPWWLo) {
                $('input[name="PWWLo"]').val(savedPWWLo);
            }

            // Save PCall and PWWLo to localStorage when changed
            $('input[name="PCall"]').change(function() {
                localStorage.setItem('PCall', $(this).val());
            });
            
            $('input[name="PWWLo"]').change(function() {
                localStorage.setItem('PWWLo', $(this).val());
            });

            // Toggle optional fields
            $('#toggleOptional').click(function() {
                $('.optional-fields').toggle();
            });

            // Update PBand based on TName selection
            $('select[name="TName"]').change(function() {
                const band = $(this).val().match(/\d+/)[0];
                $('input[name="PBand"]').val(band + ' MHz');
            }).trigger('change');

            // Validate Tuesday date
            $('input[name="TDate"]').change(function() {
                const date = new Date($(this).val());
                if (date.getDay() !== 2) { // 2 is Tuesday
                    alert('The contest date must be a Tuesday');
                    $(this).val(''); // Clear invalid date
                }
            });

            // Add new QSO row when last row is being filled
            $('#qsoContainer').on('input', '.qso-row:last-child input', function() {
                if ($(this).val().length > 0) {
                    const newRow = $('.qso-row:first').clone();
                    const currentDate = $('input[name="TDate"]').val();
                    newRow.find('input[name="qso_date[]"]').val(currentDate);
                    
                    // Set current UTC time without seconds
                    const now = new Date();
                    const utcHours = String(now.getUTCHours()).padStart(2, '0');
                    const utcMinutes = String(now.getUTCMinutes()).padStart(2, '0');
                    newRow.find('input[name="qso_time[]"]').val(`${utcHours}:${utcMinutes}`);
                    
                    // Reset other fields except date and time
                    newRow.find('input:not([name="qso_date[]"]):not([name="qso_time[]"])').val('');
                    
                    // Set default signal reports based on mode
                    const mode = newRow.find('select[name="qso_mode[]"]').val();
                    const value = (mode === 'CW') ? '599' : '59';
                    newRow.find('input[name="qso_sent[]"]').val(value);
                    newRow.find('input[name="qso_rcvd[]"]').val(value);
                    
                    $('#qsoContainer').append(newRow);
                }
            });

            // Update signal reports when mode changes
            $('#qsoContainer').on('change', 'select[name="qso_mode[]"]', function() {
                const row = $(this).closest('.qso-row');
                const value = ($(this).val() === 'CW') ? '599' : '59';
                row.find('input[name="qso_sent[]"]').val(value);
                row.find('input[name="qso_rcvd[]"]').val(value);
            });

            // Initialize first row with current UTC time
            const now = new Date();
            const utcHours = String(now.getUTCHours()).padStart(2, '0');
            const utcMinutes = String(now.getUTCMinutes()).padStart(2, '0');
            $('input[name="qso_time[]"]').first().val(`${utcHours}:${utcMinutes}`);
            
            // Set initial signal reports based on mode
            const initialMode = $('select[name="qso_mode[]"]').first().val();
            const initialValue = (initialMode === 'CW') ? '599' : '59';
            $('input[name="qso_sent[]"]').first().val(initialValue);
            $('input[name="qso_rcvd[]"]').first().val(initialValue);

            // Form validation
            $('#logForm').submit(function(e) {
                const pcall = $('input[name="PCall"]').val();
                const pwwlo = $('input[name="PWWLo"]').val();
                let validQsos = 0;
                let qsosWithoutWWL = [];
                
                if (!pcall.match(/^[A-Z0-9/]+$/i)) {
                    alert('Invalid callsign format');
                    e.preventDefault();
                    return;
                }
                
                if (!pwwlo.match(/^[A-R][A-R][0-9][0-9][A-X][A-X]$/i)) {
                    alert('Invalid WWL locator format');
                    e.preventDefault();
                    return;
                }
                
                // Validate QSO entries
                $('.qso-row').each(function() {
                    const date = $(this).find('input[name="qso_date[]"]').val();
                    const time = $(this).find('input[name="qso_time[]"]').val();
                    const call = $(this).find('input[name="qso_call[]"]').val();
                    const wwl = $(this).find('input[name="qso_wwl[]"]').val();
                    
                    // Skip incomplete records
                    if (!date || !time || !call) {
                        return true; // continue to next iteration
                    }

                    // Check for missing WWL
                    if (!wwl && call) {
                        qsosWithoutWWL.push(call);
                    }

                    // Validate WWL if provided
                    if (wwl && !wwl.match(/^[A-R][A-R][0-9][0-9][A-X][A-X]$/i)) {
                        alert('Invalid WWL format in QSO record');
                        e.preventDefault();
                        return false; // break the loop
                    }

                    validQsos++;
                });

                // Check if we have at least one valid QSO
                if (validQsos === 0) {
                    alert('At least one complete QSO record is required');
                    e.preventDefault();
                    return;
                }

                // Ask for confirmation if there are QSOs without WWL
                if (qsosWithoutWWL.length > 0) {
                    const confirmMessage = qsosWithoutWWL.map(call => 
                        `Are you sure to submit qso for ${call} without WWL?`
                    ).join('\n');
                    
                    if (!confirm(confirmMessage)) {
                        e.preventDefault();
                        return;
                    }
                }
            });
        });
    </script>
</body>
</html> 