<?php
session_start();

// Check if user is logged in and is an admin  
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrator') {
    header('Location: ../index.php');
    exit();
}

// Load residents data from JSON file  
$residents_json = file_get_contents('../json/residents.json');
$residents_data = json_decode($residents_json, true)['Residents'];

// Load admin data  
$admins_json = file_get_contents('../json/administrators.json');
$admins_data = json_decode($admins_json, true)['Administrators'];

// Find current admin's data  
$current_admin = null;
foreach ($admins_data as $admin) {
    if ($admin['Username'] === $_SESSION['username']) {
        $current_admin = $admin;
        break;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

        .badge-active {
            background-color: var(--success);
            color: white;
        }

        #residentModal .modal-header {
            background: var(--primary);
            color: white;
        }

        .search-highlight {
            background-color: yellow;
        }

        .search-highlight {
            background-color: yellow;
        }

        .view_visitors_btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 140px;
            height: 60px;
            text-decoration: none;
            border-radius: 30px;
            background-color: var(--bs-primary);
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
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-user-shield text-primary me-2"></i>
                Admin Dashboard
            </a>

            <div class="dropdown">
                <div class="d-flex align-items-center profile-dropdown" data-bs-toggle="dropdown">
                    <span
                        class="me-2"><?php echo htmlspecialchars($current_admin['First Name'] . ' ' . $current_admin['Last Name']); ?></span>
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
        <div class="search-container mb-4">
            <div class="input-group">
                <span class="input-group-text border-end-0 bg-white">
                    <i class="fas fa-search text-secondary"></i>
                </span>
                <input type="text" id="searchInput" class="form-control border-start-0"
                    placeholder="Search residents by name, ID, or room number...">
            </div>
        </div>

        <!-- Residents Grid -->
        <div class="row g-4" id="residentsGrid">
            <?php foreach ($residents_data as $resident): ?>
                <div class="col-12 col-md-6 col-lg-4 resident-item">
                    <div class="resident-card p-3" data-resident='<?php echo json_encode($resident); ?>'
                        data-bs-toggle="modal" data-bs-target="#residentModal">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">
                                    <?php echo htmlspecialchars($resident['First Name'] . ' ' . $resident['Last Name']); ?>
                                </h5>
                                <p class="text-secondary mb-2">ID: <?php echo htmlspecialchars($resident['ID']); ?></p>
                            </div>
                            <span class="badge bg-info">Resident</span>
                        </div>
                        <div class="text-secondary">
                            <small><i class="fas fa-door-open me-1"></i>Room
                                #<?php echo htmlspecialchars($resident['Room #']); ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- View Visitors Button -->
        <a href="visitors.php" class="view_visitors_btn">
            View Visitors
        </a>
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
                                <?php echo htmlspecialchars($current_admin['First Name'] . ' ' . $current_admin['Last Name']); ?>
                            </h4>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <p class="mb-1 text-secondary">ID</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($current_admin['ID']); ?></p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1 text-secondary">Room</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($current_admin['Room #']); ?></p>
                            </div>
                            <div class="col-12">
                                <p class="mb-1 text-secondary">Email</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($current_admin['Email']); ?></p>
                            </div>
                            <div class="col-12">
                                <p class="mb-1 text-secondary">Phone</p>
                                <p class="fw-bold"><?php echo htmlspecialchars($current_admin['Phone #']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resident Details Modal -->
        <div class="modal fade" id="residentModal" tabindex="-1" aria-labelledby="residentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="residentModalLabel">Resident Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="residentDetails">
                        <!-- Resident details will be populated here dynamically -->
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            // Search Functionality  
            $('#searchInput').on('input', function () {
                const query = $(this).val().toLowerCase();
                $('#residentsGrid .resident-item').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(query) > -1);
                });
            });

            // Resident Click Event  
            $('.resident-card').on('click', function () {
                const residentData = $(this).data('resident');
                $('#residentDetails').html(`  
                    <p class="mb-1 text-secondary">Name</p>  
                    <p class="fw-bold">${residentData['First Name']} ${residentData['Last Name']}</p>  
                    <p class="mb-1 text-secondary">ID</p>  
                    <p class="fw-bold">${residentData['ID']}</p>  
                    <p class="mb-1 text-secondary">Room</p>  
                    <p class="fw-bold">${residentData['Room #']}</p>  
                    <p class="mb-1 text-secondary">Email</p>  
                    <p class="fw-bold">${residentData['Email']}</p>  
                    <p class="mb-1 text-secondary">Phone</p>  
                    <p class="fw-bold">${residentData['Phone #']}</p>  
                    <p class="mb-1 text-secondary">Vehicle</p>  
                    <p class="fw-bold">${residentData['Vehicle'] ? 'Yes' : 'No'}</p>  
                    <p class="mb-1 text-secondary">Vehicle Plate</p>  
                    <p class="fw-bold">${residentData['Vehicle Plate'] || 'N/A'}</p>  
                `);
            });
        });  
    </script>
</body>

</html>