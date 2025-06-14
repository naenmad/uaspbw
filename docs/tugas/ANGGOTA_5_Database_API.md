# 🗄️ TUGAS ANGGOTA 5: DATABASE INTEGRATION & API

**Nama:** [Isi nama anggota]  
**Tanggung Jawab:** Database Operations, API Development, Backend Integration  
**Deadline:** [Isi deadline]  

---

## 🎯 OBJECTIVES

Memastikan database berjalan optimal, membuat helper functions untuk operasi database, mengembangkan API endpoints, dan mendukung semua anggota tim dalam integrasi database.

---

## 📋 TASK CHECKLIST

### **Phase 1: Database Foundation (Week 1)**
- [ ] Setup dan test database environment
- [ ] Verify database schema in `database/schema.sql`
- [ ] Test database setup process in `setup/index.php`
- [ ] Create sample data for testing
- [ ] Document database structure and relationships
- [ ] Optimize database configuration for performance

### **Phase 2: Database Helper Functions (Week 2)**
- [ ] **Enhance `config/database.php`:**
  - [ ] Add connection pooling (if needed)
  - [ ] Add error logging
  - [ ] Add database health check functions
  - [ ] Add transaction helper functions
  - [ ] Add query logging for debugging

- [ ] **Create `includes/db-functions.php`:**
  - [ ] Generic CRUD functions
  - [ ] Pagination helper functions
  - [ ] Search and filter functions
  - [ ] Data validation functions
  - [ ] Backup and restore functions

### **Phase 3: API Development (Week 2-3)**
- [ ] **Create API structure:**
  - [ ] `api/index.php` - API router
  - [ ] `api/auth.php` - Authentication endpoints
  - [ ] `api/orders.php` - Order CRUD endpoints
  - [ ] `api/customers.php` - Customer CRUD endpoints
  - [ ] `api/reports.php` - Report data endpoints

- [ ] **API Features:**
  - [ ] RESTful API design
  - [ ] JSON response format
  - [ ] Error handling and status codes
  - [ ] API authentication and rate limiting
  - [ ] CORS handling for frontend integration

### **Phase 4: Database Optimization (Week 3-4)**
- [ ] **Performance optimization:**
  - [ ] Add database indexes where needed
  - [ ] Optimize slow queries
  - [ ] Implement query caching
  - [ ] Add connection pooling
  - [ ] Monitor database performance

- [ ] **Stored Procedures & Triggers:**
  - [ ] Implement stored procedures from schema
  - [ ] Test triggers for data integrity
  - [ ] Create custom procedures for complex operations
  - [ ] Add audit trail functionality

### **Phase 5: Backup & Migration Tools (Week 4)**
- [ ] **Create `includes/backup.php`:**
  - [ ] Database backup functionality
  - [ ] Automated backup scheduling
  - [ ] Backup restoration tools
  - [ ] Data export/import utilities

- [ ] **Create `setup/migrate.php`:**
  - [ ] Database migration system
  - [ ] Version control for database changes
  - [ ] Data seeding functionality
  - [ ] Environment-specific configurations

### **Phase 6: Team Support & Integration (Week 5)**
- [ ] **Support other team members:**
  - [ ] Help Anggota 1 with authentication queries
  - [ ] Help Anggota 2 with order management queries
  - [ ] Help Anggota 3 with customer management queries
  - [ ] Help Anggota 4 with reporting queries
  - [ ] Code review and optimization assistance

---

## 📁 FILES TO WORK WITH

### **Existing Files (Enhance):**
- `config/database.php` - Enhance database configuration
- `database/schema.sql` - Verify and optimize schema
- `setup/index.php` - Enhance setup process

### **New Files (Create):**
- `includes/db-functions.php` - Database helper functions
- `includes/backup.php` - Backup and restore tools
- `setup/migrate.php` - Migration tools
- `api/` - API endpoints directory
- `logs/` - Database and API logs

---

## 🗄️ DATABASE HELPER FUNCTIONS

### **Generic CRUD Functions:**
```php
<?php
// includes/db-functions.php - Main helper file
class DatabaseHelper {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Generic insert function
     */
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        if ($stmt->execute()) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }
    
    /**
     * Generic select function with filters
     */
    public function select($table, $conditions = [], $options = []) {
        $sql = "SELECT * FROM {$table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where_clauses = [];
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $placeholders = ':' . $column . '_' . implode(', :' . $column . '_', array_keys($value));
                    $where_clauses[] = "{$column} IN ({$placeholders})";
                    foreach ($value as $i => $v) {
                        $params[$column . '_' . $i] = $v;
                    }
                } else {
                    $where_clauses[] = "{$column} = :{$column}";
                    $params[$column] = $value;
                }
            }
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }
        
        // Add ordering
        if (isset($options['order_by'])) {
            $sql .= " ORDER BY " . $options['order_by'];
            if (isset($options['order_dir'])) {
                $sql .= " " . $options['order_dir'];
            }
        }
        
        // Add pagination
        if (isset($options['limit'])) {
            $sql .= " LIMIT " . (int)$options['limit'];
            if (isset($options['offset'])) {
                $sql .= " OFFSET " . (int)$options['offset'];
            }
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return isset($options['single']) ? $stmt->fetch() : $stmt->fetchAll();
    }
    
    /**
     * Generic update function
     */
    public function update($table, $data, $conditions) {
        $set_clauses = [];
        foreach ($data as $column => $value) {
            $set_clauses[] = "{$column} = :{$column}";
        }
        
        $where_clauses = [];
        foreach ($conditions as $column => $value) {
            $where_clauses[] = "{$column} = :where_{$column}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $set_clauses) . 
               " WHERE " . implode(' AND ', $where_clauses);
        
        $stmt = $this->pdo->prepare($sql);
        
        // Bind data values
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        // Bind condition values
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":where_{$key}", $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Generic delete function
     */
    public function delete($table, $conditions) {
        $where_clauses = [];
        foreach ($conditions as $column => $value) {
            $where_clauses[] = "{$column} = :{$column}";
        }
        
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $where_clauses);
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute($conditions);
    }
    
    /**
     * Pagination helper
     */
    public function paginate($table, $conditions = [], $per_page = 20, $page = 1) {
        $offset = ($page - 1) * $per_page;
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM {$table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where_clauses = [];
            foreach ($conditions as $column => $value) {
                $where_clauses[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
            $count_sql .= " WHERE " . implode(' AND ', $where_clauses);
        }
        
        $count_stmt = $this->pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetchColumn();
        
        // Get data
        $data = $this->select($table, $conditions, [
            'limit' => $per_page,
            'offset' => $offset
        ]);
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_records' => $total_records,
                'total_pages' => ceil($total_records / $per_page),
                'has_previous' => $page > 1,
                'has_next' => $page < ceil($total_records / $per_page)
            ]
        ];
    }
}

// Initialize global helper
$db = new DatabaseHelper($pdo);
?>
```

---

## 🔌 API DEVELOPMENT

### **API Router Structure:**
```php
<?php
// api/index.php - Main API router
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../includes/db-functions.php';

class ApiRouter {
    private $request_method;
    private $endpoint;
    private $params;
    
    public function __construct() {
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        $this->endpoint = $this->getEndpoint();
        $this->params = $this->getParams();
    }
    
    private function getEndpoint() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);
        return isset($uri[3]) ? $uri[3] : '';
    }
    
    private function getParams() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);
        return array_slice($uri, 4);
    }
    
    public function route() {
        switch ($this->endpoint) {
            case 'orders':
                require_once 'orders.php';
                $api = new OrdersAPI();
                break;
            case 'customers':
                require_once 'customers.php';
                $api = new CustomersAPI();
                break;
            case 'reports':
                require_once 'reports.php';
                $api = new ReportsAPI();
                break;
            case 'auth':
                require_once 'auth.php';
                $api = new AuthAPI();
                break;
            default:
                $this->response(['error' => 'Endpoint not found'], 404);
                return;
        }
        
        $api->handleRequest($this->request_method, $this->params);
    }
    
    public function response($data, $status_code = 200) {
        http_response_code($status_code);
        echo json_encode($data);
        exit;
    }
}

$router = new ApiRouter();
$router->route();
?>
```

### **Orders API Example:**
```php
<?php
// api/orders.php
class OrdersAPI {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    public function handleRequest($method, $params) {
        switch ($method) {
            case 'GET':
                if (isset($params[0])) {
                    $this->getOrder($params[0]);
                } else {
                    $this->getOrders();
                }
                break;
            case 'POST':
                $this->createOrder();
                break;
            case 'PUT':
                if (isset($params[0])) {
                    $this->updateOrder($params[0]);
                }
                break;
            case 'DELETE':
                if (isset($params[0])) {
                    $this->deleteOrder($params[0]);
                }
                break;
            default:
                $this->response(['error' => 'Method not allowed'], 405);
        }
    }
    
    private function getOrders() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        
        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }
        
        $result = $this->db->paginate('orders', $conditions, $per_page, $page);
        $this->response($result);
    }
    
    private function getOrder($id) {
        $order = $this->db->select('orders', ['id' => $id], ['single' => true]);
        
        if ($order) {
            // Get order items
            $items = $this->db->select('order_items', ['order_id' => $id]);
            $order['items'] = $items;
            
            $this->response($order);
        } else {
            $this->response(['error' => 'Order not found'], 404);
        }
    }
    
    private function createOrder() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $this->response(['error' => 'Invalid JSON'], 400);
            return;
        }
        
        // Validate required fields
        if (!isset($input['customer_id']) || !isset($input['items'])) {
            $this->response(['error' => 'Missing required fields'], 400);
            return;
        }
        
        try {
            global $pdo;
            $pdo->beginTransaction();
            
            // Create order
            $order_data = [
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $input['customer_id'],
                'order_date' => $input['order_date'] ?? date('Y-m-d'),
                'status' => 'Pending',
                'notes' => $input['notes'] ?? ''
            ];
            
            $order_id = $this->db->insert('orders', $order_data);
            
            // Create order items
            $subtotal = 0;
            foreach ($input['items'] as $item) {
                $item_data = [
                    'order_id' => $order_id,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price']
                ];
                
                $this->db->insert('order_items', $item_data);
                $subtotal += $item_data['total_price'];
            }
            
            // Update order totals
            $tax_amount = $subtotal * 0.1; // 10% tax
            $total_amount = $subtotal + $tax_amount;
            
            $this->db->update('orders', [
                'subtotal' => $subtotal,
                'tax_amount' => $tax_amount,
                'total_amount' => $total_amount
            ], ['id' => $order_id]);
            
            $pdo->commit();
            
            $this->response(['id' => $order_id, 'message' => 'Order created successfully'], 201);
            
        } catch (Exception $e) {
            $pdo->rollback();
            $this->response(['error' => 'Failed to create order'], 500);
        }
    }
    
    private function generateOrderNumber() {
        return 'ORD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    private function response($data, $status_code = 200) {
        http_response_code($status_code);
        echo json_encode($data);
        exit;
    }
}
?>
```

---

## 🔧 DATABASE OPTIMIZATION

### **Index Creation:**
```sql
-- Add these indexes to improve performance
CREATE INDEX idx_orders_customer_id ON orders(customer_id);
CREATE INDEX idx_orders_order_date ON orders(order_date);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_customers_email ON customers(email);
CREATE INDEX idx_customers_created_at ON customers(created_at);

-- Composite indexes for common queries
CREATE INDEX idx_orders_status_date ON orders(status, order_date);
CREATE INDEX idx_orders_customer_status ON orders(customer_id, status);
```

### **Query Optimization:**
```php
<?php
// includes/db-optimization.php
class QueryOptimizer {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Analyze slow queries
     */
    public function analyzeSlowQueries() {
        $sql = "SELECT * FROM information_schema.processlist WHERE time > 5";
        return $this->pdo->query($sql)->fetchAll();
    }
    
    /**
     * Get table sizes
     */
    public function getTableSizes() {
        $sql = "SELECT 
                    table_name,
                    round(((data_length + index_length) / 1024 / 1024), 2) as size_mb
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
                ORDER BY (data_length + index_length) DESC";
        
        return $this->pdo->query($sql)->fetchAll();
    }
    
    /**
     * Check for missing indexes
     */
    public function suggestIndexes() {
        // This would analyze query patterns and suggest indexes
        // Implementation depends on query log analysis
    }
}
?>
```

---

## 💾 BACKUP & RESTORE

### **Backup System:**
```php
<?php
// includes/backup.php
class DatabaseBackup {
    private $pdo;
    private $backup_dir;
    
    public function __construct($pdo, $backup_dir = '../backups/') {
        $this->pdo = $pdo;
        $this->backup_dir = $backup_dir;
        
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
    }
    
    /**
     * Create full database backup
     */
    public function createFullBackup() {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $this->backup_dir . $filename;
        
        $tables = $this->getTables();
        $backup_content = '';
        
        foreach ($tables as $table) {
            $backup_content .= $this->backupTable($table);
        }
        
        file_put_contents($filepath, $backup_content);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => filesize($filepath)
        ];
    }
    
    /**
     * Backup single table
     */
    private function backupTable($table) {
        $output = "\n-- Table: {$table}\n";
        $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
        
        // Get table structure
        $create_table = $this->pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
        $output .= $create_table['Create Table'] . ";\n\n";
        
        // Get table data
        $rows = $this->pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $output .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $row_values = array_map(function($value) {
                    return $value === null ? 'NULL' : $this->pdo->quote($value);
                }, $row);
                $values[] = '(' . implode(', ', $row_values) . ')';
            }
            
            $output .= implode(",\n", $values) . ";\n\n";
        }
        
        return $output;
    }
    
    /**
     * Get all tables
     */
    private function getTables() {
        $tables = [];
        $result = $this->pdo->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        return $tables;
    }
    
    /**
     * Restore from backup
     */
    public function restoreBackup($backup_file) {
        if (!file_exists($backup_file)) {
            return ['success' => false, 'error' => 'Backup file not found'];
        }
        
        $sql = file_get_contents($backup_file);
        
        try {
            $this->pdo->exec($sql);
            return ['success' => true, 'message' => 'Database restored successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * List available backups
     */
    public function listBackups() {
        $backups = [];
        $files = glob($this->backup_dir . 'backup_*.sql');
        
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'filepath' => $file,
                'size' => filesize($file),
                'created_at' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        return $backups;
    }
}
?>
```

---

## 🧪 TESTING CHECKLIST

- [ ] Database connection works properly
- [ ] All CRUD operations function correctly
- [ ] API endpoints return proper JSON responses
- [ ] Error handling works for invalid requests
- [ ] Pagination works with large datasets
- [ ] Database indexes improve query performance
- [ ] Backup and restore functions work
- [ ] Transaction handling prevents data corruption
- [ ] API authentication secures endpoints
- [ ] Database optimization improves performance

---

## 🚨 COMMON ISSUES & SOLUTIONS

### **Issue:** Database connection timeouts
**Solution:** Optimize connection settings, implement connection pooling

### **Issue:** API CORS errors
**Solution:** Set proper CORS headers in API responses

### **Issue:** Slow query performance
**Solution:** Add appropriate indexes, optimize queries

### **Issue:** Memory issues with large datasets
**Solution:** Implement proper pagination, use streaming for large exports

---

## 🔗 TEAM COORDINATION

### **Support Schedule:**
- **Week 1:** Setup assistance and database validation
- **Week 2:** Helper functions and API development
- **Week 3:** Query optimization and team integration
- **Week 4:** Performance tuning and backup systems
- **Week 5:** Final integration and bug fixes

### **Communication:**
- Daily check-ins via team chat
- Code review for database queries
- Performance monitoring assistance
- Emergency support for critical issues

---

## 📚 RESOURCES

- [PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
- [MySQL Performance Tuning](https://dev.mysql.com/doc/refman/8.0/en/optimization.html)
- [REST API Best Practices](https://restfulapi.net/)
- [Database Indexing Guide](https://use-the-index-luke.com/)

---

## 📞 HELP & SUPPORT

**Your role as support:**
- Help debug database connection issues
- Optimize slow queries for other team members
- Provide database functions and API endpoints
- Assist with complex SQL queries
- Monitor system performance

---

**Success Criteria:**
✅ Database operates efficiently with optimal performance  
✅ All team members have working database integration  
✅ API endpoints function properly for frontend use  
✅ Backup and restore systems are operational  
✅ Database security and integrity maintained  
✅ Query performance optimized for production  

**Good luck! You're the backbone of the team! 🚀**
