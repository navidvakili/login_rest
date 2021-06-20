<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/classes/Message.php';

use Gregwar\Captcha\CaptchaBuilder;

require __DIR__ . '/classes/Database.php';
require __DIR__ . '/classes/JwtHandler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

$builder = new CaptchaBuilder;
$builder->build();

// IF REQUEST METHOD IS NOT EQUAL TO POST
if ($_SERVER["REQUEST_METHOD"] != "POST") :
    $returnData = Message::output(0, 404, 'Page Not Found!');

// CHECKING EMPTY FIELDS
elseif (
    !isset($data->username)
    || !isset($data->password)
    || !isset($data->captcha)
    || empty(trim($data->username))
    || empty(trim($data->password))
    || empty(trim($data->captcha))
) :

    $fields = ['fields' => ['username', 'password', 'captcha']];
    $returnData = Message::output(0, 422, 'Please Fill in all Required Fields!', $fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else :
    $username = trim($data->username);
    $password = trim($data->password);
    $captcha = trim($data->captcha);

    // CHECKING THE EMAIL FORMAT (IF INVALID FORMAT)
    // if (!filter_var($email, FILTER_VALIDATE_EMAIL)) :
    //     $returnData = Message::output(0, 422, 'Invalid Email Address!');

    // IF PASSWORD IS LESS THAN 8 THE SHOW THE ERROR
    if (strlen($password) < 8) :
        $returnData = Message::output(0, 422, 'Your password must be at least 8 characters long!');

    // IF Captcha is wrong
    elseif (!isset($_SESSION['phrase'])  || $captcha != $_SESSION['phrase']) :
        $returnData = Message::output(0, 422, 'Your captcha is invalid!');

    // THE USER IS ABLE TO PERFORM THE LOGIN ACTION
    else :
        try {

            $fetch_user_by_username = "SELECT * FROM `users` WHERE `username`=:username";
            $query_stmt = $conn->prepare($fetch_user_by_username);
            $query_stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $query_stmt->execute();

            // IF THE USER IS FOUNDED BY username
            if ($query_stmt->rowCount()) :
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                $check_password = password_verify($password, $row['password']);

                // VERIFYING THE PASSWORD (IS CORRECT OR NOT?)
                // IF PASSWORD IS CORRECT THEN SEND THE LOGIN TOKEN
                if ($check_password) :

                    $jwt = new JwtHandler();
                    $token = $jwt->_jwt_encode_data(
                        Config::PAYLOAD,
                        array("user_id" => $row['id'])
                    );

                    $returnData = [
                        'success' => 1,
                        'message' => 'You have successfully logged in.',
                        'token' => $token
                    ];

                // IF INVALID PASSWORD
                else :
                    $returnData = Message::output(0, 422, 'Invalid Password!');
                endif;

            // IF THE USER IS NOT FOUNDED BY username THEN SHOW THE FOLLOWING ERROR
            else :
                $returnData = Message::output(0, 422, 'Invalid username Address!');
            endif;
        } catch (PDOException $e) {
            $returnData = Message::output(0, 500, $e->getMessage());
        }

    endif;

endif;

echo json_encode($returnData);
