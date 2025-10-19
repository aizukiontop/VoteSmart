<?php
session_start();

// Redirect logged-in users straight to home
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>VoteSmart | Digital Election Platform</title>
  <link rel="stylesheet" href="_css/index.css">
  <link rel="stylesheet" href="_css/login.css">
</head>
<body>
  <div class="login-container">
    <h1>VoteSmart</h1>
    <p>Secure, paperless student elections made simple.</p>
    <a href="login.php" class="btn">Login to Vote</a>
  </div>
</body>
</html>
