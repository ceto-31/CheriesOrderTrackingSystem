-- Support Tickets System
-- Run this SQL file to add support ticket functionality

USE dineclick_db;

-- Support Tickets table
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    category VARCHAR(50) NOT NULL,
    status ENUM('Open', 'In Progress', 'Resolved', 'Closed') DEFAULT 'Open',
    priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_ticket (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Support Ticket Messages table (for conversation thread)
CREATE TABLE IF NOT EXISTS support_messages (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    ticket_id INT(11) NOT NULL,
    sender_id INT(11) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ticket (ticket_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
