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

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read'])) {
        $id = (int)$_POST['message_id'];
        if (markMessageAsRead($pdo, $id)) {
            $message = 'Message marked as read.';
            $messageType = 'success';
        }
    } elseif (isset($_POST['delete_message'])) {
        $id = (int)$_POST['message_id'];
        if (deleteContactMessage($pdo, $id)) {
            $message = 'Message deleted successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete message.';
            $messageType = 'error';
        }
    }
}

// Get all messages
$messages = getContactMessages($pdo);
$unreadCount = getUnreadMessagesCount($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin</title>
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
                <li><a href="messages.php" class="active"><i class="fas fa-envelope"></i> Messages <?php if($unreadCount > 0): ?><span class="badge"><?php echo $unreadCount; ?></span><?php endif; ?></a></li>
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
                <h1>Contact Messages</h1>
                <div class="user-info">
                    <?php echo $unreadCount; ?> unread messages
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Messages List -->
            <div class="content-section">
                <h2>All Messages (<?php echo count($messages); ?>)</h2>
                
                <?php if (empty($messages)): ?>
                    <div class="text-center">
                        <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <p>No messages yet. Messages from your contact form will appear here.</p>
                        <a href="../index.php#contact" target="_blank" class="btn">Visit Contact Form</a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $msg): ?>
                                    <tr <?php echo !$msg['is_read'] ? 'class="unread"' : ''; ?>>
                                        <td>
                                            <?php if ($msg['is_read']): ?>
                                                <span class="status-read"><i class="fas fa-envelope-open"></i> Read</span>
                                            <?php else: ?>
                                                <span class="status-unread"><i class="fas fa-envelope"></i> New</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>">
                                                <?php echo htmlspecialchars($msg['email']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="message-preview">
                                                <span id="message-start-<?php echo $msg['id']; ?>">
                                                    <?php echo htmlspecialchars(substr($msg['message'], 0, 100)); ?>
                                                </span>
                                                <?php if (strlen($msg['message']) > 100): ?>
                                                    <span class="expand-message" id="expand-btn-<?php echo $msg['id']; ?>" onclick="toggleMessage(<?php echo $msg['id']; ?>)">... [Read More]</span>
                                                    <span id="full-message-<?php echo $msg['id']; ?>" class="full-message" style="display: none;">
                                                        <?php echo nl2br(htmlspecialchars(substr($msg['message'], 100))); ?>
                                                        <span class="expand-message" onclick="toggleMessage(<?php echo $msg['id']; ?>)" style="color: #3498db; cursor: pointer; font-weight: bold; margin-left: 5px;">[Show Less]</span>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($msg['date_submitted'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if (!$msg['is_read']): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                                        <button type="submit" name="mark_read" class="btn btn-sm btn-success" title="Mark as Read">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <button onclick="replyWithGmail('<?php echo htmlspecialchars($msg['email']); ?>', '<?php echo htmlspecialchars($msg['name']); ?>')" 
                                                        class="btn btn-sm" title="Reply with Gmail">
                                                    <i class="fas fa-reply"></i>
                                                </button>
                                                
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this message?')">
                                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                                    <button type="submit" name="delete_message" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
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
        function replyWithGmail(email, name) {
            const subject = encodeURIComponent('Re: Portfolio Contact');
            const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${email}&subject=${subject}`;
            
            window.open(gmailUrl, '_blank');
        }

        function toggleMessage(id) {
            const fullMessage = document.getElementById('full-message-' + id);
            const expandBtn = document.getElementById('expand-btn-' + id);
            
            if (fullMessage.style.display === 'none') {
                fullMessage.style.display = 'inline';
                expandBtn.style.display = 'none';
            } else {
                fullMessage.style.display = 'none';
                expandBtn.style.display = 'inline';
            }
        }
    </script>

    <style>
        .message-preview {
            max-width: 300px;
        }
        
        .expand-message {
            color: #3498db;
            cursor: pointer;
            font-weight: bold;
        }
        
        .expand-message:hover {
            text-decoration: underline;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.25rem;
        }
        
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
        }
    </style>
</body>
</html>
