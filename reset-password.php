<?php
session_start();

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['token'])) {
    header("Location: login.html");
    exit();
}

$token = $_GET['token'];
$error_message = null;
$success_message = null;

$conn = new mysqli("localhost", "root", "", "admins");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if token exists and is valid
$check_token_sql = "SELECT Username FROM admin WHERE reset_token = ? AND reset_expires > NOW()";
$check_stmt = $conn->prepare($check_token_sql);
$check_stmt->bind_param("s", $token);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $error_message = "Invalid or expired reset link. Please request a new one.";
} else {
    $user = $result->fetch_assoc();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($password) || strlen($password) < 6) {
            $error_message = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } else {
            // Update the password directly in MySQL
            $update_sql = "UPDATE admin SET 
                password = ?, 
                reset_token = NULL, 
                reset_expires = NULL 
                WHERE reset_token = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $password, $token);
            
            if ($update_stmt->execute()) {
                $success_message = "Password updated successfully!";
                echo "<script>
                    alert('Password reset successful! Please login with your new password.');
                    window.location.href = 'login.html';
                </script>";
                exit();
            } else {
                $error_message = "Error updating password. Please try again.";
            }
            $update_stmt->close();
        }
    }
}

$check_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --danger: #ef4444;
            --success: #22c55e;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --border: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--background);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .reset-container {
            background: var(--surface);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .reset-header img {
            width: 48px;
            height: 48px;
            margin-bottom: 1rem;
        }

        .reset-header h1 {
            color: var(--text);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .reset-header p {
            color: #64748b;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        .form-text {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .btn-reset {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-reset:hover {
            background: var(--primary-dark);
        }

        .error-message {
            background: #fef2f2;
            color: var(--danger);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success-message {
            background: #f0fdf4;
            color: var(--success);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <h1>Reset Password</h1>
            <p>Enter your new password below.</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php else: ?>
            <form method="post" action="reset-password.php?token=<?php echo htmlspecialchars($token); ?>" onsubmit="return validateForm()">
                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <div class="form-text">Password must be at least 6 characters long</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn-reset">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                    </svg>
                    Reset Password
                </button>
            </form>
        <?php endif; ?>

        <div class="login-link">
            <p>Remember your password? <a href="login.html">Back to Login</a></p>
        </div>
    </div>

    <script>
    function validateForm() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password.length < 6) {
            alert('Password must be at least 6 characters long');
            return false;
        }
        
        if (password !== confirmPassword) {
            alert('Passwords do not match');
            return false;
        }
        
        return true;
    }

    // Real-time password validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                this.style.borderColor = 'var(--success)';
            } else {
                this.style.borderColor = 'var(--danger)';
            }
        } else {
            this.style.borderColor = 'var(--border)';
        }
    });
    </script>
</body>
</html> 