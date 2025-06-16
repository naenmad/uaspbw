-- Database Schema for Order Management System (UAS PBW)
-- Created: June 14, 2025

-- Create database
CREATE DATABASE IF NOT EXISTS uaspbw_db;
USE uaspbw_db;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    avatar VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- 2. CUSTOMERS TABLE
-- ========================================
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    city VARCHAR(50) NULL,
    postal_code VARCHAR(10) NULL,
    company VARCHAR(100) NULL,
    customer_type ENUM('individual', 'company') DEFAULT 'individual',
    status ENUM('active', 'inactive') DEFAULT 'active',
    notes TEXT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ========================================
-- 3. PRODUCTS/ITEMS TABLE
-- ========================================
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    category VARCHAR(50) NULL,
    unit VARCHAR(20) DEFAULT 'pcs',
    price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    stock_quantity INT DEFAULT 0,
    min_stock INT DEFAULT 0,
    image VARCHAR(255) NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ========================================
-- 4. ORDERS TABLE
-- ========================================
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    order_date DATE NOT NULL,
    due_date DATE NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid',
    payment_method ENUM('cash', 'transfer', 'credit_card', 'e_wallet') NULL,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    discount_amount DECIMAL(15,2) DEFAULT 0.00,
    shipping_cost DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    notes TEXT NULL,
    shipping_address TEXT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ========================================
-- 5. ORDER ITEMS TABLE
-- ========================================
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL, -- Store name for historical data
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(15,2) NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

-- ========================================
-- 6. ORDER STATUS HISTORY TABLE
-- ========================================
CREATE TABLE order_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    old_status VARCHAR(20) NULL,
    new_status VARCHAR(20) NOT NULL,
    notes TEXT NULL,
    changed_by INT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ========================================
-- 7. PAYMENTS TABLE
-- ========================================
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_code VARCHAR(50) UNIQUE NOT NULL,
    order_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('cash', 'transfer', 'credit_card', 'e_wallet') NOT NULL,
    reference_number VARCHAR(100) NULL,
    notes TEXT NULL,
    status ENUM('pending', 'confirmed', 'failed', 'cancelled') DEFAULT 'pending',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- ========================================
-- 8. SYSTEM SETTINGS TABLE
-- ========================================
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NULL,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- 9. USER SESSIONS TABLE (optional)
-- ========================================
CREATE TABLE user_sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- 10. ACTIVITY LOGS TABLE
-- ========================================
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    model VARCHAR(50) NULL,
    model_id INT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ========================================
-- CREATE INDEXES FOR BETTER PERFORMANCE
-- ========================================

-- Users indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);

-- Customers indexes
CREATE INDEX idx_customers_code ON customers(customer_code);
CREATE INDEX idx_customers_name ON customers(name);
CREATE INDEX idx_customers_email ON customers(email);

-- Products indexes
CREATE INDEX idx_products_code ON products(product_code);
CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_products_category ON products(category);

-- Orders indexes
CREATE INDEX idx_orders_number ON orders(order_number);
CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_date ON orders(order_date);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_payment_status ON orders(payment_status);

-- Order items indexes
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);

-- Payments indexes
CREATE INDEX idx_payments_order ON payments(order_id);
CREATE INDEX idx_payments_date ON payments(payment_date);
CREATE INDEX idx_payments_method ON payments(payment_method);

-- Activity logs indexes
CREATE INDEX idx_activity_logs_user ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_action ON activity_logs(action);
CREATE INDEX idx_activity_logs_model ON activity_logs(model, model_id);
CREATE INDEX idx_activity_logs_created ON activity_logs(created_at);

-- ========================================
-- INSERT DEFAULT DATA
-- ========================================

-- Insert default admin user
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@orderSystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('app_name', 'Order Management System', 'string', 'Application name'),
('app_version', '1.0.0', 'string', 'Application version'),
('default_currency', 'IDR', 'string', 'Default currency'),
('tax_rate', '10', 'number', 'Default tax rate percentage'),
('order_prefix', 'ORD', 'string', 'Order number prefix'),
('customer_prefix', 'CUST', 'string', 'Customer code prefix'),
('product_prefix', 'PRD', 'string', 'Product code prefix'),
('payment_prefix', 'PAY', 'string', 'Payment code prefix'),
('items_per_page', '10', 'number', 'Default items per page'),
('backup_enabled', '1', 'boolean', 'Enable automatic backup'),
('email_notifications', '1', 'boolean', 'Enable email notifications'),
('maintenance_mode', '0', 'boolean', 'Maintenance mode status');

-- Insert sample customers
INSERT INTO customers (customer_code, name, email, phone, address, city, customer_type) VALUES
('CUST001', 'John Doe', 'john.doe@email.com', '081234567890', 'Jl. Sudirman No. 123', 'Jakarta', 'individual'),
('CUST002', 'Jane Smith', 'jane.smith@email.com', '081234567891', 'Jl. Thamrin No. 456', 'Jakarta', 'individual'),
('CUST003', 'PT ABC Company', 'info@abc.com', '081234567892', 'Jl. Gatot Subroto No. 789', 'Jakarta', 'company');

-- Insert sample products
INSERT INTO products (product_code, name, description, category, unit, price, stock_quantity) VALUES
('PRD001', 'Product A', 'Description for Product A', 'Category 1', 'pcs', 50000.00, 100),
('PRD002', 'Product B', 'Description for Product B', 'Category 1', 'pcs', 75000.00, 50),
('PRD003', 'Product C', 'Description for Product C', 'Category 2', 'pcs', 100000.00, 25);

-- Insert sample orders
INSERT INTO orders (order_number, customer_id, order_date, status, payment_status, subtotal, total_amount) VALUES
('ORD001', 1, '2025-06-14', 'completed', 'paid', 150000.00, 150000.00),
('ORD002', 2, '2025-06-13', 'pending', 'unpaid', 200000.00, 200000.00),
('ORD003', 3, '2025-06-12', 'processing', 'partial', 75000.00, 75000.00);

-- Insert sample order items
INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price) VALUES
(1, 1, 'Product A', 2, 50000.00, 100000.00),
(1, 2, 'Product B', 1, 50000.00, 50000.00),
(2, 2, 'Product B', 2, 75000.00, 150000.00),
(2, 3, 'Product C', 1, 50000.00, 50000.00),
(3, 1, 'Product A', 1, 50000.00, 50000.00),
(3, 3, 'Product C', 1, 25000.00, 25000.00);

-- ========================================
-- CREATE VIEWS FOR COMMON QUERIES
-- ========================================

-- View for order summary with customer info
CREATE VIEW order_summary AS
SELECT 
    o.id,
    o.order_number,
    c.name as customer_name,
    c.email as customer_email,
    o.order_date,
    o.status,
    o.payment_status,
    o.total_amount,
    COUNT(oi.id) as total_items
FROM orders o
JOIN customers c ON o.customer_id = c.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

-- View for customer statistics
CREATE VIEW customer_stats AS
SELECT 
    c.id,
    c.customer_code,
    c.name,
    c.email,
    COUNT(o.id) as total_orders,
    COALESCE(SUM(o.total_amount), 0) as total_spent,
    MAX(o.order_date) as last_order_date
FROM customers c
LEFT JOIN orders o ON c.id = o.customer_id
GROUP BY c.id;

-- View for product sales statistics
CREATE VIEW product_sales_stats AS
SELECT 
    p.id,
    p.product_code,
    p.name,
    p.price,
    p.stock_quantity,
    COALESCE(SUM(oi.quantity), 0) as total_sold,
    COALESCE(SUM(oi.total_price), 0) as total_revenue
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id;

-- View for monthly sales report
CREATE VIEW monthly_sales AS
SELECT 
    YEAR(o.order_date) as year,
    MONTH(o.order_date) as month,
    COUNT(o.id) as total_orders,
    SUM(o.total_amount) as total_revenue,
    COUNT(DISTINCT o.customer_id) as unique_customers
FROM orders o
WHERE o.status != 'cancelled'
GROUP BY YEAR(o.order_date), MONTH(o.order_date)
ORDER BY year DESC, month DESC;

-- ========================================
-- CREATE STORED PROCEDURES
-- ========================================

DELIMITER //

-- Procedure to generate next order number
CREATE PROCEDURE GetNextOrderNumber()
BEGIN
    DECLARE next_num INT;
    DECLARE order_prefix VARCHAR(10);
    
    SELECT setting_value INTO order_prefix 
    FROM system_settings 
    WHERE setting_key = 'order_prefix';
    
    SELECT COALESCE(MAX(CAST(SUBSTRING(order_number, LENGTH(order_prefix) + 1) AS UNSIGNED)), 0) + 1 
    INTO next_num
    FROM orders 
    WHERE order_number LIKE CONCAT(order_prefix, '%');
    
    SELECT CONCAT(order_prefix, LPAD(next_num, 3, '0')) as next_order_number;
END //

-- Procedure to update order status
CREATE PROCEDURE UpdateOrderStatus(
    IN p_order_id INT,
    IN p_new_status VARCHAR(20),
    IN p_notes TEXT,
    IN p_user_id INT
)
BEGIN
    DECLARE old_status VARCHAR(20);
    
    SELECT status INTO old_status FROM orders WHERE id = p_order_id;
    
    UPDATE orders SET 
        status = p_new_status,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_order_id;
    
    INSERT INTO order_status_history (order_id, old_status, new_status, notes, changed_by)
    VALUES (p_order_id, old_status, p_new_status, p_notes, p_user_id);
END //

DELIMITER ;

-- ========================================
-- CREATE TRIGGERS
-- ========================================

DELIMITER //

-- Trigger to update order total when order items change
CREATE TRIGGER update_order_total_after_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders SET 
        subtotal = (SELECT SUM(total_price) FROM order_items WHERE order_id = NEW.order_id),
        total_amount = (SELECT SUM(total_price) FROM order_items WHERE order_id = NEW.order_id)
    WHERE id = NEW.order_id;
END //

CREATE TRIGGER update_order_total_after_update
AFTER UPDATE ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders SET 
        subtotal = (SELECT SUM(total_price) FROM order_items WHERE order_id = NEW.order_id),
        total_amount = (SELECT SUM(total_price) FROM order_items WHERE order_id = NEW.order_id)
    WHERE id = NEW.order_id;
END //

CREATE TRIGGER update_order_total_after_delete
AFTER DELETE ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders SET 
        subtotal = (SELECT COALESCE(SUM(total_price), 0) FROM order_items WHERE order_id = OLD.order_id),
        total_amount = (SELECT COALESCE(SUM(total_price), 0) FROM order_items WHERE order_id = OLD.order_id)
    WHERE id = OLD.order_id;
END //

-- Trigger to log activities
CREATE TRIGGER log_order_activity
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, action, model, model_id, description)
    VALUES (NEW.created_by, 'CREATE', 'Order', NEW.id, CONCAT('Order ', NEW.order_number, ' created'));
END //

DELIMITER ;

-- ========================================
-- GRANT PERMISSIONS (if needed)
-- ========================================

-- Create application user
-- CREATE USER 'orderapp'@'localhost' IDENTIFIED BY 'secure_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON uaspbw_db.* TO 'orderapp'@'localhost';
-- FLUSH PRIVILEGES;

-- ========================================
-- END OF SCHEMA
-- ========================================
