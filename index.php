<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions/portfolio_functions.php';

// Get dynamic content from database
$projects = getProjects($pdo);
$skillsByCategory = getSkillsByCategory($pdo);
$education = getEducation($pdo);
$achievements = getAchievements($pdo);
$aboutInfo = getAboutInfo($pdo);
$aboutContent = $aboutInfo['content'];
$cvFile = $aboutInfo['cv_file'];
$socialLinks = getSocialLinks($pdo);

// Get unique categories for project filters
$categories = array_unique(array_column($projects, 'category'));

// Check theme preference from cookie
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark'; // Default to dark mode
$bodyClass = $theme === 'dark' ? 'dark-mode' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anika Nawer - Portfolio</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body class="<?php echo $bodyClass; ?>">
    <!-- Loading Screen -->
    <div class="loading-screen">
        <div class="loader"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-content">
            <div class="nav-brand">Portfolio</div>
            <button id="theme-toggle" class="theme-toggle">
                <i class="fas <?php echo $theme === 'dark' ? 'fa-sun' : 'fa-moon'; ?>"></i>
            </button>
            <div class="nav-toggle">
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            </div>
            <ul class="nav-links">
                <li><a href="#home" class="nav-link active">Home</a></li>
                <li><a href="#about" class="nav-link">About</a></li>
                <li><a href="#skills" class="nav-link">Skills</a></li>
                <li><a href="#projects" class="nav-link">Projects</a></li>
                <li><a href="#achievements" class="nav-link">Achievements</a></li>
                <li><a href="#education" class="nav-link">Education</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
            </ul>
        </div>
    </nav>

    <!-- Home Section -->
    <section id="home" class="home">
        <div class="container">
            <div class="home-content">
                <h1 class="glitch" data-text="Hello, I'm Anika Nawer">Hello, I'm Anika Nawer</h1>
                <div class="typing-text">I'm <span id="typed-text"></span></div>
                <p class="lead">I am currently a 3rd year CSE undergraduate Student looking for new opportunities.</p>
                <div class="cta-buttons">
                    <a href="#contact" class="btn primary-btn">Contact Me</a>
                    <a href="#projects" class="btn secondary-btn">View Work</a>
                </div>
                <div class="social-links">
                    <?php foreach ($socialLinks as $social): ?>
                    <a href="<?php echo htmlspecialchars($social['url']); ?>" class="social-link" target="_blank">
                        <i class="<?php echo htmlspecialchars($social['icon_class']); ?>"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about" data-aos="fade-up">
        <div class="container">
            <h2 class="section-title">About Me</h2>
            <div class="about-content">
                <div class="about-image" data-aos="fade-right">
                    <div class="image-frame">
                        <img class="Image" src="images/2107089_nawer.JPG" alt="Profile">
                    </div>
                </div>
                <div class="about-text" data-aos="fade-left">
                    <p><?php echo nl2br(htmlspecialchars($aboutContent)); ?></p>
                    <?php if (!empty($cvFile) && file_exists($cvFile)): ?>
                        <a href="<?php echo htmlspecialchars($cvFile); ?>" class="btn download-cv" target="_blank">Download CV</a>
                    <?php else: ?>
                        <a href="Marks_CSE-3107-2K21.pdf" class="btn download-cv" target="_blank">Download CV</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section id="skills" class="skills" data-aos="fade-up">
        <div class="container">
            <h2 class="section-title">My Skills</h2>
            <div class="skills-grid">
                <?php foreach ($skillsByCategory as $category => $skills): ?>
                <div class="skill-category" data-aos="zoom-in">
                    <h3><?php echo htmlspecialchars($category); ?></h3>
                    <?php foreach ($skills as $skill): ?>
                    <div class="skill-item">
                        <div class="skill-info">
                            <span><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                            <span><?php echo $skill['percentage']; ?>%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-fill" data-width="<?php echo $skill['percentage']; ?>"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section id="projects" class="projects" data-aos="fade-up">
        <div class="container">
            <h2 class="section-title">My Projects</h2>
            <div class="project-filters">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="web">Web</button>
                <button class="filter-btn" data-filter="app">App</button>
                <button class="filter-btn" data-filter="other">Other</button>
            </div>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                <div class="project-card" data-category="<?php echo $project['category']; ?>" data-aos="fade-up">
                    <div class="project-image">
                        <img src="<?php echo htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars(html_entity_decode($project['title'], ENT_QUOTES, 'UTF-8')); ?>">
                        <div class="project-overlay">
                            <div class="project-details">
                                <h3><?php echo htmlspecialchars(html_entity_decode($project['title'], ENT_QUOTES, 'UTF-8')); ?></h3>
                                <p><?php echo htmlspecialchars(html_entity_decode($project['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                                <?php if ($project['technologies']): ?>
                                    <p class="technologies"><strong>Tech:</strong> <?php echo htmlspecialchars(html_entity_decode($project['technologies'], ENT_QUOTES, 'UTF-8')); ?></p>
                                <?php endif; ?>
                                <div class="project-links">
                                    <?php if ($project['github_link']): ?>
                                        <a href="<?php echo htmlspecialchars($project['github_link']); ?>" class="btnsmall" target="_blank">Code</a>
                                    <?php endif; ?>
                                    <?php if ($project['live_link']): ?>
                                        <a href="<?php echo htmlspecialchars($project['live_link']); ?>" class="btnsmall" target="_blank">Live</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($projects)): ?>
                <div class="no-projects">
                    <p>No projects to display yet. Check back soon!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Achievements Section -->
    <section id="achievements" class="achievements" data-aos="fade-up">
        <div class="container">
            <h2 class="section-title">Achievements</h2>
            <div class="timeline">
                <?php if (!empty($achievements)): ?>
                    <?php foreach ($achievements as $index => $achievement): ?>
                        <div class="timeline-item" data-aos="<?php echo ($index % 2 == 0) ? 'fade-right' : 'fade-left'; ?>">
                            <div class="timeline-content">
                                <h3><?php echo htmlspecialchars(html_entity_decode($achievement['title'], ENT_QUOTES, 'UTF-8')); ?></h3>
                                <span class="date"><?php echo htmlspecialchars($achievement['year']); ?></span>
                                <?php if (!empty($achievement['description'])): ?>
                                    <p><?php echo htmlspecialchars(html_entity_decode($achievement['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="timeline-item" data-aos="fade-right">
                        <div class="timeline-content">
                            <h3>Board Scholarship in SSC</h3>
                            <span class="date">2019</span>
                            <p>Achieved Talentpool scholarship</p>
                        </div>
                    </div>
                    <div class="timeline-item" data-aos="fade-left">
                        <div class="timeline-content">
                            <h3>Board Scholarship in HSC</h3>
                            <span class="date">2021</span>
                            <p>Achieved Talentpool scholarship</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section> 

    <!-- Education Section -->
    <section id="education" class="education" data-aos="fade-up">
        <div class="container">
            <h2 class="section-title">Education</h2>
            <div class="education-grid">
                <?php foreach ($education as $edu): ?>
                <div class="education-card" data-aos="flip-left">
                    <div class="education-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3><?php echo htmlspecialchars(html_entity_decode($edu['degree'], ENT_QUOTES, 'UTF-8')); ?></h3>
                    <p class="institution"><?php echo htmlspecialchars(html_entity_decode($edu['institution'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <p class="year"><?php echo htmlspecialchars($edu['year']); ?></p>
                    <?php if ($edu['grade_info']): ?>
                        <p class="description"><?php echo htmlspecialchars(html_entity_decode($edu['grade_info'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <?php endif; ?>
                    <?php if ($edu['description']): ?>
                        <p class="description"><?php echo htmlspecialchars(html_entity_decode($edu['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact" data-aos="fade-up">
        <div class="container">
            <h2 class="section-title">Contact Me</h2>
            <div class="contact-content">
                <div class="contact-info" data-aos="fade-right">
                    <div class="contact-item">
                        <i class="fas fa-envelope pulse"></i>
                        <div class="contact-details">
                            <h3>Email Me</h3>
                            <p>anikanawer10@gmail.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone-alt pulse"></i>
                        <div class="contact-details">
                            <h3>Call Me</h3>
                            <p>01311684148</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt pulse"></i>
                        <div class="contact-details">
                            <h3>Visit Me</h3>
                            <p>Rokeya Hall,KUET,Khulna,Bangladesh</p>
                        </div>
                    </div>
                </div>
                <!-- Contact Form - Using Working Structure -->
                <div data-aos="fade-left" style="background: var(--bg-secondary); padding: 2rem; border-radius: 12px;">
                    <?php
                    if (isset($_SESSION['contact_message'])) {
                        $style = $_SESSION['contact_success'] ? 
                            'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 
                            'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;';
                        echo "<div style='padding: 0.75rem; margin-bottom: 1.5rem; border-radius: 8px; font-size: 0.9rem; text-align: center; $style'>";
                        echo htmlspecialchars($_SESSION['contact_message']);
                        echo "</div>";
                        unset($_SESSION['contact_message'], $_SESSION['contact_success']);
                    }
                    ?>
                    
                    <form action="contact_handler.php" method="POST">
                        <div style="margin-bottom: 1.5rem;">
                            <label for="name" style="display: block; margin-bottom: 0.5rem; color: var(--text-primary); font-weight: 500;">Your Name</label>
                            <input type="text" id="name" name="name" required style="width: 100%; padding: 1rem; border: 2px solid var(--bg-primary); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 1rem; box-sizing: border-box;">
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label for="email" style="display: block; margin-bottom: 0.5rem; color: var(--text-primary); font-weight: 500;">Your Email</label>
                            <input type="email" id="email" name="email" required style="width: 100%; padding: 1rem; border: 2px solid var(--bg-primary); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 1rem; box-sizing: border-box;">
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label for="message" style="display: block; margin-bottom: 0.5rem; color: var(--text-primary); font-weight: 500;">Your Message</label>
                            <textarea id="message" name="message" required rows="5" style="width: 100%; padding: 1rem; border: 2px solid var(--bg-primary); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); font-size: 1rem; resize: vertical; min-height: 120px; box-sizing: border-box;"></textarea>
                        </div>
                        
                        <button type="submit" class="btn primary-btn" style="width: 100%; padding: 1rem 2rem; background: var(--primary-color); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-left">
                    <p>&copy; 2025 Anika Nawer. All rights reserved.</p>
                </div>
                <div class="footer-right">
                    <a href="admin/login.php" class="admin-link" title="Admin Access" target="_blank">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
    <script src="main.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>
