<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once '../functions/portfolio_functions.php';

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $skillData = [
                    'skill_name' => sanitizeInput($_POST['skill_name']),
                    'category' => sanitizeInput($_POST['category']),
                    'percentage' => (int)$_POST['percentage'],
                    'display_order' => (int)($_POST['display_order'] ?? 0)
                ];
                
                if (addSkill($pdo, $skillData)) {
                    $message = 'Skill added successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error adding skill.';
                    $messageType = 'error';
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $skillData = [
                    'skill_name' => sanitizeInput($_POST['skill_name']),
                    'category' => sanitizeInput($_POST['category']),
                    'percentage' => (int)$_POST['percentage'],
                    'display_order' => (int)($_POST['display_order'] ?? 0)
                ];
                
                if (updateSkill($pdo, $id, $skillData)) {
                    $message = 'Skill updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating skill.';
                    $messageType = 'error';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                if (deleteSkill($pdo, $id)) {
                    $message = 'Skill deleted successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error deleting skill.';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get all skills
$skills = fetchAll($pdo, "SELECT * FROM skills ORDER BY category, display_order ASC");

// Get skill for editing if ID is provided
$editSkill = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editSkill = getSkillById($pdo, $_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills - Admin</title>
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
                <li><a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a></li>
                <li><a href="projects.php"><i class="fas fa-project-diagram"></i> Projects</a></li>
                <li><a href="skills.php" class="active"><i class="fas fa-star"></i> Skills</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
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
                <h1>Manage Skills</h1>
                <div class="user-info">
                    Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Add/Edit Skill Form -->
            <div class="content-section">
                <h2><?php echo $editSkill ? 'Edit Skill' : 'Add New Skill'; ?></h2>
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $editSkill ? 'edit' : 'add'; ?>">
                    <?php if ($editSkill): ?>
                        <input type="hidden" name="id" value="<?php echo $editSkill['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="skill_name">Skill Name:</label>
                            <input type="text" id="skill_name" name="skill_name" required 
                                   value="<?php echo $editSkill ? htmlspecialchars($editSkill['skill_name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category:</label>
                            <input type="text" id="category" name="category" required 
                                   placeholder="e.g., Web Development, Programming Languages"
                                   value="<?php echo $editSkill ? htmlspecialchars($editSkill['category']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="percentage">Skill Level (%):</label>
                            <input type="range" id="percentage" name="percentage" min="0" max="100" 
                                   value="<?php echo $editSkill ? $editSkill['percentage'] : '50'; ?>"
                                   oninput="updatePercentageDisplay(this.value)">
                            <div class="percentage-display">
                                <span id="percentageValue"><?php echo $editSkill ? $editSkill['percentage'] : '50'; ?>%</span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="display_order">Display Order:</label>
                            <input type="number" id="display_order" name="display_order" min="0" 
                                   value="<?php echo $editSkill ? $editSkill['display_order'] : '0'; ?>">
                            <small>Lower numbers appear first</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> <?php echo $editSkill ? 'Update Skill' : 'Add Skill'; ?>
                        </button>
                        <?php if ($editSkill): ?>
                            <a href="skills.php" class="btn">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Skills List -->
            <div class="content-section">
                <h2>All Skills</h2>
                
                <?php if (empty($skills)): ?>
                    <p class="text-center">No skills found. Add your first skill above!</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Skill Name</th>
                                    <th>Category</th>
                                    <th>Percentage</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $currentCategory = '';
                                foreach ($skills as $skill): 
                                    if ($skill['category'] !== $currentCategory):
                                        $currentCategory = $skill['category'];
                                ?>
                                <?php endif; ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($skill['skill_name']); ?></td>
                                        <td><?php echo htmlspecialchars($skill['category']); ?></td>
                                        <td>
                                            <div class="skill-progress">
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: <?php echo $skill['percentage']; ?>%"></div>
                                                </div>
                                                <span><?php echo $skill['percentage']; ?>%</span>
                                            </div>
                                        </td>
                                        <td><?php echo $skill['display_order']; ?></td>
                                        <td>
                                            <span class="status-<?php echo $skill['status']; ?>">
                                                <?php echo ucfirst($skill['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="skills.php?edit=<?php echo $skill['id']; ?>" class="btn btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this skill?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $skill['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function updatePercentageDisplay(value) {
            document.getElementById('percentageValue').textContent = value + '%';
        }
    </script>

    <style>
        .skill-progress {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .progress-bar {
            width: 100px;
            height: 10px;
            background: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: #3498db;
            transition: width 0.3s ease;
        }
        
        .percentage-display {
            text-align: center;
            margin-top: 5px;
            font-weight: bold;
            color: #3498db;
        }
        
        input[type="range"] {
            width: 100%;
        }
        
        .status-active {
            color: #27ae60;
            font-weight: bold;
        }
        
        .status-inactive {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</body>
</html>
