# 📊 TUGAS ANGGOTA 4: REPORTS & ANALYTICS

**Nama:** [Isi nama anggota]  
**Tanggung Jawab:** Reporting System, Analytics Dashboard, Data Visualization  
**Deadline:** [Isi deadline]  

---

## 🎯 OBJECTIVES

Mengintegrasikan halaman reports yang sudah ada (tampilan statis) dengan database dan membuat sistem reporting lengkap dengan analytics dan visualisasi data.

---

## 📋 TASK CHECKLIST

### **Phase 1: Understanding & Setup (Week 1)**
- [ ] Setup development environment (XAMPP/Laragon)
- [ ] Run database setup: `http://localhost/uaspbw/setup/`
- [ ] Study existing files: `dashboard/reports.php`, `dashboard/index.php`
- [ ] Study database schema: all tables for reporting data
- [ ] Research chart libraries (Chart.js, Google Charts, etc.)

### **Phase 2: Dashboard Statistics (Week 2)**
- [ ] **Update `dashboard/index.php`:**
  - [ ] Connect to database for real-time stats
  - [ ] Total orders count and value
  - [ ] Total customers count
  - [ ] Monthly revenue calculation
  - [ ] Recent orders list (last 5-10 orders)
  - [ ] Top customers this month
  - [ ] Order status distribution (Pending, Processing, Completed)
  - [ ] Revenue trend chart (last 12 months)

### **Phase 3: Sales Reports (Week 2-3)**
- [ ] **Update `dashboard/reports.php`:**
  - [ ] Daily sales report
  - [ ] Weekly sales report
  - [ ] Monthly sales report
  - [ ] Yearly sales report
  - [ ] Custom date range reports
  - [ ] Sales by customer report
  - [ ] Product/service performance report

### **Phase 4: Advanced Analytics (Week 3-4)**
- [ ] **Revenue Analytics:**
  - [ ] Revenue trends and growth analysis
  - [ ] Month-over-month comparison
  - [ ] Year-over-year comparison
  - [ ] Revenue forecasting (basic trend analysis)
  - [ ] Average order value (AOV) tracking

- [ ] **Customer Analytics:**
  - [ ] Customer acquisition trends
  - [ ] Customer retention analysis
  - [ ] Customer lifetime value (CLV)
  - [ ] Top customers by revenue
  - [ ] Customer behavior patterns

### **Phase 5: Data Visualization (Week 4)**
- [ ] **Implement Charts:**
  - [ ] Revenue line chart (monthly/yearly trends)
  - [ ] Order status pie chart
  - [ ] Customer acquisition bar chart
  - [ ] Top products/services bar chart
  - [ ] Geographic distribution (if location data available)

- [ ] **Interactive Dashboards:**
  - [ ] Filter by date ranges
  - [ ] Drill-down functionality
  - [ ] Real-time data updates
  - [ ] Responsive chart design

### **Phase 6: Export & Print (Week 5)**
- [ ] **Export Functionality:**
  - [ ] Export reports to PDF
  - [ ] Export data to Excel/CSV
  - [ ] Print-friendly report layouts
  - [ ] Email report functionality (optional)

### **Phase 7: Helper Functions (Week 5)**
- [ ] **Create `includes/report-functions.php`:**
  - [ ] `get_sales_summary($date_from, $date_to)` function
  - [ ] `get_revenue_trends($period)` function
  - [ ] `get_customer_analytics()` function
  - [ ] `get_top_customers($limit, $period)` function
  - [ ] `get_order_statistics()` function
  - [ ] `export_report_pdf($report_data)` function
  - [ ] `generate_chart_data($type, $period)` function

---

## 📁 FILES TO WORK WITH

### **Existing Files (Modify):**
- `dashboard/index.php` - Add real dashboard statistics
- `dashboard/reports.php` - Add database integration

### **New Files (Create):**
- `includes/report-functions.php` - Helper functions
- `public/js/charts.js` - Chart implementation
- `reports/` - (Optional) Separate report templates
- `exports/` - (Optional) Generated export files

---

## 🗄️ DATABASE QUERIES FOR REPORTING

### **Key Metrics Queries:**
```sql
-- Total Revenue
SELECT SUM(total_amount) as total_revenue 
FROM orders 
WHERE status != 'Cancelled';

-- Monthly Revenue
SELECT 
    DATE_FORMAT(order_date, '%Y-%m') as month,
    SUM(total_amount) as revenue,
    COUNT(*) as order_count
FROM orders 
WHERE status != 'Cancelled'
GROUP BY DATE_FORMAT(order_date, '%Y-%m')
ORDER BY month DESC;

-- Top Customers
SELECT 
    c.name,
    COUNT(o.id) as total_orders,
    SUM(o.total_amount) as total_spent
FROM customers c
JOIN orders o ON c.id = o.customer_id
WHERE o.status != 'Cancelled'
GROUP BY c.id
ORDER BY total_spent DESC
LIMIT 10;

-- Order Status Distribution
SELECT 
    status,
    COUNT(*) as count,
    (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM orders)) as percentage
FROM orders
GROUP BY status;
```

---

## 💻 CODE EXAMPLES

### **Dashboard Statistics Example:**
```php
<?php
// dashboard/index.php - Add this logic
require_once '../config/database.php';
require_once '../includes/report-functions.php';

// Get current month stats
$current_month = date('Y-m');
$previous_month = date('Y-m', strtotime('-1 month'));

// Total Orders
$total_orders_sql = "SELECT COUNT(*) FROM orders WHERE status != 'Cancelled'";
$total_orders = $pdo->query($total_orders_sql)->fetchColumn();

// Total Revenue
$total_revenue_sql = "SELECT SUM(total_amount) FROM orders WHERE status != 'Cancelled'";
$total_revenue = $pdo->query($total_revenue_sql)->fetchColumn() ?: 0;

// This Month Revenue
$month_revenue_sql = "SELECT SUM(total_amount) FROM orders 
                      WHERE DATE_FORMAT(order_date, '%Y-%m') = ? AND status != 'Cancelled'";
$month_stmt = $pdo->prepare($month_revenue_sql);
$month_stmt->execute([$current_month]);
$month_revenue = $month_stmt->fetchColumn() ?: 0;

// Previous Month Revenue for comparison
$prev_month_stmt = $pdo->prepare($month_revenue_sql);
$prev_month_stmt->execute([$previous_month]);
$prev_month_revenue = $prev_month_stmt->fetchColumn() ?: 0;

// Calculate growth percentage
$growth_percentage = $prev_month_revenue > 0 
    ? (($month_revenue - $prev_month_revenue) / $prev_month_revenue) * 100 
    : 0;

// Recent Orders
$recent_orders_sql = "SELECT o.order_number, c.name as customer_name, 
                             o.order_date, o.total_amount, o.status
                      FROM orders o
                      JOIN customers c ON o.customer_id = c.id
                      ORDER BY o.created_at DESC
                      LIMIT 10";
$recent_orders = $pdo->query($recent_orders_sql)->fetchAll();
?>
```

### **Report Functions Example:**
```php
<?php
// includes/report-functions.php - Create this file
function get_sales_summary($date_from, $date_to) {
    global $pdo;
    
    $sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_order_value,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_orders
            FROM orders 
            WHERE order_date BETWEEN ? AND ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date_from, $date_to]);
    return $stmt->fetch();
}

function get_revenue_trends($months = 12) {
    global $pdo;
    
    $sql = "SELECT 
                DATE_FORMAT(order_date, '%Y-%m') as month,
                DATE_FORMAT(order_date, '%M %Y') as month_name,
                SUM(total_amount) as revenue,
                COUNT(*) as orders
            FROM orders 
            WHERE order_date >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            AND status != 'Cancelled'
            GROUP BY DATE_FORMAT(order_date, '%Y-%m')
            ORDER BY month ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$months]);
    return $stmt->fetchAll();
}

function generate_chart_data($type, $period = null) {
    switch ($type) {
        case 'revenue_trend':
            $data = get_revenue_trends($period ?: 12);
            return [
                'labels' => array_column($data, 'month_name'),
                'datasets' => [[
                    'label' => 'Revenue',
                    'data' => array_column($data, 'revenue'),
                    'borderColor' => '#3498db',
                    'backgroundColor' => 'rgba(52, 152, 219, 0.1)'
                ]]
            ];
            
        case 'order_status':
            global $pdo;
            $sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
            $data = $pdo->query($sql)->fetchAll();
            
            return [
                'labels' => array_column($data, 'status'),
                'datasets' => [[
                    'data' => array_column($data, 'count'),
                    'backgroundColor' => ['#e74c3c', '#f39c12', '#2ecc71', '#95a5a6']
                ]]
            ];
            
        default:
            return [];
    }
}
?>
```

---

## 📊 CHART IMPLEMENTATION

### **Setup Chart.js:**
```html
<!-- Add to dashboard pages -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../public/js/charts.js"></script>
```

### **Chart.js Implementation:**
```javascript
// public/js/charts.js - Create this file
function createRevenueChart(canvasId, chartData) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Revenue Trend'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function createPieChart(canvasId, chartData) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    
    new Chart(ctx, {
        type: 'pie',
        data: chartData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Order Status Distribution'
                }
            }
        }
    });
}

// Usage in PHP
echo "<script>
    const revenueData = " . json_encode(generate_chart_data('revenue_trend')) . ";
    createRevenueChart('revenueChart', revenueData);
    
    const statusData = " . json_encode(generate_chart_data('order_status')) . ";
    createPieChart('statusChart', statusData);
</script>";
```

---

## 📈 REPORT TYPES TO IMPLEMENT

### **1. Sales Reports:**
- Daily Sales Summary
- Weekly Sales Report
- Monthly Sales Report
- Quarterly Sales Report
- Yearly Sales Report
- Custom Date Range Report

### **2. Customer Reports:**
- Customer List with Statistics
- Top Customers Report
- Customer Acquisition Report
- Customer Retention Analysis
- Inactive Customers Report

### **3. Analytics Dashboards:**
- Executive Dashboard (KPIs)
- Sales Performance Dashboard
- Customer Analytics Dashboard
- Trend Analysis Dashboard

---

## 📄 PDF EXPORT EXAMPLE

### **Using TCPDF or similar:**
```php
// Include TCPDF library
require_once('../vendor/tcpdf/tcpdf.php');

function export_sales_report_pdf($date_from, $date_to) {
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Sales Report', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, "Period: $date_from to $date_to", 0, 1, 'L');
    $pdf->Ln(5);
    
    // Get data
    $summary = get_sales_summary($date_from, $date_to);
    
    // Add summary table
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(60, 8, 'Metric', 1, 0, 'L');
    $pdf->Cell(60, 8, 'Value', 1, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(60, 8, 'Total Orders', 1, 0, 'L');
    $pdf->Cell(60, 8, number_format($summary['total_orders']), 1, 1, 'R');
    
    $pdf->Cell(60, 8, 'Total Revenue', 1, 0, 'L');
    $pdf->Cell(60, 8, '$' . number_format($summary['total_revenue'], 2), 1, 1, 'R');
    
    // Output PDF
    $pdf->Output('sales_report.pdf', 'D');
}
```

---

## 🧪 TESTING CHECKLIST

- [ ] Dashboard shows correct statistics from database
- [ ] Revenue calculations are accurate
- [ ] Charts display properly with real data
- [ ] Date range filtering works correctly
- [ ] Export to PDF functions properly
- [ ] Export to Excel/CSV works
- [ ] Reports load efficiently (performance test)
- [ ] Charts are responsive on mobile devices
- [ ] All mathematical calculations are correct
- [ ] No division by zero errors in calculations

---

## 🚨 COMMON ISSUES & SOLUTIONS

### **Issue:** Charts not displaying
**Solution:** Check JavaScript console for errors, ensure Chart.js is loaded

### **Issue:** Wrong calculations in reports
**Solution:** Verify SQL queries, check for NULL values and edge cases

### **Issue:** PDF export not working
**Solution:** Check TCPDF installation and file permissions

### **Issue:** Performance issues with large datasets
**Solution:** Implement pagination, add database indexes, use query optimization

---

## 📱 UI/UX CONSIDERATIONS

### **Dashboard:**
- Key metrics cards at the top
- Charts section below metrics
- Recent activity feed
- Quick action buttons

### **Reports Page:**
- Date range picker
- Report type selector
- Filter options
- Export buttons
- Loading indicators for long-running reports

### **Charts:**
- Responsive design
- Color-coded for easy interpretation
- Interactive tooltips
- Legend placement
- Accessibility considerations

---

## 🔗 INTEGRATION POINTS

**With other team members:**
- **Anggota 1:** User role-based report access
- **Anggota 2:** Order data for sales reports
- **Anggota 3:** Customer data for customer analytics
- **Anggota 5:** Database optimization for reporting queries
- **Anggota 6:** UI enhancements and responsive charts

---

## 📚 RESOURCES

- [Chart.js Documentation](https://www.chartjs.org/docs/latest/)
- [TCPDF Documentation](https://tcpdf.org/docs/)
- [MySQL Date Functions](https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html)
- [PHP Excel Export](https://phpspreadsheet.readthedocs.io/)

---

## 📞 HELP & SUPPORT

**Jika mengalami kesulitan:**
1. Cek apakah data sample sudah ada di database
2. Test individual SQL queries di phpMyAdmin
3. Konsultasi dengan Anggota 5 untuk query optimization
4. Koordinasi dengan Anggota 2 & 3 untuk data dependencies

---

**Success Criteria:**
✅ Dashboard menampilkan statistik real dari database  
✅ Charts dan visualisasi berfungsi dengan baik  
✅ Report generation dengan berbagai filter  
✅ Export functionality (PDF, Excel) bekerja  
✅ Performance optimal untuk dataset besar  
✅ Responsive design untuk semua device  

**Good luck! 🚀**
