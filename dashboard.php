<?php
session_start();
require_once __DIR__ . '/_sql/db.php';

// 🔒 Restrict to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch the ongoing election (if any)
$election = $pdo->query("SELECT * FROM elections WHERE status = 'ongoing' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$election) {
    echo "<div class='no-election'>No ongoing election right now.</div>";
    exit;
}

$election_id = $election['election_id'];
$election_name = htmlspecialchars($election['election_name']);
$user_name = htmlspecialchars($_SESSION['full_name'] ?? 'Student');

// 🔹 Fetch positions
$positions = $pdo->prepare("SELECT * FROM positions WHERE election_id = ? ORDER BY sort_order ASC");
$positions->execute([$election_id]);
$positions = $positions->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Function to render leaderboard per position
function renderLeaderboard($pdo, $election_id, $position_id) {
    $stmt = $pdo->prepare("
        SELECT c.candidate_name, c.image_url, COUNT(v.vote_id) AS total_votes
        FROM candidates c
        LEFT JOIN votes v ON v.candidate_id = c.candidate_id
        WHERE c.election_id = ? AND c.position_id = ?
        GROUP BY c.candidate_id
        ORDER BY total_votes DESC, c.candidate_name ASC
    ");
    $stmt->execute([$election_id, $position_id]);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_start();
    echo "<div class='leaderboard-position'>";
    echo "<h2>" . htmlspecialchars(getPositionName($pdo, $position_id)) . "</h2>";

    if ($candidates) {
        echo "<div class='leaderboard-list'>";
        foreach ($candidates as $i => $cand) {
            $rank = $i + 1;
            $name = htmlspecialchars($cand['candidate_name']);
            $votes = (int)$cand['total_votes'];
            $img = htmlspecialchars($cand['image_url'] ?? 'default.png');
            $highlight = $rank === 1 ? 'leader' : '';

            echo "
            <div class='leaderboard-item $highlight'>
                <div class='rank'>$rank</div>
                <img src='_uploads/$img' alt='$name'>
                <div class='info'>
                    <span class='name'>$name</span>
                    <span class='votes'>$votes votes</span>
                </div>
            </div>";
        }
        echo "</div>";
    } else {
        echo "<p class='no-candidates'>No candidates for this position.</p>";
    }

    echo "</div>";
    return ob_get_clean();
}

// 🔹 Helper: get position name
function getPositionName($pdo, $position_id) {
    $stmt = $pdo->prepare("SELECT position_name FROM positions WHERE position_id = ?");
    $stmt->execute([$position_id]);
    return $stmt->fetchColumn() ?: 'Unknown Position';
}

// 🔹 AJAX Mode: Only return leaderboard section for JS refresh
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    echo "<div id='leaderboard-content'>";
    foreach ($positions as $pos) {
        echo renderLeaderboard($pdo, $election_id, $pos['position_id']);
    }
    echo "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Leaderboard — <?= $election_name ?></title>
    <link rel="stylesheet" href="_css/dashboard.css">
    <link rel="stylesheet" href="_css/_header.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="dashboard-container">
    <div class="dashboard-header">
        <h1>Live Leaderboard</h1>
        <p>Current standings for <strong><?= $election_name ?></strong></p>
        <p class="subtext">Updated automatically every few seconds.</p>
        <p id="clock" class="clock"></p>
    </div>

    <section id="leaderboard-content">
        <?php
        foreach ($positions as $pos) {
            echo renderLeaderboard($pdo, $election_id, $pos['position_id']);
        }
        ?>
    </section>
</main>

<script src="assets/js/realtime.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  startDateTimeClock("clock", "en-US", "Asia/Manila");
  startLiveLeaderboardRefresh("#leaderboard-content", "dashboard.php?ajax=1", 5000);
});
</script>
</body>
</html>
