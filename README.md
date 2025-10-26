# Kottackal Medical Store Management System

A comprehensive full-stack LAMP (Linux, Apache, MySQL/MariaDB, PHP) application for pharmacy inventory and sales management.

## 🚀 Live Application

**Live Demo:** http://13.60.30.251/

**Default Login Credentials:**
- Username: `admin`
- Password: `admin123`

## 📋 Overview

The Kottackal Medical Store Management System is a robust web-based application designed to streamline pharmacy operations including:

- **Inventory Management**: Track medicines, stock levels, expiry dates, and reorder points
- **Sales Processing**: Complete POS system with multiple payment methods
- **Supplier Management**: Maintain supplier records and purchase orders
- **User Management**: Role-based access control (Admin, Pharmacist, Cashier)
- **Reporting**: Sales analytics and inventory reports
- **Alerts**: Low stock and expiry date notifications

## 🛠️ Technology Stack

### Backend
- **PHP 8.4**: Modern PHP with improved performance and features
- **MariaDB 10.5**: Robust relational database management
- **PDO**: Secure database operations with prepared statements

### Frontend
- **HTML5 & CSS3**: Modern, responsive design
- **Bootstrap 5.3**: Mobile-first responsive framework
- **JavaScript**: Dynamic client-side interactions
- **Bootstrap Icons**: Comprehensive icon library

### Server & Deployment
- **Apache Web Server**: Reliable HTTP server
- **Amazon Linux 2023**: Latest AWS Linux distribution
- **AWS EC2**: Scalable cloud infrastructure
- **Firewalld**: Advanced firewall configuration

## 📁 Project Structure

```
Kottackal-Medical-Store/
├── config/
│   ├── config.php              # Main configuration file
│   └── apache-vhost.conf       # Apache virtual host configuration
├── database/
│   └── schema.sql              # Database schema and sample data
├── docs/
│   └── DEPLOYMENT.md           # Detailed deployment guide
├── includes/
│   ├── auth.php                # Authentication & session management
│   ├── database.php            # Database connection class
│   ├── functions.php           # Utility functions
│   ├── header.php              # Common header
│   └── sidebar.php             # Navigation sidebar
├── public/
│   ├── css/
│   │   └── style.css           # Custom styles
│   └── js/                     # JavaScript files
├── .htaccess                   # Apache rewrite rules & security
├── index.php                   # Login page
├── dashboard.php               # Main dashboard
├── medicines.php               # Medicine inventory management
├── sales.php                   # Sales processing
├── logout.php                  # Logout handler
└── README.md                   # This file
```

## 🚀 Features

### 1. Dashboard
- Real-time statistics: Total medicines, low stock alerts, expired items
- Today's sales summary
- Recent sales transactions
- Low stock item alerts

### 2. Medicine Inventory
- Add, edit, and delete medicines
- Track stock levels with automatic low stock alerts
- Monitor expiry dates
- Categorize by type and manufacturer
- Search and filter functionality

### 3. Sales Management
- Point-of-sale interface
- Add multiple items to cart
- Apply discounts and taxes
- Multiple payment methods (Cash, Card, UPI)
- Automatic stock deduction
- Sales history and tracking

### 4. User Management (Admin Only)
- Create and manage user accounts
- Role-based permissions
- Track user activities

### 5. Security Features
- Password hashing with bcrypt
- SQL injection prevention via PDO prepared statements
- XSS protection with input sanitization
- Session management with timeout
- CSRF token protection
- Secure HTTP headers

## 📦 Installation & Deployment

### Prerequisites
- AWS EC2 instance (Amazon Linux 2023) or similar Linux server
- Root or sudo access
- Basic Linux command-line knowledge

### Quick Start

1. **Clone the repository:**
   ```bash
   git clone https://github.com/anwinrajugeorge2028-cyber/Kottackal-Medical-Store.git
   cd Kottackal-Medical-Store
   ```

2. **Install dependencies:**
   ```bash
   sudo dnf update -y
   sudo dnf install httpd php php-mysqlnd php-pdo mariadb105-server -y
   ```

3. **Start services:**
   ```bash
   sudo systemctl start httpd mariadb
   sudo systemctl enable httpd mariadb
   ```

4. **Configure database:**
   ```bash
   sudo mysql < database/schema.sql
   ```

5. **Configure application:**
   - Edit `config/config.php` with your database credentials
   - Set proper file permissions

6. **Access the application:**
   - Open browser: `http://your-server-ip/`
   - Login with default credentials

For detailed deployment instructions, see [DEPLOYMENT.md](docs/DEPLOYMENT.md)

## 🔧 Configuration

### Database Configuration
Edit `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kottackal_medical_store');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### Apache Configuration
- Virtual host: `config/apache-vhost.conf`
- URL rewriting: `.htaccess`

## 🔐 Security

### Best Practices Implemented
- ✅ Password hashing (bcrypt)
- ✅ Prepared statements (SQL injection prevention)
- ✅ Input sanitization (XSS prevention)
- ✅ Session security with timeout
- ✅ HTTPS ready (SSL certificate can be added)
- ✅ Protected directories (config, includes, database)
- ✅ Error logging (production mode)

### Recommendations
1. Change default admin password immediately
2. Use strong database passwords
3. Enable HTTPS with SSL certificate
4. Regular security updates
5. Implement regular backups

## 🐛 Troubleshooting

### Common Issues

**HTTP 500 Error:**
- Check PHP error logs: `/var/log/httpd/error_log`
- Verify database connection
- Check file permissions
- Ensure PHP extensions are installed

**Database Connection Failed:**
- Verify MariaDB is running: `systemctl status mariadb`
- Check database credentials in `config/config.php`
- Ensure database exists and schema is imported

**Permission Denied:**
```bash
sudo chown -R apache:apache /var/www/html
sudo chmod -R 755 /var/www/html
```

**SELinux Issues:**
```bash
sudo setsebool -P httpd_can_network_connect_db 1
```

For more troubleshooting, see [DEPLOYMENT.md](docs/DEPLOYMENT.md)

## 📊 Database Schema

### Main Tables
- **users**: User accounts and authentication
- **medicines**: Medicine inventory
- **suppliers**: Supplier information
- **sales**: Sales transactions
- **sale_items**: Individual sale items
- **purchase_orders**: Purchase order management
- **purchase_order_items**: Purchase order details

## 🤝 Contributing

Contributions are welcome! Please feel free to submit issues or pull requests.

## 📄 License

This project is open source and available for educational purposes.

## 👥 Author

**Anwin Raju George**
- GitHub: [@anwinrajugeorge2028-cyber](https://github.com/anwinrajugeorge2028-cyber)
- Email: anwinrajugeorge2028@bca.ajce.in

## 🙏 Acknowledgments

- Built with modern LAMP stack technologies
- Deployed on AWS EC2 infrastructure
- Bootstrap for responsive UI design
- MariaDB for robust database management

## 📞 Support

For support, email anwinrajugeorge2028@bca.ajce.in or open an issue on GitHub.

---

**Version:** 1.0.0  
**Last Updated:** October 2025  
**Status:** ✅ Production Ready
