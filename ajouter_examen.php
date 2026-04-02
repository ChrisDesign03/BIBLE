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
    echo "Accès refusé.";
    exit;
}

$id_infirmier = $_SESSION['user_id'];
$errors = [];

// Récupérer les consultations associées à cet infirmier (via le médecin)
$stmt = $pdo->prepare("
    SELECT c.id_consultation, p.nom, p.prenom, c.date_consultation
    FROM Consultation c
    JOIN Patient p ON c.id_patient = p.id_patient
    ORDER BY c.date_consultation DESC
");
$stmt->execute();
$consultations = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_consultation = $_POST['id_consultation'] ?? null;
    $date_examen = $_POST['date_examen'] ?? null;
    $type_examen = trim($_POST['type_examen'] ?? '');
    $resultats = trim($_POST['resultats'] ?? '');
    $statut = $_POST['statut'] ?? '';

    if (!$id_consultation || !$date_examen || !$type_examen || !in_array($statut, ['en attente', 'réalisé'])) {
        $errors[] = "Tous les champs obligatoires doivent être remplis correctement.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO Examen (id_consultation, id_infirmier, type_examen, date_examen, resultats, statut)
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id_consultation, $id_infirmier, $type_examen, $date_examen, $resultats, $statut]);

        // Redirection vers la liste des examens après succès
        header("Location: liste_examens.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter un examen</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            color: #1e293b;
            margin-top: 40px;
        }
        form {
            max-width: 700px;
            margin: 40px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.05);
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #374151;
        }
        select, input, textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background-color: #f9fafb;
            transition: border-color 0.3s;
        }
        select:focus, input:focus, textarea:focus {
            border-color: #3b82f6;
            outline: none;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        button {
            background-color: #d97706;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #b45309;
        }
        .error {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<h2>Ajouter un nouvel examen</h2>

<?php if (!empty($errors)): ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST">
    <label for="id_consultation">Consultation *</label>
    <select name="id_consultation" id="id_consultation" required>
        <option value="">-- Sélectionner une consultation --</option>
        <?php foreach ($consultations as $c): ?>
            <option value="<?= $c['id_consultation'] ?>">
                <?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?> - <?= htmlspecialchars($c['date_consultation']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="date_examen">Date de l'examen *</label>
    <input type="date" name="date_examen" id="date_examen" required value="<?= date('Y-m-d') ?>">

    <label for="type_examen">Type d'examen *</label>
    <input type="text" name="type_examen" id="type_examen" required placeholder="Exemple : Analyse sanguine">

    <label for="resultats">Résultats</label>
    <textarea name="resultats" id="resultats" placeholder="Saisir les résultats si disponibles..."></textarea>

    <label for="statut">Statut *</label>
    <select name="statut" id="statut" required>
        <option value="en attente">En attente</option>
        <option value="réalisé">Réalisé</option>
    </select>

    <button type="submit">Enregistrer l'examen</button>
</form>

</body>
</html>
