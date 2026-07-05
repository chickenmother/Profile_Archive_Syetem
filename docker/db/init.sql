-- 1. Create independent lookup tables first
CREATE TABLE IF NOT EXISTS `departments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `number_of_employee` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `positions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `admin_level` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create the core employees table (dependent on departments and positions)
CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL COMMENT 'Securely hashed using bcrypt',
  `hire_date` DATE NOT NULL,
  `department_id` INT,
  `position_id` INT,
  `introduction` TEXT,
  `avatar` VARCHAR(255) DEFAULT NULL COMMENT 'Filename of the uploaded avatar image, stored in public/assets/uploads/avatars/',
  `remember_token` VARCHAR(64) DEFAULT NULL COMMENT 'Remember-me persistent login token',
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`position_id`) REFERENCES `positions`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create projects (dependent on employees for leader_id FK)
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `leader_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `status` VARCHAR(50) NOT NULL COMMENT 'planning, ongoing, completed',
  FOREIGN KEY (`leader_id`) REFERENCES `employees`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create dependent tables and junction tables
CREATE TABLE IF NOT EXISTS `employeesProjects` (
  `employee_id` INT NOT NULL,
  `project_id` INT NOT NULL,
  PRIMARY KEY (`employee_id`, `project_id`),
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `skill_id` INT NOT NULL COMMENT 'References the skill type key in config/skills.php',
  `level` VARCHAR(50) NOT NULL COMMENT 'beginner, intermediate, expert',
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `certificates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `certificate_id` INT NOT NULL COMMENT 'References the certificate type key in config/certificates.php',
  `level` VARCHAR(50) NOT NULL COMMENT 'beginner, intermediate, expert',
  `scale` VARCHAR(50) NOT NULL COMMENT 'local, national, international',
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `author_id` INT NOT NULL,
  `receiver_id` INT NOT NULL,
  `content` TEXT NOT NULL,
  `create_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`author_id`) REFERENCES `employees`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`receiver_id`) REFERENCES `employees`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- DATA SEEDING (INITIAL TEST DATA)
-- ========================================================

-- 1. Seed Departments
INSERT INTO `departments` (`id`, `name`, `number_of_employee`) VALUES
(1, 'System Development Division', 5),
(2, 'Digital Transformation Consultancy', 4),
(3, 'Global Operations', 3);

-- 2. Seed Positions
INSERT INTO `positions` (`id`, `name`, `admin_level`) VALUES
(1, 'Junior Engineer', 1),
(2, 'Senior Consultant', 3),
(3, 'Project Manager', 5);

-- 3. Seed Employees (12 total — enough to test 2 pages of 6)
-- Note: Passwords below are mock bcrypt-length hashes for 'password123'
INSERT INTO `employees` (`id`, `name`, `email`, `password`, `hire_date`, `department_id`, `position_id`, `introduction`, `avatar`) VALUES
(1,  'Song Junran',    'j.song@example.com',    '$2y$10$RtP1UPTxAGs3.RkODgQCd.0YEGFiUL/wr1u75saqybVvoTZaiLr3W', '2024-04-01', 1, 3, 'Specializes in web application development, Docker microservices, and retail digital transformation strategy.', 'avatar_1_1782988360.jpg'),
(2,  'Alice Smith',    'a.smith@example.com',   '$2y$10$RtP1UPTxAGs3.RkODgQCd.0YEGFiUL/wr1u75saqybVvoTZaiLr3W', '2025-01-10', 2, 2, 'Senior consultant focused on enterprise AI scaling and cloud integration pipeline design.', 'avatar_2_1782897644.jpg'),
(3,  'Bob Jones',      'b.jones@example.com',   '$2y$10$RtP1UPTxAGs3.RkODgQCd.0YEGFiUL/wr1u75saqybVvoTZaiLr3W', '2025-06-01', 1, 1, 'Full-stack engineer with experience building secure internal corporate archiving solutions.', null),
(4,  'Yuki Tanaka',    'y.tanaka@example.com',  '$2y$10$RtP1UPTxAGs3.RkODgQCd.0YEGFiUL/wr1u75saqybVvoTZaiLr3W', '2026-04-01', 1, 1, 'Junior developer assisting with database normalization updates and system testing.', null),
(5,  'Maria Garcia',   'm.garcia@example.com',  '$2y$10$RtP1UPTxAGs3.RkODgQCd.0YEGFiUL/wr1u75saqybVvoTZaiLr3W', '2024-07-15', 2, 2, 'Senior consultant specializing in digital transformation roadmaps and stakeholder alignment.', null),
(6,  'Chen Wei',       'c.wei@example.com',     '$2y$10$RtP1UPTxAGs3.RkODgQCd.0YEGFiUL/wr1u75saqybVvoTZaiLr3W', '2025-03-20', 1, 1, 'Backend engineer with a focus on API design, microservices architecture, and performance tuning.', null),
(7,  'Hana Yamamoto',  'h.yamamoto@example.com','$2y$10$RtP1UPTxAGs3.RkODgQCd.0YEGFiUL/wr1u75saqybVvoTZaiLr3W', '2023-09-01', 3, 3, 'Project manager overseeing global operations and cross-regional delivery coordination.', null),
(8,  'Liam O Brien',   'l.obrien@example.com',  '$2y$10$RtP1UPTxAGs3.RkODgQCd.0YEGFiUL/wr1u75saqybVvoTZaiLr3W', '2026-01-05', 3, 1, 'Junior engineer supporting global ops infrastructure and internal tooling automation.', null),
(9,  'Sara Nguyen',    's.nguyen@example.com',  '$2y$10$RtP1UPTxAGs3.RkODgQCd.0YEGFiUL/wr1u75saqybVvoTZaiLr3W', '2024-11-01', 2, 2, 'Consultant with expertise in data analytics, BI dashboards, and enterprise reporting pipelines.', null),
(10, 'Kenji Mori',     'k.mori@example.com',    '$2y$10$RtP1UPTxAGs3.RkODgQCd.0YEGFiUL/wr1u75saqybVvoTZaiLr3W', '2025-08-15', 1, 1, 'Junior developer focused on frontend development with React and TypeScript.', null),
(11, 'Priya Sharma',   'p.sharma@example.com',  '$2y$10$RtP1UPTxAGs3.RkODgQCd.0YEGFiUL/wr1u75saqybVvoTZaiLr3W', '2023-04-01', 3, 2, 'Senior consultant managing vendor relationships and global procurement strategy.', null),
(12, 'David Kim',      'd.kim@example.com',     '$2y$10$RtP1UPTxAGs3.RkODgQCd.0YEGFiUL/wr1u75saqybVvoTZaiLr3W', '2024-02-20', 1, 3, 'Project manager leading system development initiatives and technical team mentorship.', null);

-- 4. Seed Projects (leader_id references employees.id)
INSERT INTO `projects` (`id`, `name`, `leader_id`, `start_date`, `end_date`, `status`) VALUES
(1, 'Retail AI Hub Integration', 1, '2026-04-01', '2026-09-30', 'ongoing'),
(2, 'E-Commerce Shelf Inventory DX', 2, '2026-01-15', '2026-05-31', 'completed'),
(3, 'Next-Gen Profile Archive Architecture', 3, '2026-06-01', '2026-12-31', 'planning'),
(4, 'Cloud Migration Phase 2', 2, '2026-03-01', '2026-08-31', 'ongoing'),
(5, 'Mobile App Prototype', 1, '2025-10-01', '2026-02-28', 'completed');

-- 5. Seed Employees-Projects Junction (Many-to-Many)
INSERT INTO `employeesProjects` (`employee_id`, `project_id`) VALUES
(1,  1), (1,  3), (1,  5),
(2,  1), (2,  2), (2,  4),
(3,  3),
(4,  3),
(5,  4),
(6,  1), (6,  3),
(7,  4), (7,  5),
(8,  4),
(9,  2), (9,  4),
(10, 5),
(11, 2),
(12, 1), (12, 3);

-- 6. Seed Skills
-- skill_id references config/skills.php: 1=PHP, 2=JavaScript, 3=Python, 4=Java, 5=C#, 6=C++, 7=Ruby, 8=Go, 9=Swift, 10=Kotlin, 11=TypeScript
INSERT INTO `skills` (`employee_id`, `skill_id`, `level`) VALUES
(1,  1,  'expert'),        -- Song Junran: PHP
(1,  3,  'intermediate'),  -- Song Junran: Python
(2,  11, 'expert'),        -- Alice Smith: TypeScript
(3,  2,  'intermediate'),  -- Bob Jones: JavaScript
(4,  8,  'beginner'),      -- Yuki Tanaka: Go
(5,  11, 'expert'),        -- Maria Garcia: TypeScript
(5,  3,  'intermediate'),  -- Maria Garcia: Python
(6,  1,  'intermediate'),  -- Chen Wei: PHP
(6,  4,  'expert'),        -- Chen Wei: Java
(7,  5,  'intermediate'),  -- Hana Yamamoto: C#
(8,  8,  'beginner'),      -- Liam O Brien: Go
(8,  2,  'beginner'),      -- Liam O Brien: JavaScript
(9,  3,  'expert'),        -- Sara Nguyen: Python
(9,  11, 'intermediate'),  -- Sara Nguyen: TypeScript
(10, 2,  'intermediate'),  -- Kenji Mori: JavaScript
(10, 11, 'intermediate'),  -- Kenji Mori: TypeScript
(11, 5,  'expert'),        -- Priya Sharma: C#
(12, 1,  'expert'),        -- David Kim: PHP
(12, 4,  'intermediate');  -- David Kim: Java

-- 7. Seed Certificates
-- certificate_id references config/certificates.php: 1=JLPT, 2=TOEIC,  3=基本情報技術者, 4=応用情報技術者, 5=AWS Certification, 6=Oracle Certification
INSERT INTO `certificates` (`employee_id`, `certificate_id`, `level`, `scale`) VALUES
(1,  1, 'N1',       'international'), -- Song Junran: JLPT
(2,  5, 'Associate Solutions Architect',       'international'),      -- Alice Smith: AWS Certification (Associate Solutions Architect)
(3,  3, '合格', 'national'),         -- Bob Jones: 基本情報技術者
(4,  1, 'N2',     'international'),         -- Yuki Tanaka: JLPT N2
(5,  2, '700+',       'international'), -- Maria Garcia: TOEIC
(5,  5, 'Associate Solutions Architect', 'international'),      -- Maria Garcia: AWS Certification (Associate Solutions Architect)
(6,  4, '合格', 'national'),      -- Chen Wei: 応用情報技術者
(7,  1, 'N1',       'international'), -- Hana Yamamoto: JLPT
(7,  2, '900+',       'international'), -- Hana Yamamoto: TOEIC (900+)
(9,  5, 'Professional Solutions Architect',       'international'),      -- Sara Nguyen: AWS Certification (Professional Solutions Architect)
(10, 3, '合格',     'national'),         -- Kenji Mori: 基本情報技術者
(11, 2, '800+',       'international'), -- Priya Sharma: TOEIC (800+)
(12, 4, '合格',       'national'),      -- David Kim: 応用情報技術者
(12, 5, 'Professional Solutions Architect',       'international');      -- David Kim: AWS Certification (Professional Solutions Architect)

-- 8. Seed Comments
INSERT INTO `comments` (`author_id`, `receiver_id`, `content`) VALUES
(2,  1,  'Excellent architectural layout on the Retail AI Hub integration blueprint. The container isolation works flawlessly.'),
(1,  4,  'Great work handling the initial git branching setup for the repository. Keep learning the terminal commands!'),
(7,  5,  'Maria brings exceptional clarity to stakeholder presentations. Her roadmaps are always well-structured.'),
(5,  9,  'Sara''s BI dashboard work on the E-Commerce project was outstanding. The data pipeline she built is very efficient.'),
(12, 6,  'Chen Wei''s API design for the Retail AI Hub was clean and well-documented. Great attention to performance.'),
(9,  10, 'Kenji is picking up TypeScript very quickly. His frontend components are clean and well-tested.'),
(1,  12, 'David''s leadership on the system development team has been invaluable. Strong technical and people skills.');
