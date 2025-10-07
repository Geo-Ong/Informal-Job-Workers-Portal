<?php
include 'db_connection.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get job_id from URL
if (!isset($_GET['job_id'])) {
    echo "Invalid request.";
    exit();
}

$job_id = intval($_GET['job_id']);

// ✅ Handle status updates BEFORE fetching
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['application_id'], $_POST['status'])) {
    $application_id = intval($_POST['application_id']);
    $status = $_POST['status'];

    if (in_array($status, ['Hired', 'Rejected', 'Pending'])) {
        $update_stmt = $conn->prepare("UPDATE applications SET status = ? WHERE application_id = ?");
        $update_stmt->bind_param("si", $status, $application_id);
        $update_stmt->execute();
    }
    header("Location: view_applicants.php?job_id=" . $job_id);
    exit();
}

// ✅ Verify job ownership
$job_check = $conn->prepare("SELECT * FROM job WHERE job_id = ? AND user_id = ?");
$job_check->bind_param("ii", $job_id, $user_id);
$job_check->execute();
$job_result = $job_check->get_result();

if ($job_result->num_rows === 0) {
    echo "You are not authorized to view applicants for this job.";
    exit();
}

$job = $job_result->fetch_assoc();

// ✅ Fetch applicants for this job
$app_stmt = $conn->prepare("
    SELECT a.application_id, a.status, a.applied_at,
           u.firstname, u.lastname, u.username, u.phone
    FROM applications a
    JOIN users u ON a.user_id = u.user_id
    WHERE a.job_id = ?
    ORDER BY a.applied_at DESC
");
$app_stmt->bind_param("i", $job_id);
$app_stmt->execute();
$applicants = $app_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applicants</title>
    <link rel="stylesheet" href="view_applicants.css">
    <link rel="stylesheet" href="header.css">
</head>
<body>
<header>
    <div class="home-btn">
        <button id="home" onclick="window.location.href='home.php'">Informal Job Workers Portal</button>
    </div>

    <input type="search" placeholder="Search">

    <div class="nav-links">
        <a href="myApplications.php" id="myApplications">My Applications</a>
        <a href="profile.php" id="profile">Profile</a>
        <a href="logout.php" id="logout">Logout</a>
    </div>
</header>

<main class="view-applicants">
    <h2>Applicants for "<?php echo htmlspecialchars($job['title']); ?>"</h2>

    <?php if ($applicants->num_rows > 0): ?>
        <?php while ($row = $applicants->fetch_assoc()): ?>
            <div class="applicant-card">
                <h3><?php echo ucwords($row['firstname'] . ' ' . $row['lastname']); ?></h3>
                <p><b>Username:</b> <?php echo htmlspecialchars($row['username']); ?></p>
                <p><b>Phone:</b> <?php echo htmlspecialchars($row['phone']); ?></p>
                <p><b>Applied on:</b> <?php echo date('F j, Y', strtotime($row['applied_at'])); ?></p>
                <p><b>Status:</b> 
                    <span class="status <?php echo strtolower($row['status']); ?>">
                        <?php echo htmlspecialchars($row['status']); ?>
                    </span>
                </p>

                <!-- ✅ Always show buttons (to allow changing status) -->
                <form method="POST" class="action-form">
                    <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>">
                    <button type="submit" name="status" value="Hired" class="hire-btn">Hire</button>
                    <button type="submit" name="status" value="Rejected" class="reject-btn">Reject</button>
                    <?php if ($row['status'] != 'Pending'): ?>
                        <button type="submit" name="status" value="Pending" class="reset-btn">Set Pending</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-applicants">No applicants yet for this job.</p>
    <?php endif; ?>
</main>

</body>
</html>
