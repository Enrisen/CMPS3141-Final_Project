<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Load the users.json file
    $json_data = file_get_contents('json/users.json');
    $users = json_decode($json_data, true)['users'];

    // Validate user credentials
    foreach ($users as $user) {
        if ($user['username'] === $username && $user['password'] === $password) {
            // Store user data in session
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on user role
            switch ($user['role']) {
                case 'Administrator':
                    header('Location: admin/adminHome.php');
                    exit();
                case 'Resident':
                    header('Location: resident/residentHome.php');
                    exit();
                case 'Security Guard':
                    header('Location: security_guard/sgHome.php');
                    exit();
                default:
                    session_destroy();
                    header('Location: index.php?error=invalid_role');
                    exit();
            }
        }
    }

    // If login fails, set an error message
    $_SESSION['login_error'] = 'Invalid username or password';
    header('Location: index.php');
    exit();
}

// If someone tries to access this file directly without POST
header('Location: index.php');
exit();