<?php
require_once 'mt5_api.php';
$mt5 = new MT5_API($mt5_server, $mt5_login, $mt5_password);
$chart_data = $mt5->get_bars();
$price = $mt5->get_price();
$signals = [];

if ($chart_data) {
    $last_bar = end($chart_data);
    $prev_bar = prev($chart_data);
    if ($last_bar && $prev_bar) {
        if ($last_bar['close'] > $last_bar['donchian_high'] && $prev_bar['close'] <= $prev_bar['donchian_high']) {
            $signals[] = ['timestamp' => $last_bar['time'], 'signal' => 'Long', 'price' => $last_bar['close'], 'position_size' => 0.01];
            $mt5->send_order('buy', $last_bar['close'], 0.01);
            $stmt = $pdo->prepare("INSERT INTO trades (user_id, signal_type, action, price, position_size, timestamp) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], 'Long', 'buy', $last_bar['close'], 0.01, $last_bar['time']]);
            log_message($pdo, 'INFO', "Signal generated: Long at {$last_bar['close']}");
        } elseif ($last_bar['close'] < $last_bar['donchian_low'] && $prev_bar['close'] >= $prev_bar['donchian_low']) {
            $signals[] = ['timestamp' => $last_bar['time'], 'signal' => 'Short', 'price' => $last_bar['close'], 'position_size' => 0.01];
            $mt5->send_order('sell', $last_bar['close'], 0.01);
            $stmt = $pdo->prepare("INSERT INTO trades (user_id, signal_type, action, price, position_size, timestamp) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], 'Short', 'sell', $last_bar['close'], 0.01, $last_bar['time']]);
            log_message($pdo, 'INFO', "Signal generated: Short at {$last_bar['close']}");
        }
        file_put_contents('mt5_signals.json', json_encode($signals));
    }
}

$signals = file_exists('mt5_signals.json') ? json_decode(file_get_contents('mt5_signals.json'), true) : [];
log_message($pdo, 'INFO', 'Signals page loaded: Processed signals');
?>

<h2>Signals</h2>
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
<div class="price-box">
    <p>DE30 Price: <?php echo $price !== false ? htmlspecialchars($price) : 'N/A'; ?></p>
</div>