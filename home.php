<?php
session_start();
require_once __DIR__ . '/_sql/db.php';

// 🔒 Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? 'Student';

// 🎯 Get the currently active election
$election_stmt = $pdo->query("SELECT * FROM elections WHERE status = 'ongoing' LIMIT 1");
$active_election = $election_stmt->fetch(PDO::FETCH_ASSOC);

// 🚫 No active election → redirect to info page
if (!$active_election) {
    header("Location: no_election.php");
    exit;
}

$election_id = $active_election['election_id'];

// ✅ Check if the student already voted in this election
$check_vote = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE user_id = ? AND election_id = ?");
$check_vote->execute([$user_id, $election_id]);
$already_voted = $check_vote->fetchColumn() > 0;

// 🔁 Redirect to dashboard if already voted
if ($already_voted) {
    header("Location: dashboard.php");
    exit;
}

// ✅ Fetch positions and candidates
$positions_stmt = $pdo->prepare("SELECT * FROM positions WHERE election_id = ? ORDER BY sort_order ASC");
$positions_stmt->execute([$election_id]);
$positions = $positions_stmt->fetchAll(PDO::FETCH_ASSOC);

$candidates_stmt = $pdo->prepare("SELECT * FROM candidates WHERE election_id = ? AND position_id = ?");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($active_election['election_name'] ?? 'Active Election') ?> | VoteSmart</title>
    <link rel="stylesheet" href="_css/_header.css">
    <link rel="stylesheet" href="_css/vote.css">
    <style>
        .vote-container { max-width: 1100px; margin: 0 auto; padding: 2em 1em; font-family: "Segoe UI", Roboto, sans-serif; }
        .candidate-card.readonly { border: 1px solid #ddd; padding: 1em; border-radius: 10px; background: #fff; cursor: default; transition: box-shadow 0.2s; }
        .candidate-card.readonly:hover { box-shadow: none; transform: none; }
        .vote-now { text-align: center; margin-top: 2em; }
        .vote-now .submit-btn { background: #1c006d; color: white; border: none; padding: 0.8em 1.8em; border-radius: 8px; font-size: 1em; font-weight: 600; text-decoration: none; transition: background 0.2s ease; }
        .vote-now .submit-btn:hover { background: #4e2cff; }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="vote-container">
    <div class="vote-header">
        <h2><?= htmlspecialchars($active_election['election_name'] ?? 'Active Election') ?></h2>
        <p>Meet the candidates running for each position.</p>
        <p class="subtext">Review their platforms and experiences before casting your vote.</p>
    </div>

    <?php foreach ($positions as $pos): ?>
        <section class="position-section">
            <h3><?= htmlspecialchars($pos['position_name']) ?></h3>
            <div class="candidate-list">
                <?php
                $candidates_stmt->execute([$election_id, $pos['position_id']]);
                $candidates = $candidates_stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($candidates):
                    foreach ($candidates as $cand): ?>
                        <div class="candidate-card readonly">
                            <img src="_uploads/<?= htmlspecialchars($cand['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($cand['candidate_name']) ?>">
                            <div class="candidate-info">
                                <h4><?= htmlspecialchars($cand['candidate_name']) ?></h4>
                                <p class="year"><?= htmlspecialchars($cand['year_level'] ?? '') ?></p>
                                <p class="platform">
                                    <?= htmlspecialchars($cand['platform'] ?: 'No platform provided.') ?>
                                </p>
                                <p class="experience">
                                    <?= htmlspecialchars($cand['experience'] ?: 'No experience listed.') ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach;
                else: ?>
                    <p class="no-candidate">No candidates for this position yet.</p>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <div class="vote-now">
        <a href="vote.php" class="submit-btn">Go to Vote Page</a>
    </div>
</main>
</body>
</html>
