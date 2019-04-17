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

    if($row = mysqli_fetch_array($result)) {
        $res->isSucess = false;
        $res->Code = 500; //중복
    }else{
        $sql = "SELECT * FROM Student where Student_No = ". $postStNo;
        $result = mysqli_query($conn, $sql);
        if($row = mysqli_fetch_array($result)){

            $stCredit = $row["Student_Credit"];
            // - 학생의 학점 제한

            $sql = "SELECT * FROM Subject where Subject_No = ". $postSubNo;
            $result = mysqli_query($conn, $sql);

            if($row = mysqli_fetch_array($result)){

                $subCredit = $row["Subject_Credit"];
                // - 새로운 학점


                $sql = "select st.stNo as stNo ,sum(sj.subCredit) as Credit
                    from Backpack as bP , Subject as sj , Student as st
                    where bP.Subject_No = sj.Subject_No and bP.Student_No = st.Student_No and bP.Student_No =" . $postStNo;


                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                $sumCredit = $row["Credit"];

                if ($stCredit >= ($sumCredit + $subCredit)) {

                    $sql = "insert into Backpack values('$postStNo','$postSubNo')";
                    $res = (object)Array();
                    mysqli_query($conn, $sql);


                    $sql = "UPDATE Subject SET Subject_Backpack = Subject_Backpack + 1
                        where Subject_no =".$postSubNo;

                    mysqli_query($conn, $sql);
                    $res->isSucess = true;
                    $res->Code = 100; // 성공

                } else {

                    $res->isSucess = false;
                    $res->Code = 503; //학점 확인
                }
            }else{
                $res->isSucess = false;
                $res->Code = 502; // 과목확인
            }
        }else{
            $res->isSucess = false;
            $res->Code = 501; // 학생 확인
        }

    }


    echo json_encode($res);

?>
