<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/Database.php';
require __DIR__ . '/middlewares/Auth.php';
require_once __DIR__ . '/classes/Message.php';

$allHeaders = getallheaders();
$db_connection = new Database();
$conn = $db_connection->dbConnection();
$auth = new Auth($conn, $allHeaders);

// IF REQUEST METHOD IS NOT EQUAL TO GET
if ($_SERVER["REQUEST_METHOD"] != "GET") :
    $returnData = Message::output(0, 404, 'Page Not Found!');
elseif ($auth->isAuth()) :
    $returnData = $auth->isAuth();
else :
    $returnData = [
        "success" => 0,
        "status" => 401,
        "message" => "Unauthorized"
    ];
endif;

echo json_encode($returnData);
