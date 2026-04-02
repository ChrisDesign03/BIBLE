<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
require_once '../includes/db.php';

$stmt = $pdo->prepare("SELECT role FROM Utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'infirmier') {
    echo "Accès refusé.";
    exit;
}

$id_infirmier = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: liste_examens.php");
    exit;
}

$id_examen = intval($_GET['id']);

// Supprimer seulement si examen appartient à l'infirmier via consultation
$stmt = $pdo->prepare("
    DELETE e FROM Examen e
    JOIN Consultation c ON e.id_consultation = c.id_consultation
    WHERE e.id_examen = ? AND c.id_utilisateur = ?
");
$stmt->execute([$id_examen, $id_infirmier]);

header("Location: liste_examens.php");
exit;
?>
