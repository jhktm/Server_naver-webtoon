<?php

header('content-Type: apllication/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$postName = $_POST["subname"];
$postPro = $_POST["prono"];
$postCredit = $_POST["subcredit"];

$conn = mysqli_connect(
    '127.0.0.1',
    'stu',
    '123456789',
    'University_DB');

$res = (object) Array();

$sql = "SELECT * FROM Professor where Pro_No = ". $postPro;
$result = mysqli_query($conn, $sql);

if($row = mysqli_fetch_array($result)){

    $sql = "select * from Subject where Subject_Name ='".$postName."' and Pro_No ='".$postPro."'";
    $result = mysqli_query($conn, $sql);

    if($row = mysqli_fetch_array($result)) {

        $res->isSucess = false;
        $res->Code = 500; //이름 중복
    }else{
        $sql = "insert into Subject(Subject_Name,Pro_No, Subject_Credit) values( '$postName','$postPro','$postCredit')";
        $result = mysqli_query($conn, $sql);

        $res->isSucess = true;
        $res->Code = 100;
    }

}else{
    $res->isSucess = false;
    $res->Code = 501; //교수 존재 x
}

echo json_encode($res);

?>
