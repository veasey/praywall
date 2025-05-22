-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'moderator', 'admin') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Churches table
CREATE TABLE IF NOT EXISTS churches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Prayers table
CREATE TABLE IF NOT EXISTS prayers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    church_id INT UNSIGNED,
    approved BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (church_id) REFERENCES churches(id) ON DELETE SET NULL
);

-- Prayers_Prayed_By table (tracks who's praying)
CREATE TABLE IF NOT EXISTS prayers_prayed_by (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    prayer_id INT UNSIGNED,
    prayed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (prayer_id) REFERENCES prayers(id) ON DELETE CASCADE
);

-- Praises table (linked to Prayers)
CREATE TABLE IF NOT EXISTS praises (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prayer_id INT UNSIGNED,
    user_id INT UNSIGNED,
    body TEXT NOT NULL,
    date_posted DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prayer_id) REFERENCES prayers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS user_prayers (
    user_id INT NOT NULL,
    prayer_id INT NOT NULL,
    PRIMARY KEY (user_id, prayer_id)
);

CREATE TABLE IF NOT EXISTS user_settings (
    user_id INT UNSIGNED NOT NULL, 
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    PRIMARY KEY (user_id, setting_key),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
