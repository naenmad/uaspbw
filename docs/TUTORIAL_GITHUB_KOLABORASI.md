# 🚀 TUTORIAL PENGEMBANGAN KOLABORATIF DENGAN GITHUB

**Proyek:** Sistem Pencatatan Order berbasis PHP  
**Tim:** 6 Anggota  
**Platform:** GitHub untuk Version Control & Kolaborasi  

---

## 📋 DAFTAR ISI

1. [Setup GitHub Repository](#setup-github-repository)
2. [Struktur Branch & Workflow](#struktur-branch--workflow)
3. [Setup Development Environment](#setup-development-environment)
4. [Workflow Harian](#workflow-harian)
5. [Code Review Process](#code-review-process)
6. [Penanganan Konflik](#penanganan-konflik)
7. [Best Practices](#best-practices)
8. [Troubleshooting](#troubleshooting)

---

## 🏗️ SETUP GITHUB REPOSITORY

### **Langkah 1: Membuat Repository (Team Lead)**

```bash
# 1. Buat repository baru di GitHub
# - Nama: sistem-pencatatan-order
# - Visibility: Private (untuk proyek kelompok)
# - Initialize with README: Yes
# - Add .gitignore: PHP
# - Add license: MIT License
```

### **Langkah 2: Clone & Setup Initial Project**

```bash
# Clone repository
git clone https://github.com/[username]/sistem-pencatatan-order.git
cd sistem-pencatatan-order

# Copy existing project files
cp -r /path/to/uaspbw/* .

# Add initial commit
git add .
git commit -m "feat: setup initial project structure

- Add landing page and authentication
- Add dashboard layout with 6 pages
- Add database schema and setup system
- Add project documentation"

git push origin main
```

### **Langkah 3: Invite Team Members**

1. Buka repository di GitHub
2. Settings → Manage access → Invite a collaborator
3. Undang 5 anggota tim lainnya
4. Set role: **Write** (untuk bisa push ke repository)

---

## 🌳 STRUKTUR BRANCH & WORKFLOW

### **Branch Strategy:**

```
main (production-ready)
├── develop (integration branch)
├── feature/auth-system (Anggota 1)
├── feature/order-management (Anggota 2)
├── feature/customer-management (Anggota 3)
├── feature/reports-analytics (Anggota 4)
├── feature/database-api (Anggota 5)
└── feature/ui-security (Anggota 6)
```

### **Setup Branches:**

```bash
# Team Lead membuat develop branch
git checkout -b develop
git push origin develop

# Set develop sebagai default branch di GitHub
# Settings → Branches → Default branch → develop
```

---

## 💻 SETUP DEVELOPMENT ENVIRONMENT

### **Langkah 1: Clone Repository (Setiap Anggota)**

```bash
# Clone repository
git clone https://github.com/[username]/sistem-pencatatan-order.git
cd sistem-pencatatan-order

# Switch ke develop branch
git checkout develop
git pull origin develop
```

### **Langkah 2: Setup Local Environment**

```bash
# Copy project ke htdocs/www (XAMPP/Laragon)
cp -r . /path/to/htdocs/sistem-pencatatan-order/

# Atau buat symbolic link
ln -s $(pwd) /path/to/htdocs/sistem-pencatatan-order

# Setup database
# 1. Buka http://localhost/sistem-pencatatan-order/setup/
# 2. Ikuti wizard setup database
```

### **Langkah 3: Buat Feature Branch (Setiap Anggota)**

```bash
# Anggota 1 (Authentication)
git checkout -b feature/auth-system
git push -u origin feature/auth-system

# Anggota 2 (Order Management)
git checkout -b feature/order-management
git push -u origin feature/order-management

# Anggota 3 (Customer Management)
git checkout -b feature/customer-management
git push -u origin feature/customer-management

# Anggota 4 (Reports & Analytics)
git checkout -b feature/reports-analytics
git push -u origin feature/reports-analytics

# Anggota 5 (Database & API)
git checkout -b feature/database-api
git push -u origin feature/database-api

# Anggota 6 (UI/UX & Security)
git checkout -b feature/ui-security
git push -u origin feature/ui-security
```

---

## 🔄 WORKFLOW HARIAN

### **Daily Development Cycle:**

#### **1. Mulai Hari (Setiap Pagi)**

```bash
# Update branch dengan perubahan terbaru
git checkout develop
git pull origin develop

# Switch ke feature branch
git checkout feature/[nama-fitur]

# Merge perubahan dari develop
git merge develop

# Jika ada conflict, resolve dulu
# Push update ke remote
git push origin feature/[nama-fitur]
```

#### **2. Development Work**

```bash
# Buat perubahan code sesuai tugas
# Test perubahan di local

# Stage dan commit perubahan
git add .
git commit -m "feat: implement login database integration

- Connect login form to database
- Add session management
- Add input validation
- Fix authentication redirect"

# Push ke remote branch
git push origin feature/[nama-fitur]
```

#### **3. Akhir Hari (Setiap Sore)**

```bash
# Pastikan semua perubahan sudah di-commit
git status

# Push final changes
git push origin feature/[nama-fitur]

# Update progress di GitHub Issues atau Project Board
```

---

## 📝 COMMIT MESSAGE CONVENTIONS

### **Format Commit Message:**

```
<type>(<scope>): <description>

<body> (optional)

<footer> (optional)
```

### **Types:**
- `feat`: Fitur baru
- `fix`: Bug fix
- `docs`: Perubahan dokumentasi
- `style`: Formatting, missing semi colons, etc
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance

### **Contoh Commit Messages:**

```bash
# Fitur baru
git commit -m "feat(auth): implement database login integration"

# Bug fix
git commit -m "fix(orders): resolve order total calculation error"

# Documentation
git commit -m "docs: update API documentation for customer endpoints"

# UI improvements
git commit -m "style(dashboard): improve responsive design for mobile"

# Refactoring
git commit -m "refactor(database): optimize query performance"
```

---

## 🔍 CODE REVIEW PROCESS

### **Langkah 1: Membuat Pull Request**

```bash
# Pastikan feature branch up to date
git checkout feature/[nama-fitur]
git pull origin develop
git merge develop

# Push final version
git push origin feature/[nama-fitur]
```

**Di GitHub:**
1. Buka repository → Pull requests → New pull request
2. Base: `develop` ← Compare: `feature/[nama-fitur]`
3. Title: Deskripsi singkat fitur
4. Description: Jelaskan perubahan detail
5. Assign reviewers: Minimal 2 anggota tim
6. Add labels: `ready-for-review`

### **Template Pull Request:**

```markdown
## 📋 Deskripsi Perubahan

Implementasi sistem authentication dengan integrasi database.

## ✅ Checklist

- [x] Login form terhubung dengan database
- [x] Session management implemented
- [x] Input validation added
- [x] Error handling implemented
- [ ] Unit tests added (opsional)

## 🧪 Testing

1. Test login dengan user valid
2. Test login dengan user invalid
3. Test session persistence
4. Test logout functionality

## 📸 Screenshots

![Login Page](screenshots/login.png)
![Dashboard After Login](screenshots/dashboard.png)

## 🔗 Related Issues

Closes #1, #2
```

### **Langkah 2: Review Process**

**Reviewer (2 anggota tim):**
1. Check out branch untuk testing local
2. Review code changes
3. Test functionality
4. Approve atau Request changes
5. Add constructive comments

**Code Review Checklist:**
- [ ] Code mengikuti coding standards
- [ ] Functionality bekerja dengan benar
- [ ] Tidak ada security vulnerabilities
- [ ] Database queries optimal
- [ ] UI responsive dan user-friendly
- [ ] Error handling adequate
- [ ] Comments dan documentation cukup

### **Langkah 3: Merge ke Develop**

```bash
# Setelah approved, merge ke develop
# Gunakan "Squash and merge" untuk clean history
```

---

## 🚨 PENANGANAN KONFLIK

### **Merge Conflicts:**

```bash
# Jika ada conflict saat merge develop
git checkout feature/[nama-fitur]
git pull origin develop
git merge develop

# Jika ada conflict, edit file manually
# Cari markers: <<<<<<< HEAD, =======, >>>>>>>

# Setelah resolve conflict
git add .
git commit -m "resolve: merge conflicts from develop"
git push origin feature/[nama-fitur]
```

### **File Conflict Resolution:**

```php
<?php
// Conflict example
<<<<<<< HEAD
// Your changes
$user = authenticate_user($username, $password);
=======
// Incoming changes from develop
$user = login_user($username, $password);
>>>>>>> develop

// Resolution - choose the best approach
$user = authenticate_user($username, $password);
```

---

## 📊 PROJECT MANAGEMENT

### **GitHub Issues untuk Task Tracking:**

```markdown
# Issue Template
**Title:** [AUTH] Implement database login integration

**Labels:** enhancement, auth, high-priority

**Assignee:** Anggota 1

**Description:**
Implementasi integrasi login form dengan database users.

**Acceptance Criteria:**
- [ ] Form login terhubung dengan database
- [ ] Password hashing implemented
- [ ] Session management working
- [ ] Error messages displayed properly

**Estimated Time:** 2 days
```

### **GitHub Projects untuk Sprint Planning:**

**Setup Project Board:**
1. Repository → Projects → New project
2. Template: Basic kanban
3. Columns: Backlog, In Progress, Review, Done

**Card Movement:**
- **Backlog:** Task belum dimulai
- **In Progress:** Sedang dikerjakan
- **Review:** Pull request created
- **Done:** Merged ke develop

---

## 🔧 AUTOMATION & CI/CD

### **GitHub Actions untuk Basic CI:**

```yaml
# .github/workflows/ci.yml
name: CI

on:
  push:
    branches: [ develop, main ]
  pull_request:
    branches: [ develop ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.0'
        
    - name: Validate composer.json
      run: composer validate --strict
      
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
      
    - name: Run PHP syntax check
      run: find . -name "*.php" -exec php -l {} \;
      
    - name: Run basic tests
      run: php tests/basic_tests.php
```

---

## 📅 WEEKLY INTEGRATION

### **Weekly Merge to Main:**

```bash
# Team Lead setiap Jumat
git checkout main
git pull origin main

git checkout develop
git pull origin develop

# Test semua functionality
# Jika semua OK, merge ke main
git checkout main
git merge develop
git push origin main

# Tag release
git tag -a v1.0.0 -m "Weekly release v1.0.0"
git push origin v1.0.0
```

### **Weekly Team Sync:**

**Agenda Meeting:**
1. Demo progress masing-masing anggota
2. Review pull requests yang pending
3. Diskusi blocker dan dependencies
4. Planning untuk minggu depan
5. Update project timeline

---

## 🛠️ DEVELOPMENT TOOLS

### **Recommended VS Code Extensions:**

```json
{
  "recommendations": [
    "ms-vscode.vscode-git",
    "eamodio.gitlens",
    "bmewburn.vscode-intelephense-client",
    "bradlc.vscode-tailwindcss",
    "ms-vscode.live-server"
  ]
}
```

### **Git Configuration:**

```bash
# Global config
git config --global user.name "Nama Anda"
git config --global user.email "email@example.com"

# Project specific config
git config user.name "Nama Tim"
git config user.email "team@project.com"

# Useful aliases
git config --global alias.st status
git config --global alias.co checkout
git config --global alias.br branch
git config --global alias.cm commit
git config --global alias.pl pull
git config --global alias.ps push
```

---

## 📋 BEST PRACTICES

### **Code Quality:**

1. **Consistent Coding Style:**
   - Gunakan PSR-12 untuk PHP
   - Indentasi 4 spaces
   - Line endings: LF (Unix style)

2. **File Structure:**
   ```
   includes/
   ├── functions.php      # Shared functions
   ├── auth.php          # Authentication functions
   ├── database.php      # Database functions
   └── validation.php    # Validation functions
   ```

3. **Database Naming:**
   - Tables: snake_case (users, order_items)
   - Columns: snake_case (created_at, user_id)
   - Functions: camelCase (getUserById)

### **Git Best Practices:**

1. **Commit Often:** Small, focused commits
2. **Descriptive Messages:** Clear commit descriptions
3. **Branch Naming:** feature/description, fix/bug-name
4. **Keep Branches Updated:** Regular merge from develop
5. **Clean History:** Use squash merge untuk feature branches

### **Collaboration Best Practices:**

1. **Communication:**
   - Daily updates di group chat
   - Tag team members di PR comments
   - Ask questions early

2. **Code Review:**
   - Review dalam 24 jam
   - Be constructive, not critical
   - Test functionality, not just read code

3. **Documentation:**
   - Update README untuk perubahan setup
   - Comment complex code
   - Document API endpoints

---

## 🚨 TROUBLESHOOTING

### **Common Git Issues:**

#### **1. Permission Denied (publickey)**
```bash
# Generate SSH key
ssh-keygen -t rsa -b 4096 -C "your_email@example.com"

# Add to ssh-agent
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_rsa

# Add public key to GitHub
cat ~/.ssh/id_rsa.pub
# Copy output dan paste ke GitHub Settings → SSH Keys
```

#### **2. Merge Conflicts**
```bash
# Check conflict files
git status

# Edit conflicts manually
# Remove conflict markers: <<<<<<<, =======, >>>>>>>

# Stage resolved files
git add .
git commit -m "resolve: merge conflicts"
```

#### **3. Accidental Commit to Wrong Branch**
```bash
# Move commits to correct branch
git log --oneline  # Copy commit hash
git checkout correct-branch
git cherry-pick <commit-hash>

# Remove from wrong branch
git checkout wrong-branch
git reset --hard HEAD~1
```

#### **4. Large Files Issues**
```bash
# Remove large files from git history
git filter-branch --force --index-filter \
  'git rm --cached --ignore-unmatch path/to/large/file' \
  --prune-empty --tag-name-filter cat -- --all
```

### **Database Sync Issues:**

```bash
# Export database structure
mysqldump -u username -p --no-data database_name > schema.sql

# Import to team member's database
mysql -u username -p database_name < schema.sql
```

---

## 📈 MONITORING & ANALYTICS

### **GitHub Insights:**

- **Pulse:** Weekly activity summary
- **Contributors:** Individual contributions
- **Traffic:** Repository visits and clones
- **Network:** Branch visualization

### **Project Metrics:**

- Commits per week per member
- Pull request merge time
- Code review participation
- Issue resolution time

---

## 🎯 SUCCESS METRICS

### **Week 1:**
- [ ] Semua anggota setup GitHub successfully
- [ ] Feature branches created
- [ ] First commits pushed

### **Week 2:**
- [ ] Minimal 2 pull requests per anggota
- [ ] Database integration started
- [ ] Code review process established

### **Week 3:**
- [ ] Core features implemented
- [ ] Cross-team integration working
- [ ] CI/CD pipeline running

### **Week 4:**
- [ ] All features merged to develop
- [ ] UI/UX improvements completed
- [ ] Security measures implemented

### **Week 5:**
- [ ] Testing completed
- [ ] Documentation updated
- [ ] Release candidate ready

### **Week 6:**
- [ ] Final release to main
- [ ] Deployment documentation
- [ ] Project presentation ready

---

## 📞 SUPPORT & RESOURCES

### **GitHub Learning Resources:**
- [GitHub Docs](https://docs.github.com/)
- [Git Handbook](https://guides.github.com/introduction/git-handbook/)
- [GitHub Flow](https://guides.github.com/introduction/flow/)

### **Team Communication:**
- **WhatsApp Group:** Daily updates
- **GitHub Discussions:** Technical discussions
- **Email:** Formal communications

### **Emergency Contacts:**
- **Git Issues:** Anggota yang paling experienced dengan Git
- **Merge Conflicts:** Team Lead
- **Repository Issues:** Repository Owner

---

## 🎉 CONCLUSION

Dengan mengikuti tutorial ini, tim akan dapat:

✅ **Berkolaborasi efektif** menggunakan GitHub  
✅ **Mengelola code changes** dengan systematic  
✅ **Menghindari conflicts** dan data loss  
✅ **Maintain code quality** melalui reviews  
✅ **Track progress** dengan clear visibility  
✅ **Deploy dengan confidence** menggunakan tested code  

**Remember:** GitHub adalah tool, komunikasi yang baik adalah kunci sukses kolaborasi tim!

**Selamat coding dan good luck dengan proyek kalian! 🚀**

---

**Last Updated:** June 2025  
**Version:** 1.0  
**Contributors:** All team members
