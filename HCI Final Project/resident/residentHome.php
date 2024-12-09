<?php
session_start();

// Check if user is logged in and is a resident  
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Resident') {
    header('Location: ../index.php');
    exit();
}

// Load residents data from JSON file  
$residents_json = file_get_contents('../json/residents.json');
$residents_data = json_decode($residents_json, true)['Residents'];

// Load pending visitors data  
$pending_visitors_json = file_get_contents('../json/pendingVisitors.json');
$pending_visitors_data = json_decode($pending_visitors_json, true)['pendingVisitors'];

// Load past visitors data  
$scanned_visitors_json = file_get_contents('../json/scannedVisitors.json');
$scanned_visitors_data = json_decode($scanned_visitors_json, true)['scannedVisitors'];

// Find current resident's data  
$current_resident = null;
foreach ($residents_data as $resident) {
    if ($resident['Username'] === $_SESSION['username']) {
        $current_resident = $resident;
        break;
    }
}

// Filter pending visitors based on current resident's room number  
$current_room = $current_resident['Room #'];
$filtered_pending_visitors = array_filter($pending_visitors_data, function ($visitor) use ($current_room) {
    return $visitor['Room #'] === $current_room;
});

// Filter scanned visitors based on current resident's room number  
$filtered_scanned_visitors = array_filter($scanned_visitors_data, function ($visitor) use ($current_room) {
    return $visitor['Room #'] === $current_room;
});
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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

        .resident-card {
            background: var(--surface);
            border-radius: 8px;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .resident-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .profile-dropdown {
            cursor: pointer;
        }

        .view_visitors_btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 140px;
            height: 60px;
            text-decoration: none;
            border-radius: 30px;
            background-color: var(--primary);
            color: white;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .view_visitors_btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background-color: var(--primary);
            color: white;
        }

        .modal-header .btn-close {
            background-image: none;
            opacity: 1;
        }

        .modal-header .btn-close:before {
            content: '×';
            font-size: 2rem;
            color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .modal-header .btn-close {
            background: transparent;
            border: none;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-user text-primary me-2"></i>
                Resident Dashboard
            </a>

            <div class="dropdown">
                <div class="d-flex align-items-center profile-dropdown" data-bs-toggle="dropdown">
                    <span
                        class="me-2"><?php echo htmlspecialchars($current_resident['First Name'] . ' ' . $current_resident['Last Name']); ?></span>
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
        <!-- Resident Profile Card -->
        <div class="row g-2 mb-3">
            <div class="col-12">
                <div class="resident-card p-2">
                    <div class="row g-2">
                        <div class="col-12">
                            <p class="mb-1 text-secondary">Name</p>
                            <p class="fw-bold mb-1">
                                <?php echo htmlspecialchars($current_resident['First Name'] . ' ' . $current_resident['Last Name']); ?>
                            </p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1 text-secondary">ID</p>
                            <p class="fw-bold mb-1"><?php echo htmlspecialchars($current_resident['ID']); ?></p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1 text-secondary">Room</p>
                            <p class="fw-bold mb-1"><?php echo htmlspecialchars($current_resident['Room #']); ?></p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1 text-secondary">Phone</p>
                            <p class="fw-bold mb-1"><?php echo htmlspecialchars($current_resident['Phone #']); ?></p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1 text-secondary">Email</p>
                            <p class="fw-bold mb-1"><?php echo htmlspecialchars($current_resident['Email']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Profile Modal -->
        <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="profileModalLabel">Profile Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-circle fa-4x text-secondary"></i>
                            <h4 class="mt-2">
                                <?php echo htmlspecialchars($current_resident['First Name'] . ' ' . $current_resident['Last Name']); ?>
                            </h4>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <p class="mb-1 text-secondary">ID</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($current_resident['ID']); ?></p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1 text-secondary">Room</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($current_resident['Room #']); ?></p>
                            </div>
                            <div class="col-12">
                                <p class="mb-1 text-secondary">Email</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($current_resident['Email']); ?></p>
                            </div>
                            <div class="col-12">
                                <p class="mb-1 text-secondary">Phone</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($current_resident['Phone #']); ?></p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1 text-secondary">Vehicle</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($current_resident['Vehicle']); ?></p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1 text-secondary">Vehicle Plate</p>
                                <p class="fw-bold">
                                    <?php echo htmlspecialchars($current_resident['Vehicle Plate'] ?: 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Search Bar for Visitors -->
        <div class="search-container mb-4">
            <div class="input-group">
                <span class="input-group-text border-end-0 bg-white">
                    <i class="fas fa-search text-secondary"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0"
                    placeholder="Search visitors by name, ID...">
            </div>
        </div>
        <!-- Add Visitor Button -->
        <a href="#" class="view_visitors_btn" data-bs-toggle="modal" data-bs-target="#addVisitorModal">
            <i class="fas fa-plus me-2"></i>Add Visitor
        </a>
        <!-- Tabs for Visitors -->
        <ul class="nav nav-tabs mb-3" id="visitorsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                    type="button" role="tab">Current Visitors</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button"
                    role="tab">Past Visitors</button>
            </li>
        </ul>

        <!-- Visitors Content -->
        <div class="tab-content" id="visitorsTabContent">
            <!-- Current Visitors Tab -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <div class="row g-4" id="pendingVisitorsGrid">
                    <?php if (!empty($filtered_pending_visitors)): ?>
                        <?php foreach ($filtered_pending_visitors as $visitor): ?>
                            <div class="col-12 col-md-6 col-lg-4 visitor-item">
                                <div class="resident-card p-3" data-visitor='<?php echo json_encode($visitor); ?>'
                                    data-bs-toggle="modal" data-bs-target="#visitorModal">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1">
                                                <?php echo htmlspecialchars($visitor['First Name'] . ' ' . $visitor['Last Name']); ?>
                                            </h5>
                                            <p class="text-secondary mb-2">ID: <?php echo htmlspecialchars($visitor['ID']); ?>
                                            </p>
                                        </div>
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                    <div class="text-secondary">
                                        <small><i class="fas fa-info-circle me-1"></i>Reason:
                                            <?php echo htmlspecialchars($visitor['Reason']); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-center text-secondary">No current visitors.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Add Visitor Modal -->
            <div class="modal fade" id="addVisitorModal" tabindex="-1" aria-labelledby="addVisitorModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="addVisitorModalLabel">Add New Visitor</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addVisitorForm">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="firstName" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" name="lastName" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">ID</label>
                                        <input type="text" class="form-control" name="id" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" class="form-control" name="phone" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Reason for Visit</label>
                                        <input type="text" class="form-control" name="reason" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Vehicle</label>
                                        <select class="form-select" name="vehicle">
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Vehicle Plate</label>
                                        <input type="text" class="form-control" name="vehiclePlate" disabled>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Date In</label>
                                        <input type="date" class="form-control" name="dateIn"
                                            value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Date Out</label>
                                        <input type="date" class="form-control" name="dateOut"
                                            value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="mt-3 text-end">
                                    <button type="submit" class="btn btn-primary">Add Visitor</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR Code Modal -->
            <div class="modal fade" id="qrCodeModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Visitor QR Code</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="visitorQRDetails">
                            <!-- Visitor details will be populated here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="downloadQRCode" class="btn btn-primary">Download QR Code</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Past Visitors Tab -->
            <div class="tab-pane fade" id="past" role="tabpanel">
                <div class="row g-4" id="pastVisitorsGrid">
                    <?php if (!empty($filtered_scanned_visitors)): ?>
                        <?php foreach ($filtered_scanned_visitors as $visitor): ?>
                            <div class="col-12 col-md-6 col-lg-4 visitor-item">
                                <div class="resident-card p-3" data-visitor='<?php echo json_encode($visitor); ?>'>
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1">
                                                <?php echo htmlspecialchars($visitor['First Name'] . ' ' . $visitor['Last Name']); ?>
                                            </h5>
                                            <p class="text-secondary mb-2">ID: <?php echo htmlspecialchars($visitor['ID']); ?>
                                            </p>
                                        </div>
                                        <span class="badge bg-secondary">Inactive</span>
                                    </div>
                                    <div class="text-secondary mb-2">
                                        <small><i class="fas fa-calendar me-1"></i>Visited:
                                            <?php echo htmlspecialchars($visitor['Date In']); ?></small>
                                    </div>
                                    <button class="btn btn-primary w-100 add-new-visit-btn"
                                        data-visitor='<?php echo json_encode($visitor); ?>'>
                                        <i class="fas fa-plus me-2"></i>Add New Visit
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-center text-secondary">No past visitors.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Visitor Details Modal -->
    <div class="modal fade" id="visitorModal" tabindex="-1" aria-labelledby="visitorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="visitorModalLabel">Visitor Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="visitorDetails">
                    <!-- Visitor details will be populated here dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
        $(document).ready(function () {
            // Search Functionality  
            $('#searchInput').on('input', function () {
                const query = $(this).val().toLowerCase();
                $('.visitor-item').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(query) > -1);
                });
            });

            // Visitor Click Event  
            // Visitor Click Event  
            $('.visitor-item').on('click', function () {
                const visitorData = $(this).find('.resident-card').data('visitor');
                $('#visitorDetails').html(`  
        <div class="row g-3">
            <div class="col-6">
                <p class="mb-1 text-secondary">Name</p>  
                <p class="fw-bold">${visitorData['First Name']} ${visitorData['Last Name']}</p>  
            </div>
            <div class="col-6">
                <p class="mb-1 text-secondary">ID</p>  
                <p class="fw-bold">${visitorData['ID']}</p>  
            </div>
            <div class="col-4">
                <p class="mb-1 text-secondary">Room</p>  
                <p class="fw-bold">${visitorData['Room #']}</p>  
            </div>
            <div class="col-12">
                <p class="mb-1 text-secondary">Email</p>  
                <p class="fw-bold">${visitorData['Email']}</p>  
            </div>
            <div class="col-12">
                <p class="mb-1 text-secondary">Phone</p>  
                <p class="fw-bold">${visitorData['Phone #']}</p>  
            </div>
            <div class="col-6">
                <p class="mb-1 text-secondary">Vehicle</p>  
                <p class="fw-bold">${visitorData['Vehicle']}</p>  
            </div>
            <div class="col-6">
                <p class="mb-1 text-secondary">Vehicle Plate</p>  
                <p class="fw-bold">${visitorData['Vehicle Plate'] || 'N/A'}</p>  
            </div>
            <div class="col-6">
                <p class="mb-1 text-secondary">Date In</p>  
                <p class="fw-bold">${visitorData['Date In']}</p>  
            </div>
            <div class="col-6">
                <p class="mb-1 text-secondary">Date Out</p>  
                <p class="fw-bold">${visitorData['Date Out']}</p>  
            </div>
            <div class="col-12">
                <p class="mb-1 text-secondary">Reason</p>  
                <p class="fw-bold">${visitorData['Reason']}</p>  
            </div>
            ${visitorData['Time QR Scanned In'] && visitorData['Leave Time'] ? `
            <div class="col-12">
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1 text-secondary">Time In</p>  
                        <p class="fw-bold">${visitorData['Time QR Scanned In']}</p>  
                    </div>
                    <div class="col-6">
                        <p class="mb-1 text-secondary">Leave Time</p>  
                        <p class="fw-bold">${visitorData['Leave Time']}</p>  
                    </div>
                </div>
            </div>
            ` : ''}
        </div>
    `);
            });

            // Function to reset form to default state
            function resetAddVisitorForm() {
                $('#addVisitorForm')[0].reset(); // Reset all form fields
                $('#addVisitorForm input, #addVisitorForm select').prop('readonly', false).prop('disabled', false);
                $('input[name="vehiclePlate"]').prop('disabled', true);
            }

            // Reset form when Add Visitor button is clicked
            $('.view_visitors_btn').on('click', function () {
                resetAddVisitorForm();
            });

            // Add new visit button click handler
            $('.add-new-visit-btn').on('click', function () {
                const visitorData = $(this).data('visitor');

                // Open the add visitor modal
                $('#addVisitorModal').modal('show');

                // Prepopulate form fields and make them readonly
                $('input[name="firstName"]').val(visitorData['First Name']).prop('readonly', true);
                $('input[name="lastName"]').val(visitorData['Last Name']).prop('readonly', true);
                $('input[name="id"]').val(visitorData['ID']).prop('readonly', true);
                $('input[name="phone"]').val(visitorData['Phone #']).prop('readonly', true);
                $('input[name="email"]').val(visitorData['Email']).prop('readonly', true);

                // Vehicle handling
                $('select[name="vehicle"]').val(visitorData['Vehicle']).prop('disabled', true);
                const vehiclePlateInput = $('input[name="vehiclePlate"]');
                if (visitorData['Vehicle'] === 'Yes') {
                    vehiclePlateInput.val(visitorData['Vehicle Plate'] || '').prop('readonly', true);
                } else {
                    vehiclePlateInput.val('').prop('disabled', true);
                }

                // Reset dates to current date (keep these editable)
                $('input[name="dateIn"]').val('<?php echo date('Y-m-d'); ?>');
                $('input[name="dateOut"]').val('<?php echo date('Y-m-d'); ?>');

                // Clear and keep reason field editable
                $('input[name="reason"]').val('').prop('readonly', false);
            });

            // Vehicle selection toggle for plate input
            $('select[name="vehicle"]').on('change', function () {
                console.log('Vehicle selection changed to: ' + $(this).val());
                const vehiclePlateInput = $('input[name="vehiclePlate"]');
                if ($(this).val() === 'Yes') {
                    vehiclePlateInput.prop('disabled', false);
                } else {
                    vehiclePlateInput.prop('disabled', true).val('');
                }
            });

            $('#addVisitorForm').on('submit', function (e) {
                e.preventDefault();
                const formData = {
                    'First Name': $('input[name="firstName"]').val(),
                    'Last Name': $('input[name="lastName"]').val(),
                    'ID': $('input[name="id"]').val(),
                    'Room #': '<?php echo $current_room; ?>',
                    'Phone #': $('input[name="phone"]').val(),
                    'Email': $('input[name="email"]').val(),
                    'Vehicle': $('select[name="vehicle"]').val(),
                    'Vehicle Plate': $('select[name="vehicle"]').val() === 'Yes' ? $('input[name="vehiclePlate"]').val() : '',
                    'Reason': $('input[name="reason"]').val(),
                    'Date In': $('input[name="dateIn"]').val(),
                    'Date Out': $('input[name="dateOut"]').val(),
                    'QR Code ID': 'QR' + Math.random().toString(36).substr(2, 3).toUpperCase()
                };
                $.ajax({
                    url: 'add_visitor.php',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    success: function (response) {
                        $('#addVisitorModal').modal('hide');
                        location.reload(); // Refresh to show new visitor
                    },
                    error: function (xhr, status, error) {
                        alert('Error adding visitor: ' + error);
                    }
                });
            });
        });

        $('#addVisitorForm').on('submit', function (e) {
            e.preventDefault();
            const formData = {
                'First Name': $('input[name="firstName"]').val(),
                'Last Name': $('input[name="lastName"]').val(),
                'ID': $('input[name="id"]').val(),
                'Room #': '<?php echo $current_room; ?>',
                'Phone #': $('input[name="phone"]').val(),
                'Email': $('input[name="email"]').val(),
                'Vehicle': $('select[name="vehicle"]').val(),
                'Vehicle Plate': $('select[name="vehicle"]').val() === 'Yes' ? $('input[name="vehiclePlate"]').val() : '',
                'Reason': $('input[name="reason"]').val(),
                'Date In': $('input[name="dateIn"]').val(),
                'Date Out': $('input[name="dateOut"]').val(),
                'QR Code ID': 'QR' + Math.random().toString(36).substr(2, 3).toUpperCase()
            };

            // Store form data in a global variable to access later
            window.pendingVisitorData = formData;

            // Generate QR Code Modal
            $('#qrCodeModal').find('#visitorQRDetails').html(`
        <div class="visitor-card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="mb-1">${formData['First Name']} ${formData['Last Name']}</h5>
                    <p class="text-secondary mb-2">ID: ${formData['ID']}</p>
                    <p class="text-muted mb-2"><i class="fas fa-info-circle me-1"></i>${formData['Reason']}</p>
                </div>
                <span class="badge bg-secondary">Inactive</span>
            </div>
            <div class="text-secondary">
                <div class="d-flex justify-content-between mb-1">
                    <small><i class="fas fa-door-open me-1"></i>Room #${formData['Room #']}</small>
                </div>
                <div class="d-flex justify-content-between">
                    <small><i class="fas fa-calendar me-1"></i>${formData['Date In']}</small>
                </div>
            </div>
            <div class="text-center mt-3">
                <div id="qrcode" class="d-inline-block"></div>
            </div>
        </div>
    `);

            // Generate QR Code
            new QRCode(document.getElementById("qrcode"), {
                text: formData['QR Code ID'],
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
            });

            $('#addVisitorModal').modal('hide');
            $('#qrCodeModal').modal('show');
        });

        // Download QR Code and submit visitor data
        $('#downloadQRCode').on('click', function () {
            const formData = window.pendingVisitorData;

            // Generate and download QR Code
            const qrCodeElement = document.getElementById('qrcode');
            html2canvas(qrCodeElement).then(canvas => {
                const link = document.createElement('a');
                link.download = 'visitor_qr_code.png';
                link.href = canvas.toDataURL();
                link.click();

                // Submit visitor data to PHP
                $.ajax({
                    url: '../add_visitor.php',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    success: function (response) {
                        $('#qrCodeModal').modal('hide');
                        location.reload(); // Refresh to show new visitor
                    },
                    error: function (xhr, status, error) {
                        alert('Error adding visitor: ' + error);
                    }
                });
            });
        });
    </script>
</body>

</html>