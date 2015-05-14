<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . "/sys/db/conf.php";
require_once dirname(__FILE__) . "/sys/db/db.mysql.mod.php";


class srv
{

    private $srcCheck = false;
    private $csrfToken = "";

    private function securityCheck()
    {

        // Get protocol
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
                     $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $address = $protocol.$_SERVER["SERVER_NAME"];
        $token = (base64_encode("768954" . date('jnY') . $address));

        // Detect if script origin is not of servername.
        if ("POST" === $_SERVER["REQUEST_METHOD"] && isset($_SERVER["HTTP_ORIGIN"])) {
            if (strpos($address, $_SERVER["HTTP_ORIGIN"]) !== 0 && strpos($address, $_SERVER["HTTP_ORIGIN"]) !== 0) {
                exit("CSRF protection in POST request: detected invalid Origin header: " .
                     $_SERVER["HTTP_ORIGIN"] . " - " . $address);
            }
        }

        if ($this->srcCheck) {
            return true;
        }
        foreach (getallheaders() as $name => $value) {
            if (strtolower($name) === "csrftoken") {
                $this->csrfToken = $value;
                if (strtolower($value) === strtolower($token)) {
                    return true;
                    break;
                }
            }
        }
        return false;
    }

    public function convertToCSV($input_array, $output_file_name, $delimiter)
    {
        $temp_memory = fopen('php://memory', 'w');
        foreach ($input_array as $line) {
            fputcsv($temp_memory, $line, $delimiter);
        }
        fseek($temp_memory, 0);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
        fpassthru($temp_memory);
    }

    private function createOrdersCSV($CFG)
    {
        $dbc = new db();

        $dbc->host = $CFG['host'];
        $dbc->usr = $CFG['usr'];
        $dbc->pasw = $CFG['pwd'];
        $dbc->db = $CFG['db'];

        $dbc->db_open_connection();

        $dbc->tb = "orders";
        $result = $dbc->db_findall('', MYSQL_NUM);

        $dbc->db_close_connection();

        $this->convertToCSV($result, 'orders.csv', ',');
        die;
    }

    private function getOrders($CFG)
    {
        $dbc = new db();

        $dbc->host = $CFG['host'];
        $dbc->usr = $CFG['usr'];
        $dbc->pasw = $CFG['pwd'];
        $dbc->db = $CFG['db'];

        // Create new order
        $dbc->db_open_connection();

        $fields = "`ID`,`name`,`email`,`products`,`orderid`,`status`";

        $dbc->tb = "orders";
        $result = $dbc->db_findall('', MYSQL_ASSOC, $fields);

        $dbc->db_close_connection();

        $response = array(
            "success" => $this->srcCheck,
            "post" => $_POST,
            "data" => $result
        );

        header("Content-type: text/json");
        echo json_encode($response);
        die;
    }

    private function defaultResponse()
    {
        $response = array(
            "success" => "false"
        );
        header("Content-type: text/json");
        echo json_encode($response);
        die;
    }

    private function login()
    {
        $found = false;
        if ($_POST['usr'] === "superadmin" && $_POST['pwd'] === "2nd&3rd=4rd") {
            $found = true;
        }
        if ($_POST['usr'] === "demo" && $_POST['pwd'] === "demo") {
            $found = true;
        }
        if ($found) {
            $response = array(
                "success" => "true"
            );
            header("Content-type: text/json");
            echo json_encode($response);
            die;
        }
        $response = array(
            "success" => "false"
        );
        header("Content-type: text/json");
        echo json_encode($response);
        die;
    }
    
    public function execute()
    {
        global $CFG;
        $this->initRequest($CFG);
    }

    public function initRequest($CFG)
    {

        $this->srcCheck = $this->securityCheck();

        if(isset($_POST['action'])){
            switch ($_POST['action'])
            {
                case "fo_csv":
                $this->createOrdersCSV($CFG);
                break;
                case "fo":
                $this->getOrders($CFG);
                break;
                case "login":
                $this->login();
                break;
                default:
                $this->defaultResponse();
                break;
            }
        } else {
            $this->defaultResponse();
        }

    }
}
