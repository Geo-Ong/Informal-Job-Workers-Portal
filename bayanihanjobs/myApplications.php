<?php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch jobs the user has applied to
$applied_sql = "
    SELECT a.application_id, a.status, a.applied_at, 
           j.title, j.location, j.rate, 
           u.firstname, u.lastname
    FROM applications a
    JOIN job j ON a.job_id = j.job_id
    JOIN users u ON j.user_id = u.user_id
    WHERE a.user_id = ?
    ORDER BY a.applied_at DESC
";
$applied_stmt = $conn->prepare($applied_sql);
$applied_stmt->bind_param("i", $user_id);
$applied_stmt->execute();
$applied_result = $applied_stmt->get_result();

// Fetch jobs the user has posted
$posted_sql = "
    SELECT job_id, title, rate, category, location, created_at 
    FROM job 
    WHERE user_id = ?
    ORDER BY created_at DESC
";
$posted_stmt = $conn->prepare($posted_sql);
$posted_stmt->bind_param("i", $user_id);
$posted_stmt->execute();
$posted_result = $posted_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications</title>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="view_applicants.css"> <!-- reuse your existing card styles -->
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

<main style="margin:20px;">

    <!-- SECTION 1: Jobs I Applied For -->
    <h2>Jobs I Applied For</h2>
    <?php if ($applied_result->num_rows > 0): ?>
        <?php while ($row = $applied_result->fetch_assoc()): ?>
            <div class="applicant-card">
                <h3><?php echo strtoupper($row['title']); ?></h3>
                <p><b>Employer:</b> <?php echo ucwords($row['firstname'].' '.$row['lastname']); ?></p>
                <p><b>Location:</b> <?php echo htmlspecialchars($row['location']); ?></p>
                <?php if ($row['rate']): ?>
                    <p><b>Rate:</b> ₱<?php echo htmlspecialchars($row['rate']); ?></p>
                <?php endif; ?>
                <p><b>Status:</b> 
                    <span class="status status-<?php echo strtolower($row['status']); ?>">
                        <?php echo ucfirst($row['status']); ?>
                    </span>
                </p>
                <p><small>Applied on: <?php echo date('F j, Y', strtotime($row['applied_at'])); ?></small></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-applicants">You haven't applied to any jobs yet.</p>
    <?php endif; ?>

    <hr style="margin:40px 0; border:none; border-top:2px solid #ddd;">

    <!-- SECTION 2: Jobs I Posted -->
    <h2>Jobs I Posted</h2>
    <?php if ($posted_result->num_rows > 0): ?>
        <?php while ($row = $posted_result->fetch_assoc()): ?>
            <div class="applicant-card">
                <h3><?php echo strtoupper($row['title']); ?></h3>
                <p><b>Category:</b> <?php echo htmlspecialchars($row['category']); ?></p>
                <p><b>Location:</b> <?php echo htmlspecialchars($row['location']); ?></p>
                <?php if ($row['rate']): ?>
                    <p><b>Rate:</b> ₱<?php echo htmlspecialchars($row['rate']); ?></p>
                <?php endif; ?>
                <p><small>Posted on: <?php echo date('F j, Y', strtotime($row['created_at'])); ?></small></p>

                <div class="buttons">
                    <button class="hire" onclick="window.location.href='view_applicants.php?job_id=<?php echo $row['job_id']; ?>'">
                        View Applicants
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-applicants">You haven't posted any jobs yet.</p>
    <?php endif; ?>

</main>

<script src="navs.js"></script>
</body>
</html>
