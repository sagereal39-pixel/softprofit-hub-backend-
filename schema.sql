-- schema.sql
-- Run this file once to set up your database

CREATE DATABASE IF NOT EXISTS affiliate_blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE affiliate_blog;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT,
    excerpt TEXT,
    category VARCHAR(100),
    featured_image VARCHAR(500),
    meta_title VARCHAR(255),
    meta_description VARCHAR(500),
    meta_keywords VARCHAR(300),
    author VARCHAR(100),
    status ENUM('draft', 'published') DEFAULT 'draft',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS affiliates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    url VARCHAR(500) NOT NULL,
    description TEXT,
    commission DECIMAL(5,2) DEFAULT 0.00,
    category VARCHAR(100),
    logo VARCHAR(500),
    clicks INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS post_affiliates (
    post_id INT NOT NULL,
    affiliate_id INT NOT NULL,
    PRIMARY KEY (post_id, affiliate_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (affiliate_id) REFERENCES affiliates(id) ON DELETE CASCADE
);

-- Sample admin user (password: admin123 — change immediately!)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@yourblog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample categories and posts
INSERT INTO posts (title, slug, excerpt, category, status, author, meta_title, meta_description, meta_keywords) VALUES
('Best AI Writing Tools for 2024', 'best-ai-writing-tools-2024', 'Discover the top AI writing tools that help you create content faster and smarter.', 'AI Tools', 'published', 'Admin', 'Best AI Writing Tools 2024 | DigitalPro', 'Compare the top AI writing tools. Find the best software for content creation, copywriting, and SEO writing.', 'ai writing tools, best ai tools, content creation'),
('Top Online Course Platforms Compared', 'top-online-course-platforms', 'We reviewed 10 online learning platforms so you can find the best one for your needs.', 'E-Learning', 'published', 'Admin', 'Best Online Course Platforms 2024', 'In-depth comparison of Udemy, Coursera, Teachable and more. Find the right platform for your learning goals.', 'online courses, e-learning, udemy, coursera');

INSERT INTO affiliates (name, url, description, commission, category) VALUES
('Jasper AI', 'https://jasper.ai?ref=yourid', 'AI writing assistant for marketers and content creators', 30.00, 'AI Tools'),
('Teachable', 'https://teachable.com?ref=yourid', 'Platform to create and sell online courses', 30.00, 'E-Learning'),
('Canva Pro', 'https://canva.com?ref=yourid', 'Design tool for digital creators', 25.00, 'Design');
