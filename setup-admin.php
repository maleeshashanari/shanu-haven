
<?php
require_once __DIR__ . '/config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        $name === '' ||
        strlen($name) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150 ||
        strlen($password) < 12
    ) {
        $message = 'Enter valid details and a password of at least 12 characters.';
    } else {
        $hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO admins
                 (full_name, email, password_hash)
                 VALUES (?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $email,
                $hash
            ]);

            $message = 'Admin account created. Delete setup-admin.php now.';

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $message = 'Account could not be created. The email may already exist.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, initial-scale=1">
<title>Admin Setup | Shanu Haven</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #F7F4ED;
    display: grid;
    place-items: center;
    min-height: 100vh;
    margin: 0;
}

form {
    background: white;
    padding: 35px;
    border-radius: 16px;
    width: min(400px, 90%);
    box-shadow: 0 10px 30px #12343B22;
}

h2 {
    color: #12343B;
}

input, button {
    box-sizing: border-box;
    display: block;
    width: 100%;
    padding: 13px;
    margin: 12px 0;
    border-radius: 8px;
}

input {
    border: 1px solid #D7B98E;
}

button {
    background: #12343B;
    color: white;
    border: 0;
    cursor: pointer;
}

p {
    line-height: 1.6;
    color: #2D6468;
}
</style>
</head>
<body>

<form method="POST">
    <h2>Create Admin Account</h2>

    <?php if ($message): ?>
        <p>
            <?= htmlspecialchars(
                $message,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>
    <?php endif; ?>

    <input
        type="text"
        name="full_name"
        placeholder="Admin Full Name"
        maxlength="100"
        required>

    <input
        type="email"
        name="email"
        placeholder="Admin Email"
        maxlength="150"
        required>

    <input
        type="password"
        name="password"
        placeholder="Strong Password"
        minlength="12"
        autocomplete="new-password"
        required>

    <button type="submit">
        Create Admin
    </button>
</form>

</body>
</html>
