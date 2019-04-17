<?php
header('content-Type: apllication/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$postNo = $_GET["no"];

$conn = mysqli_connect(
    '127.0.0.1',
    'stu',
    '123456789',
    'University_DB');

$sql = "SELECT * FROM Student where Student_No = ".$postNo;
$result = mysqli_query($conn, $sql);

$data = Array();
$res = (object) Array();
if($row = mysqli_fetch_array($result)){

    $elements = (object) Array();

    $elements->stno= $row["Student_No"];
    $elements->stname= $row["Student_Name"];
    $elements->stdep= $row["Student_Dep"];
    $elements->stCredit = $row["Student_Credit"];

    array_push($data,$elements );

    $res->isSucess = true;
    $res->Code = 100;
    $res->data = $data;

}else{
    $res->isSucess = false;
    $res->Code = 500;
}

echo json_encode($res);
?>
