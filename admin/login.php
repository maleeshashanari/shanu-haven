
<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$email = '';

if (empty($_SESSION['admin_login_csrf'])) {
    $_SESSION['admin_login_csrf'] =
        bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals(
            $_SESSION['admin_login_csrf'],
            $token
        )
    ) {
        $error = 'Invalid request. Please try again.';

    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150 ||
        !is_string($password) ||
        $password === ''
    ) {
        $error = 'Invalid email or password.';

    } else {

        $stmt = $pdo->prepare(
            'SELECT admin_id, full_name, password_hash
             FROM admins
             WHERE email = ?
             LIMIT 1'
        );

        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if (
            $admin &&
            password_verify(
                $password,
                $admin['password_hash']
            )
        ) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] =
                (int) $admin['admin_id'];

            $_SESSION['admin_name'] =
                $admin['full_name'];

            unset($_SESSION['admin_login_csrf']);

            header('Location: dashboard.php');
            exit;

        } else {
            $error = 'Invalid email or password.';
        }
    }
}

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

<title>Admin Login | Shanu Haven</title>

<link rel="stylesheet"
      href="../assets/css/admin.css?v=1">
</head>

<body class="admin-login-page">

<header class="admin-login-header">

    <a href="../index.php" class="admin-logo">
        <img
            src="../assets/images/shanu-logo.png"
            alt="Shanu Haven">
    </a>

    <a href="../index.php"
       class="back-home">
        &larr; Back to Website
    </a>

</header>

<main class="login-main">

    <section class="login-card">

        <div class="login-brand">
            <span class="brand-label">
                SHANU HAVEN COASTAL VILLA
            </span>

            <h1>Welcome Back</h1>

            <p>
                Sign in to your admin dashboard
            </p>
        </div>

        <?php if ($error): ?>
            <div class="login-error"
                 role="alert">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="login.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(
                    $_SESSION['admin_login_csrf']
                ) ?>">

            <div class="login-field">

                <label for="email">
                    Email Address
                </label>

                <div class="input-wrapper">

                    <span class="input-icon"
                          aria-hidden="true">
                        ✉
                    </span>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter admin email"
                        value="<?= e($email) ?>"
                        autocomplete="username"
                        maxlength="150"
                        required>

                </div>
            </div>

            <div class="login-field">

                <label for="password">
                    Password
                </label>

                <div class="input-wrapper">

                    <span class="input-icon"
                          aria-hidden="true">
                        &#128274;
                    </span>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required>

                    <button
                        type="button"
                        id="togglePassword"
                        class="password-toggle"
                        aria-label="Show password"
                        aria-pressed="false">
                        Show
                    </button>

                </div>
            </div>

            <button
                type="submit"
                class="admin-login-btn">
                Sign In to Dashboard
            </button>

        </form>

        <div class="login-footer">
            <p>
                Authorized administrators only
            </p>
        </div>

    </section>

</main>

<script>
const toggleButton =
    document.getElementById('togglePassword');

const passwordInput =
    document.getElementById('password');

toggleButton.addEventListener('click', () => {

    const isHidden =
        passwordInput.type === 'password';

    passwordInput.type =
        isHidden ? 'text' : 'password';

    toggleButton.textContent =
        isHidden ? 'Hide' : 'Show';

    toggleButton.setAttribute(
        'aria-label',
        isHidden ? 'Hide password' : 'Show password'
    );

    toggleButton.setAttribute(
        'aria-pressed',
        String(isHidden)
    );
});
</script>

</body>
</html> 
