<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: welcome.php');
    exit;
}
$page = isset($_GET['page']) && in_array($_GET['page'], ['home', 'data', 'signals', 'mt5', 'logs', 'strategy']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAX Trading Helper</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #1a1a1a;
            color: #e0e0e0;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #2c2c2c;
            padding: 20px;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            border-right: 1px solid #3a3a3a;
            box-sizing: border-box;
        }
        .sidebar img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: block;
            margin: 0 auto 20px;
        }
        .sidebar a {
            display: block;
            color: #e0e0e0;
            padding: 10px;
            text-decoration: none;
            margin: 5px 0;
            border-radius: 5px;
        }
        .sidebar a:hover {
            background: #3a3a3a;
        }
        .sidebar a.active {
            background: #00ccff;
            color: #1a1a1a;
        }
        .sidebar-footer {
            position: absolute;
            bottom: 10px;
            width: 210px;
            text-align: center;
            font-size: 12px;
            color: #b0b0b0;
        }
        .main-panel {
            margin-left: 251px;
            padding: 20px;
            flex-grow: 1;
            box-sizing: border-box;
        }
        h2 {
            color: #00ccff;
            margin-top: 0;
        }
        h3 {
            color: #b0b0b0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #2c2c2c;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #3a3a3a;
        }
        th {
            background: #3a3a3a;
        }
        .price-box {
            background: #2c2c2c;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            display: inline-block;
        }
        canvas {
            max-width: 100%;
            background: #2c2c2c;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
    <script>
        window.Chart || document.write('<script src="/js/chart.min.js"><\/script>');
        window.ChartFinancial || document.write('<script src="/js/chartjs-chart-financial.min.js"><\/script>');
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-financial@0.2.0/dist/chartjs-chart-financial.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.2.1/dist/chartjs-plugin-annotation.min.js"></script>
</head>
<body>
    <div class="sidebar">
        <img src="images/logo.jpg" alt="DAX Trading Helper Logo">
        <a href="?page=home" class="<?php echo $page === 'home' ? 'active' : ''; ?>">Home</a>
        <a href="?page=data" class="<?php echo $page === 'data' ? 'active' : ''; ?>">Data</a>
        <a href="?page=signals" class="<?php echo $page === 'signals' ? 'active' : ''; ?>">Signals</a>
        <a href="?page=mt5" class="<?php echo $page === 'mt5' ? 'active' : ''; ?>">MT5</a>
        <a href="?page=logs" class="<?php echo $page === 'logs' ? 'active' : ''; ?>">Logs</a>
        <a href="?page=strategy" class="<?php echo $page === 'strategy' ? 'active' : ''; ?>">Strategy</a>
        <a href="authentication.php?logout=1">Logout</a>
        <div class="sidebar-footer">© 2025 DAX Trading Helper. Deric protected.</div>
    </div>
    <div class="main-panel">
        <?php
        if ($page === 'home') {
            include 'home.php';
        } elseif ($page === 'data') {
            include 'data.php';
        } elseif ($page === 'signals') {
            include 'signals.php';
        } elseif ($page === 'mt5') {
            include 'mt5.php';
        } elseif ($page === 'logs') {
            include 'logs.php';
        } elseif ($page === 'strategy') {
            include 'strategy.php';
        }
        ?>
    </div>
</body>
</html>