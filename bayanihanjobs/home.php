<?php
include 'db_connection.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user name
$user_stmt = $conn->prepare("SELECT firstname, lastname FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

$full_name = $user ? $user['firstname'] . ' ' . $user['lastname'] : 'Guest';

// Fetch jobs (include job_owner_id)
$sql = "SELECT j.job_id, j.user_id AS job_owner_id, j.title, j.rate, j.description, j.category, j.location, j.created_at, 
               u.firstname, u.lastname
        FROM job j 
        JOIN users u ON j.user_id = u.user_id
        ORDER BY j.created_at DESC";
$result = $conn->query($sql);

// Fetch all jobs the current user has applied to
$applied_jobs = [];
$app_stmt = $conn->prepare("SELECT job_id FROM applications WHERE user_id = ?");
$app_stmt->bind_param("i", $user_id);
$app_stmt->execute();
$app_result = $app_stmt->get_result();
while ($row_app = $app_result->fetch_assoc()) {
    $applied_jobs[] = $row_app['job_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="header.css">
    <style>
        /* Optional styling improvements */
        .apply-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .apply-btn:hover {
            background-color: #0056b3;
        }

        .apply-btn[disabled] {
            background-color: #ccc;
            color: #333;
            cursor: not-allowed;
        }

        .apply-btn.applied {
            background-color: #28a745;
            color: white;
            cursor: not-allowed;
        }

        .apply-btn.my-job {
            background-color: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <header>
        <div class="home-btn">
            <button id="home">Informal Job Workers Portal</button>
        </div>

        <input type="search" placeholder="Search">

        <div class="nav-links">
            <a href="myApplications.php" id="myApplications">My Applications</a>
            <a href="profile.php" id="profile">Profile</a>
            <a href="logout.php" id="logout">Logout</a>
        </div>
    </header>

    <main>
        <div class="post-section">
            <button id="postJobBtn" onclick="window.location.href ='post_job.php'">Post a Job</button>
        </div>

        <section class="job-listings">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="job-card">
                    <h3><?php echo strtoupper($row['title']); ?></h3>
                    <p>
                        <?php echo ucwords($row['firstname']); ?> <?php echo ucwords($row['lastname']); ?>
                        | Posted on <?php echo htmlspecialchars(date('F j, Y', strtotime($row['created_at']))); ?>
                    </p>

                    <?php 
                    if (!empty($row['rate'])) {
                        echo "<p>₱" . htmlspecialchars($row['rate']) . "</p>"; 
                    }
                    ?>

                    <p class="desc"><?php echo htmlspecialchars($row['description']); ?></p>
                    <p><b>Category:</b> <?php echo htmlspecialchars($row['category']); ?></p>
                    <p><b>Location:</b> <?php echo htmlspecialchars($row['location']); ?></p>

                    <?php
                    // Check if current user is the job owner
                    if ($row['job_owner_id'] == $user_id) {
                        echo "<button class='apply-btn my-job' disabled>My Job Post</button>";
                    } 
                    // Check if user already applied
                    elseif (in_array($row['job_id'], $applied_jobs)) {
                        echo "<button class='apply-btn applied' disabled>Applied</button>";
                    } 
                    // Show Apply Now button
                    else {
                        echo "<button class='apply-btn' onclick=\"window.location='apply.php?job_id={$row['job_id']}'\">Apply Now</button>";
                    }
                    ?>
                </div>
            <?php endwhile; ?>
        </section>
        <?php else: ?>
            <p class="center-text">No job listings available.</p>
        <?php endif; ?>
    </main>
</body>
<script src="navs.js"></script>
</html>
