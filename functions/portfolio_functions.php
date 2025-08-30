<?php
require_once __DIR__ . '/../config/database.php';

// Get all active projects
function getProjects($pdo, $category = null) {
    $sql = "SELECT * FROM projects WHERE status = 'active'";
    $params = [];
    
    if ($category && $category !== 'all') {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    return fetchAll($pdo, $sql, $params);
}

// Get all active skills by category
function getSkillsByCategory($pdo) {
    $sql = "SELECT * FROM skills WHERE status = 'active' ORDER BY category, display_order ASC";
    $skills = fetchAll($pdo, $sql);
    
    $skillsByCategory = [];
    foreach ($skills as $skill) {
        $skillsByCategory[$skill['category']][] = $skill;
    }
    
    return $skillsByCategory;
}

// Get active education records
function getEducation($pdo) {
    $sql = "SELECT * FROM education WHERE status = 'active' ORDER BY display_order ASC";
    return fetchAll($pdo, $sql);
}

// Get achievements
function getAchievements($pdo) {
    $sql = "SELECT * FROM achievements ORDER BY year DESC";
    return fetchAll($pdo, $sql);
}

// Get about information
function getAboutInfo($pdo) {
    $sql = "SELECT content, cv_file FROM about_info ORDER BY updated_at DESC LIMIT 1";
    $result = fetchOne($pdo, $sql);
    return $result ? $result : ['content' => '', 'cv_file' => ''];
}

// Get active social links
function getSocialLinks($pdo) {
    $sql = "SELECT * FROM social_links WHERE status = 'active' ORDER BY display_order ASC";
    return fetchAll($pdo, $sql);
}

// Save contact message
function saveContactMessage($pdo, $name, $email, $message) {
    $sql = "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)";
    $stmt = executeQuery($pdo, $sql, [$name, $email, $message]);
    return $stmt !== false;
}

// Get contact messages for admin
function getContactMessages($pdo, $limit = null, $unread_only = false) {
    $sql = "SELECT * FROM contact_messages";
    $params = [];
    
    if ($unread_only) {
        $sql .= " WHERE is_read = 0";
    }
    
    $sql .= " ORDER BY date_submitted DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mark message as read
function markMessageAsRead($pdo, $id) {
    $sql = "UPDATE contact_messages SET is_read = 1 WHERE id = ?";
    return executeQuery($pdo, $sql, [$id]) !== false;
}

// Delete contact message
function deleteContactMessage($pdo, $id) {
    $sql = "DELETE FROM contact_messages WHERE id = ?";
    return executeQuery($pdo, $sql, [$id]) !== false;
}

// Get unread messages count
function getUnreadMessagesCount($pdo) {
    $sql = "SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0";
    $result = fetchOne($pdo, $sql);
    return $result ? $result['count'] : 0;
}

// Add new project
function addProject($pdo, $data) {
    $sql = "INSERT INTO projects (title, description, image, category, github_link, live_link, technologies) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $params = [
        $data['title'],
        $data['description'],
        $data['image'],
        $data['category'],
        $data['github_link'],
        $data['live_link'],
        $data['technologies']
    ];
    
    return executeQuery($pdo, $sql, $params) !== false;
}

// Update project
function updateProject($pdo, $id, $data) {
    $sql = "UPDATE projects SET title = ?, description = ?, image = ?, category = ?, 
            github_link = ?, live_link = ?, technologies = ? WHERE id = ?";
    $params = [
        $data['title'],
        $data['description'],
        $data['image'],
        $data['category'],
        $data['github_link'],
        $data['live_link'],
        $data['technologies'],
        $id
    ];
    
    return executeQuery($pdo, $sql, $params) !== false;
}

// Delete project
function deleteProject($pdo, $id) {
    $sql = "DELETE FROM projects WHERE id = ?";
    return executeQuery($pdo, $sql, [$id]) !== false;
}

// Get project by ID
function getProjectById($pdo, $id) {
    $sql = "SELECT * FROM projects WHERE id = ?";
    return fetchOne($pdo, $sql, [$id]);
}

// Add new skill
function addSkill($pdo, $data) {
    $sql = "INSERT INTO skills (skill_name, category, percentage, display_order) VALUES (?, ?, ?, ?)";
    $params = [
        $data['skill_name'],
        $data['category'],
        $data['percentage'],
        $data['display_order'] ?? 0
    ];
    
    return executeQuery($pdo, $sql, $params) !== false;
}

// Update skill
function updateSkill($pdo, $id, $data) {
    $sql = "UPDATE skills SET skill_name = ?, category = ?, percentage = ?, display_order = ? WHERE id = ?";
    $params = [
        $data['skill_name'],
        $data['category'],
        $data['percentage'],
        $data['display_order'],
        $id
    ];
    
    return executeQuery($pdo, $sql, $params) !== false;
}

// Delete skill
function deleteSkill($pdo, $id) {
    $sql = "DELETE FROM skills WHERE id = ?";
    return executeQuery($pdo, $sql, [$id]) !== false;
}

// Get skill by ID
function getSkillById($pdo, $id) {
    $sql = "SELECT * FROM skills WHERE id = ?";
    return fetchOne($pdo, $sql, [$id]);
}

// Update about information
function updateAboutInfo($pdo, $content, $cvFile = null) {
    // Delete old content and insert new
    $sql1 = "DELETE FROM about_info";
    executeQuery($pdo, $sql1);
    
    $sql2 = "INSERT INTO about_info (content, cv_file) VALUES (?, ?)";
    return executeQuery($pdo, $sql2, [$content, $cvFile]) !== false;
}

// Sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_NOQUOTES, 'UTF-8');
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// File upload handler
function handleFileUpload($file, $uploadDir = 'uploads/') {
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and GIF allowed.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB.'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file.'];
    }
}
?>
