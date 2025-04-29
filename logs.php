<?php
require_once 'mt5_api.php';
$mt5 = new MT5_API($mt5_server, $mt5_login, $mt5_password);
$price = $mt5->get_price();
$logs = $pdo->query("SELECT * FROM logs ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
log_message($pdo, 'INFO', 'Logs page loaded: Fetched logs');
?>

<h2>Logs</h2>
<table>
    <tr>
        <th>Level</th>
        <th>Message</th>
        <th>Timestamp</th>
    </tr>
    <?php foreach ($logs as $log): ?>
        <tr>
            <td><?php echo htmlspecialchars($log['log_level']); ?></td>
            <td><?php echo htmlspecialchars($log['message']); ?></td>
            <td><?php echo htmlspecialchars($log['created_at']); ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<div class="price-box">
    <p>DE30 Price: <?php echo $price !== false ? htmlspecialchars($price) : 'N/A'; ?></p>
</div>