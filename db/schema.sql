-- CampusVote database schema and sample data (v2)
-- Import with: mysql -u root -p < db/schema.sql
--
-- Demo logins:
--   Voters: V1001 to V1008, password voter123
--   Admin:  admin@campusvote.test / admin123
--     (admin page: /admin/login.php)

CREATE DATABASE IF NOT EXISTS online_voting CHARACTER SET utf8mb4;
USE online_voting;

DROP TABLE IF EXISTS votes;
DROP TABLE IF EXISTS candidates;
DROP TABLE IF EXISTS voters;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS election;

CREATE TABLE election (
    id INT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    subtitle VARCHAR(250) NOT NULL DEFAULT '',
    is_open TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE voters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voter_code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    has_voted TINYINT(1) NOT NULL DEFAULT 0
);

CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    party VARCHAR(150) NOT NULL,
    bio TEXT,
    color VARCHAR(7) NOT NULL DEFAULT '#1B2F8A'
);

-- Secret ballot: a vote row stores the candidate only, never who voted.
-- The voters.has_voted flag is what stops a second vote.
CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT NOT NULL,
    cast_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id)
);

INSERT INTO election (id, title, subtitle, is_open) VALUES
(1, 'Student Council President 2026', 'One vote per student. Voting is private.', 1);

INSERT INTO candidates (name, party, bio, color) VALUES
('Amara Osei', 'Progress Alliance', 'Focused on student welfare and campus sustainability projects.', '#1B2F8A'),
('Daniel Chen', 'Unity Coalition', 'Wants better digital services and a clear, public student budget.', '#C2571A'),
('Priya Nair', 'Forward Collective', 'Champions inclusive policies and stronger mental health support.', '#2E7D5B'),
('Marcus Lee', 'Common Ground Party', 'Puts affordable housing and easy transport for students first.', '#7A3E8E');

-- Passwords are hashed with PHP password_hash (bcrypt).
INSERT INTO voters (voter_code, name, password_hash, has_voted) VALUES
('V1001', 'Aisha Rahman', '$2y$10$YOWkFCXDwrCBV6XnirHPu.yPwlqC94wHWChkOcLGR6v1tZ8ZJW0Ku', 0),
('V1002', 'Bilal Hussain', '$2y$10$lDvpadCXVy.20t6D4iPb.OsvC1WnmbNbQe3a3ndKjFAHGMHLzGVKi', 0),
('V1003', 'Chloe Tan', '$2y$10$IEFUyQTH3PSCFoiVK2lcc.5UZUfOp8KyoboJ4sOeS4Z6duHOnWYz2', 0),
('V1004', 'Devan Kumar', '$2y$10$PzV3wjV42y9JMvfGoeC5EeHa5kMdjqgKAuzeEa.3/nwtrG30wH9Gy', 0),
('V1005', 'Elena Petrova', '$2y$10$d3Qh7fyg7Hbk6gJ9/y3HEuMJUgA6EXnYWTUR8PulQAAyXCigkQEC.', 0),
('V1006', 'Farid Nasser', '$2y$10$G069VZVF8yfSDxEKYCOljOat00E3JnmnLh1kyyoVPlzeyR5CZBZ0m', 0),
('V1007', 'Grace Miller', '$2y$10$DNFCIvKXk1R54FQk7BNXFepBbLOYgnxxFAgMpsqhr261Xeoe6dbz2', 0),
('V1008', 'Hugo Santos', '$2y$10$JEv9a/5Xs4fNYrZ/In.hT.2VD.DR.LX5PYxNPgLKuiN60qaK0ETbS', 0);

INSERT INTO admins (name, email, password_hash) VALUES
('Election Officer', 'admin@campusvote.test', '$2y$10$38/QaB.lhjKNNiFWvHqtU.w5OOGjAP4O1bXwQu1FDBW0BnhDtDaaa');
