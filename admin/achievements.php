<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_achievement'])) {
        $title = trim($_POST['title']);
        $year = trim($_POST['year']);
        $description = trim($_POST['description']);
        
        if (!empty($title) && !empty($year)) {
            $sql = "INSERT INTO achievements (title, year, description) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$title, $year, $description])) {
                $message = 'Achievement added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error adding achievement.';
                $messageType = 'error';
            }
        } else {
            $message = 'Title and year are required.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['edit_achievement'])) {
        $id = (int)$_POST['achievement_id'];
        $title = trim($_POST['title']);
        $year = trim($_POST['year']);
        $description = trim($_POST['description']);
        
        if (!empty($title) && !empty($year)) {
            $sql = "UPDATE achievements SET title = ?, year = ?, description = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$title, $year, $description, $id])) {
                $message = 'Achievement updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating achievement.';
                $messageType = 'error';
            }
        } else {
            $message = 'Title and year are required.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['delete_achievement'])) {
        $id = (int)$_POST['achievement_id'];
        $sql = "DELETE FROM achievements WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$id])) {
            $message = 'Achievement deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error deleting achievement.';
            $messageType = 'error';
        }
    }
}

// Get all achievements
$achievements = fetchAll($pdo, "SELECT * FROM achievements ORDER BY year DESC");

// Check if achievements table exists, if not create it
try {
    $pdo->query("SELECT 1 FROM achievements LIMIT 1");
} catch (PDOException $e) {
    // Table doesn't exist, create it
    $createTable = "
    CREATE TABLE achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        year VARCHAR(10) NOT NULL,
        description TEXT,
        date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($createTable);
    $achievements = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements - Admin</title>
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
                <li><a href="skills.php"><i class="fas fa-star"></i> Skills</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="education.php"><i class="fas fa-graduation-cap"></i> Education</a></li>
                <li><a href="achievements.php" class="active"><i class="fas fa-trophy"></i> Achievements</a></li>
                <li><a href="about.php"><i class="fas fa-user"></i> About</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Achievements Management</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Add Achievement Form -->
            <div class="content-section">
                <h2>Add New Achievement</h2>
                <form method="POST" class="admin-form">
                    <div class="form-group">
                        <label for="title">Achievement Title *</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="year">Year *</label>
                        <input type="text" id="year" name="year" placeholder="e.g., 2024" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" placeholder="Brief description of the achievement..."></textarea>
                    </div>

                    <button type="submit" name="add_achievement" class="btn">
                        <i class="fas fa-plus"></i> Add Achievement
                    </button>
                </form>
            </div>

            <!-- Achievements List -->
            <div class="content-section">
                <h2>Current Achievements (<?php echo count($achievements); ?>)</h2>
                
                <?php if (empty($achievements)): ?>
                    <div class="text-center">
                        <i class="fas fa-trophy" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <p>No achievements added yet. Add your first achievement above!</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Year</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($achievements as $achievement): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($achievement['title']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($achievement['year']); ?></td>
                                        <td><?php echo htmlspecialchars($achievement['description'] ?? 'No description'); ?></td>
                                        <td>
                                            <button onclick="editAchievement(<?php echo $achievement['id']; ?>, '<?php echo htmlspecialchars($achievement['title'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($achievement['year'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($achievement['description'] ?? '', ENT_QUOTES); ?>')" 
                                                    class="btn btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this achievement?')">
                                                <input type="hidden" name="achievement_id" value="<?php echo $achievement['id']; ?>">
                                                <button type="submit" name="delete_achievement" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
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

    <!-- Edit Achievement Modal -->
    <div id="editModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: white; margin: 10% auto; padding: 0; border-radius: 8px; width: 90%; max-width: 500px;">
            <div class="modal-header" style="background: #2c3e50; color: white; padding: 1rem 1.5rem; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0;">Edit Achievement</h3>
                <span class="close" onclick="closeEditModal()" style="color: white; font-size: 1.5rem; font-weight: bold; cursor: pointer;">&times;</span>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form method="POST" id="editForm">
                    <input type="hidden" name="achievement_id" id="edit_id">
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="edit_title" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Achievement Title *</label>
                        <input type="text" id="edit_title" name="title" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="edit_year" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Year *</label>
                        <input type="text" id="edit_year" name="year" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="edit_description" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Description</label>
                        <textarea id="edit_description" name="description" rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; resize: vertical; box-sizing: border-box;"></textarea>
                    </div>

                    <div style="text-align: right;">
                        <button type="button" onclick="closeEditModal()" style="background: #6c757d; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; margin-right: 0.5rem; cursor: pointer;">Cancel</button>
                        <button type="submit" name="edit_achievement" style="background: #007bff; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer;">
                            <i class="fas fa-save"></i> Update Achievement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editAchievement(id, title, year, description) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_year').value = year;
            document.getElementById('edit_description').value = description;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
