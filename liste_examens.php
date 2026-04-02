<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/db.php';

// Vérifier rôle infirmier
$stmt = $pdo->prepare("SELECT role FROM Utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'infirmier') {
    echo "⛔ Accès refusé. Réservé aux infirmiers.";
    exit;
}

$id_infirmier = $_SESSION['user_id'];

// Récupérer les examens associés à l'infirmier connecté
$stmt = $pdo->prepare("
    SELECT e.*, p.nom AS nom_patient, p.prenom AS prenom_patient
    FROM Examen e
    JOIN Consultation c ON e.id_consultation = c.id_consultation
    JOIN Patient p ON c.id_patient = p.id_patient
    WHERE e.id_infirmier = ?
    ORDER BY e.date_examen DESC
");
$stmt->execute([$id_infirmier]);
$examens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Mes examens</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #d97706;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        header .logout-btn {
            background: #b45309;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            font-size: 14px;
        }

        header .logout-btn:hover {
            background: #92400e;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .actions {
            text-align: right;
            margin-bottom: 20px;
        }

        .btn {
            background-color: #d97706;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            background-color: #b45309;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #fbbf24;
            color: #1f2937;
        }

        tr:nth-child(even) {
            background-color: #fefce8;
        }

        a.action-link {
            color: #2563eb;
            margin-right: 10px;
            text-decoration: none;
            font-weight: 600;
        }

        a.action-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        Liste des examens
        <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </header>

    <div class="container">
        <div class="actions">
            <a href="ajouter_examen.php" class="btn"><i class="fas fa-plus"></i> Ajouter un examen</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Type d'examen</th>
                    <th>Statut</th>
                    <th>Résultats</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($examens): ?>
                    <?php foreach ($examens as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['date_examen']) ?></td>
                            <td><?= htmlspecialchars($e['nom_patient'] . ' ' . $e['prenom_patient']) ?></td>
                            <td><?= htmlspecialchars($e['type_examen']) ?></td>
                            <td><?= htmlspecialchars($e['statut'] ?? 'N/A') ?></td>
                            <td><?= nl2br(htmlspecialchars($e['resultats'])) ?></td>
                            <td>
                                <a class="action-link" href="modifier_examen.php?id=<?= $e['id_examen'] ?>">Modifier</a>
                                <a class="action-link" href="supprimer_examen.php?id=<?= $e['id_examen'] ?>" onclick="return confirm('Confirmer la suppression ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;">Aucun examen trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
