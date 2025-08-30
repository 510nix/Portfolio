<?php
// Forgot Password System with Real Email Verification
session_start();
require_once '../config/database.php';
require_once '../config/email.php';

$message = '';
$step = $_GET['step'] ?? $_POST['step'] ?? '1';

// Email sending function using proper configuration
function sendResetEmail($email, $code) {
    $emailSender = new EmailSender();
    
    $subject = "Portfolio Admin - Password Reset Code";
    $emailMessage = "Hello,

Your password reset verification code is: $code

This code will expire in 10 minutes.
If you didn't request this password reset, please ignore this email.

Best regards,
Portfolio Admin System";
    
    return $emailSender->sendEmail($email, $subject, $emailMessage);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === '1') {
        // Step 1: Request reset
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $message = 'Please enter your email address.';
        } else {
            // Check if email exists in admin_users
            $stmt = $pdo->prepare("SELECT id, username FROM admin_users WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if (!$admin) {
                $message = 'No admin account found with this email address.';
            } else {
                // Generate reset code
                $reset_code = sprintf('%06d', mt_rand(100000, 999999));
                
                // Store in session
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_code'] = $reset_code;
                $_SESSION['reset_time'] = time();
                $_SESSION['reset_admin_id'] = $admin['id'];
                
                // Send email
                $emailSent = sendResetEmail($email, $reset_code);
                
                if ($emailSent) {
                    $message = 'Verification code sent to your email! Please check your inbox and spam folder.';
                    $step = '2';
                } else {
                    $message = 'Failed to send email. Please check your email configuration.<br><small>For testing, your code is: <strong>' . $reset_code . '</strong></small>';
                    $step = '2'; // Continue anyway for testing
                }
            }
        }
    } elseif ($step === '2') {
        // Step 2: Verify code
        $entered_code = trim($_POST['verification_code'] ?? '');
        
        // Debug info
        $debug_info = "Debug: Step=$step, Entered code='$entered_code', Session code='" . ($_SESSION['reset_code'] ?? 'not set') . "'";
        
        if (!isset($_SESSION['reset_code']) || !isset($_SESSION['reset_time'])) {
            $message = 'Session expired. Please start over. ' . $debug_info;
            $step = '1';
        } elseif (time() - $_SESSION['reset_time'] > 600) { // 10 minutes
            $message = 'Verification code expired. Please start over.';
            unset($_SESSION['reset_email'], $_SESSION['reset_code'], $_SESSION['reset_time'], $_SESSION['reset_admin_id']);
            $step = '1';
        } elseif ($entered_code !== $_SESSION['reset_code']) {
            $message = 'Invalid verification code. Please try again. ' . $debug_info;
        } else {
            $message = 'Code verified! Now set your new password.';
            $step = '3';
        }
    } elseif ($step === '3') {
        // Step 3: Reset password
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($new_password) || empty($confirm_password)) {
            $message = 'Please fill in both password fields.';
        } elseif (strlen($new_password) < 6) {
            $message = 'Password must be at least 6 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $message = 'Passwords do not match.';
        } elseif (!isset($_SESSION['reset_admin_id'])) {
            $message = 'Session expired. Please start over.';
            $step = '1';
        } else {
            try {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['reset_admin_id']]);
                
                // Clear session
                unset($_SESSION['reset_email'], $_SESSION['reset_code'], $_SESSION['reset_time'], $_SESSION['reset_admin_id']);
                
                $message = 'Password reset successfully! <a href="login.php">Login now</a>';
                $step = 'success';
            } catch (Exception $e) {
                $message = 'Error updating password. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Portfolio Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reset-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .reset-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }

        .reset-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 0.5rem;
            background: #f5f5f5;
            margin: 0 2px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            color: #999;
        }

        .step.active {
            background: #4CAF50;
            color: white;
        }

        .step.completed {
            background: #2196F3;
            color: white;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            margin-left: 10px;
        }

        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            text-align: center;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .back-link {
            text-align: center;
            margin-top: 1rem;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .email-info {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1><i class="fas fa-key"></i> Forgot Password</h1>
            <p>Reset your admin password securely</p>
        </div>

        <?php if ($step !== 'success'): ?>
        <div class="steps">
            <div class="step <?= $step >= 1 ? ($step == 1 ? 'active' : 'completed') : '' ?>">Email</div>
            <div class="step <?= $step >= 2 ? ($step == 2 ? 'active' : 'completed') : '' ?>">Verify</div>
            <div class="step <?= $step >= 3 ? 'active' : '' ?>">Reset</div>
        </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'Error') !== false || strpos($message, 'Invalid') !== false || strpos($message, 'expired') !== false ? 'error' : 'success' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if ($step === '1'): ?>
            <!-- Step 1: Email Input -->
            <div class="email-info">
                <i class="fas fa-info-circle"></i> Enter the email address associated with your admin account
            </div>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your admin email">
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-paper-plane"></i> Send Reset Code
                </button>
            </form>

        <?php elseif ($step === '2'): ?>
            <!-- Step 2: Email Verification -->
            <div class="email-info">
                <i class="fas fa-envelope"></i> Check your email: <?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?>
                <br><small>Don't see the email? Check your spam/junk folder</small>
            </div>
            
            <form method="POST">
                <input type="hidden" name="step" value="2">
                <div class="form-group">
                    <label for="verification_code">Verification Code:</label>
                    <input type="text" id="verification_code" name="verification_code" required 
                           placeholder="Enter 6-digit code from email" maxlength="6">
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-check"></i> Verify Code
                </button>
                <a href="forgot_password.php" class="btn btn-secondary" style="display: inline-block; text-align: center; text-decoration: none; margin-top: 10px;">
                    <i class="fas fa-redo"></i> Start Over
                </a>
            </form>

        <?php elseif ($step === '3'): ?>
            <!-- Step 3: Reset Password -->
            <div class="email-info">
                <i class="fas fa-shield-alt"></i> Choose a strong password for your account
            </div>
            <form method="POST">
                <input type="hidden" name="step" value="3">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required 
                           placeholder="Enter new password" minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Confirm new password">
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Reset Password
                </button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="login.php">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</body>
</html>
