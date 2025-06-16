# 🌳 BRANCH STRATEGY & NAMING CONVENTIONS

**Proyek:** Sistem Pencatatan Order  
**Tim:** 6 Anggota  
**Strategy:** GitFlow Modified untuk Tim Kecil  

---

## 🏗️ STRUKTUR BRANCH

### **Branch Hierarchy:**

```
📁 sistem-pencatatan-order/
├── 🟢 main (production-ready)
├── 🔵 develop (integration branch)
├── 🟡 feature/auth-system (Anggota 1)
├── 🟡 feature/order-management (Anggota 2)
├── 🟡 feature/customer-management (Anggota 3)
├── 🟡 feature/reports-analytics (Anggota 4)
├── 🟡 feature/database-api (Anggota 5)
├── 🟡 feature/ui-security (Anggota 6)
├── 🔴 hotfix/critical-bug (jika diperlukan)
└── 🟣 release/v1.0.0 (untuk release preparation)
```

---

## 📋 BRANCH NAMING CONVENTIONS

### **Feature Branches:**
```bash
feature/[description]
feature/auth-system
feature/order-management
feature/customer-management
feature/reports-analytics
feature/database-api
feature/ui-security
feature/email-notifications
feature/export-functionality
```

### **Bug Fix Branches:**
```bash
bugfix/[description]
bugfix/login-session-timeout
bugfix/order-calculation-error
bugfix/customer-search-issue
bugfix/dashboard-loading-slow
```

### **Hotfix Branches (Critical):**
```bash
hotfix/[description]
hotfix/security-vulnerability
hotfix/database-connection-error
hotfix/payment-processing-bug
```

### **Release Branches:**
```bash
release/[version]
release/v1.0.0
release/v1.1.0
release/v2.0.0
```

---

## 🔄 WORKFLOW PROCESS

### **1. Feature Development:**

```bash
# Start dari develop
git checkout develop
git pull origin develop

# Buat feature branch
git checkout -b feature/auth-system
git push -u origin feature/auth-system

# Development work
# ... coding ...

# Regular commits
git add .
git commit -m "feat(auth): implement login form validation"
git push origin feature/auth-system

# Saat ready untuk merge
# Buat Pull Request: feature/auth-system → develop
```

### **2. Bug Fix Process:**

```bash
# Start dari develop (untuk non-critical bugs)
git checkout develop
git pull origin develop

# Buat bugfix branch
git checkout -b bugfix/login-session-timeout
git push -u origin bugfix/login-session-timeout

# Fix bug
# ... coding ...

# Commit fix
git add .
git commit -m "fix(auth): resolve session timeout issue"
git push origin bugfix/login-session-timeout

# Pull Request: bugfix/login-session-timeout → develop
```

### **3. Hotfix Process (Critical Issues):**

```bash
# Start dari main (untuk critical issues)
git checkout main
git pull origin main

# Buat hotfix branch
git checkout -b hotfix/security-vulnerability
git push -u origin hotfix/security-vulnerability

# Fix critical issue
# ... coding ...

# Commit fix
git add .
git commit -m "fix(security): patch XSS vulnerability"
git push origin hotfix/security-vulnerability

# Pull Request ke main DAN develop
```

### **4. Release Process:**

```bash
# Buat release branch dari develop
git checkout develop
git pull origin develop
git checkout -b release/v1.0.0
git push -u origin release/v1.0.0

# Final testing dan bug fixes
# Update version numbers
# Update CHANGELOG.md

# Merge ke main
git checkout main
git merge release/v1.0.0
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin main --tags

# Merge back ke develop
git checkout develop
git merge release/v1.0.0
git push origin develop

# Delete release branch
git branch -d release/v1.0.0
git push origin --delete release/v1.0.0
```

---

## 👥 BRANCH OWNERSHIP

### **Branch Assignments:**

| Branch | Owner | Backup |
|--------|-------|---------|
| `main` | Team Lead | Senior Developer |
| `develop` | Team Lead | All members |
| `feature/auth-system` | Anggota 1 | - |
| `feature/order-management` | Anggota 2 | - |
| `feature/customer-management` | Anggota 3 | - |
| `feature/reports-analytics` | Anggota 4 | - |
| `feature/database-api` | Anggota 5 | - |
| `feature/ui-security` | Anggota 6 | - |

### **Permissions:**

**Main Branch:**
- ❌ Direct push tidak diperbolehkan
- ✅ Hanya melalui Pull Request
- ✅ Minimal 2 approvals required
- ✅ All checks must pass

**Develop Branch:**
- ❌ Direct push tidak diperbolehkan
- ✅ Hanya melalui Pull Request
- ✅ Minimal 1 approval required
- ✅ All checks must pass

**Feature Branches:**
- ✅ Direct push diperbolehkan (untuk owner)
- ✅ Pull Request untuk merge ke develop
- ✅ Self-merge tidak diperbolehkan

---

## 📝 COMMIT CONVENTIONS

### **Commit Message Format:**

```
<type>(<scope>): <subject>

<body>

<footer>
```

### **Types:**
- `feat`: Fitur baru
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Formatting, semicolons, etc (no code change)
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Adding tests
- `chore`: Build process, auxiliary tools, libraries

### **Scopes (Optional):**
- `auth`: Authentication system
- `orders`: Order management
- `customers`: Customer management
- `reports`: Reports and analytics
- `database`: Database operations
- `ui`: User interface
- `api`: API endpoints

### **Examples:**

```bash
# Feature commits
git commit -m "feat(auth): implement user login with database validation"
git commit -m "feat(orders): add order creation form with validation"
git commit -m "feat(api): create REST endpoints for customer management"

# Bug fix commits
git commit -m "fix(auth): resolve session expiration issue"
git commit -m "fix(orders): correct order total calculation logic"
git commit -m "fix(ui): fix responsive design on mobile devices"

# Documentation commits
git commit -m "docs: update API documentation for order endpoints"
git commit -m "docs(readme): add setup instructions for database"

# Style commits
git commit -m "style(dashboard): improve card layout and spacing"
git commit -m "style: fix indentation and code formatting"

# Refactor commits
git commit -m "refactor(database): optimize query performance"
git commit -m "refactor(auth): simplify authentication logic"
```

---

## 🔀 PULL REQUEST STRATEGY

### **PR Naming Convention:**

```
[TYPE] Brief description of changes

Examples:
[FEATURE] Implement user authentication system
[BUGFIX] Fix order calculation error
[HOTFIX] Patch security vulnerability
[DOCS] Update API documentation
```

### **PR Description Template:**

```markdown
## 📋 Tipe Perubahan
- [ ] 🚀 Fitur baru (feature)
- [ ] 🐛 Bug fix
- [ ] 🚨 Hotfix (critical)
- [ ] 📖 Documentation
- [ ] 🎨 Style/UI improvements
- [ ] ♻️ Refactoring
- [ ] ⚡ Performance improvements

## 📝 Deskripsi Perubahan
Jelaskan perubahan yang dibuat secara detail.

## ✅ Testing Checklist
- [ ] Functionality tested locally
- [ ] No breaking changes
- [ ] All existing tests pass
- [ ] New tests added (if applicable)

## 📸 Screenshots (jika applicable)
![Before](link-to-before-image)
![After](link-to-after-image)

## 🔗 Related Issues
- Closes #123
- Related to #456

## 📋 Review Checklist (untuk Reviewer)
- [ ] Code follows project standards
- [ ] Changes are well tested
- [ ] Documentation updated
- [ ] No security issues
- [ ] Performance not degraded
```

### **Review Requirements:**

**Feature PRs:**
- Minimal 2 reviewers
- 1 technical review (code quality)
- 1 functional review (testing)
- All automated checks pass

**Bugfix PRs:**
- Minimal 1 reviewer
- Focus on fix correctness
- No regression testing

**Hotfix PRs:**
- Emergency review (dalam 2 jam)
- Senior developer must approve
- Immediate testing required

---

## 📊 BRANCH LIFECYCLE

### **Feature Branch Lifecycle:**

```
1. Create from develop
2. Development work (1-2 weeks)
3. Regular pushes and commits
4. Create PR when ready
5. Code review process
6. Merge to develop
7. Delete feature branch
```

### **Merge Strategies:**

**Feature Branches → Develop:**
- ✅ "Squash and merge" (clean history)
- ✅ Delete branch after merge

**Develop → Main:**
- ✅ "Create merge commit" (preserve history)
- ✅ Keep develop branch

**Hotfix → Main:**
- ✅ "Create merge commit"
- ✅ Also merge to develop
- ✅ Delete hotfix branch

---

## 🛡️ BRANCH PROTECTION RULES

### **Main Branch Protection:**

```yaml
Protection Rules:
- Require pull request reviews: true
- Required approving reviews: 2
- Dismiss stale reviews: true
- Require review from code owners: true
- Restrict pushes to specific people: false
- Require status checks: true
- Require branches up to date: true
- Include administrators: true
```

### **Develop Branch Protection:**

```yaml
Protection Rules:
- Require pull request reviews: true
- Required approving reviews: 1
- Dismiss stale reviews: false
- Require status checks: true
- Require branches up to date: true
- Include administrators: false
```

---

## 📈 BRANCH METRICS & MONITORING

### **Daily Metrics:**
- Active branches count
- PRs created/merged
- Code review time
- Branch age (warn if >1 week)

### **Weekly Review:**
- Branch cleanup (delete merged branches)
- Stale branch identification
- Merge conflicts analysis
- Team productivity review

---

## 🚨 EMERGENCY PROCEDURES

### **Critical Bug in Production:**

```bash
# 1. Immediate hotfix
git checkout main
git checkout -b hotfix/critical-fix

# 2. Fix and test quickly
# ... coding ...

# 3. Fast review process
# Create PR with "URGENT" label
# Ping senior developers

# 4. Merge and deploy
git checkout main
git merge hotfix/critical-fix
git tag -a v1.0.1 -m "Hotfix: critical bug"

# 5. Merge back to develop
git checkout develop
git merge hotfix/critical-fix
```

### **Rollback Strategy:**

```bash
# If hotfix causes issues
git checkout main
git revert <commit-hash>
git push origin main

# Or rollback to previous tag
git checkout main
git reset --hard v1.0.0
git push --force-with-lease origin main
```

---

## 📚 BEST PRACTICES

### **DO's:**
✅ Keep feature branches small and focused  
✅ Regular commits with descriptive messages  
✅ Update from develop regularly  
✅ Delete merged branches  
✅ Use meaningful branch names  
✅ Test before creating PR  

### **DON'Ts:**
❌ Direct push to main/develop  
❌ Long-running feature branches  
❌ Mixing multiple features in one branch  
❌ Force push to shared branches  
❌ Merge without review  
❌ Leave stale branches  

---

## 🎯 SUCCESS METRICS

**Weekly Goals:**
- Feature branches < 1 week old
- PR review time < 24 hours
- 0 conflicts in main branch
- 100% test coverage on critical paths
- Clean commit history

**Quality Gates:**
- All PRs reviewed
- No direct commits to protected branches
- Automated tests passing
- Documentation updated
- Security checks passed

---

**🌟 Remember: Good branching strategy = Happy team collaboration!**
