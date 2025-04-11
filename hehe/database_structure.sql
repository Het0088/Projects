-- Database structure
CREATE DATABASE IF NOT EXISTS login;
USE login;

-- User table
CREATE TABLE IF NOT EXISTS hehe (
    Sr INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    email VARCHAR(255)
);

-- Reset tokens table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expiry DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 