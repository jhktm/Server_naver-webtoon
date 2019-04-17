<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'pdoConnect.php';

function test()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM User_TB;";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;
}

function getUserType($userId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT User_Type FROM User_TB where User_Id = :userid;";
    $st = $pdo->prepare($query);
    $st->bindParam(':userid', $userId);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetch();
    $st = null;
    $pdo = null;
    return $res[User_Type];
}

function ValidHeader($userId)
{
    $pdo = pdoSqlConnect();
    $query = " SELECT * FROM User_TB where User_Id = :userid and User_Status = 1";
    $st = $pdo->prepare($query);
    $st->bindParam(':userid', $userId);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetch();
    $st = null;
    $pdo = null;
    return $res; //있으면 1 없으면 0
}

function ValidHeaderPw($userId, $userPw)
{
    $pdo = pdoSqlConnect();
    $query = " SELECT * FROM User_TB where User_Id = :userid and User_Pw = :userpw and User_Status = 1";
    $st = $pdo->prepare($query);
    $st->bindParam(':userid', $userId);
    $st->bindParam(':userpw', $userPw);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetch();
    $st = null;
    $pdo = null;
    return $res; //있으면 1 없으면 0
}

function ValidHeaderEmail($userMail)
{
    $pdo = pdoSqlConnect();
    $query = " SELECT * FROM User_TB where User_Email =:mail and User_Status = 1";
    $st = $pdo->prepare($query);
    $st->bindParam(':mail', $userMail);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetch();
    $st = null;
    $pdo = null;
    return $res; //있으면 1 없으면 0
}

function MakeUser($userId, $userPw, $userMail, $userTel)
{
    $pdo = pdoSqlConnect();
    $query = "insert into User_TB(User_Id,User_Pw,User_Email,User_Tel) values(:userId,:userPw,:userMail,:usertel)";
    $st = $pdo->prepare($query);
    $st->bindParam(':userId', $userId);
    $st->bindParam(':userPw', $userPw);
    $st->bindParam(':userMail', $userMail);
    $st->bindParam(':usertel', $userTel);
    $st->execute();
    $st = null;
    $pdo = null;
    return; //있으면 1 없으면 0
}
function deleteUser($userId, $userPw)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE User_TB SET User_Status =0  WHERE User_Id = :userId and User_Pw = :userPw and User_Status = 1";
    $st = $pdo->prepare($query);
    $st->bindParam(':userId', $userId);
    $st->bindParam(':userPw', $userPw);
    $st->execute();
    $st = null;
    $pdo = null;
    return "회원탈퇴되었습니다.";
}


function ComicAll()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM Comic_TB;";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $res;
}

function ComicDay($day)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('SELECT * FROM Comic_TB where Comic_Day = :comicday');
    $st->bindParam(':comicday', $day);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function ComicContent($comicNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('SELECT * FROM Content_TB where Comic_No = :comicno order by Content_No desc limit 5');
    $st->bindParam(':comicno', $comicNo);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function PagingContent($contentNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('SELECT Comic_No FROM Content_TB where Content_No = :contentno order by Content_No desc limit 3');
    $st->bindParam(':contentno', $contentNo);
    $st->execute();
    $res = $st->fetch();
    $comicNo = $res['Comic_No'];

    $st = $pdo->prepare('SELECT * FROM Content_TB where Comic_No = :comicno and Content_No <= :contentno order by Content_No desc limit 3');
    $st->bindParam(':comicno', $comicNo);
    $st->bindParam(':contentno', $contentNo);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function Content($contentNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('SELECT Content_Content FROM Content_TB where Content_No = :contentno ');
    $st->bindParam(':contentno', $contentNo);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function firstContent($userNo, $ComicNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('SELECT cm.Comic_Name , ct.Content_Name , ct.Content_Content 
FROM Comic_TB as cm join Content_TB as ct on cm.Comic_No = ct.Comic_No
where cm.Comic_No = :comicno');
    $st->bindParam(':comicno', $ComicNo);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetch();

    $st = null;
    $pdo = null;

    return $res;
}

function MakeComment($userNo, $contentNo, $comment)
{
    $pdo = pdoSqlConnect();

    $st = $pdo->prepare('insert into Comment_TB(Content_No, User_No, Comment_Content)
values (' . $contentNo . ',' . $userNo . ',:comment)');
    $st->bindParam(':comment', $comment);
    $st->execute();

    return;
}

function getNo($userId)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('select User_No from User_TB where User_Id = :userid');
    $st->bindParam(':userid', $userId);
    $st->execute();
    $res = $st->fetch();
    $st = null;
    $pdo = null;
    return $res["User_No"];
}

function Comment($contentNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('select User_Id ,Comment_No ,Comment_Content ,Comment_Like, Comment_DisLike , Comment_Date
 from Comment_TB as cm join User_TB as us on cm.User_No = us.User_No
where Content_No = :contentno and Comment_Status = 1 order by Comment_Date desc ');
    $st->bindParam(':contentno', $contentNo);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function BestComment($contentNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('select User_Id ,Comment_No ,Comment_Content ,cm.Comment_Like, Comment_DisLike , Comment_Date
from Comment_TB as cm join User_TB as us on cm.User_No = us.User_No
where Content_No = :contentno and Comment_Status = 1 ORDER BY cm.Comment_Like DESC limit 3');
    $st->bindParam(':contentno', $contentNo);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}

function DeleteComment($userNo, $commentNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('Select *from Comment_TB where Comment_No = :commentno and User_No = :userno and Comment_Status = 1');
    $st->bindParam(':commentno', $commentNo);
    $st->bindParam(':userno', $userNo);
    $st->execute();
    $res = $st->fetch();

    if ($res["Comment_No"]) {
        $st = $pdo->prepare('UPDATE Comment_TB SET Comment_Status =0  WHERE Comment_No=:commentno');
        $st->bindParam(':commentno', $commentNo);
        $st->execute();
        $message = "삭제되었습니다.";
    } else {
        $message = "삭제할게 없다.";
    }
    $st = null;
    $pdo = null;
    return $message;
}

function ComicLike($userNo, $comicNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('Select * from Heart_TB where  User_No = :userno and Post_No = :comicno and Post_Type = 1');
    $st->bindParam(':userno', $userNo);
    $st->bindParam(':comicno', $comicNo);
    $st->execute();
    $res = $st->fetch();

    if ($res["Heart_No"]) {// 있을때
        $st = $pdo->prepare('DELETE FROM Heart_TB WHERE User_No = :userno and Post_No = :comicno and Post_Type = 1');
        $st->bindParam(':comicno', $comicNo);
        $st->bindParam(':userno', $userNo);
        $st->execute();
        $message = "만화에 좋아요 취소";
    } else { // 없을때
        $st = $pdo->prepare('insert into Heart_TB(User_No, Post_No , Post_Type)  values (:userno,:comicno,1)');
        $st->bindParam(':comicno', $comicNo);
        $st->bindParam(':userno', $userNo);
        $st->execute();
        $message = "만화 좋아요 누름!";
    }

    $st = $pdo->prepare('SELECT COUNT(Heart_No) as countsum fROM Heart_TB where Post_No = :comicno and Post_Type = 1');
    $st->bindParam(':comicno', $comicNo);
    $st->execute();
    $res = $st->fetch();
    $countSum = $res["countsum"];

    $st = $pdo->prepare('update Comic_TB set Comic_Heart = :countsum where Comic_No = :comicno');
    $st->bindParam(':countsum', $countSum);
    $st->bindParam(':comicno', $comicNo);

    $st->execute();


    $st = $pdo->prepare('Select * from Comic_TB where  Comic_No = :comicno ');
    $st->bindParam(':comicno', $comicNo);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $message;
}

function ContentLike($userNo, $contnetNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('Select * from Heart_TB where  User_No = :userno and Post_No = :contentno and Post_Type = 2');
    $st->bindParam(':userno', $userNo);
    $st->bindParam(':contentno', $contnetNo);
    $st->execute();
    $res = $st->fetch();

    if ($res["Heart_No"]) {// 있을때
        $st = $pdo->prepare('DELETE FROM Heart_TB WHERE User_No = :userno and Post_No = :contentno and Post_Type = 2');
        $st->bindParam(':contentno', $contnetNo);
        $st->bindParam(':userno', $userNo);
        $st->execute();
        $message = "만화에 좋아요 취소";
    } else { // 없을때
        $st = $pdo->prepare('insert into Heart_TB(User_No, Post_No , Post_Type)  values (:userno,:contentno,2)');
        $st->bindParam(':contentno', $contnetNo);
        $st->bindParam(':userno', $userNo);
        $st->execute();
        $message = "만화 좋아요 누름!";
    }

    $st = $pdo->prepare('SELECT COUNT(Heart_No) as countsum fROM Heart_TB where Post_No = :contentno and Post_Type = 2');
    $st->bindParam(':contentno', $contnetNo);
    $st->execute();
    $res = $st->fetch();
    $countSum = $res["countsum"];

    $st = $pdo->prepare('update Content_TB set Content_Heart = :countsum where Content_No = :contentno');
    $st->bindParam(':countsum', $countSum);
    $st->bindParam(':contentno', $contnetNo);

    $st->execute();


    $st = $pdo->prepare('Select * from Content_TB where  Content_No = :contentno ');
    $st->bindParam(':contentno', $contnetNo);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return $message;
}

function CommentLike($userNo, $commentNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('Select * from Heart_TB where  User_No = :userno and Post_No = :commentno and Post_Type = 3');
    $st->bindParam(':userno', $userNo);
    $st->bindParam(':commentno', $commentNo);
    $st->execute();
    $res = $st->fetch();

    if ($res["Heart_No"]) {// 있을때
        $st = $pdo->prepare('DELETE FROM Heart_TB WHERE User_No = :userno and Post_No = :commentno and Post_Type = 3');
        $st->bindParam(':commentno', $commentNo);
        $st->bindParam(':userno', $userNo);
        $st->execute();
        $message = "댓글에 좋아요 취소";
    } else { // 없을때
        $st = $pdo->prepare('insert into Heart_TB(User_No, Post_No , Post_Type)  values (:userno,:commentno,3)');
        $st->bindParam(':commentno', $commentNo);
        $st->bindParam(':userno', $userNo);
        $st->execute();
        $message = "댓글 좋아요 누름!";
    }

    $st = $pdo->prepare('SELECT COUNT(Heart_No) as countsum fROM Heart_TB where Post_No = :commentno and Post_Type = 3');
    $st->bindParam(':commentno', $commentNo);
    $st->execute();
    $res = $st->fetch();
    $countSum = $res["countsum"];

    $st = $pdo->prepare('update Comment_TB set Comment_Like = :countsum where Comment_No = :commentno');
    $st->bindParam(':countsum', $countSum);
    $st->bindParam(':commentno', $commentNo);
    $st->execute();

    $st = null;
    $pdo = null;
    return $message;
}
function CommentDislike($userNo, $commentNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('Select * from Heart_TB where  User_No = :userno and Post_No = :commentno and Post_Type = 4');
    $st->bindParam(':userno', $userNo);
    $st->bindParam(':commentno', $commentNo);
    $st->execute();
    $res = $st->fetch();

    if ($res["Heart_No"]) {// 있을때
        $st = $pdo->prepare('DELETE FROM Heart_TB WHERE User_No = :userno and Post_No = :commentno and Post_Type = 4');
        $st->bindParam(':commentno', $commentNo);
        $st->bindParam(':userno', $userNo);
        $st->execute();
        $message = "댓글에 싫어요 취소";
    } else { // 없을때
        $st = $pdo->prepare('insert into Heart_TB(User_No, Post_No , Post_Type)  values (:userno,:commentno,4)');
        $st->bindParam(':commentno', $commentNo);
        $st->bindParam(':userno', $userNo);
        $st->execute();
        $message = "댓글 싫어요 누름!";
    }

    $st = $pdo->prepare('SELECT COUNT(Heart_No) as countsum fROM Heart_TB where Post_No = :commentno and Post_Type = 4');
    $st->bindParam(':commentno', $commentNo);
    $st->execute();
    $res = $st->fetch();
    $countSum = $res["countsum"];

    $st = $pdo->prepare('update Comment_TB set Comment_DisLike = :countsum where Comment_No = :commentno');
    $st->bindParam(':countsum', $countSum);
    $st->bindParam(':commentno', $commentNo);
    $st->execute();

    $st = null;
    $pdo = null;
    return $message;
}

function ContentRate($userNo, $contentNo, $contentRate)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('Select * from Rate_TB where  User_No = :userno and Content_No = :contentno');
    $st->bindParam(':userno', $userNo);
    $st->bindParam(':contentno', $contentNo);
    $st->execute();
    $res = $st->fetch();

    if ($res["Rate_No"]) {// 있을때
        $message = "이미 참여하셨습니다.";
        $st = null;
        $pdo = null;
        return $message;
    } else { // 없을때
        $st = $pdo->prepare('insert into Rate_TB(User_No, Content_No , Rate_Rate)  values (:userno,:contentno,:raterate)');
        $st->bindParam(':userno', $userNo);
        $st->bindParam(':contentno', $contentNo);
        $st->bindParam(':raterate', $contentRate);
        $st->execute();
    }
//    $st = $pdo->prepare('SELECT  Comic_No fROM Content_TB  where Content_No = :contentno');
//    $st->bindParam(':contentno',$contentNo);
//    $st->execute();
//    $res = $st->fetch();
//    $comicNo = $res["Comic_No"];

    $st = $pdo->prepare('SELECT COUNT(Rate_No) as ratecount , SUM(Rate_Rate) as ratesum 
fROM Rate_TB  where Content_No = :contentno');
    $st->bindParam(':contentno', $contentNo);
    $st->execute();
    $res = $st->fetch();
    $rateCount = $res["ratecount"];
    $rateSum = $res["ratesum"];


    $rate = $rateSum / $rateCount;

    $st = $pdo->prepare('update Content_TB set Content_Rating = :rate where Content_No = :contentno');
    $st->bindParam(':rate', $rate);
    $st->bindParam(':contentno', $contentNo);
    $st->execute();


    $st = $pdo->prepare('SELECT COUNT(ct.Content_No) as contentcount , SUM(ct.Content_Rating) as contentsum ,cm.Comic_No as Comic_No
fROM Content_TB as ct join (SELECT Content_No ,Comic_No fROM Content_TB  where Content_No = :contentno)as cm
  on ct.Comic_No = cm.Comic_No;');

    $st->bindParam(':contentno', $contentNo);
    //$st->bindParam(':comicno',$comicNo);
    $st->execute();
    $res = $st->fetch();
    $comicNo = $res["Comic_No"];
    $contentCount = $res["contentcount"];
    $contentSum = $res["contentsum"];
    $contentrate = $contentSum / $contentCount;

    $st = $pdo->prepare('update Comic_TB set Comic_Rating = :rate where Comic_No = :comicno');
    $st->bindParam(':rate', $contentrate);
    $st->bindParam(':comicno', $comicNo);
    $st->execute();

    $st = $pdo->prepare('Select Content_No , Content_Name , Content_Rating , cm.Comic_No , Comic_Rating
from Content_TB as ct join Comic_TB as cm 
on ct.Comic_No = cm.Comic_No 
where  Content_No = :contentno ');
    $st->bindParam(':contentno', $contentNo);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;
}

function MakeMyComic($userNo, $comicNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('Select * from My_comic_TB where Comic_No= :comicno and User_No = :userno');
    $st->bindParam(':comicno', $comicNo);
    $st->bindParam(':userno', $userNo);
    $st->execute();
    $res = $st->fetch();

    if ($res["Comic_No"]) {// 있을때
        $st = $pdo->prepare('DELETE FROM My_comic_TB WHERE Comic_No =:comicno and User_No = :userno');
        $st->bindParam(':comicno', $comicNo);
        $st->bindParam(':userno', $userNo);
        $st->execute();
        $message = "관심 웹툰에서 삭제되었습니다.";
    } else { // 없을때
        $st = $pdo->prepare('insert into My_comic_TB(User_No, Comic_No)  values (:userno,:comicno )');
        $st->bindParam(':comicno', $comicNo);
        $st->bindParam(':userno', $userNo);
        $st->execute();
        $message = "관심 웹툰에 등록되었습니다.";
    }
    $st = null;
    $pdo = null;
    return $message;
}

function MyComicList($userNo)
{
    $pdo = pdoSqlConnect();
    $st = $pdo->prepare('Select * from My_comic_TB as my join Comic_TB as cm on my.Comic_No = cm.Comic_No
    where User_No = :userno');
    $st->bindParam(':userno', $userNo);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;
}
function comicSearch($input)
{
    $pdo = pdoSqlConnect();
    $str = "%".$input."%";
    $st = $pdo->prepare('Select * from Comic_TB 
where Comic_Name like :comicname or Comic_Painting like :comicname or Comic_Story like :comicname' );
    $st->bindParam(':comicname', $str);
   // $st->bindParam(':painting', $input);
    //$st->bindParam(':comicstory', $input);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res;
}
