<?php
session_start();
require_once __DIR__ . '/_sql/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_number = trim($_POST['student_number'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($student_number === '' || $password === '') {
        $error = "Please enter both student number and password.";
    } else {
        try {
            // Fetch active user by student number
            $stmt = $pdo->prepare("
                SELECT user_id, full_name, student_number, password, role, status
                FROM users 
                WHERE student_number = :student_number AND status = 'active'
                LIMIT 1
            ");
            $stmt->execute(['student_number' => $student_number]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // ⚠️ TEMPORARY: Compare passwords directly (no hashing)
                if ($password === $user['password']) {

                    // Update last login timestamp
                    $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = :id");
                    $update->execute(['id' => $user['user_id']]);

                    // Start session
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['full_name'];

                    // Redirect by role
                    if ($user['role'] === 'admin') {
                        header("Location: admin_control.php");
                        exit;
                    }

                    // STUDENT: find ongoing election
                    $stmt = $pdo->query("SELECT election_id FROM elections WHERE status = 'ongoing' LIMIT 1");
                    $election = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($election) {
                        // redirect to home.php first (instead of vote.php)
                        header("Location: home.php");
                        exit;
                    } else {
                        header("Location: no_election.php");
                        exit;
                    }

                } else {
                    $error = "Invalid student number or password!";
                }
            } else {
                $error = "Invalid student number or inactive account.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>VoteSmart | Student Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="_css/login.css">
</head>
<body>
  <div class="login-container">
    <h1>VoteSmart</h1>
    <p>Digital Election Platform</p>

    <form method="POST" action="">
      <label for="student_number">Student Number</label>
      <input type="text" id="student_number" name="student_number" placeholder="Enter your student number" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter your password" required>

      <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
