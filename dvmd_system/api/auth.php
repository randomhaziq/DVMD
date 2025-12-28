<?php
require_once 'dbconnect.php';

class Auth {
    
    public static function login($email, $password) {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error];
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

        if (!password_verify($password, $user['password'])) {
            return ['status' => 'error', 'message' => 'Invalid credentials'];
        }

        unset($user['password']);

        return [
            'status' => 'success',
            'user' => $user
            // REMOVED: 'redirect' => self::getRoleDashboard($user['role'])
        ];
    }
    
    // Keep other methods but don't use redirectToRoleDashboard in login flow
    public static function getRoleDashboard($role) {
        $dashboards = [
            'citizen'          => 'views/citizen/dashboard.php',
            'ketua kampung'    => 'views/ketua_kampung/dashboard.php',
            'penghulu'         => 'views/penghulu/dashboard.php',
            'district'         => 'views/district/dashboard.php',
            'hq'          => 'views/hq/dashboard.php'
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
    
    // Only use this for manual redirects, not for login
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