-- Think Fast Quiz Database Initialization
-- Run this script to set up the complete database

-- Create database (run this first if database doesn't exist)
-- CREATE DATABASE IF NOT EXISTS quiz_app;
-- USE quiz_app;

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS quiz_results;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    total_quizzes INT DEFAULT 0,
    best_score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create quiz_results table
CREATE TABLE quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_quiz_results_user_id ON quiz_results(user_id);
CREATE INDEX idx_quiz_results_category ON quiz_results(category);
CREATE INDEX idx_quiz_results_created_at ON quiz_results(created_at);

-- Insert sample data (optional)
-- You can uncomment these lines to add test data

-- INSERT INTO users (first_name, last_name, email, password, total_quizzes, best_score) VALUES
-- ('John', 'Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 0),
-- ('Jane', 'Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 0);

-- Verify tables were created
SHOW TABLES;

-- Show table structure
DESCRIBE users;
DESCRIBE quiz_results;





