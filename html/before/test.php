<?php

//header('content-Type: apllication/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$postName = $_POST['subname'];

$conn = mysqli_connect(
    '127.0.0.1',
    'stu',
    '123456789',
    'University_DB');

$res = (object) Array();
echo $postName;
echo 123456789;
$Name = $postName;
$sql = "select * from Subject where Subject_Name ='".$postName."'";

$data = Array();
$result = mysqli_query($conn, $sql);
echo $sql;
 while($row = mysqli_fetch_array($result)){

        $elements = (object) Array();

        $elements->subno= $row["Subject_No"];
        $elements->subname= $row["Subject_Name"];
        $elements->prono= $row["Pro_No"];
        $elements->subdcredit= $row["Subject_Credit"];
        $elements->sublimit= $row["Subject_Limit"];
        $elements->subbackpack= $row["Subject_Backpack"];

        array_push($data,$elements );
    }

    $res->isSucess = true;
    $res->Code = 100;
    $res->data = $data;

//    if($row = mysqli_fetch_array($result)) {
//        $res->isSucess = false;
//        $res->Code = 500; //이름 중복임
//    }else{
//
//        $res->isSucess = true;
//        $res->Code = 100; //이름 중복 아님
//
//    }
//    echo($row);

echo json_encode($res);

?>
