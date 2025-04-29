<?php
require_once 'mt5_api.php';
$mt5 = new MT5_API($mt5_server, $mt5_login, $mt5_password);
$is_connected = $mt5->connect();
$price = $mt5->get_price();
$position = $mt5->get_position();
$signals = file_exists('mt5_signals.json') ? json_decode(file_get_contents('mt5_signals.json'), true) : [];
log_message($pdo, 'INFO', 'MT5 page loaded: Connection status ' . ($is_connected ? 'Connected' : 'Failed'));
?>

<h2>MT5 Connection</h2>
<p>Status: <span style="color: <?php echo $is_connected ? '#00ff00' : '#ff0000'; ?>"><?php echo $is_connected ? 'Connected' : 'Failed'; ?></span></p>
<h3>Signals Sent</h3>
<table>
    <tr>
        <th>Timestamp</th>
        <th>Signal</th>
        <th>Price</th>
        <th>Position Size</th>
    </tr>
    <?php foreach ($signals as $signal): ?>
        <tr>
            <td><?php echo htmlspecialchars($signal['timestamp']); ?></td>
            <td><?php echo htmlspecialchars($signal['signal']); ?></td>
            <td><?php echo htmlspecialchars($signal['price']); ?></td>
            <td><?php echo htmlspecialchars($signal['position_size']); ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if ($position): ?>
    <h3>Current Position</h3>
    <p>Signal: <?php echo htmlspecialchars($position['signal_type']); ?>, Price: <?php echo htmlspecialchars($position['price']); ?>, Size: <?php echo htmlspecialchars($position['position_size']); ?>, Time: <?php echo htmlspecialchars($position['timestamp']); ?></p>
<?php else: ?>
    <p>No open position.</p>
<?php endif; ?>
<div class="price-box">
    <p>DE30 Price: <?php echo $price !== false ? htmlspecialchars($price) : 'N/A'; ?></p>
</div>