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

$sql = "SELECT * FROM Professor where Pro_No = ".$postNo;
$result = mysqli_query($conn, $sql);

$data = Array();
$data1 = Array();
$res = (object) Array();


if($row = mysqli_fetch_array($result)){



    $elements = (object) Array();

    $elements->prono= $row["Pro_No"];
    $elements->proname= $row["Pro_Name"];
    $elements->prodep= $row["Pro_Dep"];
    array_push($data,$elements );

    $sql = "SELECT * FROM Subject where Pro_No = ".$postNo;
    $result = mysqli_query($conn, $sql);

    while($row = mysqli_fetch_array($result)){

        $sub = (object) Array();
        $sub->subno= $row["Subject_No"];
        $sub->subname= $row["Subject_Name"];
        $sub->prono= $row["Pro_No"];
        array_push($data1,$sub );
    }

    $res->isSucess = true;
    $res->Code = 100;
    $res->data = $data;
    $res->subdata = $data1;

}else{
    $res->isSucess = false;
    $res->Code = 500;
}

echo json_encode($res);
?>
