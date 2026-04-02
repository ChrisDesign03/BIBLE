<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../includes/db.php';

// Vérifier rôle infirmier
$stmt = $pdo->prepare("SELECT role FROM Utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'infirmier') {
    echo "⛔ Accès refusé.";
    exit;
}

$id_infirmier = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: liste_examens.php");
    exit;
}

$id_examen = intval($_GET['id']);

// Récupérer l'examen en joignant Consultation et Patient pour vérifier que l'examen appartient bien à l'infirmier
$stmt = $pdo->prepare("
    SELECT e.*, c.id_utilisateur, p.nom, p.prenom
    FROM Examen e
    JOIN Consultation c ON e.id_consultation = c.id_consultation
    JOIN Patient p ON c.id_patient = p.id_patient
    WHERE e.id_examen = ? AND c.id_utilisateur = ?
");
$stmt->execute([$id_examen, $id_infirmier]);
$examen = $stmt->fetch();

if (!$examen) {
    echo "Examen introuvable ou non autorisé.";
    exit;
}

$errors = [];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_examen = $_POST['date_examen'];
    $type_examen = trim($_POST['type_examen']);
    $resultats = trim($_POST['resultats']);
    $statut = $_POST['statut'];

    if (!$date_examen || !$type_examen || !in_array($statut, ['en attente', 'réalisé'])) {
        $errors[] = "Tous les champs obligatoires doivent être remplis correctement.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE Examen
            SET date_examen = ?, type_examen = ?, resultats = ?, statut = ?
            WHERE id_examen = ?
        ");
        $stmt->execute([$date_examen, $type_examen, $resultats, $statut, $id_examen]);

        $message = "Examen mis à jour avec succès !";

        // Recharger l'examen mis à jour
        $stmt = $pdo->prepare("
            SELECT e.*, c.id_utilisateur, p.nom, p.prenom
            FROM Examen e
            JOIN Consultation c ON e.id_consultation = c.id_consultation
            JOIN Patient p ON c.id_patient = p.id_patient
            WHERE e.id_examen = ? AND c.id_utilisateur = ?
        ");
        $stmt->execute([$id_examen, $id_infirmier]);
        $examen = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Modifier un examen</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            color: #92400e;
            margin-top: 40px;
        }
        form {
            max-width: 700px;
            margin: 40px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.05);
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444444;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background-color: #f9fafb;
            transition: border-color 0.3s;
            font-size: 16px;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #d97706;
            outline: none;
            background-color: #fff;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        button {
            background-color: #d97706;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            width: 100%;
            cursor: pointer;
            font-weight: 700;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #b45309;
        }
        .message {
            max-width: 700px;
            margin: 20px auto;
            background-color: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
            font-weight: 600;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .error {
            max-width: 700px;
            margin: 20px auto;
            background-color: #fee2e2;
            border: 1px solid #b91c1c;
            color: #b91c1c;
            font-weight: 600;
            padding: 15px;
            border-radius: 8px;
        }
        .error ul {
            margin: 0;
            padding-left: 20px;
        }
        .back-link {
            display: block;
            max-width: 700px;
            margin: 10px auto 30px;
            color: #d97706;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>Modifier l'examen de <?= htmlspecialchars($examen['nom'] . ' ' . $examen['prenom']) ?></h2>

<a href="liste_examens.php" class="back-link">← Retour à la liste des examens</a>

<?php if (!empty($errors)): ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" novalidate>
    <label for="date_examen">Date de l'examen *</label>
    <input type="date" name="date_examen" id="date_examen" required value="<?= htmlspecialchars($examen['date_examen']) ?>">

    <label for="type_examen">Type d'examen *</label>
    <input type="text" name="type_examen" id="type_examen" required value="<?= htmlspecialchars($examen['type_examen']) ?>">

    <label for="resultats">Résultats</label>
    <textarea name="resultats" id="resultats"><?= htmlspecialchars($examen['resultats']) ?></textarea>

    <label for="statut">Statut *</label>
    <select name="statut" id="statut" required>
        <option value="en attente" <?= $examen['statut'] === 'en attente' ? 'selected' : '' ?>>En attente</option>
        <option value="réalisé" <?= $examen['statut'] === 'réalisé' ? 'selected' : '' ?>>Réalisé</option>
    </select>

    <button type="submit">Mettre à jour</button>
</form>

</body>
</html>
