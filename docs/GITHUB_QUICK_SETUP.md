# ⚡ QUICK SETUP GUIDE - GITHUB KOLABORASI

**Target:** Setup GitHub collaboration dalam 30 menit!

---

## 🚀 SETUP SUPER CEPAT (Team Lead)

### **Step 1: Buat Repository GitHub (5 menit)**

```bash
# 1. Buka github.com → New repository
# 2. Repository name: sistem-pencatatan-order
# 3. Description: Sistem Pencatatan Order - Proyek Kelompok UAS PBW
# 4. Private repository
# 5. Initialize with README
# 6. Add .gitignore: PHP
# 7. Create repository

# 8. Clone ke local
git clone https://github.com/[username]/sistem-pencatatan-order.git
cd sistem-pencatatan-order

# 9. Copy existing project
cp -r /path/to/uaspbw/* .
rm -rf .git  # Remove old git if exists
git init
git add .
git commit -m "Initial project setup"
git push -u origin main

# 10. Buat develop branch
git checkout -b develop
git push -u origin develop
```

### **Step 2: Invite Team Members (2 menit)**

1. Repository → Settings → Manage access
2. Invite collaborator → Masukkan username/email 5 anggota
3. Role: Write access
4. Send invitation

### **Step 3: Setup Project Structure (3 menit)**

```bash
# Buat branch untuk setiap anggota
git checkout develop

# Branch untuk setiap fitur
git checkout -b feature/auth-system
git push -u origin feature/auth-system

git checkout develop
git checkout -b feature/order-management  
git push -u origin feature/order-management

git checkout develop
git checkout -b feature/customer-management
git push -u origin feature/customer-management

git checkout develop
git checkout -b feature/reports-analytics
git push -u origin feature/reports-analytics

git checkout develop
git checkout -b feature/database-api
git push -u origin feature/database-api

git checkout develop
git checkout -b feature/ui-security
git push -u origin feature/ui-security
```

---

## 👥 SETUP UNTUK ANGGOTA TIM (10 menit)

### **Step 1: Accept Invitation & Clone**

```bash
# 1. Check email dan accept GitHub invitation
# 2. Clone repository
git clone https://github.com/[team-lead-username]/sistem-pencatatan-order.git
cd sistem-pencatatan-order

# 3. Verify access
git branch -a  # Should see all branches
```

### **Step 2: Setup Local Development**

```bash
# Copy ke htdocs/www folder
cp -r . /path/to/htdocs/sistem-pencatatan-order/

# Test access
# Buka: http://localhost/sistem-pencatatan-order/
# Buka: http://localhost/sistem-pencatatan-order/setup/
```

### **Step 3: Setup Your Feature Branch**

```bash
# Checkout ke branch sesuai tugas Anda
# Anggota 1:
git checkout feature/auth-system

# Anggota 2:
git checkout feature/order-management

# Anggota 3:
git checkout feature/customer-management

# Anggota 4:
git checkout feature/reports-analytics

# Anggota 5:
git checkout feature/database-api

# Anggota 6:
git checkout feature/ui-security

# Update branch
git pull origin develop
git merge develop
```

---

## 🔄 DAILY WORKFLOW (5 menit/hari)

### **Pagi Hari:**
```bash
# Update develop
git checkout develop
git pull origin develop

# Switch ke feature branch
git checkout feature/[your-feature]

# Merge latest changes
git merge develop

# Start coding!
```

### **Sore Hari:**
```bash
# Save work
git add .
git commit -m "feat: implement [what you did today]"
git push origin feature/[your-feature]

# Update di WhatsApp grup:
# "Progress hari ini: [describe your work]"
```

---

## 📝 COMMIT MESSAGE TEMPLATES

Copy-paste templates ini untuk commit messages:

```bash
# Fitur baru
git commit -m "feat(auth): implement login database integration"
git commit -m "feat(orders): add order creation form"
git commit -m "feat(customers): implement customer CRUD"
git commit -m "feat(reports): add dashboard statistics"
git commit -m "feat(api): create order API endpoints"
git commit -m "feat(ui): improve responsive design"

# Bug fixes
git commit -m "fix(auth): resolve session timeout issue"
git commit -m "fix(orders): fix order total calculation"
git commit -m "fix(customers): resolve customer search bug"

# Improvements
git commit -m "style(dashboard): enhance card design"
git commit -m "refactor(database): optimize query performance"
git commit -m "docs: update API documentation"
```

---

## 🔀 PULL REQUEST TEMPLATE

**Judul PR:** `[FITUR] Deskripsi singkat`

**Deskripsi:**
```markdown
## 📋 Perubahan yang dibuat
- Implementasi login dengan database
- Session management
- Error handling

## ✅ Testing
- [x] Login berhasil dengan user valid
- [x] Login gagal dengan user invalid  
- [x] Session tersimpan setelah login
- [x] Logout berfungsi dengan benar

## 📸 Screenshot
![Login Page](link-to-screenshot)

## 🔗 Related Issues
Closes #1
```

---

## 🚨 QUICK TROUBLESHOOTING

### **Problem: Permission denied**
```bash
# Solution: Setup SSH key
ssh-keygen -t rsa -b 4096 -C "your_email@gmail.com"
# Copy public key ke GitHub Settings → SSH Keys
cat ~/.ssh/id_rsa.pub
```

### **Problem: Merge conflict**
```bash
# Solution: Resolve manually
git status  # Check conflicted files
# Edit files, remove conflict markers
git add .
git commit -m "resolve: merge conflict"
```

### **Problem: Wrong branch commit**
```bash
# Solution: Move commit to correct branch
git log --oneline  # Copy commit hash
git checkout correct-branch
git cherry-pick [commit-hash]
```

---

## 📱 MOBILE WORKFLOW

### **GitHub Mobile App:**
1. Download GitHub app
2. Login dengan account
3. Follow repository
4. Enable notifications
5. Review PR on mobile

### **Quick Commands di Mobile:**
- Review code changes
- Approve/request changes  
- Merge pull requests
- Check build status
- Comment on issues

---

## 🎯 WEEKLY TARGETS

### **Week 1:** Setup & First Commits
- [ ] Semua anggota join repository
- [ ] Feature branches created
- [ ] First commits pushed
- [ ] Database setup working

### **Week 2:** Core Development
- [ ] Authentication working (Anggota 1)
- [ ] Order form working (Anggota 2)  
- [ ] Customer list working (Anggota 3)
- [ ] Dashboard stats working (Anggota 4)
- [ ] Database functions ready (Anggota 5)
- [ ] UI improvements started (Anggota 6)

### **Week 3:** Integration
- [ ] All features integrated
- [ ] Cross-module testing
- [ ] Bug fixes completed
- [ ] UI/UX polished

### **Week 4:** Finalization
- [ ] All features complete
- [ ] Security implemented
- [ ] Performance optimized
- [ ] Documentation updated

---

## 📞 EMERGENCY CONTACTS

| Issue | Contact Person |
|-------|----------------|
| Repository access | Team Lead |
| Git conflicts | Most experienced team member |
| Database issues | Anggota 5 |
| UI/CSS issues | Anggota 6 |
| General questions | WhatsApp grup |

---

## 🎉 SUCCESS CHECKLIST

**Repository Setup:**
- [ ] Repository created
- [ ] All members invited
- [ ] Branches created
- [ ] Local setup working

**Development Flow:**
- [ ] Daily commits working
- [ ] Pull requests created
- [ ] Code reviews happening
- [ ] Merges successful

**Team Collaboration:**
- [ ] Communication active
- [ ] Progress tracking
- [ ] Help when needed
- [ ] Conflicts resolved

**Final Delivery:**
- [ ] All features working
- [ ] Code quality good
- [ ] Documentation complete
- [ ] Ready for presentation

---

**🚀 Mulai sekarang dan good luck dengan kolaborasi GitHub tim kalian!**
