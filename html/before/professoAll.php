<?php
header('content-Type: apllication/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = mysqli_connect(
    '127.0.0.1',
    'stu',
    '123456789',
    'University_DB');

$sql = "SELECT * FROM Professor ";
$result = mysqli_query($conn, $sql);

$data = Array();
$res = (object) Array();

while($row = mysqli_fetch_array($result)){

    $elements = (object) Array();

    $elements->prono= $row["Pro_No"];
    $elements->proname= $row["Pro_Name"];
    $elements->prodep= $row["Pro_Dep"];

    array_push($data,$elements );
}

$res->isSucess = true;
$res->Code = 100;
$res->data = $data;
echo json_encode($res);
?>
