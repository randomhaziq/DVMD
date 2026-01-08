<?php
require_once 'dbconnect.php';

class Auth {
    
    // 1. Helper function for Email Verification (Regex/Filter Check)
    private static function validateEmailFormat($email) {
        
        
        //Manual Regex
        // This checks for basic format: text + @ + text + . + text
        if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
            return false;
        }

        return true;
    }

    public static function login($email, $password) {
        global $conn;

        // --- SECURITY IMPROVEMENT 1: SANITIZATION ---
        // Remove extra spaces from inputs
        $email = trim($email);
        
        // --- SECURITY IMPROVEMENT 2: REG CHECK / VALIDATION ---
        // Check if email format is valid before even asking the database
        if (empty($email) || !self::validateEmailFormat($email)) {
            return ['status' => 'error', 'message' => 'Invalid email format (missing @, .com, etc.)'];
        }

        // --- SECURITY IMPROVEMENT 3: PREPARED STATEMENTS (SQL Injection Prevention) ---
        // Your existing code here is already secure. 
        // The '?' placeholder ensures input is treated as data, not executable code.
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        
        if (!$stmt) {
            // Log this error internally in production, don't expose DB details to user
            error_log('Database Prepare Failed: ' . $conn->error); 
            return ['status' => 'error', 'message' => 'System error. Please try again later.'];
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['status' => 'error', 'message' => 'Invalid credentials'];
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify Password
        if (!password_verify($password, $user['password'])) {
            return ['status' => 'error', 'message' => 'Invalid credentials'];
        }

        // Remove sensitive data before returning
        unset($user['password']);

        return [
            'status' => 'success',
            'user' => $user
        ];
    }
    
    // ... [Keep your existing helper methods below: getRoleDashboard, etc.] ...
    
    public static function getRoleDashboard($role) {
        $dashboards = [
            'citizen'          => 'views/citizen/dashboard.php',
            'ketua kampung'    => 'views/ketua_kampung/dashboard.php',
            'penghulu'         => 'views/penghulu/dashboard.php',
            'district'         => 'views/district/dashboard.php',
            'hq'               => 'views/hq/dashboard.php'
        ];
        
        return $dashboards[$role] ?? 'views/citizen/dashboard.php';
    }
    
    public static function getRoleHierarchy() {
        return [
            'citizen' => 1,
            'ketua kampung' => 2,
            'penghulu' => 3,
            'district' => 4,
            'hq' => 5
        ];
    }
    
    public static function canAccess($userRole, $requiredRole) {
        $hierarchy = self::getRoleHierarchy();
        return $hierarchy[$userRole] >= $hierarchy[$requiredRole];
    }
    
    public static function redirectToRoleDashboard($role) {
        $dashboard = self::getRoleDashboard($role);
        header("Location: $dashboard");
        exit();
    }
    
    public static function getRoleName($role) {
        $names = [
            'citizen' => 'Penduduk',
            'ketua kampung' => 'Ketua Kampung',
            'penghulu' => 'Penghulu',
            'district' => 'District Office',
            'hq' => 'KPLB HQ'
        ];
        
        return $names[$role] ?? $role;
    }
}
?>