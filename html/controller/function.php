<?php


use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

require 'key.php';

function getSQLErrorException($errorLogs, $e, $req)
{
    $res = (Object)Array();
    http_response_code(500);
    $res->code = 500;
    $res->message = "SQL Exception -> " . $e->getTraceAsString();
    echo json_encode($res);

    addErrorLogs($errorLogs, $res, $req);
}

function isValidHeader($jwt, $key)
{
    try {
        $data = getDataByJWToken($jwt, $key);
        return isValidJWToken($data->userId, $data->userPw);
        //return $data;
    } catch (Exception $e) {
        return false;
    }
}

function isValidJWToken($userId, $userpw)
{
    try {
        return ValidHeaderPw($userId, $userpw);
    } catch (Exception $e) {
        return false;
    }
}

function getTodayByTimeStamp()
{
    return date("Y-m-d H:i:s");
}

function getJWToken($userId, $userPw, $userType, $secretKey)
{
    $data = array(
        'date' => (string)getTodayByTimeStamp(),
        'userId' => (string)$userId,
        'userPw' => (string)$userPw,
        'userType' => (string)$userType,
    );
    //echo json_encode($data);
    return $jwt = JWT::encode($data, $secretKey);

    //    echo "encoded jwt: " . $jwt . "n";
    //    $decoded = JWT::decode($jwt, $secretKey, array('HS256'))
    //    print_r($decoded);
}

function getDataByJWToken($jwt, $secretKey)
{
    $decoded = JWT::decode($jwt, $secretKey, array('HS256'));
    //print_r($decoded);
    return $decoded;

}

function sendFcm($fcmToken, $data, $key, $deviceType)
{
    $url = 'https://fcm.googleapis.com/fcm/send';

    $headers = array(
        'Authorization: key=' . $key,
        'Content-Type: application/json'
    );

    $fields['data'] = $data;

    if ($deviceType == 'IOS') {
        $notification['title'] = $data['title'];
        $notification['body'] = $data['body'];
        $notification['sound'] = 'default';
        $fields['notification'] = $notification;
    }

    $fields['to'] = $fcmToken;
    $fields['content_available'] = true;
    $fields['priority'] = "high";
    $fields = json_encode($fields, JSON_NUMERIC_CHECK);

//    echo $fields;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

    $result = curl_exec($ch);
    if ($result === FALSE) {
        //die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
    return $result;
}

/*
 * AUTHOR : YOUNGMINJUN
 *
 * $EMAIL : 보내는 사람 메일 주소
 * $NAME : 보내는 사람 이름
 * $SUBJECT : 메일 제목
 * $CONTENT : 메일 내용
 * $MAILTO : 받는 사람 메일 주소
 * $MAILTONAME : 받는 사람 이름
 */
function sendMail($EMAIL, $NAME, $SUBJECT, $CONTENT, $MAILTO, $MAILTONAME)
{
    $mail = new PHPMailer();
    $body = $CONTENT;

    $mail->IsSMTP(); // telling the class to use SMTP
    //$mail->Host       = "www.softcomics.co.kr"; // SMTP server
    $mail->SMTPDebug = 2;                     // enables SMTP debug information (for testing)
    // 1 = errors and messages
    // 2 = messages only
    $mail->CharSet = "utf-8";
    $mail->SMTPAuth = true;                  // enable SMTP authentication
    $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
    $mail->Host = "smtp.gmail.com";      // sets GMAIL as the SMTP server
    $mail->Port = 465;                   // set the SMTP port for the GMAIL server
    $mail->Username = "jhktm1729@gmail.com";             // GMAIL username
    $mail->Password = MAIL_PW;              // GMAIL password

    $mail->SetFrom($EMAIL, $NAME);

    $mail->AddReplyTo($EMAIL, $NAME);

    $mail->Subject = $SUBJECT;

    $mail->MsgHTML($body);

    $address = $MAILTO;
    $mail->AddAddress($address, $MAILTONAME);

    if (!$mail->Send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    } else {
        echo "Message sent!";
    }
}

function formSubmit($file)
{
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES[$file]["name"]);
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES[$file]["tmp_name"]);
        if($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }
// Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }
// Check file size
    if ($_FILES[$file]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
// Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
// Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

}

function checkAndroidBillingReceipt($credentialsPath, $token, $pid)
{

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope("https://www.googleapis.com/auth/androidpublisher");
    $client->setSubject("bigs-admin@api-6265089933527833864-983283.iam.gserviceaccount.com");


    $service = new Google_Service_AndroidPublisher($client);
    $optParams = array('token' => $token);
//    $results = $service->inappproducts->listInappproducts('kr.co.bigsapp.www', $optParams);


//    $res = new Google_Service_AndroidPublisher_Resource_PurchasesProducts($service, "androidpublisher", 'products', array(
//        'methods' => array(
//            'get' => array(
//                'path' => '{packageName}/purchases/products/{productId}/tokens/{token}',
//                'httpMethod' => 'GET',
//                'parameters' => array(
//                    'packageName' => array(
//                        'location' => 'kr.co.bigsapp.www',
//                        'type' => 'string',
//                        'required' => true,
//                    ),
//                    'productId' => array(
//                        'location' =>$pid,
//                        'type' => 'string',
//                        'required' => true,
//                    ),
//                    'token' => array(
//                        'location' => $token,
//                        'type' => 'string',
//                        'required' => true,
//                    ),
//                ),
//            ),
//        )));

    return $service->purchases_products->get("kr.co.bigsapp.www", $pid, $token);
}

function addAccessLogs($accessLogs, $body)
{

    if (isset($_SERVER['HTTP_X_ACCESS_TOKEN']))
        $logData["JWT"] = getDataByJWToken($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY);
    $logData["GET"] = $_GET;
    $logData["BODY"] = $body;
    $logData["REQUEST_METHOD"] = $_SERVER["REQUEST_METHOD"];
    $logData["REQUEST_URI"] = $_SERVER["REQUEST_URI"];
//    $logData["SERVER_SOFTWARE"] = $_SERVER["SERVER_SOFTWARE"];
    $logData["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
    $logData["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];
    $accessLogs->addInfo(json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

}

function addErrorLogs($errorLogs, $res, $body)
{

    if (isset($_SERVER['HTTP_X_ACCESS_TOKEN']))
        $req["JWT"] = getDataByJWToken($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY);
    $req["GET"] = $_GET;
    $req["BODY"] = $body;
    $req["REQUEST_METHOD"] = $_SERVER["REQUEST_METHOD"];
    $req["REQUEST_URI"] = $_SERVER["REQUEST_URI"];
//    $req["SERVER_SOFTWARE"] = $_SERVER["SERVER_SOFTWARE"];
    $req["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
    $req["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];

    $logData["REQUEST"] = $req;
    $logData["RESPONSE"] = $res;

    $errorLogs->addError(json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
//        sendDebugEmail("Error : " . $req["REQUEST_METHOD"] . " " . $req["REQUEST_URI"] , "<pre>" . json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>");
}


function getLogs($path)
{
    $fp = fopen($path, "r", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$fp) echo "error";

    while (!feof($fp)) {
        $str = fgets($fp, 10000);
        $arr[] = $str;
    }
    for ($i = sizeof($arr) - 1; $i >= 0; $i--) {
        echo $arr[$i] . "<br>";
    }
//        fpassthru($fp);
    fclose($fp);
}
