<?php
session_start();
require_once __DIR__ . '/_sql/db.php';

// Restrict access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_name = htmlspecialchars($_SESSION['full_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VoteSmart | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="_css/admin_control.css">
    <link rel="stylesheet" href="_css/_header.css">
    <script src="-js/admin_control.js" defer></script>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="admin-container">
    <!-- LEFT SIDEBAR (ELECTION CONTROL) -->
    <?php include 'includes/admin_sidebar.php'; ?>

    <!-- RIGHT SIDEBAR (CANDIDATE CONTROL) -->
    <?php include 'includes/admin_candidate.php'; ?>
</div>

</body>
</html>
