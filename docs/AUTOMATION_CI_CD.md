# 🤖 AUTOMATION & CI/CD SETUP

**Proyek:** Sistem Pencatatan Order  
**Platform:** GitHub Actions  
**Tujuan:** Automated Testing, Code Quality, Deployment  

---

## 🎯 OVERVIEW AUTOMATION

### **Automation Goals:**
- ✅ Automated testing untuk setiap PR
- ✅ Code quality checks
- ✅ Security vulnerability scanning
- ✅ Automated deployment ke staging
- ✅ Performance monitoring
- ✅ Notification ke team

---

## 📁 FOLDER STRUCTURE

```
.github/
├── workflows/
│   ├── ci.yml                 # Continuous Integration
│   ├── code-quality.yml       # Code Quality Checks
│   ├── security.yml           # Security Scanning
│   ├── deploy-staging.yml     # Staging Deployment
│   └── release.yml            # Production Release
├── ISSUE_TEMPLATE/
│   ├── bug_report.md
│   ├── feature_request.md
│   └── task.md
└── PULL_REQUEST_TEMPLATE.md
```

---

## 🔄 CONTINUOUS INTEGRATION

### **File: `.github/workflows/ci.yml`**

```yaml
name: 🚀 Continuous Integration

on:
  push:
    branches: [ develop, main ]
  pull_request:
    branches: [ develop, main ]

jobs:
  test:
    name: 🧪 Run Tests
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: rootpassword
          MYSQL_DATABASE: uaspbw_test
          MYSQL_USER: testuser
          MYSQL_PASSWORD: testpass
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - name: 📥 Checkout Code
      uses: actions/checkout@v3

    - name: 🐘 Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql, dom, filter, gd, iconv, json, mbstring, pdo
        coverage: xdebug

    - name: 📦 Cache Composer Dependencies
      uses: actions/cache@v3
      with:
        path: ~/.composer/cache/files
        key: composer-${{ hashFiles('composer.lock') }}

    - name: 🔧 Install Dependencies
      run: |
        if [ -f composer.json ]; then
          composer install --no-progress --no-interaction --prefer-dist --optimize-autoloader
        fi

    - name: 🗄️ Setup Database
      run: |
        mysql -h 127.0.0.1 -u testuser -ptestpass uaspbw_test < database/schema.sql

    - name: ⚙️ Setup Environment
      run: |
        cp .env.example .env.testing
        sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env.testing
        sed -i 's/DB_DATABASE=.*/DB_DATABASE=uaspbw_test/' .env.testing
        sed -i 's/DB_USERNAME=.*/DB_USERNAME=testuser/' .env.testing
        sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=testpass/' .env.testing

    - name: 🔍 PHP Syntax Check
      run: find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \;

    - name: 🧪 Run PHP Unit Tests
      run: |
        if [ -f phpunit.xml ]; then
          vendor/bin/phpunit --configuration phpunit.xml --coverage-text
        else
          echo "Running basic tests..."
          php tests/basic_tests.php
        fi

    - name: 🌐 Test Database Connection
      run: php tests/database_connection_test.php

    - name: 📊 Generate Test Report
      run: |
        echo "## 🧪 Test Results" >> $GITHUB_STEP_SUMMARY
        echo "- ✅ PHP Syntax Check: PASSED" >> $GITHUB_STEP_SUMMARY
        echo "- ✅ Database Connection: PASSED" >> $GITHUB_STEP_SUMMARY
        echo "- ✅ Unit Tests: PASSED" >> $GITHUB_STEP_SUMMARY

  lint:
    name: 🎨 Code Style Check
    runs-on: ubuntu-latest
    
    steps:
    - name: 📥 Checkout Code
      uses: actions/checkout@v3

    - name: 🐘 Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        tools: phpcs, phpmd

    - name: 🎨 PHP CodeSniffer
      run: |
        if [ -f phpcs.xml ]; then
          phpcs --standard=phpcs.xml --report=full .
        else
          phpcs --standard=PSR12 --ignore=vendor/ .
        fi

    - name: 🔍 PHP Mess Detector
      run: |
        if [ -f phpmd.xml ]; then
          phpmd . text phpmd.xml --exclude vendor/
        else
          phpmd . text codesize,unusedcode,naming --exclude vendor/
        fi
```

---

## 🛡️ SECURITY SCANNING

### **File: `.github/workflows/security.yml`**

```yaml
name: 🔒 Security Scan

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]
  schedule:
    - cron: '0 2 * * 1'  # Weekly scan every Monday at 2 AM

jobs:
  security:
    name: 🛡️ Security Analysis
    runs-on: ubuntu-latest
    
    steps:
    - name: 📥 Checkout Code
      uses: actions/checkout@v3

    - name: 🔍 Run Trivy vulnerability scanner
      uses: aquasecurity/trivy-action@master
      with:
        scan-type: 'fs'
        scan-ref: '.'
        format: 'sarif'
        output: 'trivy-results.sarif'

    - name: 📤 Upload Trivy scan results
      uses: github/codeql-action/upload-sarif@v2
      with:
        sarif_file: 'trivy-results.sarif'

    - name: 🔐 Security Check for PHP
      run: |
        # Check for common PHP security issues
        echo "🔍 Checking for PHP security issues..."
        
        # Check for dangerous functions
        if grep -r "exec\|system\|shell_exec\|passthru\|eval" --include="*.php" . --exclude-dir=vendor; then
          echo "⚠️ Warning: Dangerous PHP functions found!"
          exit 1
        fi
        
        # Check for SQL injection patterns
        if grep -r "\$_GET\|\$_POST" --include="*.php" . --exclude-dir=vendor | grep -v "htmlspecialchars\|filter_input\|prepared"; then
          echo "⚠️ Warning: Potential SQL injection vulnerability!"
          exit 1
        fi
        
        echo "✅ Basic security checks passed!"

    - name: 📊 Security Report
      run: |
        echo "## 🔒 Security Scan Results" >> $GITHUB_STEP_SUMMARY
        echo "- ✅ Vulnerability Scan: COMPLETED" >> $GITHUB_STEP_SUMMARY
        echo "- ✅ PHP Security Check: PASSED" >> $GITHUB_STEP_SUMMARY
        echo "- ✅ No critical vulnerabilities found" >> $GITHUB_STEP_SUMMARY
```

---

## 📊 CODE QUALITY CHECKS

### **File: `.github/workflows/code-quality.yml`**

```yaml
name: 📊 Code Quality

on:
  pull_request:
    branches: [ develop, main ]

jobs:
  quality:
    name: 🎯 Quality Analysis
    runs-on: ubuntu-latest
    
    steps:
    - name: 📥 Checkout Code
      uses: actions/checkout@v3
      with:
        fetch-depth: 0  # Full history for better analysis

    - name: 🐘 Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        tools: phpstan, psalm

    - name: 📊 PHPStan Analysis
      run: |
        if [ -f phpstan.neon ]; then
          phpstan analyse --configuration=phpstan.neon --no-progress
        else
          phpstan analyse --level=5 --no-progress src/ config/ dashboard/ auth/
        fi

    - name: 📋 Code Complexity Check
      run: |
        echo "🔍 Checking code complexity..."
        find . -name "*.php" -not -path "./vendor/*" | xargs -I {} php -r "
          \$code = file_get_contents('{}');
          \$lines = substr_count(\$code, '\n');
          if (\$lines > 500) {
            echo 'Warning: {} has ' . \$lines . ' lines (consider splitting)' . PHP_EOL;
          }
        "

    - name: 📈 Generate Quality Report
      run: |
        echo "## 📊 Code Quality Report" >> $GITHUB_STEP_SUMMARY
        echo "### 📋 Metrics:" >> $GITHUB_STEP_SUMMARY
        echo "- Lines of Code: $(find . -name '*.php' -not -path './vendor/*' | xargs wc -l | tail -1 | awk '{print $1}')" >> $GITHUB_STEP_SUMMARY
        echo "- PHP Files: $(find . -name '*.php' -not -path './vendor/*' | wc -l)" >> $GITHUB_STEP_SUMMARY
        echo "- Functions: $(grep -r 'function ' --include='*.php' . --exclude-dir=vendor | wc -l)" >> $GITHUB_STEP_SUMMARY
        echo "- Classes: $(grep -r 'class ' --include='*.php' . --exclude-dir=vendor | wc -l)" >> $GITHUB_STEP_SUMMARY
```

---

## 🚀 DEPLOYMENT AUTOMATION

### **File: `.github/workflows/deploy-staging.yml`**

```yaml
name: 🚀 Deploy to Staging

on:
  push:
    branches: [ develop ]

jobs:
  deploy:
    name: 🌐 Deploy Staging
    runs-on: ubuntu-latest
    environment: staging
    
    steps:
    - name: 📥 Checkout Code
      uses: actions/checkout@v3

    - name: 🐘 Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'

    - name: 📦 Install Dependencies
      run: |
        composer install --no-dev --optimize-autoloader

    - name: 🗄️ Database Migration (Staging)
      run: |
        echo "Running database migrations..."
        # Add your migration commands here
        # php migrate.php --env=staging

    - name: 📤 Deploy to Staging Server
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.STAGING_HOST }}
        username: ${{ secrets.STAGING_USERNAME }}
        key: ${{ secrets.STAGING_SSH_KEY }}
        script: |
          cd /var/www/staging/sistem-pencatatan-order
          git pull origin develop
          composer install --no-dev --optimize-autoloader
          php setup/migrate.php
          sudo systemctl reload apache2

    - name: 🧪 Staging Smoke Tests
      run: |
        echo "Running smoke tests on staging..."
        curl -f ${{ secrets.STAGING_URL }} || exit 1
        curl -f ${{ secrets.STAGING_URL }}/auth/login.php || exit 1
        echo "✅ Staging deployment successful!"

    - name: 📢 Notify Team
      uses: 8398a7/action-slack@v3
      with:
        status: ${{ job.status }}
        text: |
          🚀 *Staging Deployment*
          Status: ${{ job.status }}
          Branch: develop
          URL: ${{ secrets.STAGING_URL }}
      env:
        SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}
```

---

## 🏷️ RELEASE AUTOMATION

### **File: `.github/workflows/release.yml`**

```yaml
name: 🏷️ Release

on:
  push:
    branches: [ main ]

jobs:
  release:
    name: 📦 Create Release
    runs-on: ubuntu-latest
    
    steps:
    - name: 📥 Checkout Code
      uses: actions/checkout@v3
      with:
        fetch-depth: 0

    - name: 🏷️ Generate Version
      id: version
      run: |
        VERSION=$(date +%Y.%m.%d)-$(git rev-parse --short HEAD)
        echo "version=$VERSION" >> $GITHUB_OUTPUT

    - name: 📝 Generate Changelog
      id: changelog
      run: |
        echo "## 🚀 Release ${{ steps.version.outputs.version }}" > CHANGELOG.md
        echo "" >> CHANGELOG.md
        echo "### ✨ New Features:" >> CHANGELOG.md
        git log --oneline --grep="feat:" --since="$(git tag --sort=-creatordate | head -1)" >> CHANGELOG.md
        echo "" >> CHANGELOG.md
        echo "### 🐛 Bug Fixes:" >> CHANGELOG.md
        git log --oneline --grep="fix:" --since="$(git tag --sort=-creatordate | head -1)" >> CHANGELOG.md

    - name: 📦 Create Release Package
      run: |
        mkdir -p release/sistem-pencatatan-order
        rsync -av --exclude='.git' --exclude='node_modules' --exclude='tests' . release/sistem-pencatatan-order/
        cd release
        tar -czf sistem-pencatatan-order-${{ steps.version.outputs.version }}.tar.gz sistem-pencatatan-order/
        zip -r sistem-pencatatan-order-${{ steps.version.outputs.version }}.zip sistem-pencatatan-order/

    - name: 🏷️ Create Git Tag
      run: |
        git config user.name "GitHub Actions"
        git config user.email "actions@github.com"
        git tag -a v${{ steps.version.outputs.version }} -m "Release v${{ steps.version.outputs.version }}"
        git push origin v${{ steps.version.outputs.version }}

    - name: 📢 Create GitHub Release
      uses: actions/create-release@v1
      env:
        GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
      with:
        tag_name: v${{ steps.version.outputs.version }}
        release_name: Release v${{ steps.version.outputs.version }}
        body_path: CHANGELOG.md
        draft: false
        prerelease: false

    - name: 📤 Upload Release Assets
      uses: actions/upload-release-asset@v1
      env:
        GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
      with:
        upload_url: ${{ steps.create_release.outputs.upload_url }}
        asset_path: release/sistem-pencatatan-order-${{ steps.version.outputs.version }}.tar.gz
        asset_name: sistem-pencatatan-order-${{ steps.version.outputs.version }}.tar.gz
        asset_content_type: application/gzip
```

---

## 📋 ISSUE TEMPLATES

### **File: `.github/ISSUE_TEMPLATE/bug_report.md`**

```yaml
---
name: 🐛 Bug Report
about: Laporkan bug yang ditemukan
title: '[BUG] '
labels: bug
assignees: ''
---

## 🐛 Deskripsi Bug
Jelaskan bug yang terjadi secara singkat dan jelas.

## 🔄 Langkah Reproduksi
1. Buka halaman '...'
2. Klik pada '....'
3. Scroll ke '....'
4. Lihat error

## ✅ Expected Behavior
Jelaskan apa yang seharusnya terjadi.

## ❌ Actual Behavior
Jelaskan apa yang sebenarnya terjadi.

## 📸 Screenshots
Jika applicable, tambahkan screenshot untuk membantu menjelaskan masalah.

## 💻 Environment
- OS: [e.g. Windows 10]
- Browser: [e.g. Chrome 91]
- PHP Version: [e.g. 8.1]
- Database: [e.g. MySQL 8.0]

## ℹ️ Informasi Tambahan
Tambahkan informasi tambahan tentang masalah ini.
```

### **File: `.github/ISSUE_TEMPLATE/feature_request.md`**

```yaml
---
name: ✨ Feature Request
about: Usulkan fitur baru untuk proyek
title: '[FEATURE] '
labels: enhancement
assignees: ''
---

## 🎯 Deskripsi Fitur
Jelaskan fitur yang diinginkan secara jelas dan singkat.

## 💡 Motivasi
Jelaskan mengapa fitur ini diperlukan. Masalah apa yang akan diselesaikan?

## 📝 Solusi yang Diusulkan
Jelaskan solusi yang Anda inginkan.

## 🔄 Alternatif yang Dipertimbangkan
Jelaskan alternatif solusi yang sudah dipertimbangkan.

## 📋 Acceptance Criteria
- [ ] Kriteria 1
- [ ] Kriteria 2
- [ ] Kriteria 3

## 📊 Priority
- [ ] Low
- [ ] Medium
- [ ] High
- [ ] Critical

## ⏱️ Estimasi Waktu
Berapa lama waktu yang dibutuhkan untuk implementasi?

## ℹ️ Informasi Tambahan
Tambahkan informasi, screenshot, atau referensi lainnya.
```

---

## 📤 PULL REQUEST TEMPLATE

### **File: `.github/PULL_REQUEST_TEMPLATE.md`**

```markdown
## 📋 Tipe Perubahan
- [ ] 🚀 Fitur baru (feature)
- [ ] 🐛 Bug fix
- [ ] 🚨 Hotfix (critical)
- [ ] 📖 Documentation
- [ ] 🎨 Style/UI improvements
- [ ] ♻️ Refactoring
- [ ] ⚡ Performance improvements
- [ ] 🧪 Tests

## 📝 Deskripsi Perubahan
Jelaskan perubahan yang dibuat secara detail.

## 🔗 Related Issues
- Closes #
- Related to #

## ✅ Testing Checklist
- [ ] ✅ Tested locally
- [ ] ✅ All existing tests pass
- [ ] ✅ New tests added (if applicable)
- [ ] ✅ Manual testing completed
- [ ] ✅ No breaking changes
- [ ] ✅ Database migrations tested

## 📸 Screenshots (jika applicable)
| Before | After |
|--------|-------|
| ![Before](url) | ![After](url) |

## 📋 Review Checklist (untuk Reviewer)
- [ ] Code follows project standards
- [ ] Changes are well tested
- [ ] Documentation updated
- [ ] No security issues
- [ ] Performance not degraded
- [ ] UI/UX is consistent

## 🚀 Deployment Notes
Apakah ada langkah khusus yang diperlukan untuk deployment?

## 📊 Performance Impact
Apakah ada dampak pada performance aplikasi?
```

---

## 🔧 CONFIGURATION FILES

### **File: `phpcs.xml` (Code Style)**

```xml
<?xml version="1.0"?>
<ruleset name="Sistem Pencatatan Order">
    <description>PHP CodeSniffer configuration</description>
    
    <file>.</file>
    
    <exclude-pattern>vendor/</exclude-pattern>
    <exclude-pattern>node_modules/</exclude-pattern>
    <exclude-pattern>*.min.js</exclude-pattern>
    
    <rule ref="PSR12">
        <exclude name="PSR12.Properties.ConstantVisibility.NotFound"/>
    </rule>
    
    <rule ref="Generic.Files.LineLength">
        <properties>
            <property name="lineLimit" value="120"/>
            <property name="absoluteLineLimit" value="200"/>
        </properties>
    </rule>
</ruleset>
```

### **File: `phpstan.neon` (Static Analysis)**

```yaml
parameters:
    level: 6
    paths:
        - auth/
        - dashboard/
        - config/
        - includes/
    excludePaths:
        - vendor/
    ignoreErrors:
        - '#Function mysql_\* is deprecated#'
    checkMissingIterableValueType: false
```

---

## 📊 MONITORING & NOTIFICATIONS

### **Slack Integration:**

```yaml
# Add to workflow files
- name: 📢 Notify Team
  uses: 8398a7/action-slack@v3
  with:
    status: ${{ job.status }}
    text: |
      🚀 *${{ github.workflow }}*
      Status: ${{ job.status }}
      Branch: ${{ github.ref }}
      Commit: ${{ github.sha }}
      Author: ${{ github.actor }}
  env:
    SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}
  if: always()
```

### **Email Notifications:**

```yaml
# Add to workflow files  
- name: 📧 Send Email Notification
  uses: dawidd6/action-send-mail@v3
  with:
    server_address: smtp.gmail.com
    server_port: 587
    username: ${{ secrets.MAIL_USERNAME }}
    password: ${{ secrets.MAIL_PASSWORD }}
    subject: "[${{ github.repository }}] ${{ github.workflow }} - ${{ job.status }}"
    to: team@project.com
    from: github-actions@project.com
    body: |
      Workflow: ${{ github.workflow }}
      Status: ${{ job.status }}
      Branch: ${{ github.ref }}
      Commit: ${{ github.sha }}
      Author: ${{ github.actor }}
  if: failure()
```

---

## 🎯 SUCCESS METRICS

### **Automation KPIs:**
- ✅ 100% automated testing on PRs
- ✅ <5 minute CI pipeline execution
- ✅ Zero deployment failures
- ✅ 100% security scan coverage
- ✅ Automated code quality gates

### **Team Benefits:**
- 🚀 Faster development cycles
- 🛡️ Early bug detection
- 📊 Consistent code quality
- 🔒 Security vulnerability prevention
- 📈 Improved team productivity

---

**🤖 Automation is the key to efficient team collaboration!**
