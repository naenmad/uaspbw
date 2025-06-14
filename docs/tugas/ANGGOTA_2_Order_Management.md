# 📦 TUGAS ANGGOTA 2: ORDER MANAGEMENT (CRUD)

**Nama:** [Isi nama anggota]  
**Tanggung Jawab:** Order CRUD Operations, Order Management System  
**Deadline:** [Isi deadline]  

---

## 🎯 OBJECTIVES

Mengintegrasikan halaman order management yang sudah ada (tampilan statis) dengan database dan membuat sistem CRUD lengkap untuk mengelola orders.

---

## 📋 TASK CHECKLIST

### **Phase 1: Understanding & Setup (Week 1)**
- [ ] Setup development environment (XAMPP/Laragon)
- [ ] Run database setup: `http://localhost/uaspbw/setup/`
- [ ] Study existing files: `dashboard/add-order.php`, `dashboard/orders.php`
- [ ] Study database schema: `orders`, `order_items`, `customers` tables
- [ ] Test current order pages (note: currently static data)

### **Phase 2: Add Order Integration (Week 2)**
- [ ] **Update `dashboard/add-order.php`:**
  - [ ] Connect to database using `config/database.php`
  - [ ] Load customers from database for dropdown
  - [ ] Create dynamic product selection (if products table exists)
  - [ ] Process form submission to save order to database
  - [ ] Calculate totals (subtotal, tax, grand total)
  - [ ] Validate form inputs (required fields, numeric values)
  - [ ] Show success/error messages
  - [ ] Generate order number automatically

### **Phase 3: Orders List & Management (Week 2-3)**
- [ ] **Update `dashboard/orders.php`:**
  - [ ] Load orders from database with pagination
  - [ ] Display order data in table (order_number, customer, date, status, total)
  - [ ] Add search functionality (by order number, customer name)
  - [ ] Add filtering by status (Pending, Processing, Completed, Cancelled)
  - [ ] Add sorting options (date, total, status)
  - [ ] Add action buttons (View, Edit, Delete, Change Status)

### **Phase 4: Order Detail & Edit (Week 3)**
- [ ] **Create `dashboard/order-detail.php`:**
  - [ ] Display complete order information
  - [ ] Show customer details
  - [ ] List all order items with quantities and prices
  - [ ] Display order totals and calculations
  - [ ] Show order history/status changes
  - [ ] Add print/export functionality

- [ ] **Create `dashboard/order-edit.php`:**
  - [ ] Load existing order data for editing
  - [ ] Allow modification of order items
  - [ ] Update customer information
  - [ ] Recalculate totals when items change
  - [ ] Save changes to database
  - [ ] Validate permissions (only pending orders can be edited)

### **Phase 5: Order Status Management (Week 4)**
- [ ] **Order status workflow:**
  - [ ] Status change functionality (Pending → Processing → Completed)
  - [ ] Cancel order functionality
  - [ ] Status history tracking
  - [ ] Email notifications on status change (optional)
  - [ ] Bulk status updates for multiple orders

### **Phase 6: Helper Functions & Validation (Week 4-5)**
- [ ] **Create `includes/order-functions.php`:**
  - [ ] `create_order($order_data, $items)` function
  - [ ] `get_order_by_id($order_id)` function
  - [ ] `update_order($order_id, $data)` function
  - [ ] `delete_order($order_id)` function
  - [ ] `get_orders_list($filters, $pagination)` function
  - [ ] `change_order_status($order_id, $status)` function
  - [ ] `calculate_order_total($items)` function
  - [ ] `generate_order_number()` function

---

## 📁 FILES TO WORK WITH

### **Existing Files (Modify):**
- `dashboard/add-order.php` - Add database integration
- `dashboard/orders.php` - Add database integration with CRUD

### **New Files (Create):**
- `dashboard/order-detail.php` - Order detail view
- `dashboard/order-edit.php` - Order edit form
- `includes/order-functions.php` - Helper functions
- `api/orders.php` - (Optional) AJAX endpoints

---

## 🗄️ DATABASE TABLES

### **Primary Tables:**
```sql
-- Study these tables structure in database/schema.sql

orders (
    id, order_number, customer_id, 
    order_date, status, subtotal, tax_amount, 
    total_amount, notes, created_at, updated_at
)

order_items (
    id, order_id, product_name, 
    quantity, unit_price, total_price
)

customers (
    id, name, email, phone, address, 
    created_at, updated_at
)
```

---

## 💻 CODE EXAMPLES

### **Add Order Processing Example:**
```php
<?php
// dashboard/add-order.php - Add this logic
require_once '../config/database.php';
require_once '../includes/order-functions.php';

if ($_POST) {
    $order_data = [
        'customer_id' => $_POST['customer_id'],
        'order_date' => $_POST['order_date'],
        'status' => 'Pending',
        'notes' => $_POST['notes']
    ];
    
    $items = [];
    for ($i = 0; $i < count($_POST['product_name']); $i++) {
        $items[] = [
            'product_name' => $_POST['product_name'][$i],
            'quantity' => $_POST['quantity'][$i],
            'unit_price' => $_POST['unit_price'][$i]
        ];
    }
    
    if (create_order($order_data, $items)) {
        $success = "Order created successfully!";
    } else {
        $error = "Failed to create order.";
    }
}

// Load customers for dropdown
$customers_sql = "SELECT id, name FROM customers ORDER BY name";
$customers = $pdo->query($customers_sql)->fetchAll();
?>
```

### **Order Functions Example:**
```php
<?php
// includes/order-functions.php - Create this file
function create_order($order_data, $items) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Calculate totals
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }
        $tax_amount = $subtotal * 0.1; // 10% tax
        $total_amount = $subtotal + $tax_amount;
        
        // Generate order number
        $order_number = generate_order_number();
        
        // Insert order
        $order_sql = "INSERT INTO orders (order_number, customer_id, order_date, status, subtotal, tax_amount, total_amount, notes) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $order_stmt = $pdo->prepare($order_sql);
        $order_stmt->execute([
            $order_number,
            $order_data['customer_id'],
            $order_data['order_date'],
            $order_data['status'],
            $subtotal,
            $tax_amount,
            $total_amount,
            $order_data['notes']
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Insert order items
        $item_sql = "INSERT INTO order_items (order_id, product_name, quantity, unit_price, total_price) 
                     VALUES (?, ?, ?, ?, ?)";
        $item_stmt = $pdo->prepare($item_sql);
        
        foreach ($items as $item) {
            $total_price = $item['quantity'] * $item['unit_price'];
            $item_stmt->execute([
                $order_id,
                $item['product_name'],
                $item['quantity'],
                $item['unit_price'],
                $total_price
            ]);
        }
        
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $pdo->rollback();
        return false;
    }
}
?>
```

---

## 📊 ORDER STATUS WORKFLOW

```
Pending → Processing → Completed
   ↓
Cancelled (can be cancelled from any status)
```

### **Status Rules:**
- **Pending:** New orders, can be edited/cancelled
- **Processing:** Orders being prepared, can only be completed/cancelled
- **Completed:** Finished orders, cannot be modified
- **Cancelled:** Cancelled orders, cannot be modified

---

## 🧪 TESTING CHECKLIST

- [ ] Create new order with multiple items
- [ ] Order total calculations are correct
- [ ] Orders list displays with correct data
- [ ] Search orders by order number/customer works
- [ ] Filter orders by status works
- [ ] Edit order functionality works
- [ ] Order detail page shows complete information
- [ ] Change order status works
- [ ] Delete order works (with confirmation)
- [ ] Pagination works for large order lists

---

## 🚨 COMMON ISSUES & SOLUTIONS

### **Issue:** Order total calculation incorrect
**Solution:** Check JavaScript calculations match PHP calculations

### **Issue:** Order items not saving
**Solution:** Ensure proper array handling in form processing

### **Issue:** Foreign key constraint errors
**Solution:** Verify customer_id exists before creating order

### **Issue:** Order status not updating
**Solution:** Check if order status enum values match database

---

## 📱 UI/UX CONSIDERATIONS

### **Add Order Form:**
- Dynamic add/remove item rows
- Auto-calculate totals with JavaScript
- Customer search/autocomplete
- Form validation before submit

### **Orders List:**
- Status badges with colors
- Quick actions (status change)
- Export functionality
- Responsive table design

### **Order Detail:**
- Print-friendly layout
- Order timeline/history
- Customer contact info
- Action buttons based on status

---

## 🔗 INTEGRATION POINTS

**With other team members:**
- **Anggota 1:** User authentication for order access control
- **Anggota 3:** Customer data integration
- **Anggota 4:** Order data for reports and analytics
- **Anggota 5:** Database functions and API endpoints
- **Anggota 6:** UI improvements and JavaScript functionality

---

## 📚 RESOURCES

- [PHP PDO Transactions](https://www.php.net/manual/en/pdo.transactions.php)
- [MySQL Foreign Keys](https://dev.mysql.com/doc/refman/8.0/en/create-table-foreign-keys.html)
- [Form Validation Best Practices](https://developer.mozilla.org/en-US/docs/Learn/Forms/Form_validation)

---

## 📞 HELP & SUPPORT

**Jika mengalami kesulitan:**
1. Cek database schema di `database/README.md`
2. Test dengan data sample yang sudah ada
3. Konsultasi dengan Anggota 5 (Database Integration)
4. Koordinasi dengan Anggota 3 untuk customer data

---

**Success Criteria:**
✅ Create order with multiple items berhasil  
✅ Orders list menampilkan data dari database  
✅ Edit dan delete order berfungsi  
✅ Order status management bekerja  
✅ Calculations dan validations akurat  
✅ UI responsive dan user-friendly  

**Good luck! 🚀**
