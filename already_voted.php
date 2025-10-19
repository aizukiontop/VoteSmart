<?php
session_start();
require_once __DIR__ . '/_sql/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the most recent or active election
$election = $pdo->query("SELECT * FROM elections WHERE status IN ('ongoing', 'closed') ORDER BY election_id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if (!$election) {
    $election_name = "School of Computing Election 2025";
} else {
    $election_name = htmlspecialchars($election['election_name']);
    $election_id = $election['election_id'];
}

// Fetch the user’s votes
$stmt = $pdo->prepare("
    SELECT p.position_name, c.candidate_name
    FROM votes v
    JOIN candidates c ON v.candidate_id = c.candidate_id
    JOIN positions p ON c.position_id = p.position_id
    WHERE v.user_id = ? AND v.election_id = ?
    ORDER BY p.sort_order ASC
");
$stmt->execute([$user_id, $election_id]);
$votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Vote Summary — <?= $election_name ?></title>
    <link rel="stylesheet" href="_css/_header.css">
    <link rel="stylesheet" href="_css/already_voted.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <main class="summary-container">
        <h2>🗳️ Your Vote Summary</h2>
        <p>Here are the candidates you voted for in the <strong><?= $election_name ?></strong>.</p>

        <?php if ($votes): ?>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>Your Voted Candidate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($votes as $vote): ?>
                        <tr>
                            <td><?= htmlspecialchars($vote['position_name']) ?></td>
                            <td><?= htmlspecialchars($vote['candidate_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="thank-you">
                ✅ Thank you for voting! Your ballot has been recorded.
            </div>

            <div class="buttons">
                <a href="dashboard.php" class="btn primary">View Results</a>
                <a href="logout.php" class="btn secondary">Logout</a>
            </div>

        <?php else: ?>
            <p class="no-vote">You have not voted yet in this election.</p>
        <?php endif; ?>
    </main>
</body>
</html>
