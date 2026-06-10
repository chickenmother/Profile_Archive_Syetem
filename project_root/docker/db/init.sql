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

CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `leader_name` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` VARCHAR(50) NOT NULL COMMENT 'planning, ongoing, completed'
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
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`position_id`) REFERENCES `positions`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create dependent tables and junction tables
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
  `level` VARCHAR(50) NOT NULL COMMENT 'beginner, intermediate, expert',
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `certificates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
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
(1, 'System Development Division', 3),
(2, 'Digital Transformation Consultancy', 2),
(3, 'Global Operations', 0);

-- 2. Seed Positions
INSERT INTO `positions` (`id`, `name`, `admin_level`) VALUES
(1, 'Junior Engineer', 1),
(2, 'Senior Consultant', 3),
(3, 'Project Manager', 5);

-- 3. Seed Projects
INSERT INTO `projects` (`id`, `name`, `leader_name`, `start_date`, `end_date`, `status`) VALUES
(1, 'Retail AI Hub Integration', 'Song Junran', '2026-04-01', '2026-09-30', 'ongoing'),
(2, 'E-Commerce Shelf Inventory DX', 'Alice Smith', '2026-01-15', '2026-05-31', 'completed'),
(3, 'Next-Gen Profile Archive Architecture', 'Bob Jones', '2026-06-01', '2026-12-31', 'planning');

-- 4. Seed Employees
-- Note: Passwords below are mock bcrypt-length hashes for 'password123'
INSERT INTO `employees` (`id`, `name`, `email`, `password`, `hire_date`, `department_id`, `position_id`, `introduction`) VALUES
(1, 'Song Junran', 'j.song@example.com', '$2y$10$7vR7D6P1XvL3p2O7fK6uO.gI1Fv6zT2e1vQ4jH5g7f8d9s0a1b2c3', '2024-04-01', 1, 3, 'Specializes in web application development, Docker microservices, and retail digital transformation strategy.'),
(2, 'Alice Smith', 'a.smith@example.com', '$2y$10$7vR7D6P1XvL3p2O7fK6uO.gI1Fv6zT2e1vQ4jH5g7f8d9s0a1b2c3', '2025-01-10', 2, 2, 'Senior consultant focused on enterprise AI scaling and cloud integration pipeline design.'),
(3, 'Bob Jones', 'b.jones@example.com', '$2y$10$7vR7D6P1XvL3p2O7fK6uO.gI1Fv6zT2e1vQ4jH5g7f8d9s0a1b2c3', '2025-06-01', 1, 1, 'Full-stack engineer with experience building secure internal corporate archiving solutions.'),
(4, 'Yuki Tanaka', 'y.tanaka@example.com', '$2y$10$7vR7D6P1XvL3p2O7fK6uO.gI1Fv6zT2e1vQ4jH5g7f8d9s0a1b2c3', '2026-04-01', 1, 1, 'Junior developer assisting with database normalization updates and system testing.');

-- 5. Seed Employees-Projects Junction (Many-to-Many)
INSERT INTO `employeesProjects` (`employee_id`, `project_id`) VALUES
(1, 1), -- Song Junran working on Retail AI Hub
(1, 3), -- Song Junran working on Profile Archive Architecture
(2, 1), -- Alice Smith working on Retail AI Hub
(2, 2), -- Alice Smith finished E-Commerce DX
(3, 3), -- Bob Jones working on Profile Archive Architecture
(4, 3); -- Yuki Tanaka working on Profile Archive Architecture

-- 6. Seed Skills
INSERT INTO `skills` (`employee_id`, `level`) VALUES
(1, 'expert'),        -- Song Junran (PHP/Docker/SQL)
(1, 'intermediate'),  -- Song Junran (Python/Django)
(2, 'expert'),        -- Alice Smith (Consulting)
(3, 'intermediate'),  -- Bob Jones (JavaScript/CSS)
(4, 'beginner');      -- Yuki Tanaka (Git/Linux)

-- 7. Seed Certificates
INSERT INTO `certificates` (`employee_id`, `level`, `scale`) VALUES
(1, 'expert', 'international'), -- Song Junran (e.g., JLPT N1 or Global IT)
(2, 'expert', 'national'),      -- Alice Smith
(3, 'intermediate', 'local');   -- Bob Jones

-- 8. Seed Comments
INSERT INTO `comments` (`author_id`, `receiver_id`, `content`) VALUES
(2, 1, 'Excellent architectural layout on the Retail AI Hub integration blueprint. The container isolation works flawlessly.'),
(1, 4, 'Great work handling the initial git branching setup for the repository. Keep learning the terminal commands!');