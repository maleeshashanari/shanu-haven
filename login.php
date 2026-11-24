<?php

session_start();

require_once __DIR__ . '/config/database.php';

$errors = [];
$email = '';

// Already logged-in customer
if (!empty($_SESSION['customer_id'])) {
    header('Location: index.php');
    exit;
}

// Create CSRF token
if (empty($_SESSION['login_token'])) {
    $_SESSION['login_token'] = bin2hex(random_bytes(32));
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    // CSRF validation
    if (!hash_equals($_SESSION['login_token'], $token)) {
        $errors[] = 'Invalid request. Please try again.';
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($email) > 150) {

        $errors[] = 'Please enter a valid email address.';
    }

    // Password validation
    if ($password === '' || strlen($password) > 72) {
        $errors[] = 'Please enter a valid password.';
    }

    if (empty($errors)) {

        try {

            // Find registered customer
            $stmt = $pdo->prepare(
                "SELECT user_id, name, email, password
                 FROM users
                 WHERE email = ?
                 AND role = 'customer'
                 LIMIT 1"
            );

            $stmt->execute([$email]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify hashed password
            if ($user && password_verify(
                $password,
                $user['password']
            )) {

                // Prevent session fixation
                session_regenerate_id(true);

                // Store customer session
                $_SESSION['customer_id'] = $user['user_id'];
                $_SESSION['customer_name'] = $user['name'];
                $_SESSION['customer_email'] = $user['email'];

                unset($_SESSION['login_token']);

                // Redirect to home page
                header('Location: index.php');
                exit;

            } else {

                $errors[] = 'Invalid email or password.';

            }

        } catch (PDOException $e) {

            error_log($e->getMessage());

            $errors[] = 'Login failed. Please try again.';

        }
    }
}

// Escape HTML output
function e($value) {

    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Customer Login | Shanu Haven</title>

    <link rel="stylesheet"
          href="assets/css/customer-auth.css">

</head>

<body class="customer-auth-page">

<main class="auth-container login-container">

    <div class="auth-brand">

        <h1>SHANU HAVEN</h1>

        <p>COASTAL VILLA</p>

    </div>

    <section class="auth-card login-card">

        <h2>Welcome Back</h2>

        <p class="auth-subtitle">
            Your coastal escape awaits.
        </p>

        <?php if (!empty($errors)): ?>

            <div class="error-message" role="alert">

                <?php foreach ($errors as $error): ?>

                    <p><?= e($error) ?></p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <form method="POST"
              action="login.php"
              id="loginForm">

            <input type="hidden"
                   name="csrf_token"
                   value="<?= e($_SESSION['login_token']) ?>">

            <!-- Email Address -->

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input type="email"
                       id="email"
                       name="email"
                       placeholder="Enter your email"
                       value="<?= e($email) ?>"
                       maxlength="150"
                       autocomplete="email"
                       required>

            </div>

            <!-- Password -->

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <div class="password-wrapper">

                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Enter your password"
                           maxlength="72"
                           autocomplete="current-password"
                           required>

                    <button type="button"
                            class="toggle-password"
                            data-target="password">

                        Show

                    </button>

                </div>

            </div>

            <p id="loginError"
               class="client-error"
               role="alert"></p>

            <!-- Login Button -->

            <button type="submit" class="auth-button">

                Login

            </button>

        </form>

        <!-- Registration Link -->

        <p class="auth-footer">

            Don't have an account?

            <a href="register.php">
                Create Account
            </a>

        </p>

    </section>

    <a href="index.php" class="back-home">

        &larr; Back to Home

    </a>

</main>

<script src="assets/js/customer-auth.js"></script>

</body>
</html>