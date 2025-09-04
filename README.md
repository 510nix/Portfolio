# Portfolio Project

## Overview
A full-stack, database-driven portfolio website with a secure admin panel. Easily showcase your projects, skills, education, achievements, and more, with all content managed dynamically from a MySQL database.

---

## Features
- Dynamic content loading from MySQL database
- Admin panel for content management (CRUD)
- Responsive design with dark/light theme toggle
- Secure contact form with validation and database storage
- Email-based password reset for admin
- File upload for project images and CVs
- Security best practices (input sanitization, password hashing, prepared statements)

---

## File & Function Breakdown

### index.php
- Loads all portfolio content (projects, skills, education, achievements, about info, social links) from the database using helper functions.
- Handles theme switching (dark/light) via cookies.
- Renders all main sections: Home, About, Skills, Projects, Achievements, Education, Contact.
- Displays a contact form that submits to `contact_handler.php`.

### main.js
- Handles all frontend interactivity:
  - Animates elements on scroll (AOS).
  - Typing effect for the hero section (Typed.js).
  - Theme toggle with cookie persistence.
  - Mobile navigation menu.
  - Smooth scrolling and active navigation highlighting.
  - Animates skill bars and filters projects by category.

### style.css
- Main CSS for the public site.
- Uses CSS variables for easy theme switching.
- Styles all sections, navigation, buttons, forms, and animations.
- Supports both light and dark modes and is fully responsive.

### contact_handler.php
- Processes contact form submissions.
- Sanitizes and validates user input.
- Saves messages to the `contact_messages` table in the database.
- Sets session messages for feedback and redirects back to the contact section.

### database_setup.sql
- SQL script to create all necessary tables and insert initial data.
- Defines structure for projects, skills, education, achievements, about info, social links, admin users, and contact messages.

### config/database.php
- Sets up the database connection using PDO.
- Provides helper functions for executing queries and fetching results.

### config/email.php
- Handles email sending (for notifications or contact form, if needed).
- Supports both PHPMailer and basic PHP mail.

### functions/portfolio_functions.php
- Contains all business logic for the portfolio:
  - Fetches projects, skills, education, achievements, about info, and social links from the database.
  - Handles adding, updating, deleting projects and skills.
  - Manages contact messages (save, fetch, mark as read).
  - Provides input sanitization and validation helpers.
  - Handles file uploads for images and CVs.

---

## Admin Panel (admin/)

### about.php
- Admin page to edit the About section and upload a new CV.

### achievements.php
- Admin page to add, edit, or delete achievements.

### admin_style.css
- Styles the admin panel sidebar, dashboard, forms, and tables.

### dashboard.php
- Admin landing page after login.
- Shows statistics (projects, skills, messages) and quick action links.

### education.php
- Admin page to manage education records.

### forgot_password.php
- Admin password reset page with email verification and code entry.

### login.php
- Admin login page.
- Authenticates admin users using the `admin_users` table.

### logout.php
- Ends the admin session and logs out the user.

### messages.php
- Admin page to view, mark as read, or delete contact messages.

### projects.php
- Admin page to add, edit, or delete projects.
- Handles image uploads for new or updated projects.

### skills.php
- Admin page to manage skills (add, edit, delete, set category and percentage).

---

## Security & Best Practices
- All user input is sanitized and validated.
- SQL queries use prepared statements to prevent SQL injection.
- Sessions are used for authentication and feedback.
- File uploads are validated for type and size.
- Passwords are hashed securely.

---

## How It Works
- All content is loaded dynamically from the database.
- The admin panel allows you to manage all portfolio content.
- The public site displays the latest data and provides a contact form for visitors.

---


## License
This project is for educational and personal portfolio use.
