<?php

header('content-Type: apllication/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$postProNo = $_POST["prono"];
$postSubNo = $_POST["subno"];

$conn = mysqli_connect(
    '127.0.0.1',
    'stu',
    '123456789',
    'University_DB');

$res = (object) Array();

$sql = "select * from Subject 
    where Pro_No = ".$postProNo ." and  Subject_No = ".$postSubNo;

$result = mysqli_query($conn, $sql);

if($row = mysqli_fetch_array($result)){ // 과목이 있는지 확인한다.

    $sql = "delete from Subject where Subject_No = ".$postSubNo;
    mysqli_query($conn, $sql);
    // 과목 삭제

    mysqli_query($conn, $sql);

    $res->isSucess = true;
    $res->Code = 100;

}else{ // 과목이 없는 경우
    $res->isSucess = false;
    $res->Code = 500;
}

echo json_encode($res);

?>
