<?php
session_start();

// Check if user is logged in and is a security guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Security Guard') {
    header('Location: ../index.php');
    exit();
}

// Load visitor data from correct files
$scanned_visitors_json = file_get_contents('../json/scannedVisitors.json');
$scanned_visitors_data = json_decode($scanned_visitors_json, true)['scannedVisitors'];

$pending_visitors_json = file_get_contents('../json/pendingVisitors.json');
$pending_visitors_data = json_decode($pending_visitors_json, true)['pendingVisitors'];

// Load security guard data
$guards_json = file_get_contents('../json/securityGuard.json');
$guards_data = json_decode($guards_json, true)['SecurityGuards'];

// Find current guard's data
$current_guard = null;
foreach ($guards_data as $guard) {
    if ($guard['Username'] === $_SESSION['username']) {
        $current_guard = $guard;
        break;
    }
}

// Get current time for active/inactive separation
$current_time = date('H:i');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Guard Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #64748b;
            --success: #22c55e;
            --background: #f8fafc;
            --surface: #ffffff;
        }

        body {
            background: var(--background);
            min-height: 100vh;
            padding-bottom: 80px;
        }

        .navbar {
            background: var(--surface);
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
        }

        .search-container {
            max-width: 500px;
            margin: 2rem auto;
        }

        .visitor-card {
            background: var(--surface);
            border-radius: 8px;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .visitor-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .scan-qr-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 30px;
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            border: none;
            transition: transform 0.2s ease;
        }

        .scan-qr-btn:hover {
            transform: scale(1.05);
        }

        .profile-dropdown {
            cursor: pointer;
        }

        .badge-active {
            background-color: var(--success);
            color: white;
        }

        #visitorModal .modal-header {
            background: var(--primary);
            color: white;
        }

        .search-highlight {
            background-color: yellow;
        }

        .scan-qr-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--bs-primary);
            color: white;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s, box-shadow 0.2s;
            z-index: 1000;
        }

        .scan-qr-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .visitor-card {
            background-color: white;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: box-shadow 0.2s;
        }

        .visitor-card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .badge {
            padding: 0.5em 1em;
            border-radius: 20px;
        }

        .search-highlight {
            background-color: yellow;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-shield-alt text-primary me-2"></i>
                Security Dashboard
            </a>

            <div class="dropdown">
                <div class="d-flex align-items-center profile-dropdown" data-bs-toggle="dropdown">
                    <span
                        class="me-2"><?php echo htmlspecialchars($current_guard['First Name'] . ' ' . $current_guard['Last Name']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">
                            <i class="fas fa-user me-2"></i>Profile
                        </a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Search Bar -->
        <div class="search-container">
            <div class="input-group mb-4">
                <span class="input-group-text border-end-0 bg-white">
                    <i class="fas fa-search text-secondary"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0"
                    placeholder="Search visitors by name, ID, room number, or reason...">
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" id="active-tab" data-bs-toggle="tab" href="#active">Active Visitors</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="past-tab" data-bs-toggle="tab" href="#past">Past Visitors</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Active Visitors Tab Content -->
            <div class="tab-pane fade show active" id="active">
                <div class="row g-4" id="activeVisitorsGrid">
                    <?php foreach ($pending_visitors_data as $visitor): ?>
                        <div class="col-12 col-md-6 col-lg-4 visitor-item">
                            <div class="visitor-card p-3" data-visitor='<?php echo json_encode($visitor); ?>'>
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1">
                                            <?php echo htmlspecialchars($visitor['First Name'] . ' ' . $visitor['Last Name']); ?>
                                        </h5>
                                        <p class="text-secondary mb-2">ID: <?php echo htmlspecialchars($visitor['ID']); ?>
                                        </p>
                                        <p class="text-muted mb-2"><i
                                                class="fas fa-info-circle me-1"></i><?php echo htmlspecialchars($visitor['Reason']); ?>
                                        </p>
                                    </div>
                                    <span class="badge bg-success">Active</span>
                                </div>
                                <div class="d-flex justify-content-between text-secondary">
                                    <small><i class="fas fa-door-open me-1"></i>Room
                                        #<?php echo htmlspecialchars($visitor['Room #']); ?></small>
                                    <small><i
                                            class="fas fa-calendar me-1"></i><?php echo htmlspecialchars($visitor['Date In']); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Past Visitors Tab Content -->
            <div class="tab-pane fade" id="past">
                <div class="row g-4" id="pastVisitorsGrid">
                    <?php foreach ($scanned_visitors_data as $visitor): ?>
                        <div class="col-12 col-md-6 col-lg-4 visitor-item">
                            <div class="visitor-card p-3" data-visitor='<?php echo json_encode($visitor); ?>'>
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1">
                                            <?php echo htmlspecialchars($visitor['First Name'] . ' ' . $visitor['Last Name']); ?>
                                        </h5>
                                        <p class="text-secondary mb-2">ID: <?php echo htmlspecialchars($visitor['ID']); ?>
                                        </p>
                                        <p class="text-muted mb-2"><i
                                                class="fas fa-info-circle me-1"></i><?php echo htmlspecialchars($visitor['Reason']); ?>
                                        </p>
                                    </div>
                                    <span class="badge bg-secondary">Inactive</span>
                                </div>
                                <div class="text-secondary">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small><i class="fas fa-door-open me-1"></i>Room
                                            #<?php echo htmlspecialchars($visitor['Room #']); ?></small>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small><i
                                                class="fas fa-calendar me-1"></i><?php echo htmlspecialchars($visitor['Date In']); ?></small>
                                        <small><i
                                                class="fas fa-clock me-1"></i><?php echo htmlspecialchars($visitor['Time QR Scanned In']); ?>
                                            - <?php echo htmlspecialchars($visitor['Leave Time']); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- QR Scan Button -->
            <button class="scan-qr-btn" data-bs-toggle="modal" data-bs-target="#qrScannerModal">
                <i class="fas fa-qrcode"></i>
            </button>

            <div class="modal fade" id="profileModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title text-center">Profile Information</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <i class="fas fa-user-circle fa-4x text-secondary"></i>
                                <h4 class="mt-2">
                                    <?php echo htmlspecialchars($current_guard['First Name'] . ' ' . $current_guard['Last Name']); ?>
                                </h4>
                                <p class="text-secondary">Security Guard -
                                    <?php echo htmlspecialchars($current_guard['Shift']); ?> Shift</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <p class="mb-1 text-secondary">Guard ID</p>
                                    <p class="fw-bold"><?php echo htmlspecialchars($current_guard['ID']); ?></p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1 text-secondary">Room</p>
                                    <p class="fw-bold"><?php echo htmlspecialchars($current_guard['Room #']); ?></p>
                                </div>
                                <div class="col-12">
                                    <p class="mb-1 text-secondary">Email</p>
                                    <p class="fw-bold"><?php echo htmlspecialchars($current_guard['Email']); ?></p>
                                </div>
                                <div class="col-12">
                                    <p class="mb-1 text-secondary">Phone</p>
                                    <p class="fw-bold"><?php echo htmlspecialchars($current_guard['Phone #']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visitor Details Modal -->
            <div class="modal fade" id="visitorModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Visitor Details</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="visitorDetails">
                            <!-- Visitor details will be populated here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR Scanner Modal -->
            <div class="modal fade" id="qrScannerModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Scan QR Code</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div id="qr-reader" class="mb-3"></div>
                            <p class="text-secondary">Position the QR code within the frame to scan</p>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
            <script>
                // Search functionality
                document.getElementById('searchInput').addEventListener('input', function (e) {
                    const searchTerm = e.target.value.toLowerCase();

                    // Function to highlight matching text
                    const highlightMatch = (text, term) => {
                        if (!term) return text;
                        const regex = new RegExp(`(${term})`, 'gi');
                        return text.replace(regex, '<span class="search-highlight">$1</span>');
                    };

                    // Function to handle search within a visitor grid
                    const searchVisitorGrid = (gridId) => {
                        const visitors = document.querySelector(gridId).getElementsByClassName('visitor-item');

                        Array.from(visitors).forEach(visitor => {
                            const visitorCard = visitor.querySelector('.visitor-card');
                            const visitorData = JSON.parse(visitorCard.dataset.visitor);

                            // Create search string from visitor data
                            const searchString = `${visitorData['First Name']} ${visitorData['Last Name']} ${visitorData['ID']} ${visitorData['Room #']} ${visitorData['Reason']}`.toLowerCase();

                            // Check if the search term matches the visitor data
                            const matches = searchString.includes(searchTerm);

                            // Show/hide based on match
                            visitor.style.display = matches ? '' : 'none';

                            // Update the visible text with highlights if there's a match
                            if (matches && searchTerm) {
                                const nameElement = visitorCard.querySelector('h5');
                                const idElement = visitorCard.querySelector('p.text-secondary');
                                const reasonElement = visitorCard.querySelector('p.text-muted');

                                // Update text with highlights
                                nameElement.innerHTML = highlightMatch(`${visitorData['First Name']} ${visitorData['Last Name']}`, searchTerm);
                                idElement.innerHTML = highlightMatch(`ID: ${visitorData['ID']}`, searchTerm);
                                reasonElement.innerHTML = `<i class="fas fa-info-circle me-1"></i>${highlightMatch(visitorData['Reason'], searchTerm)}`;
                            }
                        });
                    };

                    // Search in both active and past visitors
                    searchVisitorGrid('#activeVisitorsGrid');
                    searchVisitorGrid('#pastVisitorsGrid');
                });

                // Restore original text when search is cleared
                document.getElementById('searchInput').addEventListener('change', function (e) {
                    if (!e.target.value) {
                        document.querySelectorAll('.visitor-item').forEach(visitor => {
                            visitor.style.display = '';
                            const visitorData = JSON.parse(visitor.querySelector('.visitor-card').dataset.visitor);
                            const nameElement = visitor.querySelector('h5');
                            const idElement = visitor.querySelector('p.text-secondary');
                            const reasonElement = visitor.querySelector('p.text-muted');

                            nameElement.textContent = `${visitorData['First Name']} ${visitorData['Last Name']}`;
                            idElement.textContent = `ID: ${visitorData['ID']}`;
                            reasonElement.innerHTML = `<i class="fas fa-info-circle me-1"></i>${visitorData['Reason']}`;
                        });
                    }
                });
                // Visitor card click handler
                document.querySelectorAll('.visitor-card').forEach(card => {
                    card.addEventListener('click', function () {
                        const visitor = JSON.parse(this.dataset.visitor);
                        const detailsHtml = `
            <div class="d-flex align-items-center mb-4">
                <i class="fas fa-user-circle fa-3x text-secondary me-3"></i>
                <div>
                    <h4 class="mb-1">${visitor['First Name']} ${visitor['Last Name']}</h4>
                    <p class="text-secondary mb-0">ID: ${visitor['ID']}</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <p class="mb-1 text-secondary">Room Number</p>
                    <p class="fw-bold">${visitor['Room #']}</p>
                </div>
                <div class="col-6">
                    <p class="mb-1 text-secondary">Phone</p>
                    <p class="fw-bold">${visitor['Phone #']}</p>
                </div>
                <div class="col-12">
                    <p class="mb-1 text-secondary">Email</p>
                    <p class="fw-bold">${visitor['Email']}</p>
                </div>
                <div class="col-6">
                    <p class="mb-1 text-secondary">Vehicle</p>
                    <p class="fw-bold">${visitor['Vehicle']}</p>
                </div>
                <div class="col-6">
                    <p class="mb-1 text-secondary">Vehicle Plate</p>
                    <p class="fw-bold">${visitor['Vehicle'] === 'Yes' ? visitor['Vehicle Plate'] : '-'}</p>
                </div>
                <div class="col-12">
                    <p class="mb-1 text-secondary">Reason for Visit</p>
                    <p class="fw-bold">${visitor['Reason']}</p>
                </div>
                <div class="col-6">
                    <p class="mb-1 text-secondary">Date In</p>
                    <p class="fw-bold">${visitor['Date In']}</p>
                </div>
                <div class="col-6">
                    <p class="mb-1 text-secondary">Date Out</p>
                    <p class="fw-bold">${visitor['Date Out'] || '-'}</p>
                </div>
                <div class="col-12">
                    <p class="mb-1 text-secondary">QR Code ID</p>
                    <p class="fw-bold">${visitor['QR Code ID']}</p>
                </div>
                <div class="col-6">
                    <p class="mb-1 text-secondary">Time Scanned In</p>
                    <p class="fw-bold">${visitor['Time QR Scanned In'] || '-'}</p>
                </div>
                <div class="col-6">
                    <p class="mb-1 text-secondary">Leave Time</p>
                    <p class="fw-bold">${visitor['Leave Time'] || '-'}</p>
                </div>
            </div>
        `;

                        document.getElementById('visitorDetails').innerHTML = detailsHtml;
                        new bootstrap.Modal(document.getElementById('visitorModal')).show();
                    });
                });

                // Function to start QR scanning
                // QR Scanner initialization and data management
                let html5QrcodeScanner = null;
                let pendingVisitors = null;
                let scannedOnceVisitors = null;
                let scannedVisitors = null;

                // Fetch all necessary data when the page loads
                Promise.all([
                    fetch('../json/pendingVisitors.json').then(response => response.json()),
                    fetch('../json/scannedOnce.json').then(response => response.json()).catch(() => ({ scannedOnceVisitors: [] })),
                    fetch('../json/scannedVisitors.json').then(response => response.json()).catch(() => ({ scannedVisitors: [] }))
                ])
                    .then(([pendingData, scannedOnceData, scannedData]) => {
                        pendingVisitors = pendingData.pendingVisitors;
                        scannedOnceVisitors = scannedOnceData.scannedOnceVisitors || [];
                        scannedVisitors = scannedData.scannedVisitors || [];
                    })
                    .catch(error => console.error('Error loading data:', error));

                // Function to get current time in HH:MM format
                function getCurrentTime() {
                    const now = new Date();
                    return now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
                }

                // Function to find visitor by QR code
                function findVisitorByQRCode(qrCodeId) {
                    // Check scanned visitors first
                    const fullyScanned = scannedVisitors.find(visitor => visitor['QR Code ID'] === qrCodeId);
                    if (fullyScanned) {
                        return { visitor: fullyScanned, status: 'completed' };
                    }

                    // Check pending visitors
                    const pending = pendingVisitors.find(visitor => visitor['QR Code ID'] === qrCodeId);
                    if (pending) {
                        if (pending['Time QR Scanned In']) {
                            // Already checked in, needs checkout
                            return { visitor: pending, status: 'checkout' };
                        } else {
                            // First scan
                            return { visitor: pending, status: 'checkin' };
                        }
                    }

                    return null;
                }

                // Function to handle first-time scan
                function handleFirstScan(visitor) {
                    const currentTime = getCurrentTime();
                    const visitorWithTime = {
                        ...visitor,
                        'Time QR Scanned In': currentTime
                    };

                    // Save to pendingVisitors.json with scan time
                    fetch('../php/saveFirstScan.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            visitor: visitorWithTime
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update local pending visitors data
                                const visitorIndex = pendingVisitors.findIndex(v => v['QR Code ID'] === visitor['QR Code ID']);
                                if (visitorIndex !== -1) {
                                    pendingVisitors[visitorIndex] = visitorWithTime;
                                }
                                displayVisitorInQRModal(visitorWithTime, 'checkin');
                            } else {
                                alert('Error saving scan data. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error saving scan data. Please try again.');
                        });
                }

                // Function to handle checkout scan
                function handleCheckoutScan(visitor) {
                    const currentTime = getCurrentTime();
                    const visitorWithCheckout = {
                        ...visitor,
                        'Leave Time': currentTime
                    };

                    // Move to scannedVisitors.json
                    fetch('../php/saveCheckout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            visitor: visitorWithCheckout
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update local data
                                scannedVisitors.push(visitorWithCheckout);
                                pendingVisitors = pendingVisitors.filter(v => v['QR Code ID'] !== visitor['QR Code ID']);
                                displayVisitorInQRModal(visitorWithCheckout, 'checkout');
                            } else {
                                alert('Error saving checkout data. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error saving checkout data. Please try again.');
                        });
                }

                // Function to start QR scanning
                function startScanning() {
                    const modalBody = document.querySelector('#qrScannerModal .modal-body');
                    modalBody.innerHTML = `
        <div id="qr-reader" class="mb-3"></div>
        <div id="raw-qr-value" class="alert alert-info mb-3" style="display: none;">
            <strong>Raw QR Code:</strong> <span id="qr-content"></span>
        </div>
        <p class="text-secondary">Position the QR code within the frame to scan</p>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    `;

                    html5QrcodeScanner = new Html5Qrcode("qr-reader");
                    html5QrcodeScanner.start(
                        { facingMode: "environment" },
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 }
                        },
                        (decodedText, decodedResult) => {
                            // Display the raw QR code value
                            const rawValueDiv = document.getElementById('raw-qr-value');
                            const qrContent = document.getElementById('qr-content');
                            rawValueDiv.style.display = 'block';
                            qrContent.textContent = decodedText;

                            // Existing logic to process the QR code
                            const result = findVisitorByQRCode(decodedText);
                            if (result) {
                                html5QrcodeScanner.stop();
                                switch (result.status) {
                                    case 'checkin':
                                        handleFirstScan(result.visitor);
                                        break;
                                    case 'checkout':
                                        handleCheckoutScan(result.visitor);
                                        break;
                                    case 'completed':
                                        displayVisitorInQRModal(result.visitor, 'completed');
                                        break;
                                }
                            } else {
                                // Update the error message in the UI
                                rawValueDiv.className = 'alert alert-danger mb-3';
                                qrContent.textContent = `Invalid QR Code: ${decodedText}`;
                            }
                        },
                        (error) => {
                            // Handle scan error silently
                        }
                    ).catch((err) => {
                        console.error(`Unable to start scanning: ${err}`);
                        // Display scanner error in the UI
                        const modalBody = document.querySelector('#qrScannerModal .modal-body');
                        modalBody.innerHTML = `
            <div class="alert alert-danger">
                Failed to start scanner: ${err.message || 'Unknown error'}
            </div>
            <button class="btn btn-primary" onclick="startScanning()">Try Again</button>
            <button class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancel</button>
        `;
                    });
                }

                // Update the displayVisitorInQRModal function to include the raw QR code
                function displayVisitorInQRModal(visitor, scanStatus) {
                    let statusMessage = '';
                    let statusClass = '';

                    switch (scanStatus) {
                        case 'checkin':
                            statusMessage = `Checked In at ${visitor['Time QR Scanned In']}`;
                            statusClass = 'text-success';
                            break;
                        case 'checkout':
                            statusMessage = `Checked Out at ${visitor['Leave Time']}`;
                            statusClass = 'text-danger';
                            break;
                        case 'completed':
                            statusMessage = `Previous visit: In: ${visitor['Time QR Scanned In']} - Out: ${visitor['Leave Time']}`;
                            statusClass = 'text-secondary';
                            break;
                    }

                    const visitorHtml = `
        <div class="d-flex align-items-center mb-4">
            <i class="fas fa-user-circle fa-3x text-secondary me-3"></i>
            <div>
                <h4 class="mb-1">${visitor['First Name']} ${visitor['Last Name']}</h4>
                <p class="text-secondary mb-0">ID: ${visitor['ID']}</p>
                <p class="mb-0 ${statusClass}"><i class="fas fa-clock me-2"></i>${statusMessage}</p>
            </div>
        </div>
        <div class="alert alert-info mb-3">
            <strong>QR Code ID:</strong> ${visitor['QR Code ID']}
        </div>
        <div class="row g-3">
            <div class="col-6">
                <p class="mb-1 text-secondary">Room Number</p>
                <p class="fw-bold">${visitor['Room #']}</p>
            </div>
            <div class="col-6">
                <p class="mb-1 text-secondary">Phone</p>
                <p class="fw-bold">${visitor['Phone #']}</p>
            </div>
            <div class="col-12">
                <p class="mb-1 text-secondary">Email</p>
                <p class="fw-bold">${visitor['Email']}</p>
            </div>
            <div class="col-6">
                <p class="mb-1 text-secondary">Vehicle</p>
                <p class="fw-bold">${visitor['Vehicle']}</p>
            </div>
            <div class="col-6">
                <p class="mb-1 text-secondary">Vehicle Plate</p>
                <p class="fw-bold">${visitor['Vehicle'] === 'Yes' ? visitor['Vehicle Plate'] : '-'}</p>
            </div>
            <div class="col-12">
                <p class="mb-1 text-secondary">Reason for Visit</p>
                <p class="fw-bold">${visitor['Reason']}</p>
            </div>
            <div class="col-12 mt-4">
                <div class="d-flex justify-content-between">
                    <button class="btn btn-primary" onclick="startScanning()">Scan Next</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;

                    document.getElementById('qr-reader').style.display = 'none';
                    const modalBody = document.querySelector('#qrScannerModal .modal-body');
                    modalBody.innerHTML = visitorHtml;
                }
                document.getElementById('qrScannerModal').addEventListener('shown.bs.modal', function () {
                    startScanning();
                });

                document.getElementById('qrScannerModal').addEventListener('hidden.bs.modal', function () {
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.stop().then(() => {
                            html5QrcodeScanner = null;
                        });
                    }
                });
            </script>
</body>

</html>