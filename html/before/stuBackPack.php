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

    $res = (object) Array();

    $sql = "SELECT * FROM Student where Student_No = ". $postNo;
    $result = mysqli_query($conn, $sql);
    if($row = mysqli_fetch_array($result)){


        $sql = "SELECT st.Student_Name , sj.Subject_Name , sj.Subject_No
FROM Backpack AS bP , Subject AS sj ,Student AS st
WHERE bP.Subject_No = sj.Subject_No and  bP.Student_No = st.Student_No and bP.Student_No=". $postNo;

        $result = mysqli_query($conn, $sql);
        $data = Array();

        while($row = mysqli_fetch_array($result)){

            $elements = (object) Array();

            $elements->stname= $row["Student_Name"];
            $elements->subno= $row["Subject_No"];
            $elements->subname= $row["Subject_Name"];

            array_push($data,$elements );
        }

        $res->isSucess = true;
        $res->Code = 100;
        $res->data = $data;

    }else{
        $res->isSucess = false;
        $res->Code = 500;
    }


    echo json_encode($res);
?>
