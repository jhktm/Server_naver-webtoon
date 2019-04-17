<?php

    header('content-Type: apllication/json');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $postStNo = $_POST["stno"];
    $postSubNo = $_POST["subno"];

    $conn = mysqli_connect(
        '127.0.0.1',
        'stu',
        '123456789',
        'University_DB');

    $res = (object) Array();

    $sql = "select * from Backpack 
    where Student_No = ".$postStNo ." and  Subject_No = ".$postSubNo;

    $result = mysqli_query($conn, $sql);

    if($row = mysqli_fetch_array($result)){

        $sql = "delete from Backpack where Student_No = ".$postStNo ." and  Subject_No = ".$postSubNo;
        mysqli_query($conn, $sql);

        $sql = "UPDATE Subject SET Subject_Backpack = Subject_Backpack -1
        where Subject_No =".$postSubNo;;
        mysqli_query($conn, $sql);
        
        $res->isSucess = true;
        $res->Code = 100;

    }else{
        $res->isSucess = false;
        $res->Code = 500;
    }

    echo json_encode($res);

?>
