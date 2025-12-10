CREATE DATABASE IF NOT EXISTS user_db;
USE user_db;

CREATE TABLE IF NOT EXISTS certificate_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    valid_months INT NOT NULL DEFAULT 12
);

INSERT INTO certificate_types (name, valid_months) VALUES
('Back Safety', 12), ('Hearing Conservation', 12), ('Heat Stress Safety', 12), ('Fall Protection', 12),
('Workplace Infections Disease Control', 12), ('Slips, Trips, & Falls', 12), ('Workplace Bloodborne Pathogens', 12),
('Workplace Fire', 12), ('Hand & Power Tools', 12), ('Respiratory Crystalline Silica', 12), ('Electrical Safety', 12),
('PPE Safety', 12), ('Crane Safety', 36), ('Forklift Safety', 36), ('Lock Out Tag Out (LOTO)', 12), ('Asbestos', 12),
('Excavation & Trenching Safety', 12), ('Hazard Communication Safety', 12), ('Confined Space Entry', 12),
('Decontamination Procedures', 12), ('OSHA 10', 60), ('OSHA 30', 60), ('EPA Rules', 12), ('DOT Rules', 12),
('CPR/AED/First Aid Heartsaver', 24), ('Flagger Training', 36), ('811 Web Ticket Entry', 12),
('Asbestos O&M Training', 12), ('Lead Exposure Training', 12), ('Excavation & Trenching Competency', 12),
('HAZWOPER', 12), ('MSHA', 12), ('Netradyne System', 12), ('MVR Request', 12);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    admin_type ENUM('master', 'regular') DEFAULT NULL,
    company VARCHAR(100),
    department VARCHAR(50),
    sub_department VARCHAR(50) DEFAULT NULL,
    date_of_hire DATE,
    is_active TINYINT(1) DEFAULT 1,
    assigned_admin INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_assigned_admin (assigned_admin),
    INDEX idx_department (department),
    INDEX idx_sub_department (sub_department),
    FOREIGN KEY (assigned_admin) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    uploaded_by INT,
    certificate_type_id INT NOT NULL,
    certificate_number VARCHAR(100),
    issue_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_expiry (expiry_date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (certificate_type_id) REFERENCES certificate_types(id)
);

CREATE TABLE IF NOT EXISTS incident_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reported_by INT,
    incident_date DATE NOT NULL,
    incident_time TIME,
    witness_name VARCHAR(100),
    description TEXT NOT NULL,
    affected_parties VARCHAR(255),
    witness_statements TEXT,
    incident_result TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE VIEW expiring_certificates AS
SELECT c.*, ct.name as cert_name, u.name as user_name
FROM certificates c
JOIN certificate_types ct ON c.certificate_type_id = ct.id
JOIN users u ON c.user_id = u.id
WHERE c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY);

INSERT INTO users (name, first_name, middle_name, last_name, email, phone, password, role, admin_type, company, department, sub_department, date_of_hire, is_active) VALUES
('Master Admin', 'Master', '', 'Admin', 'master@admin.com', '555-0100', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'master', 'Iron Women Learning', 'Administration', NULL, '2020-01-01', 1),
('Regular Admin', 'Regular', '', 'Admin', 'admin@test.com', '555-0101', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'regular', 'Iron Women Learning', 'Operations', 'Operations A', '2021-06-15', 1),
('John Michael Doe', 'John', 'Michael', 'Doe', 'john@test.com', '555-1001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 'Iron Works Inc', 'Safety', NULL, '2023-01-15', 1),
('Jane Marie Smith', 'Jane', 'Marie', 'Smith', 'jane@test.com', '555-1002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 'Steel Co', 'Operations', 'Operations A', '2022-03-10', 0),
('Bob Allen Wilson', 'Bob', 'Allen', 'Wilson', 'bob@test.com', '555-1003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 'Iron Works Inc', 'Training', NULL, '2023-07-20', 1),
('Sarah Johnson', 'Sarah', '', 'Johnson', 'sarah@test.com', '555-1004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'regular', 'Iron Women Learning', 'Operations', 'Operations B', '2022-01-10', 1),
('Mike R Davis', 'Mike', 'R', 'Davis', 'mike@test.com', '555-1005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 'Iron Works Inc', 'Operations', 'Operations B', '2023-03-01', 1);

UPDATE users SET assigned_admin = 2 WHERE id IN (3, 4, 5);
UPDATE users SET assigned_admin = 6 WHERE id = 7;

INSERT INTO certificates (user_id, uploaded_by, certificate_type_id, certificate_number, issue_date, expiry_date) VALUES
(3, 1, 21, 'OSHA10-2024-001', '2024-01-15', '2029-01-15'),
(3, 1, 4, 'FP-2024-001', '2024-02-10', '2025-01-10'),
(5, 2, 11, 'ES-2024-001', '2024-03-05', '2025-03-05'),
(3, 1, 25, 'CPR-2024-001', '2024-06-01', '2026-06-01');

INSERT INTO incident_reports (user_id, reported_by, incident_date, incident_time, witness_name, description, affected_parties, witness_statements, incident_result, notes) VALUES
(3, 1, '2024-11-15', '14:30:00', 'Mike Johnson', 'Minor back strain while lifting heavy equipment', 'John Michael Doe', 'Employee was lifting without proper assistance', 'First aid provided, return to work next day', '');

-- If updating existing database, run this:
-- ALTER TABLE users ADD COLUMN sub_department VARCHAR(50) DEFAULT NULL AFTER department;
-- CREATE INDEX idx_sub_department ON users(sub_department);
