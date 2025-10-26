# API Documentation - Kottackal Medical Store

## Overview

This document describes the internal API structure and database interactions for the Kottackal Medical Store Management System.

## Database Connection

### Connection Class: `Database`
Location: `/includes/database.php`

```php
$database = new Database();
$db = $database->getConnection();
```

**Features:**
- PDO-based connection
- UTF-8 character set support
- Error handling with exceptions
- Prepared statements support

## Authentication

### Auth Class
Location: `/includes/auth.php`

#### Methods

**`login($username, $password)`**
- Authenticates user credentials
- Returns: `boolean`
- Creates session on success

**`logout()`**
- Destroys current session
- Clears session data

**`isLoggedIn()`**
- Checks if user is authenticated
- Validates session timeout
- Returns: `boolean`

**`requireLogin()`**
- Redirects to login if not authenticated
- Used in protected pages

**`hasRole($role)`**
- Checks if user has specific role
- Roles: `admin`, `pharmacist`, `cashier`
- Returns: `boolean`

**`getUserId()`**
- Returns current user's ID
- Returns: `int|null`

## Database Schema

### Tables

#### users
```sql
- user_id (INT, PRIMARY KEY)
- username (VARCHAR(50), UNIQUE)
- password (VARCHAR(255))
- full_name (VARCHAR(100))
- email (VARCHAR(100))
- role (ENUM: admin, pharmacist, cashier)
- is_active (BOOLEAN)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### medicines
```sql
- medicine_id (INT, PRIMARY KEY)
- name (VARCHAR(200))
- generic_name (VARCHAR(200))
- category (VARCHAR(100))
- manufacturer (VARCHAR(200))
- unit_price (DECIMAL(10,2))
- quantity_in_stock (INT)
- reorder_level (INT)
- expiry_date (DATE)
- description (TEXT)
- is_active (BOOLEAN)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### sales
```sql
- sale_id (INT, PRIMARY KEY)
- sale_date (TIMESTAMP)
- customer_name (VARCHAR(100))
- customer_phone (VARCHAR(20))
- total_amount (DECIMAL(10,2))
- discount (DECIMAL(10,2))
- tax (DECIMAL(10,2))
- final_amount (DECIMAL(10,2))
- payment_method (ENUM: cash, card, upi, other)
- cashier_id (INT, FOREIGN KEY -> users)
```

#### sale_items
```sql
- item_id (INT, PRIMARY KEY)
- sale_id (INT, FOREIGN KEY -> sales)
- medicine_id (INT, FOREIGN KEY -> medicines)
- quantity (INT)
- unit_price (DECIMAL(10,2))
- total_price (DECIMAL(10,2))
```

#### suppliers
```sql
- supplier_id (INT, PRIMARY KEY)
- name (VARCHAR(200))
- contact_person (VARCHAR(100))
- phone (VARCHAR(20))
- email (VARCHAR(100))
- address (TEXT)
- is_active (BOOLEAN)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### purchase_orders
```sql
- order_id (INT, PRIMARY KEY)
- supplier_id (INT, FOREIGN KEY -> suppliers)
- order_date (DATE)
- total_amount (DECIMAL(10,2))
- status (ENUM: pending, received, cancelled)
- created_by (INT, FOREIGN KEY -> users)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### purchase_order_items
```sql
- item_id (INT, PRIMARY KEY)
- order_id (INT, FOREIGN KEY -> purchase_orders)
- medicine_id (INT, FOREIGN KEY -> medicines)
- quantity (INT)
- unit_price (DECIMAL(10,2))
- total_price (DECIMAL(10,2))
```

## Utility Functions

Location: `/includes/functions.php`

### Security Functions

**`sanitize_input($data)`**
- Sanitizes user input
- Prevents XSS attacks
- Returns: `string|array`

**`generate_csrf_token()`**
- Generates CSRF token
- Stores in session
- Returns: `string`

**`verify_csrf_token($token)`**
- Verifies CSRF token
- Returns: `boolean`

### Formatting Functions

**`format_currency($amount)`**
- Formats number as currency (₹)
- Returns: `string`

**`format_date($date)`**
- Formats date as DD/MM/YYYY
- Returns: `string`

**`format_datetime($datetime)`**
- Formats datetime
- Returns: `string`

### Response Functions

**`redirect_with_message($url, $message, $type)`**
- Redirects with flash message
- Types: `success`, `error`, `warning`, `info`

**`display_flash_message()`**
- Displays stored flash message
- Auto-clears after display

**`json_response($data, $status_code)`**
- Sends JSON response
- Sets appropriate headers

## Form Operations

### Medicine Management

**Add Medicine (POST to medicines.php)**
```php
$_POST = [
    'action' => 'add',
    'name' => 'Medicine Name',
    'generic_name' => 'Generic Name',
    'category' => 'Category',
    'manufacturer' => 'Manufacturer',
    'unit_price' => 10.50,
    'quantity_in_stock' => 100,
    'reorder_level' => 20,
    'expiry_date' => '2026-12-31',
    'description' => 'Description'
];
```

**Edit Medicine (POST to medicines.php)**
```php
$_POST = [
    'action' => 'edit',
    'medicine_id' => 1,
    // ... same fields as add
];
```

**Delete Medicine (POST to medicines.php)**
```php
$_POST = [
    'action' => 'delete',
    'medicine_id' => 1
];
```

### Sales Processing

**Create Sale (POST to sales.php)**
```php
$_POST = [
    'action' => 'add_sale',
    'customer_name' => 'Customer Name',
    'customer_phone' => '1234567890',
    'payment_method' => 'cash',
    'discount' => 0,
    'tax' => 0,
    'items' => json_encode([
        [
            'medicine_id' => 1,
            'quantity' => 2,
            'price' => 10.50
        ]
    ])
];
```

## Security Features

### Input Validation
- All user inputs are sanitized
- HTML special characters escaped
- SQL injection prevented via prepared statements

### Password Security
- Passwords hashed with bcrypt
- Cost factor: 10
- Salted automatically

### Session Security
- HttpOnly cookies
- Session timeout: 1 hour (configurable)
- Session regeneration on login

### Database Security
- Prepared statements with PDO
- Named parameters
- No direct SQL concatenation

### File Security
- Protected directories (.htaccess)
- No directory listing
- Hidden files protected

## Error Handling

### Production Mode
```php
display_errors = Off
log_errors = On
error_log = /var/www/html/logs/php_errors.log
```

### Error Pages
- 404: Page not found
- 500: Internal server error
- 403: Unauthorized access

## Configuration

### Database Configuration
File: `/config/config.php`

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kottackal_medical_store');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

### Application Settings
```php
define('APP_NAME', 'Kottackal Medical Store');
define('SESSION_TIMEOUT', 3600);
date_default_timezone_set('Asia/Kolkata');
```

## Best Practices

1. **Always use prepared statements** for database queries
2. **Sanitize all user inputs** before display
3. **Check authentication** on protected pages
4. **Log errors** instead of displaying in production
5. **Use transactions** for multi-step operations
6. **Validate data** before processing
7. **Handle exceptions** gracefully
8. **Keep sessions secure** with timeout

## Support

For technical support or questions about the API, contact the development team.
