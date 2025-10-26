<?php
// Authentication and session management

class Auth {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Start session if not already started
    public function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Login user
    public function login($username, $password) {
        try {
            $query = "SELECT user_id, username, password, full_name, role, is_active 
                      FROM users 
                      WHERE username = :username AND is_active = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $user['password'])) {
                    $this->startSession();
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['last_activity'] = time();
                    
                    return true;
                }
            }
            
            return false;
        } catch(PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Logout user
    public function logout() {
        $this->startSession();
        session_unset();
        session_destroy();
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        $this->startSession();
        
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            $this->logout();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    // Check if user has specific role
    public function hasRole($role) {
        $this->startSession();
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    // Check if user has one of the specified roles
    public function hasAnyRole($roles) {
        $this->startSession();
        return isset($_SESSION['role']) && in_array($_SESSION['role'], $roles);
    }
    
    // Get current user ID
    public function getUserId() {
        $this->startSession();
        return $_SESSION['user_id'] ?? null;
    }
    
    // Get current user role
    public function getUserRole() {
        $this->startSession();
        return $_SESSION['role'] ?? null;
    }
    
    // Require login
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit();
        }
    }
    
    // Require specific role
    public function requireRole($role) {
        $this->requireLogin();
        if (!$this->hasRole($role)) {
            header('Location: /unauthorized.php');
            exit();
        }
    }
}
?>
