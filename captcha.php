<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/Message.php';

use Gregwar\Captcha\CaptchaBuilder;



$data = json_decode(file_get_contents("php://input"));
$returnData = [];


// IF REQUEST METHOD IS NOT EQUAL TO GET
if ($_SERVER["REQUEST_METHOD"] != "GET") :
    $returnData = Message::output(0, 404, 'Page Not Found!');
else :
    $builder = new CaptchaBuilder;
    $builder->build();
    $_SESSION['phrase'] = $builder->getPhrase();

    $returnData = [
        'captcha' => $builder->inline(),
    ];
endif;

echo json_encode($returnData);
