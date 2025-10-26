# Kottackal Medical Store - Deployment Guide

## AWS EC2 Deployment on Amazon Linux 2023

This guide covers the deployment of the Kottackal Medical Store application on AWS EC2 with Amazon Linux 2023, Apache, PHP 8.4, and MariaDB 10.5.

### Prerequisites

- AWS EC2 instance running Amazon Linux 2023
- Root or sudo access to the server
- Basic knowledge of Linux command line

### Step 1: Install Required Packages

```bash
# Update system packages
sudo dnf update -y

# Install Apache web server
sudo dnf install httpd -y

# Install PHP 8.4 and required extensions
sudo dnf install php php-mysqlnd php-pdo php-gd php-mbstring php-xml php-json -y

# Install MariaDB 10.5
sudo dnf install mariadb105-server -y
```

### Step 2: Configure Services

```bash
# Start and enable Apache
sudo systemctl start httpd
sudo systemctl enable httpd

# Start and enable MariaDB
sudo systemctl start mariadb
sudo systemctl enable mariadb

# Secure MariaDB installation
sudo mysql_secure_installation
```

### Step 3: Configure Firewall

```bash
# Configure firewalld
sudo systemctl start firewalld
sudo systemctl enable firewalld

# Allow HTTP and HTTPS traffic
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### Step 4: AWS Security Group Configuration

In AWS Console:
1. Go to EC2 > Security Groups
2. Select your instance's security group
3. Add inbound rules:
   - Type: HTTP, Protocol: TCP, Port: 80, Source: 0.0.0.0/0
   - Type: HTTPS, Protocol: TCP, Port: 443, Source: 0.0.0.0/0
   - Type: SSH, Protocol: TCP, Port: 22, Source: Your IP

### Step 5: Deploy Application Files

```bash
# Navigate to web root
cd /var/www/html

# Remove default files
sudo rm -rf *

# Clone or upload your application files
# Option 1: Clone from repository
sudo git clone https://github.com/anwinrajugeorge2028-cyber/Kottackal-Medical-Store.git .

# Option 2: Upload via SCP
# scp -r /local/path/* ec2-user@your-instance-ip:/tmp/
# sudo mv /tmp/* /var/www/html/

# Set proper permissions
sudo chown -R apache:apache /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 770 /var/www/html/logs
```

### Step 6: Configure Database

```bash
# Login to MariaDB
sudo mysql -u root -p

# Create database and import schema
source /var/www/html/database/schema.sql;

# Create database user (recommended for production)
CREATE USER 'kottackal_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON kottackal_medical_store.* TO 'kottackal_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 7: Update Configuration

Edit `/var/www/html/config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kottackal_medical_store');
define('DB_USER', 'kottackal_user');
define('DB_PASS', 'your_secure_password');
```

### Step 8: Configure Apache

```bash
# Copy virtual host configuration
sudo cp /var/www/html/config/apache-vhost.conf /etc/httpd/conf.d/kottackal.conf

# Enable mod_rewrite
sudo sed -i 's/AllowOverride None/AllowOverride All/g' /etc/httpd/conf/httpd.conf

# Restart Apache
sudo systemctl restart httpd
```

### Step 9: Configure PHP

Edit `/etc/php.ini`:

```ini
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
date.timezone = Asia/Kolkata
```

Restart Apache:
```bash
sudo systemctl restart httpd
```

### Step 10: Create Logs Directory

```bash
# Create logs directory
sudo mkdir -p /var/www/html/logs
sudo chown apache:apache /var/www/html/logs
sudo chmod 770 /var/www/html/logs
```

### Step 11: Test the Application

1. Open your browser and navigate to: `http://your-ec2-public-ip/`
2. Login with default credentials:
   - Username: `admin`
   - Password: `admin123`

### Troubleshooting

#### HTTP Error 500

Common causes and solutions:

1. **PHP/MariaDB Connection Issue**
   ```bash
   # Check PHP can connect to MySQL
   sudo php -r "new PDO('mysql:host=localhost;dbname=kottackal_medical_store', 'root', 'password');"
   
   # Check MariaDB is running
   sudo systemctl status mariadb
   
   # Check error logs
   sudo tail -f /var/log/httpd/error_log
   sudo tail -f /var/www/html/logs/php_errors.log
   ```

2. **File Permissions**
   ```bash
   # Reset permissions
   sudo chown -R apache:apache /var/www/html
   sudo chmod -R 755 /var/www/html
   ```

3. **SELinux Issues**
   ```bash
   # Check SELinux status
   getenforce
   
   # If enforcing, allow httpd network connect
   sudo setsebool -P httpd_can_network_connect_db 1
   sudo setsebool -P httpd_can_network_connect 1
   
   # Or temporarily disable for testing
   sudo setenforce 0
   ```

4. **Missing PHP Extensions**
   ```bash
   # Check loaded PHP modules
   php -m
   
   # Install missing modules
   sudo dnf install php-mysqlnd php-pdo -y
   sudo systemctl restart httpd
   ```

#### Firewall Issues

```bash
# Check firewall status
sudo firewall-cmd --list-all

# Check if ports are listening
sudo netstat -tulpn | grep :80
sudo netstat -tulpn | grep :3306
```

#### Database Connection Issues

```bash
# Test database connection
mysql -u kottackal_user -p kottackal_medical_store

# Check MySQL error log
sudo tail -f /var/log/mariadb/mariadb.log
```

### Security Recommendations

1. **Change Default Credentials**
   - Update the admin password immediately
   - Use strong, unique passwords

2. **Enable HTTPS**
   ```bash
   # Install certbot for Let's Encrypt
   sudo dnf install certbot python3-certbot-apache -y
   sudo certbot --apache -d your-domain.com
   ```

3. **Restrict Database Access**
   - Use a dedicated database user with limited privileges
   - Change the default database password

4. **Regular Updates**
   ```bash
   # Keep system updated
   sudo dnf update -y
   ```

5. **Backup Strategy**
   ```bash
   # Backup database
   mysqldump -u root -p kottackal_medical_store > backup_$(date +%Y%m%d).sql
   
   # Backup application files
   tar -czf backup_$(date +%Y%m%d).tar.gz /var/www/html
   ```

### Monitoring

```bash
# Check Apache status
sudo systemctl status httpd

# Check MariaDB status
sudo systemctl status mariadb

# Monitor error logs
sudo tail -f /var/log/httpd/error_log
sudo tail -f /var/www/html/logs/php_errors.log
```

### Performance Optimization

1. **Enable PHP OPcache**
   ```bash
   sudo dnf install php-opcache -y
   sudo systemctl restart httpd
   ```

2. **Configure Apache for better performance**
   Edit `/etc/httpd/conf/httpd.conf`:
   ```apache
   KeepAlive On
   MaxKeepAliveRequests 100
   KeepAliveTimeout 5
   ```

3. **MariaDB Optimization**
   Edit `/etc/my.cnf.d/server.cnf`:
   ```ini
   [mysqld]
   innodb_buffer_pool_size = 256M
   max_connections = 200
   ```

## Live Application

The application is currently running at: http://13.60.30.251/

### Support

For issues or questions, please contact the system administrator.
