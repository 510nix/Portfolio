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
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload = handleFileUpload($_FILES['image'], '../uploads/');
                    if ($upload['success']) {
                        $image = 'uploads/' . $upload['filename'];
                    } else {
                        $message = $upload['message'];
                        $messageType = 'error';
                        break;
                    }
                }
                
                $projectData = [
                    'title' => sanitizeInput($_POST['title']),
                    'description' => sanitizeInput($_POST['description']),
                    'image' => $image,
                    'category' => $_POST['category'],
                    'github_link' => sanitizeInput($_POST['github_link']),
                    'live_link' => sanitizeInput($_POST['live_link']),
                    'technologies' => sanitizeInput($_POST['technologies'])
                ];
                
                if (addProject($pdo, $projectData)) {
                    $message = 'Project added successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error adding project.';
                    $messageType = 'error';
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $project = getProjectById($pdo, $id);
                
                $image = $project['image']; // Keep existing image by default
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $upload = handleFileUpload($_FILES['image'], '../uploads/');
                    if ($upload['success']) {
                        $image = 'uploads/' . $upload['filename'];
                        // Delete old image if it exists
                        if ($project['image'] && file_exists('../' . $project['image'])) {
                            unlink('../' . $project['image']);
                        }
                    } else {
                        $message = $upload['message'];
                        $messageType = 'error';
                        break;
                    }
                }
                
                $projectData = [
                    'title' => sanitizeInput($_POST['title']),
                    'description' => sanitizeInput($_POST['description']),
                    'image' => $image,
                    'category' => $_POST['category'],
                    'github_link' => sanitizeInput($_POST['github_link']),
                    'live_link' => sanitizeInput($_POST['live_link']),
                    'technologies' => sanitizeInput($_POST['technologies'])
                ];
                
                if (updateProject($pdo, $id, $projectData)) {
                    $message = 'Project updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating project.';
                    $messageType = 'error';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $project = getProjectById($pdo, $id);
                
                if (deleteProject($pdo, $id)) {
                    // Delete image file
                    if ($project['image'] && file_exists('../' . $project['image'])) {
                        unlink('../' . $project['image']);
                    }
                    $message = 'Project deleted successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error deleting project.';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get all projects
$projects = fetchAll($pdo, "SELECT * FROM projects ORDER BY created_at DESC");

// Get project for editing if ID is provided
$editProject = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editProject = getProjectById($pdo, $_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects - Admin</title>
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
                <li><a href="projects.php" class="active"><i class="fas fa-project-diagram"></i> Projects</a></li>
                <li><a href="skills.php"><i class="fas fa-star"></i> Skills</a></li>
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
                <h1>Manage Projects</h1>
                <div class="user-info">
                    Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Add/Edit Project Form -->
            <div class="content-section">
                <h2><?php echo $editProject ? 'Edit Project' : 'Add New Project'; ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $editProject ? 'edit' : 'add'; ?>">
                    <?php if ($editProject): ?>
                        <input type="hidden" name="id" value="<?php echo $editProject['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Project Title:</label>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo $editProject ? htmlspecialchars($editProject['title']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Category:</label>
                            <select id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="web" <?php echo ($editProject && $editProject['category'] === 'web') ? 'selected' : ''; ?>>Web</option>
                                <option value="app" <?php echo ($editProject && $editProject['category'] === 'app') ? 'selected' : ''; ?>>App</option>
                                <option value="other" <?php echo ($editProject && $editProject['category'] === 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="4" required><?php echo $editProject ? htmlspecialchars($editProject['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="technologies">Technologies Used:</label>
                        <input type="text" id="technologies" name="technologies" 
                               placeholder="e.g., PHP, JavaScript, MySQL"
                               value="<?php echo $editProject ? htmlspecialchars($editProject['technologies']) : ''; ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="github_link">GitHub Link:</label>
                            <input type="url" id="github_link" name="github_link" 
                                   value="<?php echo $editProject ? htmlspecialchars($editProject['github_link']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="live_link">Live Demo Link:</label>
                            <input type="url" id="live_link" name="live_link" 
                                   value="<?php echo $editProject ? htmlspecialchars($editProject['live_link']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Project Image:</label>
                        <?php if ($editProject && $editProject['image']): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="../<?php echo htmlspecialchars($editProject['image']); ?>" 
                                     alt="Current Image" style="max-width: 200px; height: auto;">
                                <p><small>Current image. Upload a new one to replace it.</small></p>
                            </div>
                        <?php endif; ?>
                        <div class="file-upload">
                            <input type="file" id="image" name="image" accept="image/*">
                            <label for="image" class="file-upload-label">
                                <i class="fas fa-upload"></i> Choose Image File
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> <?php echo $editProject ? 'Update Project' : 'Add Project'; ?>
                        </button>
                        <?php if ($editProject): ?>
                            <a href="projects.php" class="btn">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Projects List -->
            <div class="content-section">
                <h2>All Projects</h2>
                <div class="card-grid">
                    <?php foreach ($projects as $project): ?>
                        <div class="item-card">
                            <?php if ($project['image']): ?>
                                <img src="../<?php echo htmlspecialchars($project['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <?php endif; ?>
                            <div class="item-card-content">
                                <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                <p><strong>Category:</strong> <?php echo ucfirst($project['category']); ?></p>
                                <p><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?></p>
                                <?php if ($project['technologies']): ?>
                                    <p><strong>Technologies:</strong> <?php echo htmlspecialchars($project['technologies']); ?></p>
                                <?php endif; ?>
                                <div class="item-card-actions">
                                    <a href="projects.php?edit=<?php echo $project['id']; ?>" class="btn btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this project?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($projects)): ?>
                    <p class="text-center">No projects found. Add your first project above!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // File upload preview
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const label = document.querySelector('.file-upload-label');
                label.innerHTML = '<i class="fas fa-check"></i> ' + file.name;
            }
        });
    </script>
</body>
</html>
