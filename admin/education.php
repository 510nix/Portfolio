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
                $educationData = [
                    'degree' => sanitizeInput($_POST['degree']),
                    'institution' => sanitizeInput($_POST['institution']),
                    'year' => sanitizeInput($_POST['year']),
                    'description' => sanitizeInput($_POST['description']),
                    'grade_info' => sanitizeInput($_POST['grade_info']),
                    'display_order' => (int)($_POST['display_order'] ?? 0)
                ];
                
                $sql = "INSERT INTO education (degree, institution, year, description, grade_info, display_order) VALUES (?, ?, ?, ?, ?, ?)";
                if (executeQuery($pdo, $sql, array_values($educationData))) {
                    header('Location: education.php?added=1');
                    exit;
                } else {
                    $message = 'Error adding education entry.';
                    $messageType = 'error';
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $educationData = [
                    'degree' => sanitizeInput($_POST['degree']),
                    'institution' => sanitizeInput($_POST['institution']),
                    'year' => sanitizeInput($_POST['year']),
                    'description' => sanitizeInput($_POST['description']),
                    'grade_info' => sanitizeInput($_POST['grade_info']),
                    'display_order' => (int)($_POST['display_order'] ?? 0)
                ];
                
                $sql = "UPDATE education SET degree = ?, institution = ?, year = ?, description = ?, grade_info = ?, display_order = ? WHERE id = ?";
                $params = array_merge(array_values($educationData), [$id]);
                if (executeQuery($pdo, $sql, $params)) {
                    // Redirect to clear the form and show success message
                    header('Location: education.php?updated=1');
                    exit;
                } else {
                    $message = 'Error updating education entry.';
                    $messageType = 'error';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $sql = "DELETE FROM education WHERE id = ?";
                if (executeQuery($pdo, $sql, [$id])) {
                    $message = 'Education entry deleted successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error deleting education entry.';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Check for success messages
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $message = 'Education entry updated successfully!';
    $messageType = 'success';
} elseif (isset($_GET['added']) && $_GET['added'] == '1') {
    $message = 'Education entry added successfully!';
    $messageType = 'success';
}

// Get all education entries
$education = fetchAll($pdo, "SELECT * FROM education ORDER BY display_order ASC");

// Get education entry for editing if ID is provided
$editEducation = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editEducation = fetchOne($pdo, "SELECT * FROM education WHERE id = ?", [$_GET['edit']]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Education - Admin</title>
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
                <li><a href="education.php" class="active"><i class="fas fa-graduation-cap"></i> Education</a></li>
                <li><a href="achievements.php"><i class="fas fa-trophy"></i> Achievements</a></li>
                <li><a href="about.php"><i class="fas fa-user"></i> About</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Manage Education</h1>
                <div class="user-info">
                    Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Add/Edit Education Form -->
            <div class="content-section">
                <h2><?php echo $editEducation ? 'Edit Education Entry' : 'Add New Education Entry'; ?></h2>
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $editEducation ? 'edit' : 'add'; ?>">
                    <?php if ($editEducation): ?>
                        <input type="hidden" name="id" value="<?php echo $editEducation['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="degree">Degree/Certificate:</label>
                            <input type="text" id="degree" name="degree" required 
                                   placeholder="e.g., Bachelor's Degree, HSC, Diploma"
                                   value="<?php echo $editEducation ? htmlspecialchars(html_entity_decode($editEducation['degree'], ENT_QUOTES, 'UTF-8')) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="institution">Institution:</label>
                            <input type="text" id="institution" name="institution" required 
                                   placeholder="e.g., University Name, College Name"
                                   value="<?php echo $editEducation ? htmlspecialchars(html_entity_decode($editEducation['institution'], ENT_QUOTES, 'UTF-8')) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="year">Year/Duration:</label>
                            <input type="text" id="year" name="year" required 
                                   placeholder="e.g., 2023-2027, 2019-2021"
                                   value="<?php echo $editEducation ? htmlspecialchars($editEducation['year']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="grade_info">Grade/CGPA:</label>
                            <input type="text" id="grade_info" name="grade_info" 
                                   placeholder="e.g., CGPA: 3.68, GPA: 5.00"
                                   value="<?php echo $editEducation ? htmlspecialchars(html_entity_decode($editEducation['grade_info'], ENT_QUOTES, 'UTF-8')) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description (Optional):</label>
                        <textarea id="description" name="description" rows="3" 
                                  placeholder="Additional details about your studies, achievements, etc."><?php echo $editEducation ? htmlspecialchars(html_entity_decode($editEducation['description'], ENT_QUOTES, 'UTF-8')) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="display_order">Display Order:</label>
                        <input type="number" id="display_order" name="display_order" min="0" 
                               value="<?php echo $editEducation ? $editEducation['display_order'] : '0'; ?>">
                        <small>Lower numbers appear first (0 = first, 1 = second, etc.)</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> <?php echo $editEducation ? 'Update Education' : 'Add Education'; ?>
                        </button>
                        <?php if ($editEducation): ?>
                            <a href="education.php" class="btn">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Education List -->
            <div class="content-section">
                <h2>All Education Entries</h2>
                
                <?php if (empty($education)): ?>
                    <p class="text-center">No education entries found. Add your first education entry above!</p>
                <?php else: ?>
                    <div class="education-grid">
                        <?php foreach ($education as $edu): ?>
                            <div class="education-card">
                                <div class="education-header">
                                    <h3><?php echo htmlspecialchars($edu['degree']); ?></h3>
                                    <span class="education-year"><?php echo htmlspecialchars($edu['year']); ?></span>
                                </div>
                                <p class="institution"><?php echo htmlspecialchars($edu['institution']); ?></p>
                                <?php if ($edu['grade_info']): ?>
                                    <p class="grade"><?php echo htmlspecialchars($edu['grade_info']); ?></p>
                                <?php endif; ?>
                                <?php if ($edu['description']): ?>
                                    <p class="description"><?php echo htmlspecialchars($edu['description']); ?></p>
                                <?php endif; ?>
                                <div class="education-actions">
                                    <span class="order-badge">Order: <?php echo $edu['display_order']; ?></span>
                                    <div class="action-buttons">
                                        <a href="education.php?edit=<?php echo $edu['id']; ?>" class="btn btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this education entry?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $edu['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .education-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .education-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
        }
        
        .education-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .education-header h3 {
            color: #2c3e50;
            margin: 0;
            flex: 1;
        }
        
        .education-year {
            background: #3498db;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            margin-left: 1rem;
        }
        
        .institution {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .grade {
            color: #27ae60;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .description {
            color: #7f8c8d;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        
        .education-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #ecf0f1;
        }
        
        .order-badge {
            background: #f8f9fa;
            color: #7f8c8d;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
    </style>
</body>
</html>
