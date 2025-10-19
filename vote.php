<?php
require_once "_sql/db.php";

// Fetch active election
$activeElection = $pdo->query("SELECT * FROM elections WHERE status = 'Ongoing' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($activeElection['election_name'] ?? 'School of Computing Election 2025') ?></title>

  <!-- CSS Link -->
  <link rel="stylesheet" href="_css/vote.css">

  <!-- Optional Google Font (Inter, as used in mockup) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <main class="vote-container">
    <section class="election-header">
      <h1><?= htmlspecialchars($activeElection['election_name'] ?? 'School of Computing Election 2025') ?></h1>
      <p>Select your preferred candidate for each position. You can only vote once.</p>

      <div class="note">
        🗳️ Review each candidate’s platform and experience, then select your choice and click
        <strong>“Submit Vote”</strong> to cast your ballot.
      </div>
    </section>

    <form action="submit_vote.php" method="POST" class="vote-form">
      <?php
      if ($activeElection) {
        $election_id = $activeElection['election_id'];
        echo "<input type='hidden' name='election_id' value='" . htmlspecialchars($election_id) . "'>";

        // Fetch positions
        $positions = $pdo->prepare("SELECT * FROM positions WHERE election_id = ? ORDER BY sort_order ASC");
        $positions->execute([$election_id]);

        foreach ($positions as $position) {
          $position_id = $position['position_id'];
          $position_name = htmlspecialchars($position['position_name']);
          echo "<section class='position-section'>";
          echo "<h2>{$position_name}</h2>";

          // Fetch candidates for this position
          $stmt = $pdo->prepare("SELECT * FROM candidates WHERE position_id = ? AND election_id = ?");
          $stmt->execute([$position_id, $election_id]);
          $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if ($candidates) {
            echo "<div class='candidate-grid'>";
            foreach ($candidates as $c) {
              $cid = $c['candidate_id'];
              $name = htmlspecialchars($c['candidate_name']);
              $year = htmlspecialchars($c['year_level']);
              $platform = nl2br(htmlspecialchars($c['platform']));
              $experience = htmlspecialchars($c['experience']);

              // Avatar initials
              $initials = strtoupper(substr($name, 0, 2));

              echo "
              <label class='candidate-card'>
                <input type='radio' name='vote[$position_id]' value='$cid' required>
                <div class='card-content'>
                  <div class='candidate-info'>
                    <div class='avatar'>$initials</div>
                    <div class='basic'>
                      <h3>$name</h3>
                      <p class='position'>$position_name</p>
                      <p class='year'>$year</p>
                    </div>
                  </div>
                  <div class='details'>
                    <h4>Platform</h4>
                    <p>$platform</p>
                    <h4>Experience</h4>
                    <div class='experience-tags'>";
                      foreach (explode(',', $experience) as $exp) {
                        $exp = trim($exp);
                        if ($exp !== '') {
                          echo "<span class='tag'>" . htmlspecialchars($exp) . "</span>";
                        }
                      }
              echo "    </div>
                  </div>
                </div>
              </label>";
            }
            echo "</div>"; // end candidate-grid
          } else {
            echo "<p class='no-candidates'>No candidates available for this position.</p>";
          }

          echo "</section>";
        }

        echo "<button type='submit' class='submit-btn'>Submit Vote</button>";
      } else {
        echo "<p class='no-election'>No active election is currently open for voting.</p>";
      }
      ?>
    </form>
  </main>
</body>
</html>
