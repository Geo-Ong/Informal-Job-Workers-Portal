<?php
include 'db_connection.php';
session_start();


if ($_SERVER["REQUEST_METHOD"] === "POST") {


    if (isset($_POST['register'])) {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $skills = $_POST['skills'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // ✅ Secure password

        $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $checkUser->bind_param("s", $username);
        $checkUser->execute();
        $result = $checkUser->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Username already exists!');</script>";
        } else {
            $insertUser = $conn->prepare("
                INSERT INTO users (firstname, lastname, phone, address, skills, username, password)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $insertUser->bind_param("sssssss", $firstname, $lastname, $phone, $address, $skills, $username, $password);

            if ($insertUser->execute()) {
                echo "<script>alert('Registration successful! Please log in.');</script>";
            } else {
                echo "<script>alert('Error during registration.');</script>";
            }
        }
    }


    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password']; 

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
      
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['firstname'] = $row['firstname'];
                header("Location: home.php");
                exit;
            } else {
                echo "<script>alert('Incorrect password.');</script>";
            }
        } else {
            echo "<script>alert('User not found.');</script>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BayanihanJob</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <!-- Registration Form -->
    <form  action="login_form.php" method="POST" name="registerForm" id="registerForm"  style="display: none;">
        <h1>Create Account</h1>
        <label for="firstName">Enter First Name</label>
        <input type="text" name="firstname" placeholder="Enter First Name" id="firstname" required>
        <br>

        <label for="lastName">Enter Last Name</label>
        <input type="text" name="lastname" placeholder="Enter Last Name" id="lastname" required>
        <br>

        <label for="phone">Enter Phone Number</label>
        <input type="tel" name="phone" placeholder="Enter Phone Number" id="phone" required>
        <br>

        <label for="address">Enter Address</label>
        <input type="text" name="address" placeholder="Enter Address" id="address" required>
        <br>

        <label for="skills">Enter Skills</label>
        <input type="text" name="skills" placeholder="'e.g, Carpenter, Plumber, etc.'" id="skills" required>
        <br>

        <label for="username">Enter Username</label>
        <input type="text" name="username" placeholder="Enter Username" id="uname" required>
        <br>

        <label for="password">Enter Password</label>
        <input type="password" name="password" placeholder="Enter Password" id="password" required>
        <br>
        <button type="submit" name="register" id="createAccBtn">Create Account</button>
        <p>Already have an account? <a href="#" id="showLogin">Sign in here!</a></p>
    </form>

    <!-- Login Form -->
    <form action="login_form.php" method="POST" name="loginForm" id="loginForm" style="display: block;">
        <h1>Login</h1>
        <label for="username">Enter Username</label>
        <input type="text" name="username" placeholder="Enter Username" required>
        <label for="password">Enter Password</label>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit" name="login">Login</button>
        <p>Don't have an account? <a href="#" id="showRegister">Create account!</a></p>
    </form>

    <script src="login.js"></script>
</body>
</html>