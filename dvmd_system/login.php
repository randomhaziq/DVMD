<?php
// login.php - Login page with form handling
session_start();

// Include auth functions
require_once 'api/auth.php';

// If user is already logged in, redirect to index
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        $result = Auth::login($email, $password);
        if ($result['status'] === 'success') {
            // Store user in session
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['user_data'] = $result['user'];
            $_SESSION['logged_in'] = true;
            $_SESSION['user_role'] = $result['user']['role']; // CRITICAL: Set the role!
            
            // Redirect to dashboard (router will decide which dashboard based on role)
            header('Location: dashboard.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DVMD - Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h1>DVMD</h1>
            <p>Digital Village Management Dashboard</p>
        </div>

        <div class="login-body">
            <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="text" class="form-control" id="email" name="email" placeholder="Enter email" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Enter password" required>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>

        <div class="login-footer">
            <p>Emergency and Disaster Management System</p>
            <p>Compliant with PDPA 2010 & MyGovCloud Standards</p>
        </div>
    </div>
</body>

</html>