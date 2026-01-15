USE robotdb;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','viewer') NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at TIMESTAMP NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- user เริ่มต้น: admin / admin1234
-- แนะนำให้ล็อกอินแล้ว "เปลี่ยนรหัสทันที"
INSERT IGNORE INTO users (username, password_hash, role)
VALUES (
  'admin',
  '$2y$10$w3p7bVZk8w2iQe7G1p0mUeQnq2pQ0bJm8bqQvPz0fM7iY2rXl9k8a',
  'admin'
);
