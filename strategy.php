<?php
require_once 'mt5_api.php';
$mt5 = new MT5_API($mt5_server, $mt5_login, $mt5_password);
$price = $mt5->get_price();
log_message($pdo, 'INFO', 'Strategy page loaded: Fetched DE30 price');

// Mock data for simulated chart (50 bars, Jan 2025)
$mock_data = [];
$base_time = strtotime('2025-01-06 00:00:00');
$base_price = 22300;
for ($i = 0; $i < 50; $i++) {
    $time = date('Y-m-d\TH:i:s', $base_time + $i * 15 * 60);
    $open = $base_price + rand(-50, 50);
    $high = $open + rand(10, 30);
    $low = $open - rand(10, 30);
    $close = $open + rand(-20, 20);
    $mock_data[] = [
        'time' => $time,
        'open' => $open,
        'high' => $high,
        'low' => $low,
        'close' => $close
    ];
}
// Calculate Donchian Channels
$highs = array_column($mock_data, 'high');
$lows = array_column($mock_data, 'low');
for ($i = 0; $i < count($mock_data); $i++) {
    $start = max(0, $i - 19);
    $mock_data[$i]['donchian_high'] = max(array_slice($highs, $start, 20));
    $mock_data[$i]['donchian_low'] = min(array_slice($lows, $start, 20));
}
// Add mock signals (buy/sell)
$mock_signals = [];
for ($i = 1; $i < count($mock_data); $i++) {
    $curr = $mock_data[$i];
    $prev = $mock_data[$i - 1];
    if ($curr['close'] > $curr['donchian_high'] && $prev['close'] <= $prev['donchian_high']) {
        $mock_signals[] = [
            'type' => 'point',
            'xValue' => $curr['time'],
            'yValue' => $curr['low'] - 10,
            'backgroundColor' => '#00ff00',
            'borderColor' => '#00ff00',
            'radius' => 8,
            'label' => ['content' => 'Buy', 'enabled' => true, 'position' => 'bottom']
        ];
    } elseif ($curr['close'] < $curr['donchian_low'] && $prev['close'] >= $prev['donchian_low']) {
        $mock_signals[] = [
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
?>

<h2>Strategy</h2>
<h3>Turtle Breakout Strategy Guide</h3>
<p>The DAX Trading Helper employs the Turtle Breakout strategy, a trend-following approach designed to capitalize on significant price movements in the DE30 (DAX index). Below is a detailed guide on how the strategy works in live markets, illustrated with a simulated chart to demonstrate expected behavior.</p>

<h4>How the Strategy Works</h4>
<p>The Turtle Breakout strategy generates trading signals based on price breakouts from the 20-period Donchian Channel, which tracks the highest high and lowest low over the past 20 15-minute bars:</p>
<ul>
    <li><strong>Buy Signal (Long)</strong>: Triggered when the DE30 closing price breaks above the Donchian Channel high, indicating a potential uptrend. For example, if the high is 22350 and the price closes at 22360, a buy order is placed.</li>
    <li><strong>Sell Signal (Short)</strong>: Triggered when the DE30 closing price breaks below the Donchian Channel low, signaling a potential downtrend. For example, if the low is 22250 and the price closes at 22240, a sell order is placed.</li>
</ul>
<p>Each trade uses a fixed position size of 0.01 lots to manage risk. The strategy aims to capture sustained trends while filtering out minor price fluctuations.</p>

<h4>Live Market Expectations</h4>
<p>In a live market (Monday 00:00 – Friday 22:00 CET), the strategy operates as follows:</p>
<ol>
    <li><strong>Data Retrieval</strong>: The system fetches real-time 15-minute DE30 price bars via MetaTrader 5 (MT5).</li>
    <li><strong>Signal Generation</strong>: Every 15 minutes, the system checks for breakouts. A buy signal occurs when the price crosses above the Donchian high, and a sell signal when it crosses below the low.</li>
    <li><strong>Trade Execution</strong>: Signals are sent to MT5, executing buy or sell orders with 0.01 lots. Trades are logged in the database and displayed on the Signals and Home pages.</li>
    <li><strong>Monitoring</strong>: The Home page shows a live chart with Donchian Channels and entry markers (green for buy, red for sell). The MT5 page confirms connection and trade status.</li>
</ol>
<p><strong>Example</strong>: On a trending day, if DE30 rises from 22300 to 22400, a buy signal at 22350 (breaking the Donchian high) could capture the uptrend. Conversely, a drop below 22250 triggers a short position.</p>

<h4>Risk Management</h4>
<p>The strategy uses a small position size (0.01 lots) to limit exposure. Investors should:</p>
<ul>
    <li>Monitor market volatility, as DE30 can experience rapid swings.</li>
    <li>Review historical trades on the Home page to assess performance.</li>
    <li>Ensure sufficient account balance in MT5 to cover margin requirements.</li>
</ul>
<p><strong>Note</strong>: Past performance does not guarantee future results. Always trade with capital you can afford to lose.</p>

<h4>Simulated Chart</h4>
<p>The chart below simulates the Turtle Breakout strategy on a 15-minute DE30 chart (January 2025). It shows:</p>
<ul>
    <li><strong>Candlesticks</strong>: DE30 price movements (blue for up, red for down).</li>
    <li><strong>Donchian Channels</strong>: Green line (20-period high), red line (20-period low).</li>
    <li><strong>Entry Markers</strong>: Green points (buy signals), red points (sell signals) where breakouts occur.</li>
</ul>
<canvas id="strategyChart"></canvas>

<div class="price-box">
    <p>DE30 Price: <?php echo $price !== false ? htmlspecialchars($price) : 'N/A'; ?></p>
</div>

<script>
    try {
        const ctx = document.getElementById('strategyChart').getContext('2d');
        const chartData = <?php echo json_encode($mock_data); ?>;
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
                        label: 'DE30 (Simulated)',
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
                        annotations: <?php echo json_encode($mock_signals); ?>
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
        console.error('Strategy chart loading failed:', e);
        document.getElementById('strategyChart').replaceWith(document.createTextNode('Simulated chart failed to load.'));
    }
</script>