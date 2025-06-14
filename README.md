# 🚀 QUICK START GUIDE - SISTEM PENCATATAN ORDER

**Selamat datang di proyek Sistem Pencatatan Order!**  
Ini adalah panduan cepat untuk memulai development.

---

## 📋 WHAT'S READY

✅ **Landing Page:** `index.html` & `index.php`  
✅ **Authentication:** `auth/login.php`, `auth/register.php`  
✅ **Dashboard:** `dashboard/index.php` + 5 halaman lainnya  
✅ **Database Schema:** `database/schema.sql` + setup system  
✅ **Navigation:** Sidebar & user dropdown working  

## ❌ WHAT'S MISSING

❌ **Database Integration:** Dashboard masih static data  
❌ **CRUD Operations:** Belum connect ke database  
❌ **User Authentication:** Belum verify dari database  
❌ **Security:** Belum implement protection  
❌ **UI Polish:** Belum responsive & enhanced  

---

## 🏃‍♂️ QUICK START (5 MENIT)

### 1. **Setup Environment**
```bash
# Pastikan XAMPP/Laragon running
# Copy project ke htdocs/www
# Buka browser: http://localhost/uaspbw/
```

### 2. **Setup Database**
```bash
# Buka: http://localhost/uaspbw/setup/
# Ikuti wizard setup database
# Test dengan login dummy
```

### 3. **Test Current System**
```bash
# Landing page: http://localhost/uaspbw/
# Login page: http://localhost/uaspbw/auth/login.php
# Dashboard: http://localhost/uaspbw/dashboard/
```

---

## 📁 PROJECT STRUCTURE

```
uaspbw/
├── 📄 index.html          # Landing page
├── 📄 index.php           # PHP landing page
├── 📁 auth/               # Login & Register
├── 📁 dashboard/          # Dashboard pages (6 files)
├── 📁 config/             # Database config
├── 📁 database/           # Schema & docs
├── 📁 setup/              # Database setup
├── 📁 docs/               # Documentation
│   ├── 📄 PEMBAGIAN_TUGAS.md
│   ├── 📄 PROJECT_COORDINATION.md
│   └── 📁 tugas/          # Individual task guides
└── 📁 public/             # CSS, JS, assets
```

---

## 👥 SIAPA NGERJAIN APA?

| Anggota | Tugas | File Utama |
|---------|-------|------------|
| **Anggota 1** | 🔐 Auth & User Management | `auth/`, `dashboard/settings.php` |
| **Anggota 2** | 📦 Order Management | `dashboard/add-order.php`, `dashboard/orders.php` |
| **Anggota 3** | 👥 Customer Management | `dashboard/customers.php` |
| **Anggota 4** | 📊 Reports & Analytics | `dashboard/reports.php`, `dashboard/index.php` |
| **Anggota 5** | 🗄️ Database & API | `config/`, `includes/`, `api/` |
| **Anggota 6** | 🎨 UI/UX & Security | `public/`, semua file UI |

---

## 🎯 LANGKAH SELANJUTNYA

### **Untuk Anggota 1:**
1. Baca: `docs/tugas/ANGGOTA_1_Authentication.md`
2. Connect login form ke database
3. Implement session management

### **Untuk Anggota 2:**
1. Baca: `docs/tugas/ANGGOTA_2_Order_Management.md`
2. Connect add-order form ke database
3. Implement order CRUD

### **Untuk Anggota 3:**
1. Baca: `docs/tugas/ANGGOTA_3_Customer_Management.md`
2. Connect customers page ke database
3. Implement customer CRUD

### **Untuk Anggota 4:**
1. Baca: `docs/tugas/ANGGOTA_4_Reports_Analytics.md`
2. Update dashboard dengan data real
3. Implement charts & reports

### **Untuk Anggota 5:**
1. Baca: `docs/tugas/ANGGOTA_5_Database_API.md`
2. Create database helper functions
3. Support team dengan database issues

### **Untuk Anggota 6:**
1. Baca: `docs/tugas/ANGGOTA_6_UI_UX_Security.md`
2. Improve CSS & make responsive
3. Add JavaScript functionality

---

## 🔧 DEVELOPMENT TOOLS

### **Recommended:**
- **IDE:** VS Code with PHP extensions
- **Database:** phpMyAdmin or Adminer
- **Browser:** Chrome with DevTools
- **Testing:** Postman for API testing

### **Helpful Extensions:**
- PHP Intelephense
- HTML CSS Support
- JavaScript ES6 snippets
- GitLens (if using Git)

---

## 🚨 TROUBLESHOOTING

### **Database Connection Error:**
```
1. Check database credentials in config/database.php
2. Ensure MySQL service is running
3. Run setup wizard again
```

### **Navigation Not Working:**
```
1. Check file paths in sidebar links
2. Verify file permissions
3. Clear browser cache
```

### **Styling Issues:**
```
1. Check CSS file paths
2. Inspect element in browser
3. Verify Bootstrap CDN links
```

---

## 📞 HELP & SUPPORT

### **Documentation:**
- 📄 `PEMBAGIAN_TUGAS.md` - Overview semua tugas
- 📄 `PROJECT_COORDINATION.md` - Manajemen tim
- 📁 `docs/tugas/` - Panduan detail per anggota

### **Technical Support:**
- **Database Issues:** Contact Anggota 5
- **UI/UX Issues:** Contact Anggota 6
- **General Issues:** Group chat atau team meeting

### **Resources:**
- Database schema: `database/README.md`
- Sample data: Already included in setup
- API documentation: Will be created by Anggota 5

---

## 🎉 SUCCESS METRICS

**Week 1:** ✅ Setup complete, understand codebase  
**Week 2:** ✅ Database integration working  
**Week 3:** ✅ All CRUD operations functional  
**Week 4:** ✅ UI polished, security implemented  
**Week 5:** ✅ Testing complete, bugs fixed  
**Week 6:** ✅ Deployment ready, documentation done  

---

## 🌟 FINAL NOTES

**Remember:**
- This is a **team project** - help each other!
- **Communication** is key - ask questions early
- **Test frequently** - don't let bugs accumulate
- **Document everything** - future you will thank you

**Current Status:** Foundation ready, ready for database integration!

**Let's build something amazing together! 🚀**

---

**Need help? Check your individual task guide in `docs/tugas/` folder!**
