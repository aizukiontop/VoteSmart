<?php
session_start();
require_once __DIR__ . '/_sql/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$election_id = $_POST['election_id'] ?? null;

if (!$election_id) {
    die("Invalid election.");
}

// ✅ Check if election exists and is ongoing
$stmt = $pdo->prepare("SELECT * FROM elections WHERE election_id = ? AND status = 'Ongoing'");
$stmt->execute([$election_id]);
$election = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$election) {
    die("Invalid election or election not active.");
}

// ✅ Prevent double voting
$stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE user_id = ? AND election_id = ?");
$stmt->execute([$user_id, $election_id]);
if ($stmt->fetchColumn() > 0) {
    header("Location: already_voted.php");
    exit;
}

// ✅ Record votes
if (isset($_POST['vote']) && is_array($_POST['vote'])) {
    foreach ($_POST['vote'] as $position_id => $candidate_id) {
        $insert = $pdo->prepare("INSERT INTO votes (user_id, candidate_id, election_id, position_id) VALUES (?, ?, ?, ?)");
        $insert->execute([$user_id, $candidate_id, $election_id, $position_id]);
    }
}

// ✅ Redirect to dashboard after successful voting
header("Location: dashboard.php");
exit;
?>
