-- Portfolio Database Setup
-- Execute this in phpMyAdmin after creating 'portfolio_db' database

CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

-- Projects Table
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    category ENUM('app', 'web', 'other') NOT NULL,
    github_link VARCHAR(255),
    live_link VARCHAR(255),
    technologies VARCHAR(500),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Skills Table
CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skill_name VARCHAR(100) NOT NULL,
    category VARCHAR(100) NOT NULL,
    percentage INT NOT NULL CHECK (percentage >= 0 AND percentage <= 100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact Messages Table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    date_submitted TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Education Table
CREATE TABLE education (
    id INT AUTO_INCREMENT PRIMARY KEY,
    degree VARCHAR(255) NOT NULL,
    institution VARCHAR(255) NOT NULL,
    year VARCHAR(50) NOT NULL,
    description TEXT,
    grade_info VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- About Information Table
CREATE TABLE about_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Social Links Table
CREATE TABLE social_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform VARCHAR(50) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon_class VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin Users Table (for login)
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Initial Data
-- Insert default about information
INSERT INTO about_info (content) VALUES (
'As a passionate Computer Science student, I\'m constantly eager to explore new technologies and continuously expand my knowledge. My primary aspiration lies in becoming a Machine Learning Engineer, driven by a deep fascination with AI\'s potential.

Beyond the world of algorithms and code, I possess a keen interest in geopolitics, finding it endlessly compelling to understand the intricate dynamics that shape our world. My love for learning also extends to world history and the profound impact of religion on human civilization.

When I\'m not diving into data science or global affairs, you\'ll often find me lost in the pages of a good book. I believe in the power of relentless effort and a positive mindset to turn dreams into reality, whether it\'s building an innovative AI model or understanding the complexities of our shared past.'
);

-- Insert initial skills
INSERT INTO skills (skill_name, category, percentage, display_order) VALUES
('HTML5/CSS3', 'Web Development', 90, 1),
('JavaScript', 'Web Development', 75, 2),
('PHP', 'Web Development', 50, 3),
('Arduino', 'Others', 50, 4),
('Android Studio', 'Others', 75, 5),
('SQL', 'Others', 50, 6);

-- Insert initial education
INSERT INTO education (degree, institution, year, description, grade_info, display_order) VALUES
('Bachelor\'s Degree', 'Khulna University of Engineering and Technology', '2023-2027', 'Computer Science and Engineering', 'CGPA: 3.68 (up to 2nd year)', 1),
('HSC', 'Joypurhat Girls\' Cadet College', '2019-2021', 'Higher Secondary Certificate', 'GPA: 5.00', 2);

-- Insert initial social links
INSERT INTO social_links (platform, url, icon_class, display_order) VALUES
('Instagram', 'https://www.instagram.com/anika_nawer10/?hl=en', 'fab fa-instagram', 1),
('Facebook', 'https://www.facebook.com/anika.nawer.859168', 'fab fa-facebook', 2),
('Telegram', 'https://t.me/+8801311684148', 'fab fa-telegram', 3),
('WhatsApp', 'https://wa.me/8801311684148', 'fab fa-whatsapp', 4);

-- Insert initial project (Epiphany)
INSERT INTO projects (title, description, image, category, github_link, technologies) VALUES
('Epiphany', 'A mobile app developed in Android Studio with Java which can track your mood, sleep cycle and upcoming plans', 'images/epiphany.jpeg', 'app', 'https://github.com/510nix/Epiphany', 'Java, Android Studio, SQLite');

-- Insert default admin user
INSERT INTO admin_users (username, password, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'anikanawer10@gmail.com');
