<?php
class MT5_API {
    private $server;
    private $login;
    private $password;

    public function __construct($server, $login, $password) {
        $this->server = $server;
        $this->login = $login;
        $this->password = $password;
    }

    private function execute($command, $retries = 3) {
        $output = [];
        $return_var = 0;
        while ($retries > 0) {
            exec("python mt5_connect.py $command \"$this->server\" $this->login \"$this->password\" 2>&1", $output, $return_var);
            if ($return_var === 0) {
                return $output;
            }
            $retries--;
            sleep(1); // Wait before retry
        }
        return $output;
    }

    public function connect() {
        $output = $this->execute('connect');
        return end($output) === 'Connection successful';
    }

    public function get_price() {
        $output = $this->execute('price');
        $price = end($output);
        return is_numeric($price) ? floatval($price) : false;
    }

    public function get_bars() {
        $output = $this->execute('bars');
        $data = end($output);
        return json_decode($data, true) ?: [];
    }

    public function send_order($action, $price, $position_size) {
        $output = $this->execute("order $action $price $position_size");
        return end($output) === 'Order successful';
    }

    public function get_position() {
        $output = $this->execute('position');
        $data = end($output);
        return $data !== 'No positions' ? json_decode($data, true) : false;
    }
}
?>