
<?php
session_start();

require_once __DIR__ . '/config/database.php';

$errors = [];
$name = '';
$email = '';
$contact = '';
$success = false;

// Create CSRF security token
if (empty($_SESSION['register_token'])) {
    $_SESSION['register_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $contact = trim($_POST['contact_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    // CSRF validation
    if (!hash_equals($_SESSION['register_token'], $token)) {
        $errors[] = 'Invalid request. Please try again.';
    }

    // Server-side validation
    if ($name === '' || strlen($name) > 100) {
        $errors[] = 'Please enter a valid full name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($email) > 150) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!preg_match('/^\+?[0-9]{9,15}$/', $contact)) {
        $errors[] = 'Please enter a valid contact number.';
    }

    if (strlen($password) < 8 || strlen($password) > 72) {
        $errors[] = 'Password must contain 8 to 72 characters.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {

        try {

            // Check duplicate email
            $stmt = $pdo->prepare(
                "SELECT user_id FROM users WHERE email = ?"
            );

            $stmt->execute([$email]);

            if ($stmt->fetch()) {

                $errors[] = 'This email is already registered.';

            } else {

                // Secure password hashing
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Save customer account
                $stmt = $pdo->prepare(
                    "INSERT INTO users
                    (name, email, contact_number, password, role)
                    VALUES (?, ?, ?, ?, 'customer')"
                );

                $stmt->execute([
                    $name,
                    $email,
                    $contact,
                    $hashedPassword
                ]);

                $_SESSION['registration_success'] = true;

                unset($_SESSION['register_token']);

                header('Location: register.php');
                exit;
            }

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {
                $errors[] = 'This email is already registered.';
            } else {
                error_log($e->getMessage());
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}

if (!empty($_SESSION['registration_success'])) {
    $success = true;
    unset($_SESSION['registration_success']);
}

function e($value) {
    return htmlspecialchars(
        $value,
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

    <title>Create Account | Shanu Haven</title>

    <link rel="stylesheet"
          href="assets/css/customer-auth.css">

</head>

<body class="customer-auth-page">

<main class="auth-container">

    <div class="auth-brand">
        <h1>SHANU HAVEN</h1>
        <p>COASTAL VILLA</p>
    </div>

    <section class="auth-card">

        <?php if ($success): ?>

            <div class="success-icon">&#10003;</div>

            <h2>Registration Successful!</h2>

            <p class="auth-subtitle">
                Your account has been created successfully.
            </p>

            <a href="index.php" class="auth-button">
                Back to Home
            </a>

        <?php else: ?>

            <h2>Create Account</h2>

            <p class="auth-subtitle">
                Your coastal escape begins here.
            </p>

            <?php if (!empty($errors)): ?>

                <div class="error-message" role="alert">

                    <?php foreach ($errors as $error): ?>

                        <p><?= e($error) ?></p>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <form method="POST"
                  action="register.php"
                  id="registerForm">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= e($_SESSION['register_token']) ?>">

                <div class="form-group">

                    <label for="name">Full Name</label>

                    <input type="text"
                           id="name"
                           name="name"
                           placeholder="Enter your full name"
                           value="<?= e($name) ?>"
                           maxlength="100"
                           autocomplete="name"
                           required>

                </div>

                <div class="form-group">

                    <label for="email">Email Address</label>

                    <input type="email"
                           id="email"
                           name="email"
                           placeholder="Enter your email"
                           value="<?= e($email) ?>"
                           maxlength="150"
                           autocomplete="email"
                           required>

                </div>

                <div class="form-group">

                    <label for="contact_number">
                        Contact Number
                    </label>

                    <input type="tel"
                           id="contact_number"
                           name="contact_number"
                           placeholder="Enter your phone number"
                           value="<?= e($contact) ?>"
                           pattern="\+?[0-9]{9,15}"
                           maxlength="16"
                           autocomplete="tel"
                           required>

                </div>

                <div class="form-group">

                    <label for="password">Password</label>

                    <div class="password-wrapper">

                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Minimum 8 characters"
                               minlength="8"
                               maxlength="72"
                               autocomplete="new-password"
                               required>

                        <button type="button"
                                class="toggle-password"
                                data-target="password">
                            Show
                        </button>

                    </div>

                </div>

                <div class="form-group">

                    <label for="confirm_password">
                        Confirm Password
                    </label>

                    <div class="password-wrapper">

                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               placeholder="Confirm your password"
                               minlength="8"
                               maxlength="72"
                               autocomplete="new-password"
                               required>

                        <button type="button"
                                class="toggle-password"
                                data-target="confirm_password">
                            Show
                        </button>

                    </div>

                </div>

                <p id="formError"
                   class="client-error"
                   role="alert"></p>

                <button type="submit" class="auth-button">
                    Create Account
                </button>

            </form>

          <p class="auth-footer">

    Already have an account?

    <a href="login.php">Login</a>

</p>  

        <?php endif; ?>

    </section>

    <a href="login.php" class="auth-button">
    Go to Login
</a>

</main>

<script src="assets/js/customer-auth.js"></script>

</body>
</html>
