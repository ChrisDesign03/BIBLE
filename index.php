<?php
session_start();
require_once 'includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id_utilisateur'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    } else {
        $message = "❌ Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Gestion Hospitalière</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(to right, #2563eb, #1e3a8a);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .login-container {
            max-width: 420px;
            margin: 80px auto;
            background: white;
            border-radius: 12px;
            padding: 40px 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #1e3a8a;
        }

        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        .form-group i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        .login-container button {
            width: 100%;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .login-container button:hover {
            background: #1e40af;
        }

        .message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }

        @media (max-width: 500px) {
            .login-container {
                margin: 40px 20px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Connexion</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <input type="email" name="email" placeholder="Adresse Email" required>
            <i class="fas fa-envelope"></i>
        </div>

        <div class="form-group">
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
            <i class="fas fa-lock"></i>
        </div>

        <button type="submit">Se connecter</button>
    </form>
</div>

</body>
</html>
