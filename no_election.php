<?php
session_start();
require_once __DIR__ . '/_sql/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_name = htmlspecialchars($_SESSION['full_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>VoteSmart | No Active Election</title>
  <link rel="stylesheet" href="_css/style.css">
  <link rel="stylesheet" href="_css/_header.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

  <main style="text-align:center; margin-top: 100px;">
    <h1>No Active Elections</h1>
    <p>There are currently no ongoing elections. Please check back later.</p>
    <a href="index.php">Return to Home</a>
  </main>
</body>
</html>
