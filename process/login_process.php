<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Use prepared statements to avoid SQL injection
    $username_input = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password_input = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username_input === '' || $password_input === '') {
        header("Location: ../login.html?error=Please+enter+username+and+password");
        exit();
    }

    $stmt = mysqli_prepare($conn, "SELECT id, username, password, user_type FROM users WHERE username = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $username_input);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id, $username_db, $password_hash, $user_type);
        if (mysqli_stmt_fetch($stmt)) {
            // Verify password
            if (password_verify($password_input, $password_hash)) {
                // Successful login
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username_db;
                $_SESSION['user_type'] = $user_type;

                if ($user_type === 'admin') {
                    header("Location: ../dashboard.php");
                } else {
                    header("Location: ../user_dashboard.php");
                }
                mysqli_stmt_close($stmt);
                exit();
            }
        }
        mysqli_stmt_close($stmt);
    }

    // If we reach here, authentication failed
    header("Location: ../login.html?error=Invalid+credentials");
    exit();
}
?>