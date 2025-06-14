# 👥 TUGAS ANGGOTA 3: CUSTOMER MANAGEMENT

**Nama:** [Isi nama anggota]  
**Tanggung Jawab:** Customer CRUD Operations, Customer Management System  
**Deadline:** [Isi deadline]  

---

## 🎯 OBJECTIVES

Mengintegrasikan halaman customer management yang sudah ada (tampilan statis) dengan database dan membuat sistem CRUD lengkap untuk mengelola customers.

---

## 📋 TASK CHECKLIST

### **Phase 1: Understanding & Setup (Week 1)**
- [ ] Setup development environment (XAMPP/Laragon)
- [ ] Run database setup: `http://localhost/uaspbw/setup/`
- [ ] Study existing file: `dashboard/customers.php`
- [ ] Study database schema: `customers` table and relationships
- [ ] Test current customers page (note: currently static data)

### **Phase 2: Customers List Integration (Week 2)**
- [ ] **Update `dashboard/customers.php`:**
  - [ ] Connect to database using `config/database.php`
  - [ ] Load customers from database with pagination
  - [ ] Display customer data in table (name, email, phone, address, orders count)
  - [ ] Add search functionality (by name, email, phone)
  - [ ] Add sorting options (name, created date, order count)
  - [ ] Add action buttons (View, Edit, Delete, Add Order)
  - [ ] Show customer statistics (total customers, new this month)

### **Phase 3: Add Customer (Week 2)**
- [ ] **Create `dashboard/customer-add.php`:**
  - [ ] Create customer registration form
  - [ ] Form validation (required fields, email format, phone format)
  - [ ] Check if email already exists
  - [ ] Save new customer to database
  - [ ] Show success/error messages
  - [ ] Redirect to customer detail after successful creation

### **Phase 4: Edit Customer (Week 3)**
- [ ] **Create `dashboard/customer-edit.php`:**
  - [ ] Load existing customer data for editing
  - [ ] Pre-populate form with current data
  - [ ] Form validation and duplicate email check
  - [ ] Update customer information in database
  - [ ] Show success/error messages
  - [ ] Handle customer profile picture upload (optional)

### **Phase 5: Customer Detail & History (Week 3-4)**
- [ ] **Create `dashboard/customer-detail.php`:**
  - [ ] Display complete customer information
  - [ ] Show customer order history
  - [ ] Calculate customer statistics (total orders, total spent, average order)
  - [ ] Display customer timeline/activity
  - [ ] Add quick actions (Edit, Delete, New Order)
  - [ ] Show customer notes/comments

### **Phase 6: Advanced Features (Week 4-5)**
- [ ] **Customer search & filtering:**
  - [ ] Advanced search with multiple criteria
  - [ ] Filter by customer status (Active, Inactive)
  - [ ] Filter by order activity (Recent, Inactive)
  - [ ] Export customer list (CSV, Excel)

- [ ] **Customer analytics:**
  - [ ] Top customers by order volume
  - [ ] Customer acquisition trends
  - [ ] Customer retention metrics

### **Phase 7: Helper Functions (Week 5)**
- [ ] **Create `includes/customer-functions.php`:**
  - [ ] `create_customer($data)` function
  - [ ] `get_customer_by_id($id)` function
  - [ ] `update_customer($id, $data)` function
  - [ ] `delete_customer($id)` function
  - [ ] `get_customers_list($filters, $pagination)` function
  - [ ] `get_customer_orders($customer_id)` function
  - [ ] `get_customer_statistics($customer_id)` function
  - [ ] `search_customers($query)` function

---

## 📁 FILES TO WORK WITH

### **Existing Files (Modify):**
- `dashboard/customers.php` - Add database integration

### **New Files (Create):**
- `dashboard/customer-add.php` - Add customer form
- `dashboard/customer-edit.php` - Edit customer form  
- `dashboard/customer-detail.php` - Customer detail view
- `includes/customer-functions.php` - Helper functions
- `api/customers.php` - (Optional) AJAX endpoints

---

## 🗄️ DATABASE TABLES

### **Primary Table: `customers`**
```sql
-- Study this table structure in database/schema.sql
customers (
    id, name, email, phone, address,
    city, postal_code, country,
    status, notes, created_at, updated_at
)
```

### **Related Tables:**
```sql
orders (
    id, customer_id, order_date, 
    status, total_amount, ...
)
```

---

## 💻 CODE EXAMPLES

### **Customers List Example:**
```php
<?php
// dashboard/customers.php - Add this logic
require_once '../config/database.php';
require_once '../includes/customer-functions.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get customers with order count
$sql = "SELECT c.*, COUNT(o.id) as order_count,
               COALESCE(SUM(o.total_amount), 0) as total_spent
        FROM customers c
        LEFT JOIN orders o ON c.id = o.customer_id
        $where_clause
        GROUP BY c.id
        ORDER BY c.created_at DESC
        LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

// Count total for pagination
$count_sql = "SELECT COUNT(*) FROM customers c $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_customers = $count_stmt->fetchColumn();
$total_pages = ceil($total_customers / $per_page);
?>
```

### **Customer Functions Example:**
```php
<?php
// includes/customer-functions.php - Create this file
function create_customer($data) {
    global $pdo;
    
    // Validate required fields
    if (empty($data['name']) || empty($data['email'])) {
        return false;
    }
    
    // Check if email exists
    $check_sql = "SELECT id FROM customers WHERE email = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$data['email']]);
    if ($check_stmt->fetch()) {
        return false; // Email already exists
    }
    
    try {
        $sql = "INSERT INTO customers (name, email, phone, address, city, postal_code, country, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'] ?? '',
            $data['address'] ?? '',
            $data['city'] ?? '',
            $data['postal_code'] ?? '',
            $data['country'] ?? '',
            $data['notes'] ?? ''
        ]);
        
        return $pdo->lastInsertId();
        
    } catch (PDOException $e) {
        return false;
    }
}

function get_customer_statistics($customer_id) {
    global $pdo;
    
    $sql = "SELECT 
                COUNT(o.id) as total_orders,
                COALESCE(SUM(o.total_amount), 0) as total_spent,
                COALESCE(AVG(o.total_amount), 0) as average_order,
                MAX(o.order_date) as last_order_date,
                MIN(o.order_date) as first_order_date
            FROM orders o 
            WHERE o.customer_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer_id]);
    return $stmt->fetch();
}
?>
```

---

## 📊 CUSTOMER METRICS TO TRACK

### **Basic Stats:**
- Total customers
- New customers this month
- Active customers (ordered in last 3 months)
- Inactive customers

### **Customer Analytics:**
- Top 10 customers by order value
- Customer lifetime value (CLV)
- Average orders per customer
- Customer acquisition trends
- Customer retention rate

---

## 🧪 TESTING CHECKLIST

- [ ] Add new customer successfully
- [ ] Customer form validation works
- [ ] Duplicate email prevention works
- [ ] Edit customer information works
- [ ] Customer list displays with correct data
- [ ] Search customers by name/email/phone works
- [ ] Customer detail shows order history
- [ ] Customer statistics calculations are correct
- [ ] Export customer list works
- [ ] Delete customer works (with order history check)
- [ ] Pagination works for large customer lists

---

## 🚨 COMMON ISSUES & SOLUTIONS

### **Issue:** Cannot delete customer with existing orders
**Solution:** Either prevent deletion or implement soft delete

### **Issue:** Duplicate email validation not working
**Solution:** Check email validation logic and database constraints

### **Issue:** Customer statistics showing wrong values
**Solution:** Verify JOIN conditions and NULL handling in SQL

### **Issue:** Search not finding partial matches
**Solution:** Use LIKE with wildcards (%)

---

## 📱 UI/UX CONSIDERATIONS

### **Customer List:**
- Quick search box at top
- Status indicators (Active/Inactive)
- Order count and total spent columns
- Quick action buttons
- Export functionality

### **Customer Form:**
- Clear field labels and validation
- Address fields organization
- Phone number formatting
- Email validation feedback

### **Customer Detail:**
- Contact information panel
- Order history table
- Customer statistics cards
- Timeline of activities
- Quick actions (Edit, New Order)

---

## 🔗 INTEGRATION POINTS

**With other team members:**
- **Anggota 1:** User authentication for customer access control
- **Anggota 2:** Customer selection in order creation
- **Anggota 4:** Customer data for reports and analytics
- **Anggota 5:** Database functions and API endpoints
- **Anggota 6:** UI improvements and form validation

---

## 📤 EXPORT FUNCTIONALITY

### **Customer Export Features:**
```php
// Export customers to CSV
function export_customers_csv($filters = []) {
    global $pdo;
    
    $sql = "SELECT name, email, phone, address, city, 
                   COUNT(o.id) as total_orders,
                   COALESCE(SUM(o.total_amount), 0) as total_spent,
                   c.created_at
            FROM customers c
            LEFT JOIN orders o ON c.id = o.customer_id
            GROUP BY c.id
            ORDER BY c.name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="customers.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'Email', 'Phone', 'Address', 'City', 'Total Orders', 'Total Spent', 'Created']);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, $row);
    }
    
    fclose($output);
}
```

---

## 📚 RESOURCES

- [PHP CSV Export](https://www.php.net/manual/en/function.fputcsv.php)
- [Email Validation](https://www.php.net/manual/en/filter.filters.validate.php)
- [MySQL JOIN Operations](https://dev.mysql.com/doc/refman/8.0/en/join.html)
- [Pagination Best Practices](https://www.smashingmagazine.com/2007/11/pagination-gallery-examples-and-good-practices/)

---

## 📞 HELP & SUPPORT

**Jika mengalami kesulitan:**
1. Cek database schema di `database/README.md`
2. Test dengan data sample customers yang sudah ada
3. Konsultasi dengan Anggota 5 (Database Integration)
4. Koordinasi dengan Anggota 2 untuk order integration

---

**Success Criteria:**
✅ Customer CRUD operations berfungsi sempurna  
✅ Customer list dengan search dan pagination  
✅ Customer detail dengan order history  
✅ Customer statistics dan analytics  
✅ Export functionality bekerja  
✅ Form validation dan error handling  

**Good luck! 🚀**
