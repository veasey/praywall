-- Test Churches
INSERT INTO churches (name, location) VALUES 
('Hope Church', 'Sunderland'),
('Grace Chapel', 'Durham');

-- Test Users
INSERT INTO users (name, email, password_hash, role) 
VALUES ('Clint Rivers', 'clint@example.com', '$2a$12$A7SL6IWxw9omdNLeHluir.fG78NWgjIqjc0COdbRp4t7uOI8M2BLK', 'user'),
('Sarah Jones', 'sarah@example.com', '$2a$12$wnrsxXINFQ.bxX7eIskCGePFs.hP73UyAiCHt3IY4Ga1QOtRmEnVq', 'moderator'),
('Tom Lee', 'tom@example.com', '$2a$12$tX1Ngq3BBKuJa2bOErzxPOtvOuFmSOy9/MfvLP8xwntUK3kAxRv0y', 'admin');


-- Test Prayers
-- Insert 30 mixed prayers
INSERT INTO prayers (user_id, church_id, title, body, approved) VALUES
(1, 1, 'Peace at work', 'Work has been really stressful lately. Prayers appreciated.', TRUE),
(2, 1, 'Healing for mum', 'My mum is undergoing tests. Praying for healing.', TRUE),
(3, 2, 'Wisdom for decisions', 'Lots of changes happening, need wisdom.', TRUE),
(1, 1, 'Financial strain', 'Struggling to make ends meet this month.', TRUE),
(2, 2, 'Family tensions', 'Our home has been full of arguments. Praying for peace.', TRUE),
(3, 1, 'Health recovery', 'Recovering from flu. Need strength.', TRUE),
(1, 2, 'Exam nerves', 'Final exams this week. Please pray.', TRUE),
(2, 1, 'Relationship healing', 'Trying to reconcile with a friend.', TRUE),
(3, 2, 'Housing issues', 'Praying for a stable place to live.', TRUE),
(1, 1, 'New job', 'Started a new job. Need confidence.', TRUE),
(2, 1, 'Baby on the way', 'Expecting! Pray for a smooth delivery.', FALSE),
(3, 2, 'Church growth', 'Pray that our church continues to grow in love.', TRUE),
(1, 1, 'Battling anxiety', 'Anxiety is overwhelming. Please pray.', TRUE),
(2, 2, 'Faith renewal', 'Struggling spiritually. Need guidance.', TRUE),
(3, 1, 'Helping a friend', 'A friend is going through a hard time.', TRUE),
(1, 2, 'Lost phone', 'Minor issue but stressful — prayer appreciated.', TRUE),
(2, 1, 'Praise report', 'God answered a big prayer last week!', TRUE),
(3, 2, 'Upcoming surgery', 'Going under next Friday. Please pray.', TRUE),
(1, 1, 'Child struggling in school', 'Need clarity and help for my son.', FALSE),
(2, 2, 'Addiction recovery', 'Praying for someone close to find strength.', FALSE),
(3, 1, 'Clarity in calling', 'Trying to discern my next step in ministry.', FALSE),
(1, 2, 'Health for spouse', 'My spouse has been feeling unwell.', TRUE),
(2, 1, 'Gratitude for community', 'So thankful for support recently.', FALSE),
(3, 2, 'Safe travels', 'Traveling across the country next week.', TRUE),
(1, 1, 'Guidance in parenting', 'Need patience and wisdom.', TRUE),
(2, 2, 'Hope in tough times', 'Times are hard. Trying to keep hope alive.', TRUE),
(3, 1, 'Job interview tomorrow', 'Feeling nervous — appreciate prayers.', TRUE),
(1, 2, 'Rest and Sabbath', 'Feeling burned out. Need rest.', TRUE),
(2, 1, 'Praying for our leaders', 'May they lead with justice.', TRUE),
(3, 2, 'Loneliness', 'Been feeling isolated lately.', FALSE);

-- Simulate users praying for some of the above
INSERT INTO user_prayers (user_id, prayer_id) VALUES
(2, 1),
(3, 1),
(1, 2),
(3, 2),
(1, 3),
(2, 3),
(2, 4),
(3, 5),
(1, 6),
(3, 6),
(1, 7),
(2, 7),
(1, 10),
(2, 11),
(3, 12),
(1, 13),
(3, 14),
(2, 15),
(1, 17),
(2, 17),
(3, 17),
(1, 18),
(2, 20),
(1, 21),
(3, 23),
(2, 24),
(1, 26),
(3, 27),
(2, 28),
(3, 30);