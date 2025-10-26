#!/bin/bash
# Kottackal Medical Store - Installation Script for Amazon Linux 2023
# This script automates the installation and configuration process

set -e  # Exit on error

echo "============================================"
echo "Kottackal Medical Store - Installation"
echo "Amazon Linux 2023 / LAMP Stack Setup"
echo "============================================"
echo ""

# Check if running as root or with sudo
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root or with sudo" 
   exit 1
fi

# Update system
echo "[1/10] Updating system packages..."
dnf update -y

# Install Apache
echo "[2/10] Installing Apache web server..."
dnf install httpd -y

# Install PHP 8.4 and extensions
echo "[3/10] Installing PHP 8.4 and extensions..."
dnf install php php-mysqlnd php-pdo php-gd php-mbstring php-xml php-json php-opcache -y

# Install MariaDB 10.5
echo "[4/10] Installing MariaDB 10.5..."
dnf install mariadb105-server -y

# Start and enable services
echo "[5/10] Starting and enabling services..."
systemctl start httpd
systemctl enable httpd
systemctl start mariadb
systemctl enable mariadb

# Configure firewall
echo "[6/10] Configuring firewall..."
systemctl start firewalld
systemctl enable firewalld
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --reload

# Set permissions
echo "[7/10] Setting file permissions..."
chown -R apache:apache /var/www/html
chmod -R 755 /var/www/html

# Create logs directory
echo "[8/10] Creating logs directory..."
mkdir -p /var/www/html/logs
chown apache:apache /var/www/html/logs
chmod 770 /var/www/html/logs

# Configure SELinux
echo "[9/10] Configuring SELinux..."
setsebool -P httpd_can_network_connect_db 1
setsebool -P httpd_can_network_connect 1

# Enable Apache modules
echo "[10/10] Configuring Apache..."
sed -i 's/AllowOverride None/AllowOverride All/g' /etc/httpd/conf/httpd.conf

# Restart Apache
systemctl restart httpd

echo ""
echo "============================================"
echo "Installation Complete!"
echo "============================================"
echo ""
echo "Next steps:"
echo "1. Secure MariaDB: sudo mysql_secure_installation"
echo "2. Import database: sudo mysql < /var/www/html/database/schema.sql"
echo "3. Update config: Edit /var/www/html/config/config.php"
echo "4. Configure AWS Security Group to allow HTTP/HTTPS"
echo "5. Access application: http://YOUR_SERVER_IP/"
echo ""
echo "Default credentials: admin / admin123"
echo ""
echo "For detailed instructions, see /var/www/html/docs/DEPLOYMENT.md"
echo ""
