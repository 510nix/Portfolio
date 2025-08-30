<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once '../functions/portfolio_functions.php';

// Get dashboard statistics
$totalProjects = fetchOne($pdo, "SELECT COUNT(*) as count FROM projects WHERE status = 'active'")['count'];
$totalSkills = fetchOne($pdo, "SELECT COUNT(*) as count FROM skills WHERE status = 'active'")['count'];
$unreadMessages = getUnreadMessagesCount($pdo);
$totalMessages = fetchOne($pdo, "SELECT COUNT(*) as count FROM contact_messages")['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-dashboard"></i> Dashboard</a></li>
                <li><a href="projects.php"><i class="fas fa-project-diagram"></i> Projects</a></li>
                <li><a href="skills.php"><i class="fas fa-star"></i> Skills</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages <?php if($unreadMessages > 0): ?><span class="badge"><?php echo $unreadMessages; ?></span><?php endif; ?></a></li>
                <li><a href="education.php"><i class="fas fa-graduation-cap"></i> Education</a></li>
                <li><a href="achievements.php"><i class="fas fa-trophy"></i> Achievements</a></li>
                <li><a href="about.php"><i class="fas fa-user"></i> About</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                </div>
            </div>

            <?php if (isset($_GET['welcome'])): ?>
                <div class="alert alert-success" style="margin: 20px; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;">
                    <i class="fas fa-check-circle"></i> 
                    <strong>Welcome!</strong> Your admin account has been created successfully. Your portfolio is now ready to manage!
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalProjects; ?></h3>
                        <p>Active Projects</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalSkills; ?></h3>
                        <p>Skills</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $unreadMessages; ?></h3>
                        <p>Unread Messages</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $totalMessages; ?></h3>
                        <p>Total Messages</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-section">
                <h2>Quick Actions</h2>
                <div class="quick-actions">
                    <a href="projects.php?action=add" class="action-card">
                        <i class="fas fa-plus"></i>
                        <span>Add Project</span>
                    </a>
                    <a href="skills.php?action=add" class="action-card">
                        <i class="fas fa-plus"></i>
                        <span>Add Skill</span>
                    </a>
                    <a href="education.php?action=add" class="action-card">
                        <i class="fas fa-plus"></i>
                        <span>Add Education</span>
                    </a>
                    <a href="achievements.php?action=add" class="action-card">
                        <i class="fas fa-plus"></i>
                        <span>Add Achievement</span>
                    </a>
                    <a href="messages.php" class="action-card">
                        <i class="fas fa-envelope-open"></i>
                        <span>Check Messages</span>
                    </a>
                    <a href="about.php" class="action-card">
                        <i class="fas fa-edit"></i>
                        <span>Edit About</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
