<?php
include 'db_connection.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit();
}

// Check if job_id is provided
if (!isset($_GET['job_id'])) {
    echo "Invalid request.";
    exit();
}

$user_id = $_SESSION['user_id'];
$job_id = intval($_GET['job_id']);

// Check if the user has already applied for this job
$check_stmt = $conn->prepare("SELECT * FROM applications WHERE user_id = ? AND job_id = ?");
$check_stmt->bind_param("ii", $user_id, $job_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo "<script>alert('You have already applied for this job!'); window.location.href='home.php';</script>";
    exit();
}

// Insert new application
$stmt = $conn->prepare("INSERT INTO applications (user_id, job_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $job_id);

if ($stmt->execute()) {
    echo "<script>alert('Application submitted successfully!'); window.location.href='myApplications.php';</script>";
} else {
    echo "<script>alert('Error submitting application.'); window.location.href='home.php';</script>";
}

$stmt->close();
$conn->close();

?>