<?php
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($username === '' || $password === '' || $confirm_password === '') {
        echo "<script>
            alert('Please fill all required fields.');
            window.location.href = '../register.html';
        </script>";
        exit();
    }

    if ($password !== $confirm_password) {
        echo "<script>
            alert('Passwords do not match! Please try again.');
            window.location.href = '../register.html';
        </script>";
        exit();
    }

    // Check if username exists using prepared statement
    $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? LIMIT 1");
    if ($check_stmt) {
        mysqli_stmt_bind_param($check_stmt, 's', $username);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            mysqli_stmt_close($check_stmt);
            echo "<script>
                alert('Username already exists! Please choose a different username.');
                window.location.href = '../register.html';
            </script>";
            exit();
        }
        mysqli_stmt_close($check_stmt);
    }

    // Hash password and insert new user using prepared statement
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, user_type) VALUES (?, ?, 'user')");
    if ($insert_stmt) {
        mysqli_stmt_bind_param($insert_stmt, 'ss', $username, $hashed_password);
        if (mysqli_stmt_execute($insert_stmt)) {
            mysqli_stmt_close($insert_stmt);
            echo "<script>
                alert('Customer account created successfully! You can now login as a user.');
                window.location.href = '../login.html?type=user';
            </script>";
            exit();
        } else {
            mysqli_stmt_close($insert_stmt);
            echo "<script>
                alert('Error creating account. Please try again.');
                window.location.href = '../register.html';
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('Server error. Please try again later.');
            window.location.href = '../register.html';
        </script>";
        exit();
    }
}

mysqli_close($conn);
?>