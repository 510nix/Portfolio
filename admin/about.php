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

// Check for session messages
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    $messageType = $_SESSION['admin_message_type'];
    unset($_SESSION['admin_message'], $_SESSION['admin_message_type']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_about'])) {
    $content = trim($_POST['content']);
    $cvFile = null;
    
    // Handle CV file upload
    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleCVUpload($_FILES['cv_file']);
        if ($uploadResult['success']) {
            $cvFile = $uploadResult['filepath'];
        } else {
            $message = $uploadResult['message'];
            $messageType = 'error';
        }
    } else {
        // Keep existing CV file if no new one uploaded
        $currentAbout = getAboutInfo($pdo);
        $cvFile = $currentAbout['cv_file'];
    }
    
    if (!empty($content) && empty($message)) {
        if (updateAboutInfo($pdo, $content, $cvFile)) {
            $_SESSION['admin_message'] = 'About section updated successfully!';
            $_SESSION['admin_message_type'] = 'success';
            header('Location: about.php');
            exit;
        } else {
            $message = 'Error updating about section.';
            $messageType = 'error';
        }
    } elseif (empty($content)) {
        $message = 'Content cannot be empty.';
        $messageType = 'error';
    }
}

// Handle CV file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cv'])) {
    $currentAbout = getAboutInfo($pdo);
    if (!empty($currentAbout['cv_file']) && file_exists('../' . $currentAbout['cv_file'])) {
        unlink('../' . $currentAbout['cv_file']);
    }
    
    if (updateAboutInfo($pdo, $currentAbout['content'], null)) {
        $_SESSION['admin_message'] = 'CV file deleted successfully!';
        $_SESSION['admin_message_type'] = 'success';
        header('Location: about.php');
        exit;
    } else {
        $message = 'Error deleting CV file.';
        $messageType = 'error';
    }
}

// Get current about content
$aboutInfo = getAboutInfo($pdo);
$aboutContent = $aboutInfo['content'];
$currentCV = $aboutInfo['cv_file'];

// Helper function for CV upload
function handleCVUpload($file) {
    $uploadDir = '../cv_files/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $maxSize = 10 * 1024 * 1024; // 10MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only PDF, DOC, and DOCX files are allowed.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 10MB.'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'cv_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    $relativePath = 'cv_files/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $relativePath];
    } else {
        return ['success' => false, 'message' => 'Failed to upload CV file.'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage About Section - Admin</title>
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
                <li><a href="achievements.php"><i class="fas fa-trophy"></i> Achievements</a></li>
                <li><a href="about.php" class="active"><i class="fas fa-user"></i> About</a></li>
                <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Manage About Section</h1>
                <div class="user-info">
                    Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- About Content Editor -->
            <div class="content-section">
                <h2>Edit About Section Content</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="content">About Me Content:</label>
                        <textarea id="content" name="content" rows="12" required 
                                  placeholder="Write about yourself, your goals, interests, and professional journey..."><?php echo htmlspecialchars($aboutContent); ?></textarea>
                        <small>This content will appear in the About section of your portfolio. You can use line breaks for paragraphs.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="cv_file">Upload CV/Resume (Optional):</label>
                        <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx">
                        <small>Supported formats: PDF, DOC, DOCX. Maximum size: 10MB.</small>
                        
                        <?php if (!empty($currentCV)): ?>
                            <div class="current-cv">
                                <p><strong>Current CV:</strong> 
                                    <a href="../<?php echo htmlspecialchars($currentCV); ?>" target="_blank">
                                        <i class="fas fa-file-pdf"></i> View Current CV
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="update_about" class="btn btn-success">
                            <i class="fas fa-save"></i> Update About Section
                        </button>
                        <a href="../index.php#about" target="_blank" class="btn">
                            <i class="fas fa-eye"></i> Preview on Site
                        </a>
                        <?php if (!empty($currentCV)): ?>
                            <button type="submit" name="delete_cv" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete the current CV?')">
                                <i class="fas fa-trash"></i> Delete Current CV
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Preview Section -->
            <div class="content-section">
                <h2>Current Content Preview</h2>
                <div class="about-preview">
                    <?php if ($aboutContent): ?>
                        <div class="preview-content">
                            <?php echo nl2br(htmlspecialchars($aboutContent)); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center"><em>No content available. Add some content above.</em></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tips Section -->
            <div class="content-section">
                <h2>Writing Tips</h2>
                <div class="tips-grid">
                    <div class="tip-card">
                        <i class="fas fa-lightbulb"></i>
                        <h3>Be Personal</h3>
                        <p>Share your passion, interests, and what drives you in your field.</p>
                    </div>
                    <div class="tip-card">
                        <i class="fas fa-target"></i>
                        <h3>Show Goals</h3>
                        <p>Mention your career aspirations and what you're working towards.</p>
                    </div>
                    <div class="tip-card">
                        <i class="fas fa-star"></i>
                        <h3>Highlight Strengths</h3>
                        <p>Showcase your key skills and what makes you unique.</p>
                    </div>
                    <div class="tip-card">
                        <i class="fas fa-heart"></i>
                        <h3>Add Personality</h3>
                        <p>Include hobbies and interests to show your human side.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .about-preview {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 8px;
            border-left: 4px solid #3498db;
            margin-top: 1rem;
        }
        
        .preview-content {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .tips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .tip-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .tip-card i {
            font-size: 2rem;
            color: #3498db;
            margin-bottom: 1rem;
        }
        
        .tip-card h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .tip-card p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        textarea {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
    </style>
</body>
</html>
