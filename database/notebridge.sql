-- ============================================================
-- NoteBridge Database Setup
-- Run this file in phpMyAdmin or MySQL CLI to create the database.
-- ============================================================

-- Create the database (skip if it already exists)
CREATE DATABASE IF NOT EXISTS notebridge
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE notebridge;

-- ============================================================
-- 1. USERS TABLE
--    Stores registered student and admin accounts.
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,           -- bcrypt hashed
    role        ENUM('student', 'admin') DEFAULT 'student',
    avatar      VARCHAR(255) DEFAULT 'default.png',
    bio         TEXT DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. DEPARTMENTS TABLE
--    Academic departments for categorising notes.
-- ============================================================
CREATE TABLE IF NOT EXISTS departments (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ============================================================
-- 3. COURSES TABLE
--    Courses belong to a department.
-- ============================================================
CREATE TABLE IF NOT EXISTS courses (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150) NOT NULL,
    department_id INT NOT NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 4. NOTES TABLE
--    Each uploaded PDF note is one row here.
-- ============================================================
CREATE TABLE IF NOT EXISTS notes (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(200) NOT NULL,
    description   TEXT DEFAULT NULL,
    file_name     VARCHAR(255) NOT NULL,          -- stored filename on disk
    file_size     INT NOT NULL DEFAULT 0,         -- bytes
    course_id     INT NOT NULL,
    year          INT NOT NULL,                   -- academic year e.g. 2025
    user_id       INT NOT NULL,
    downloads     INT DEFAULT 0,
    status        ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 5. RATINGS TABLE
--    Students can rate a note (1 – 5 stars).
-- ============================================================
CREATE TABLE IF NOT EXISTS ratings (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    note_id  INT NOT NULL,
    user_id  INT NOT NULL,
    rating   TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rating (note_id, user_id),
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 6. BOOKMARKS TABLE
--    Students can bookmark notes for quick access.
-- ============================================================
CREATE TABLE IF NOT EXISTS bookmarks (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    note_id  INT NOT NULL,
    user_id  INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_bookmark (note_id, user_id),
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA — Departments
-- ============================================================
INSERT IGNORE INTO departments (id, name) VALUES
    (1, 'Computer Science'),
    (2, 'Electrical Engineering'),
    (3, 'Business Administration'),
    (4, 'Mathematics'),
    (5, 'Physics');

-- ============================================================
-- SEED DATA — Courses
-- ============================================================
INSERT IGNORE INTO courses (id, name, department_id) VALUES
    (1, 'Introduction to Programming', 1),
    (2, 'Data Structures and Algorithms', 1),
    (3, 'Database Systems', 1),
    (4, 'Web Development', 1),
    (5, 'Operating Systems', 1),
    (6, 'Circuit Analysis', 2),
    (7, 'Digital Electronics', 2),
    (8, 'Signal Processing', 2),
    (9, 'Principles of Management', 3),
    (10, 'Financial Accounting', 3),
    (11, 'Marketing Fundamentals', 3),
    (12, 'Calculus I', 4),
    (13, 'Linear Algebra', 4),
    (14, 'Probability and Statistics', 4),
    (15, 'Classical Mechanics', 5),
    (16, 'Thermodynamics', 5);

-- ============================================================
-- SEED DATA — Initial Administrator Account
-- Default Admin Password: admin123
-- ============================================================
INSERT IGNORE INTO users (id, full_name, email, password, role, bio) VALUES
    (1, 'Administrator', 'admin@notebridge.com',
     '$2y$10$ke3L2IFXtr6CdCOSxSYR2Os3ve2409lqqblMMdzzdxjUbQec538si', 'admin', 'System administrator and platform moderator.');

