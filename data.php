<?php
require_once 'mt5_api.php';
$mt5 = new MT5_API($mt5_server, $mt5_login, $mt5_password);
$price = $mt5->get_price();
log_message($pdo, 'INFO', 'Data page loaded: Fetched DE30 price');
?>

<h2>Data</h2>
<div class="price-box">
    <p>DE30 Price: <?php echo $price !== false ? htmlspecialchars($price) : 'N/A'; ?></p>
</div>