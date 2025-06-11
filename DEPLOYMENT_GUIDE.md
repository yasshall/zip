# Luxury Watches Store - cPanel Deployment Guide

## Prerequisites
- cPanel hosting account with PHP 7.4+ and MySQL support
- File Manager or FTP access
- Database creation privileges

## Step 1: Database Setup

### 1.1 Create MySQL Database
1. Login to your cPanel
2. Go to "MySQL Databases"
3. Create a new database: `luxury_watches`
4. Create a database user with full privileges
5. Note down: database name, username, password, and host (usually localhost)

### 1.2 Update Database Configuration
Edit `backend/config/database.php` with your cPanel database credentials:

```php
private $host = 'localhost';           // Usually localhost
private $db_name = 'your_db_name';     // Your database name
private $username = 'your_db_user';    // Your database username
private $password = 'your_db_password'; // Your database password
```

## Step 2: File Upload

### 2.1 Upload Files via File Manager
1. In cPanel, open "File Manager"
2. Navigate to `public_html` (or your domain's document root)
3. Upload all files maintaining this structure:

```
public_html/
├── index.html
├── products.html
├── product.html
├── cart.html
├── checkout.html
├── thank-you.html
├── contact.html
├── assets/
│   ├── css/
│   │   └── styles.css
│   └── js/
│       ├── main.js
│       ├── cart.js
│       ├── checkout.js
│       ├── contact.js
│       ├── product.js
│       ├── products.js
│       └── thank-you.js
└── backend/
    ├── config/
    │   └── database.php
    ├── api/
    │   ├── products.php
    │   ├── orders.php
    │   ├── categories.php
    │   └── newsletter.php
    └── admin/
        ├── index.php
        ├── dashboard.php
        ├── assets/
        │   └── admin.css
        └── includes/
            ├── header.php
            └── sidebar.php
```

### 2.2 Set File Permissions
Set these permissions via File Manager:
- PHP files: 644
- Directories: 755
- CSS/JS files: 644

## Step 3: Configuration

### 3.1 Update Contact Information
Edit these files to replace placeholder contact info:

**In all HTML files**, replace:
- `+212XXXXXXXXX` with your actual phone number
- `info@luxurywatches.ma` with your actual email
- WhatsApp links with your WhatsApp number

**In `assets/js/main.js`**, update:
- Facebook Pixel ID: Replace `YOUR_PIXEL_ID`
- Google Analytics ID: Replace `GA_MEASUREMENT_ID`

### 3.2 Admin Panel Setup
1. Access: `https://yourdomain.com/backend/admin/`
2. Default login:
   - Username: `admin`
   - Password: `luxury2024`
3. **IMPORTANT**: Change these credentials in `backend/admin/index.php`

## Step 4: Testing

### 4.1 Test Database Connection
1. Visit: `https://yourdomain.com/backend/api/products.php`
2. Should return JSON with sample products
3. If error, check database credentials

### 4.2 Test Admin Panel
1. Visit: `https://yourdomain.com/backend/admin/`
2. Login with credentials
3. Check if dashboard loads with statistics

### 4.3 Test Frontend
1. Visit your main domain
2. Test navigation between pages
3. Test adding products to cart
4. Test checkout process

## Step 5: Security & Optimization

### 5.1 Secure Admin Panel
1. Change admin credentials immediately
2. Consider adding IP restrictions
3. Use HTTPS for admin access

### 5.2 Database Security
1. Use strong database passwords
2. Limit database user privileges
3. Regular backups

### 5.3 Performance
1. Enable gzip compression in cPanel
2. Set up caching if available
3. Optimize images

## Step 6: SSL Certificate
1. In cPanel, go to "SSL/TLS"
2. Install SSL certificate (Let's Encrypt is free)
3. Force HTTPS redirects

## Troubleshooting

### Common Issues:

**Database Connection Error:**
- Check database credentials in `backend/config/database.php`
- Ensure database exists and user has privileges
- Check if MySQL service is running

**404 Errors:**
- Verify file paths and names
- Check file permissions
- Ensure files are in correct directories

**Admin Panel Not Loading:**
- Check PHP version (requires 7.4+)
- Verify file permissions
- Check error logs in cPanel

**Products Not Displaying:**
- Check database connection
- Verify API endpoints are accessible
- Check browser console for JavaScript errors

## Support
If you encounter issues:
1. Check cPanel error logs
2. Enable PHP error reporting temporarily
3. Test API endpoints directly
4. Verify database structure

## Post-Deployment Checklist
- [ ] Database connected successfully
- [ ] Admin panel accessible
- [ ] Products displaying on frontend
- [ ] Cart functionality working
- [ ] Checkout process functional
- [ ] Contact forms working
- [ ] SSL certificate installed
- [ ] Admin credentials changed
- [ ] Contact information updated
- [ ] Analytics tracking codes added

Your luxury watches store should now be live and fully functional!