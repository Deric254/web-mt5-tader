<?php
require_once 'mt5_api.php';
$mt5 = new MT5_API($mt5_server, $mt5_login, $mt5_password);
$chart_data = $mt5->get_bars();
$trades = $pdo->query("SELECT * FROM trades WHERE user_id = {$_SESSION['user_id']} ORDER BY timestamp DESC")->fetchAll(PDO::FETCH_ASSOC);
$position = $mt5->get_position();
$error_message = '';

if (empty($chart_data)) {
    exec("python mt5_connect.py bars \"$mt5_server\" $mt5_login \"$mt5_password\" 2>&1", $output);
    $error_message = end($output) ?: 'Unknown error fetching chart data';
    log_message($pdo, 'ERROR', 'Chart data fetch failed: ' . $error_message);
} else {
    log_message($pdo, 'INFO', 'Home page loaded: Fetched chart data and position');
}

// Generate annotations for entry points (buy/sell signals)
$annotations = [];
if (!empty($chart_data)) {
    for ($i = 1; $i < count($chart_data); $i++) {
        $curr = $chart_data[$i];
        $prev = $chart_data[$i - 1];
        if ($curr['close'] > $curr['donchian_high'] && $prev['close'] <= $prev['donchian_high']) {
            $annotations[] = [
                'type' => 'point',
                'xValue' => $curr['time'],
                'yValue' => $curr['low'] - 10,
                'backgroundColor' => '#00ff00',
                'borderColor' => '#00ff00',
                'radius' => 8,
                'label' => ['content' => 'Buy', 'enabled' => true, 'position' => 'bottom']
            ];
        } elseif ($curr['close'] < $curr['donchian_low'] && $prev['close'] >= $prev['donchian_low']) {
            $annotations[] = [
                'type' => 'point',
                'xValue' => $curr['time'],
                'yValue' => $curr['high'] + 10,
                'backgroundColor' => '#ff0000',
                'borderColor' => '#ff0000',
                'radius' => 8,
                'label' => ['content' => 'Sell', 'enabled' => true, 'position' => 'top']
            ];
        }
    }
}
?>

<h2>Home</h2>
<?php if (empty($chart_data)): ?>
    <p style="color: #ff5555;">Error: Unable to load chart data. Details: <?php echo htmlspecialchars($error_message); ?>. Check MT5 connection and logs.</p>
<?php else: ?>
    <canvas id="daxChart"></canvas>
<?php endif; ?>
<h3>Historical Trades</h3>
<table>
    <tr>
        <th>Signal</th>
        <th>Action</th>
        <th>Price</th>
        <th>Position Size</th>
        <th>Timestamp</th>
    </tr>
    <?php foreach ($trades as $trade): ?>
        <tr>
            <td><?php echo htmlspecialchars($trade['signal_type']); ?></td>
            <td><?php echo htmlspecialchars($trade['action']); ?></td>
            <td><?php echo htmlspecialchars($trade['price']); ?></td>
            <td><?php echo htmlspecialchars($trade['position_size']); ?></td>
            <td><?php echo htmlspecialchars($trade['timestamp']); ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if ($position): ?>
    <h3>Current Position</h3>
    <p>Signal: <?php echo htmlspecialchars($position['signal_type']); ?>, Price: <?php echo htmlspecialchars($position['price']); ?>, Size: <?php echo htmlspecialchars($position['position_size']); ?>, Time: <?php echo htmlspecialchars($position['timestamp']); ?></p>
<?php else: ?>
    <p>No open position.</p>
<?php endif; ?>

<script>
    <?php if (!empty($chart_data)): ?>
        try {
            const ctx = document.getElementById('daxChart').getContext('2d');
            const chartData = <?php echo json_encode($chart_data); ?>;
            const candlestickData = chartData.map(d => ({
                x: new Date(d.time),
                o: d.open,
                h: d.high,
                l: d.low,
                c: d.close
            }));
            const donchianHigh = chartData.map(d => ({ x: new Date(d.time), y: d.donchian_high }));
            const donchianLow = chartData.map(d => ({ x: new Date(d.time), y: d.donchian_low }));

            new Chart(ctx, {
                type: 'candlestick',
                data: {
                    datasets: [
                        {
                            label: 'DE30',
                            data: candlestickData,
                            borderColor: '#e0e0e0',
                            backgroundColor: d => d.o < d.c ? '#00ccff' : '#ff5555'
                        },
                        {
                            type: 'line',
                            label: 'Donchian High',
                            data: donchianHigh,
                            borderColor: '#00ff00',
                            fill: false,
                            pointRadius: 0
                        },
                        {
                            type: 'line',
                            label: 'Donchian Low',
                            data: donchianLow,
                            borderColor: '#ff0000',
                            fill: false,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true },
                        annotation: {
                            annotations: <?php echo json_encode($annotations); ?>
                        },
                        tooltip: {
                            enabled: true,
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            type: 'time',
                            time: { unit: 'minute' },
                            grid: { color: '#3a3a3a' }
                        },
                        y: {
                            beginAtZero: false,
                            grid: { color: '#3a3a3a' }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    animation: {
                        duration: 1000
                    }
                }
            });
        } catch (e) {
            console.error('Chart loading failed:', e);
            document.getElementById('daxChart').replaceWith(document.createTextNode('Chart failed to load. See console for details.'));
        }
    <?php endif; ?>
</script>