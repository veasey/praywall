-- Test Churches
INSERT INTO churches (name, location) VALUES 
('Hope Church', 'Sunderland'),
('Grace Chapel', 'Durham');

-- Test Users
INSERT INTO users (name, email, password_hash, role) VALUES
('Clint Rivers', 'clint@example.com', 'c11bb66099c9ecd197cdaf69415bbb9a', 'admin'),
('Sarah Jones', 'sarah@example.com', 'hashedpassword2', 'moderator'),
('Tom Lee', 'tom@example.com', 'hashedpassword3', 'user');

-- Test Prayers
INSERT INTO prayers (user_id, church_id, title, body, approved) VALUES
(1, 1, 'Healing for my wife', 'Please pray for my wife who is undergoing surgery next week.', TRUE),
(2, 2, 'Job interview', 'Praying for guidance and peace as I go into an interview tomorrow.', FALSE);

-- Test Prayed By records
INSERT INTO prayers_prayed_by (user_id, prayer_id) VALUES
(2, 1),
(3, 1),
(3, 2);

-- Test Praises
INSERT INTO praises (prayer_id, user_id, body) VALUES
(1, 2, 'Surgery went well — thank you everyone for praying!'),
(2, 3, 'Got the job — praise God!');
