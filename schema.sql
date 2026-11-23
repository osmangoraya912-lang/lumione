-- LumiCore One - Database schema (MySQL 8 / MariaDB)
-- Run through install.php, or: mysql -u root -p lumione < schema.sql
-- NOTE: sales are NOT seeded. Malik has 0 sales so far - income starts at $0 honestly.

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    store VARCHAR(30) NOT NULL DEFAULT 'Other',
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    product_link VARCHAR(500) NOT NULL DEFAULT '',
    category VARCHAR(80) NOT NULL DEFAULT '',
    status VARCHAR(20) NOT NULL DEFAULT 'Live',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT DEFAULT NULL,
    platform VARCHAR(60) NOT NULL DEFAULT 'Other',
    amount DECIMAL(10,2) NOT NULL,
    sale_date DATE NOT NULL,
    buyer_note VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    due_date DATE NOT NULL,
    priority VARCHAR(10) NOT NULL DEFAULT 'Medium',
    done TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    platform VARCHAR(30) NOT NULL DEFAULT 'Threads',
    scheduled_at DATETIME NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Planned',
    link VARCHAR(500) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    body TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    url VARCHAR(500) NOT NULL,
    category VARCHAR(30) NOT NULL DEFAULT 'Work',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed products: his 25 real live products (14 goraya244 + 11 mangoraya)
INSERT INTO products (id, name, store, price, product_link, category, status) VALUES
(1, 'Invoice Pro', 'goraya244', 24, 'https://goraya244.gumroad.com/l/tayopd', 'Business Tools', 'Live'),
(2, 'Expense Tracker', 'goraya244', 19, 'https://goraya244.gumroad.com/l/vteqcf', 'Business Tools', 'Live'),
(3, 'Inventory Management System', 'goraya244', 24, 'https://goraya244.gumroad.com/l/borbwi', 'Business Tools', 'Live'),
(4, 'Simple CRM', 'goraya244', 24, 'https://goraya244.gumroad.com/l/vfynt', 'Business Tools', 'Live'),
(5, 'TimePulse', 'goraya244', 24, 'https://goraya244.gumroad.com/l/ubnyno', 'Business Tools', 'Live'),
(6, 'PayrollIQ', 'goraya244', 19, 'https://goraya244.gumroad.com/l/zqskiu', 'Business Tools', 'Live'),
(7, 'Kanban Flow', 'goraya244', 24, 'https://goraya244.gumroad.com/l/kvyks', 'Business Tools', 'Live'),
(8, 'Client Gate', 'goraya244', 24, 'https://goraya244.gumroad.com/l/vzldi', 'Business Tools', 'Live'),
(9, 'QuoteForge', 'goraya244', 24, 'https://goraya244.gumroad.com/l/tkftbd', 'Business Tools', 'Live'),
(10, 'BooklyPro', 'goraya244', 24, 'https://goraya244.gumroad.com/l/iwpyx', 'Business Tools', 'Live'),
(11, 'ReportCraft', 'goraya244', 24, 'https://goraya244.gumroad.com/l/ytnchc', 'Automation', 'Live'),
(12, 'ScrapeSave', 'goraya244', 24, 'https://goraya244.gumroad.com/l/whmbth', 'Automation', 'Live'),
(13, 'FormFlow Connect', 'goraya244', 24, 'https://goraya244.gumroad.com/l/pgfkvd', 'Automation', 'Live'),
(14, 'Sortly', 'goraya244', 19, 'https://goraya244.gumroad.com/l/efecgn', 'Automation', 'Live'),
(15, 'QuickTill', 'mangoraya', 24, 'https://mangoraya.gumroad.com/l/xndqbb', 'Business Tools', 'Live'),
(16, 'FlowTask', 'mangoraya', 19, 'https://mangoraya.gumroad.com/l/fbtcgp', 'Business Tools', 'Live'),
(17, 'DocCraft', 'mangoraya', 24, 'https://mangoraya.gumroad.com/l/diytcz', 'Business Tools', 'Live'),
(18, 'ProfitFX', 'mangoraya', 19, 'https://mangoraya.gumroad.com/l/jkzzn', 'Business Tools', 'Live'),
(19, 'NexaBoard', 'mangoraya', 24, 'https://mangoraya.gumroad.com/l/khhzql', 'Business Tools', 'Live'),
(20, 'FlowReply', 'mangoraya', 29, 'https://mangoraya.gumroad.com/l/pswukz', 'Automation', 'Live'),
(21, 'TeleFlow', 'mangoraya', 29, 'https://mangoraya.gumroad.com/l/mlhsvv', 'Automation', 'Live'),
(22, 'InboxPilot', 'mangoraya', 24, 'https://mangoraya.gumroad.com/l/luemsb', 'Automation', 'Live'),
(23, 'PostPilot', 'mangoraya', 24, 'https://mangoraya.gumroad.com/l/eibqvr', 'Automation', 'Live'),
(24, 'SheetFlow', 'mangoraya', 24, 'https://mangoraya.gumroad.com/l/wxjbovb', 'Automation', 'Live'),
(25, 'Messaging Trio Bundle', 'mangoraya', 53, 'https://mangoraya.gumroad.com/l/vfqkia', 'Bundle', 'Live')
ON DUPLICATE KEY UPDATE name = VALUES(name), price = VALUES(price), product_link = VALUES(product_link);

-- Seed links: his real stores and profiles
INSERT INTO links (id, title, url, category) VALUES
(1, 'goraya244 Gumroad store', 'https://goraya244.gumroad.com', 'Store'),
(2, 'mangoraya Gumroad store', 'https://mangoraya.gumroad.com', 'Store'),
(3, 'WPBay Digishop store', 'https://wpbay.com/store/digishop/', 'Store'),
(4, 'Fiverr profile', 'https://www.fiverr.com/malikusman_786', 'Work'),
(5, 'YouTube - NextWave Trends', 'https://www.youtube.com/@NextWave-trends', 'Social'),
(6, 'Portfolio', 'https://osmangoraya912-lang.github.io/Portfolio/', 'Work')
ON DUPLICATE KEY UPDATE title = VALUES(title), url = VALUES(url);

-- Seed tasks: his real daily schedule (due dates use the install day)
INSERT INTO tasks (id, title, due_date, priority, done) VALUES
(1, '20 Gumroad products publish karo (10+10)', CURDATE(), 'High', 0),
(2, '70 images Shutterstock upload karo', CURDATE(), 'High', 0),
(3, '70 images Dreamstime upload karo', CURDATE(), 'High', 0),
(4, 'Adobe Stock Payoneer add karo', '2026-11-24', 'Medium', 0)
ON DUPLICATE KEY UPDATE title = VALUES(title), due_date = VALUES(due_date);

-- Seed notes: his real standing rules
INSERT INTO notes (id, title, body) VALUES
(1, 'Discount codes',
 'Discount codes: goraya244 = GORAYA20, mangoraya = MANGORAYA20 - 20% off, singles only, expiry 31 Dec 2026. Posts mein sirf Limited-time offer likhna, 40 uses ka number kabhi nahi.'),
(2, 'Daily schedule',
 'Daily schedule: Gumroad 20/day, images 70/day Shutterstock + 70/day Dreamstime.')
ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body);

-- Seed posts: M-B01 bundle posts, planned (not posted yet)
INSERT INTO posts (id, title, platform, scheduled_at, status, link) VALUES
(1, 'M-B01 Messaging Trio - Threads + Bluesky', 'Threads', CONCAT(CURDATE(), ' 18:00:00'), 'Planned', 'https://mangoraya.gumroad.com/l/vfqkia'),
(2, 'M-B01 Messaging Trio - Threads + Bluesky', 'Bluesky', CONCAT(CURDATE(), ' 18:05:00'), 'Planned', 'https://mangoraya.gumroad.com/l/vfqkia')
ON DUPLICATE KEY UPDATE title = VALUES(title), platform = VALUES(platform);
