<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    
    $conn = new mysqli("localhost", "root", "", "admins");
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if reset_token column exists
    $check_columns = $conn->query("SHOW COLUMNS FROM admin LIKE 'reset_token'");
    if ($check_columns->num_rows == 0) {
        // Add the columns if they don't exist
        $conn->query("ALTER TABLE admin ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
        $conn->query("ALTER TABLE admin ADD COLUMN reset_expires DATETIME DEFAULT NULL");
    }
    
    // Check if username exists
    $sql = "SELECT * FROM admin WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Update database with reset token
        $sql = "UPDATE admin SET reset_token = '$token', reset_expires = '$expires' WHERE username = '$username'";
        $conn->query($sql);
        
        // Update the success message format
        $reset_url = "reset-password.php?token=" . $token;
        $success_message = '<div class="token-container">
            <p class="token-label">Your password reset token is:</p>
            <div class="token-value" id="token">'. $token .'</div>
            <div class="token-actions">
                <button onclick="copyToken()" class="btn btn-outline">
                    <i class="fas fa-copy"></i>
                    Copy Token
                </button>
                <a href="'. $reset_url .'" class="btn btn-primary">
                    <i class="fas fa-key"></i>
                    Reset Password
                </a>
            </div>
            <p class="token-expiry">This token will expire in 1 hour.</p>
        </div>';
    } else {
        $error_message = "No account found with that username.";
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Clothing Store Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
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
            padding: 2rem;
        }

        .forgot-container {
            background: var(--surface);
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            width: 100%;
            max-width: 400px;
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .forgot-header i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .forgot-header h1 {
            font-size: 1.875rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .forgot-header p {
            color: var(--text-light);
            font-size: 0.875rem;
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
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .forgot-footer {
            margin-top: 1.5rem;
            text-align: center;
        }

        .forgot-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-footer a:hover {
            text-decoration: underline;
        }

        .success-message {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .token-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .token-label {
            color: var(--text);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .token-value {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            padding: 1rem;
            border-radius: 0.5rem;
            font-family: monospace;
            font-size: 0.875rem;
            word-break: break-all;
            max-width: 100%;
            text-align: center;
        }

        .token-expiry {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .token-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            width: 100%;
        }

        .token-actions .btn {
            flex: 1;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text);
            text-decoration: none;
        }

        .btn-primary {
            text-decoration: none;
        }

        .error-message {
            background: rgba(239,68,68,0.1);
            color: var(--danger);
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <i class="fas fa-key"></i>
            <h1>Forgot Password</h1>
            <p>Enter your username to reset your password.</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($success_message)): ?>
            <form method="post">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                    Send Reset Link
                </button>
            </form>
        <?php endif; ?>

        <div class="forgot-footer">
            <p>Remember your password? <a href="login.html">Back to Login</a></p>
        </div>
    </div>
    <script>
        function copyToken() {
            const token = document.getElementById('token').innerText;
            navigator.clipboard.writeText(token).then(() => {
                alert('Token copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy token: ', err);
            });
        }
    </script>
</body>
</html>