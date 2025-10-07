<?php
// Include database connection
include "db_connection.php";

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
  header("Location: login_form.php");
  exit();
}

$user_id = $_SESSION["user_id"];

// Fetch user info securely
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
  echo "User not found.";
  exit();
}

// Handle profile update form submission
if (isset($_POST['update_profile'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Handle profile picture upload
    if (!empty($_FILES['profile-pic']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = basename($_FILES['profile-pic']['name']);
        $targetFilePath = $targetDir . time() . "_" . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'jfif', 'webp'];
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['profile-pic']['tmp_name'], $targetFilePath)) {
                $updatePic = $conn->prepare("UPDATE users SET profile = ? WHERE user_id = ?");
                $updatePic->bind_param("si", $targetFilePath, $user_id);
                $updatePic->execute();
            }
        }
    }

    // Update user details
    $update = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, address = ?, username = ?, phone = ? WHERE user_id = ?");
    $update->bind_param("sssssi", $firstname, $lastname, $address, $email, $phone, $user_id);

    if ($update->execute()) {
        header("Location: profile.php?updated=true");
        exit();
    } else {
        echo "Error updating profile.";
    }
}
// Handle skills update form submission
if (isset($_POST['update_skills'])) {
    $skills = $_POST['skills'];
    $recent_projects = $_POST['recent_projects'];
    $certificates = $_POST['certificates'];

    $updateSkills = $conn->prepare("UPDATE users SET skills = ?, recent_projects = ?, certificates = ? WHERE user_id = ?");
    $updateSkills->bind_param("sssi", $skills, $recent_projects, $certificates, $user_id);

    if ($updateSkills->execute()) {
        header("Location: profile.php?skills_updated=true");
        exit();
    } else {
        echo "Error updating skills.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($user['firstname']); ?>'s Profile</title>
  <link rel="stylesheet" href="profile.css">
  <link rel="stylesheet" href="header.css">
</head>
<body>
<header>
    <div class="home-btn">
        <button id="home">Informal Job Workers Portal</button>
    </div>

    <input type="search" placeholder="Search">

    <div class="nav-links">
        <a href="#" id="myApplications">My Applications</a>
        <a href="#" id="profile">Profile</a>
        <a href="logout.php" id="logout">Logout</a>
    </div>
</header>

<!-- Profile Header -->
 <div class="profile-container">
<div class="profile-image">
  <img src="<?php echo !empty($user['profile']) ? htmlspecialchars($user['profile']) : 'default-profile.png'; ?>" 
       alt="Profile Picture" width="150px" height="150px">
</div>

<!-- Profile Info -->
<div class="profile-info">
  <h2><?php echo ucfirst($user['firstname'] . " " . $user['lastname']); ?></h2>
  <p>📍 <?php echo ucfirst($user['address']); ?></p>
  <div class="social-links">
      <p>👤 <?php echo ucfirst($user['username']); ?></p>
      <p>📞 <?php echo ucfirst($user['phone']); ?></p>
  </div>
  <button id="edit-profile-btn">Edit Profile</button>
</div>
</div>

<!-- Edit Profile Modal -->
<div id="edit-profile-modal" style="display: none;">
  <form id="edit-profile-form" method="POST" action="profile.php" enctype="multipart/form-data">
      <h3>Edit Profile</h3>

      <label for="profile-pic">Profile Picture:</label>
      <input type="file" id="profile-pic" name="profile-pic">

      <label for="firstname">First Name:</label>
      <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
      
      <label for="lastname">Last Name:</label>
      <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>

      <label for="address">Address:</label>
      <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['username']); ?>">

      <label for="phone">Phone Number:</label>
      <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">

      <button type="submit" name="update_profile">Save Changes</button>
      <button type="button" id="cancel-edit">Cancel</button>
  </form>
</div>

<!-- User Content -->
<div class="content">
  <div class="card">
      <h3>Skills</h3>
      <p><?php echo htmlspecialchars($user['skills']); ?></p>
  </div>

  <div class="recent-projects card">
      <h3>Recent Projects</h3>
      <p><?php echo htmlspecialchars($user['recent_projects']); ?></p>
  </div>

  <div class="certificate card">
      <h3>Certificates</h3>
      <p><?php echo htmlspecialchars($user['certificates']); ?></p>
  </div>
  <button id="update-skills-btn">Update Profile</button>
</div>

<!-- Update Skills Modal -->
<div id="update-skills-modal" style="display: none;">
  <form id="update-skills-form" method="POST" action="profile.php">
      <h3>Update Skills, Projects, and Certificates</h3>

      <label for="skills">Skills:</label>
      <textarea id="skills" name="skills"><?php echo htmlspecialchars($user['skills']); ?></textarea>

      <label for="recent_projects">Recent Projects:</label>
      <textarea id="recent_projects" name="recent_projects"><?php echo htmlspecialchars($user['recent_projects']); ?></textarea>

      <label for="certificates">Certificates:</label>
      <textarea id="certificates" name="certificates"><?php echo htmlspecialchars($user['certificates']); ?></textarea>

      <button type="submit" name="update_skills">Save Changes</button>
      <button type="button" id="cancel-skills-edit">Cancel</button>
  </form>
</div>
<script src="navs.js"></script>
<script src="profile.js"></script>
</body>
</html>
