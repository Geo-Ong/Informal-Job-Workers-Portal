<?php
session_start();
include "db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login_form.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];

    // Collect and sanitize form input
    $title = $_POST['title'];
    $rate = $_POST['rate'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $location = $_POST['location'];

    // ✅ Use placeholders (no quotes around ?)
    $sql = "INSERT INTO job (user_id, title, rate, description, category, location, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $user_id, $title, $rate , $description, $category, $location);

    if ($stmt->execute()) {
        echo "<script>alert('Job posted successfully!'); window.location.href='home.php';</script>";
        exit();
    } else {
        echo "Error posting job: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post A Job</title>
    <link rel="stylesheet" href="post_job.css">
    <link rel="stylesheet" href="header.css">
</head>
<body>
<header>
        <div class="home-btn">
            <button id="home">BayanihanJobs</button>
        </div>

        <input type="search" placeholder="Search">

        <div class="nav-links">
            <a href="myApplications.php" id="myApplications">My Applications</a>
            <a href="#" id="profile">Profile</a>
        </div>
    </header>
    <main>
        <h2>Post A Job</h2>

        <form action="post_job.php" method="post">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" placeholder="Enter Title" required>

            <label for="rate">Pricing</label>
            <input type="text" id="rate" name="rate" placeholder="e.g. 500/day, 3000/month" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Enter Description" required></textarea>

            <label for="category">Category</label>
            <input type="text" id="category" name="category" placeholder="e.g. Plumber, Carpenter" required>

            <label for="location">Location</label>
            <input type="text" id="location" name="location" placeholder="Brgy., Municipality, City" required>
            
            <button type="submit" name="submit">Submit</button>
        </form>
    </main>
</body>
<script src="navs.js"></script>
</html>