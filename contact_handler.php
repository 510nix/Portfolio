<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions/portfolio_functions.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    // Get and sanitize input
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    // Validate input
    if (empty($name) || empty($email) || empty($message)) {
        $response['message'] = 'All fields are required.';
    } elseif (!validateEmail($email)) {
        $response['message'] = 'Please enter a valid email address.';
    } elseif (strlen($message) < 10) {
        $response['message'] = 'Message must be at least 10 characters long.';
    } else {
        // Save to database
        if (saveContactMessage($pdo, $name, $email, $message)) {
            $response['success'] = true;
            $response['message'] = 'Thank you for your message! I will get back to you soon.';
        } else {
            $response['message'] = 'Sorry, there was an error sending your message. Please try again.';
        }
    }
    
    // Return JSON response for AJAX requests
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // For regular form submission, redirect with message
    $_SESSION['contact_message'] = $response['message'];
    $_SESSION['contact_success'] = $response['success'];
    
    // Use absolute URL for redirect
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['REQUEST_URI']);
    $redirectUrl = $protocol . '://' . $host . $path . '/index.php#contact';
    
    header('Location: ' . $redirectUrl);
    exit;
}
?>
