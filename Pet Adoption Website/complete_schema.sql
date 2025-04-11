DROP DATABASE IF EXISTS streeties_chat;
CREATE DATABASE streeties_chat;
USE streeties_chat;

-- Create users table
CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role VARCHAR(10) DEFAULT 'client',
    PRIMARY KEY (id)
);

-- Create messages table
CREATE TABLE messages (
    id INT NOT NULL AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0,
    PRIMARY KEY (id)
);

-- Add indexes
CREATE INDEX idx_sender ON messages(sender_id);
CREATE INDEX idx_receiver ON messages(receiver_id);
CREATE INDEX idx_timestamp ON messages(sent_at);

-- Insert default admin
INSERT INTO users (username, password, email, full_name, role)
VALUES ('admin', 'admin123', 'admin@streeties.com', 'System Administrator', 'admin');

-- Insert default employee
INSERT INTO users (username, password, email, full_name, role)
VALUES ('employee1', 'employee123', 'employee1@streeties.com', 'Default Employee', 'employee'); 