# Quick Reference Guide

## Kottackal Medical Store Management System

### Quick Start

#### First Time Setup
```bash
# Run installation script
sudo ./install.sh

# Secure MySQL
sudo mysql_secure_installation

# Import database
sudo mysql < database/schema.sql

# Update configuration
sudo nano config/config.php
```

#### Access Application
- URL: `http://YOUR_SERVER_IP/`
- Default Login: `admin` / `admin123`

### Common Tasks

#### Adding a New Medicine
1. Navigate to **Medicines** page
2. Click **Add Medicine** button
3. Fill in medicine details:
   - Name (required)
   - Generic Name
   - Category
   - Manufacturer
   - Unit Price (required)
   - Quantity (required)
   - Reorder Level (required)
   - Expiry Date
4. Click **Save Medicine**

#### Processing a Sale
1. Navigate to **Sales** page
2. Click **New Sale** button
3. Select medicine from dropdown
4. Enter quantity
5. Click **Add** to add to cart
6. Repeat for multiple items
7. Enter customer details (optional)
8. Apply discount/tax if needed
9. Select payment method
10. Click **Complete Sale**

#### Managing Stock
1. Navigate to **Medicines** page
2. Find medicine with low stock (highlighted in red)
3. Click **Edit** button
4. Update **Quantity in Stock**
5. Click **Save Medicine**

#### Viewing Sales History
1. Navigate to **Sales** page
2. View recent sales in table
3. Click **View** to see sale details

### User Roles

#### Admin
- Full system access
- Can manage users
- View all reports
- Access all features

#### Pharmacist
- Manage medicines
- Process sales
- View inventory
- Manage suppliers

#### Cashier
- Process sales only
- View inventory
- Limited access

### Database Backup

#### Manual Backup
```bash
# Run backup script
sudo ./backup.sh

# Backups stored in /var/backups/kottackal/
```

#### Automated Backup (Cron)
```bash
# Edit crontab
sudo crontab -e

# Add daily backup at 2 AM
0 2 * * * /var/www/html/backup.sh >> /var/log/backup.log 2>&1
```

### Restore Database
```bash
# Restore from backup
gunzip < backup_file.sql.gz | mysql -u root -p kottackal_medical_store
```

### Troubleshooting

#### Can't Login
1. Check database connection in `config/config.php`
2. Verify user exists in database:
   ```sql
   SELECT * FROM users WHERE username = 'admin';
   ```
3. Reset password if needed:
   ```sql
   UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
   WHERE username = 'admin';
   ```
   (Password will be: admin123)

#### HTTP 500 Error
1. Check Apache logs:
   ```bash
   sudo tail -f /var/log/httpd/error_log
   ```
2. Check PHP logs:
   ```bash
   sudo tail -f /var/www/html/logs/php_errors.log
   ```
3. Verify file permissions:
   ```bash
   sudo chown -R apache:apache /var/www/html
   sudo chmod -R 755 /var/www/html
   ```

#### Database Connection Failed
1. Check MariaDB is running:
   ```bash
   sudo systemctl status mariadb
   ```
2. Verify credentials in `config/config.php`
3. Test connection:
   ```bash
   mysql -u root -p kottackal_medical_store
   ```

#### Page Not Found (404)
1. Check `.htaccess` exists
2. Verify Apache mod_rewrite is enabled
3. Check AllowOverride is set to All

### Useful Commands

#### Service Management
```bash
# Restart Apache
sudo systemctl restart httpd

# Restart MariaDB
sudo systemctl restart mariadb

# Check service status
sudo systemctl status httpd
sudo systemctl status mariadb
```

#### Log Viewing
```bash
# Apache error log
sudo tail -f /var/log/httpd/error_log

# Apache access log
sudo tail -f /var/log/httpd/access_log

# PHP error log
sudo tail -f /var/www/html/logs/php_errors.log
```

#### Database Operations
```bash
# Login to MySQL
sudo mysql -u root -p

# Show databases
SHOW DATABASES;

# Use database
USE kottackal_medical_store;

# Show tables
SHOW TABLES;

# View users
SELECT * FROM users;

# View medicines
SELECT * FROM medicines;
```

### Security Checklist

- [ ] Changed default admin password
- [ ] Updated database password
- [ ] Configured firewall
- [ ] AWS Security Group configured
- [ ] Regular backups enabled
- [ ] SSL certificate installed (optional)
- [ ] Error logging enabled
- [ ] File permissions set correctly
- [ ] SELinux configured

### Performance Tips

1. **Enable PHP OPcache**
   ```bash
   sudo dnf install php-opcache -y
   sudo systemctl restart httpd
   ```

2. **Optimize MariaDB**
   - Edit `/etc/my.cnf.d/server.cnf`
   - Increase `innodb_buffer_pool_size`
   - Increase `max_connections`

3. **Enable Apache KeepAlive**
   - Edit `/etc/httpd/conf/httpd.conf`
   - Set `KeepAlive On`

### Support

- **Documentation**: See `/docs/` folder
- **Email**: anwinrajugeorge2028@bca.ajce.in
- **GitHub**: https://github.com/anwinrajugeorge2028-cyber/Kottackal-Medical-Store

### Version Information

- **Current Version**: 1.0.0
- **PHP Version**: 8.4
- **MariaDB Version**: 10.5
- **Bootstrap Version**: 5.3
- **Last Updated**: October 2025
