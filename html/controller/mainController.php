<?php

require 'function.php';
$res = (Object)Array();
header('Content-Type: json');

$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {

        case "ACCESS_LOGS":
//            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
//            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;
        /*
        * API No. 0
        * API Name : 홈 API
        * 마지막 수정 날짜 : 19.03.24
        */
        case "index":
            $res->code = 100;
            $res->message = "API Server에 들어왔습니다";
            echo json_encode($res);
            break;
        /*
                    * API No. 1
                    * API Name : 데이터 베이스 접속 API
                    * 마지막 수정 날짜 : 19.03.24
                    */
        case "test":
            $no = $vars["no"];
            http_response_code(200);
            $res->result = test();
            $res->code = 100;
            $res->message = $no;
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
               * API No. 1
               * API Name : 회원가입
               * 마지막 수정 날짜 : 19.03.24
               */
        case "user":

            $userId = $req->id;
            $userPw = $req->pw;
            $userSubPw = $req->subpw;
            $userMail = $req->mail;
            $userTel = $req->tel;

            if (!(ValidHeader($userId))) {
                if ($userPw == $userSubPw) {
                    $check_email = filter_var($userMail, FILTER_VALIDATE_EMAIL);
                    if ($check_email == true) {
                        if (!ValidHeaderEmail($userMail)) {
                            MakeUser($userId, $userPw, $userMail, $userTel);
                            $res->id = $userId;
                            $res->code = 100;
                            $res->message = "아이디 생성됨";
                            echo json_encode($res);
                        } else {
                            $res->code = 202;
                            $res->message = "중복된 이메일";
                            echo json_encode($res);
                            return;
                        }
                    } else {
                        $res->code = 203;
                        $res->message = "잘못된 이메일 형식";
                        echo json_encode($res);
                        return;
                    }
                } else {
                    $res->code = 201;
                    $res->message = "비밀번호 일치 하지 않음";
                    echo json_encode($res);
                    return;
                }
            } else {
                $res->code = 200;
                $res->message = "아이디 중복";
                echo json_encode($res);
                return;
            }
            break;
        /*
           * API No. 2
           * API Name : 회원탈퇴
           * 마지막 수정 날짜 : 19.04.01
           */
        case "deleteUser":


            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];

            $userPw = $req->pw;
            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    if (ValidHeaderPw($userId, $userPw)) {
                        //$userNo=getNo($userId);
                        $message = deleteUser($userId, $userPw);
                        $res->code = 100;
                        $res->message = $message;
                    } else {
                        $res->code = 201;
                        $res->message = "비밀번호를 제대로 입력해주세요";
                    }

                }
            } else {
                $res->code = 200;
                $res->message = "로그인이 필요한 서비스 입니다.";
            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
           * API No. 3
           * API Name : 로그인
           * 마지막 수정 날짜 : 19.03.25
           */

        case "token":
            $userId = $vars["id"];
            $userPw = $vars["pw"];

            if (!ValidHeader($userId)) {
                $res->code = 200;
                $res->message = "아이디 확인바람";
                echo json_encode($res);
                return;
            }
            if (!ValidHeaderPw($userId, $userPw)) {
                $res->code = 201;
                $res->message = "비밀번호 확인바람";
                echo json_encode($res);
                return;
            }

            $userType = getUserType($userId);
            $jwt = getJWToken($userId, $userPw, $userType, JWT_SECRET_KEY);
            $res->result->jwt = $jwt;
            // jwt 유효성 검사
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 205;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            $res->code = 100;
            $res->message = "토큰 생성";
            echo json_encode($res);
            $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userId = $userInfo->userId;
            break;
        /*
       * API No. 4
       * API Name : 홈 현재는 전체 만화를 다 보여줌
       * 마지막 수정 날짜 : 19.03.25
       */
        case "comicAll":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                }
            }
            $res->result = ComicAll();
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 4
         * API Name : 홈 현재는 전체 만화를 다 보여줌
         * 마지막 수정 날짜 : 19.03.25
         */
        case "comicDay":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $day = $vars["day"];
            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {

                }
            }
            $res->result = ComicDay($day);
            $res->code = 100;
            $res->message = "요일 만화 출력";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 5
         * API Name : 선택한 만화의 컨텐츠 목록 보기
         * 마지막 수정 날짜 : 19.03.25
         */
        case "contentAll":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $comicNo = $vars['comicno'];

            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                }
            }

            $res->result = ComicContent($comicNo);
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "pagingContent":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $contentNo = $vars['contentno'];

            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                }
            }

            $res->result = PagingContent($contentNo);
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
        * API No. 6
        * API Name : 선택한 만화의 컨텐츠 보기
        * 마지막 수정 날짜 : 19.03.25
        */
        case "comicContent":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $contentNo = $vars['contentno'];
            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                }
            }
            $res->result = Content($contentNo);
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
        * API No. 7
        * API Name : 선택한 만화의 댓글 보기
        * 마지막 수정 날짜 : 19.03.26
        */
        case "comment":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $contentNo = $vars['contentno'];
            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $data = Comment($contentNo);
                }
            } else {

            }
            $res->data = $data;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 8
      * API Name : 선택한 만화의 댓글 베스트 보기
        * 마지막 수정 날짜 : 19.03.26
      */
        case "bestComment":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $contentNo = $vars['contentno'];
            // jwt 유효성 검사
            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $res->isSuccess = TRUE;
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                }
            } else {
            }
            $data = BestComment($contentNo);
            $res->data = $data;

            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
        * API No. 9
        * API Name : 선택한 만화의 댓글 쓰기
        * 마지막 수정 날짜 : 19.03.26
        */
        case "makeComment":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $Comment = $req->comment;
            $contentNo = $req->contentno;
            if ($jwt) {
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
                    MakeComment($userNo, $contentNo, $Comment);
                    $res->code = 100;
                    $res->message = "댓글 달림";
                }

            } else {
                $res->code = 200;
                $res->message = "로그인이 필요한 서비스 입니다.";
            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 10
        * API Name : 댓글 삭제
        * 마지막 수정 날짜 : 19.03.26
        */
        case "deleteComment":

            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $commentNo = $req->commentno;
            if ($jwt) {
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
                    $message = DeleteComment($userNo, $commentNo);
                    $res->code = 100;
                    $res->message = $message;
                }
            } else {
                $res->code = 200;
                $res->message = "로그인이 필요한 서비스 입니다.";
            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
    * API No. 11
    * API Name : 만화 좋아요
    * 마지막 수정 날짜 : 19.03.26
    */
        case "comicLike":

            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $comicNo = $req->comicno;
            // jwt 유효성 검사
            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
                    $message = ComicLike($userNo, $comicNo);

                    $res->code = 100;
                    $res->message = "$message";
                }

            } else {
                $res->message = "로그인이 필요한 서비스 입니다.";
                $res->code = 200;
            }


            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
        * API No. 12
        * API Name : 관심 웹툰 추가
        * 마지막 수정 날짜 : 19.03.26
        */
        case "myComic":

            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $comicNo = $req->comicno;
            // jwt 유효성 검사
            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
                    $message = MakeMyComic($userNo, $comicNo);
                }
                $res->code = 100;
                $res->message = $message;
            } else {
                $res->code = 200;
                $res->message = "로그인이 필요합니다.";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
        * API No. 13
        * API Name : 관심 웹툰 보기
        * 마지막 수정 날짜 : 19.03.26
        */
        case "myComicList":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            if ($jwt) {

                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
                    $data = MyComicList($userNo);
                    $res->data = $userInfo;
                    $res->code = 100;
                    $res->list = $data;
                    $res->message = "성공";
                }
            } else {
                $res->code = 200;
                $res->message = "로그인이 필요합니다.";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
        * API No. 14
        * API Name : 웹툰 첫회 보기
        * 마지막 수정 날짜 : 19.03.26
        */
        case "contentFirst":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $comicNo = $vars['comicno'];

            // jwt 유효성 검사
            if($jwt){
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);

                }
            }
            $data = firstContent($userNo, $comicNo);
            $res->code = 100;
            $res->list = $data;


            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
        * API No. 15
        * API Name : 컨텐츠 좋아요 누르기
        * 마지막 수정 날짜 : 19.03.27
        */
        case "contentLike":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $contentNo = $req->contentno;
            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
                    $message = ContentLike($userNo, $contentNo);
                    $res->code = 100;
                    $res->message = $message;
                }
            } else {
                $res->code = 200;
                $res->message = "로그인이 필요한 서비스 입니다.";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
            * API No. 16
            * API Name : 댓글 좋아요 누르기
            * 마지막 수정 날짜 : 19.03.27
            */
        case "commentLike":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $commentNo = $req->commentno;
            // jwt 유효성 검사
            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
                    $message = CommentLike($userNo, $commentNo);

                    $res->code = 100;
                }
            } else {
                $res->code = 200;
                $res->message = "로그인이 필요한 서비스 입니다.";
            }
            $res->message = $message;
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
        * API No. 17
        * API Name : 컨텐츠 평점 매기기
        * 마지막 수정 날짜 : 19.03.27
        */
        case "contentRate":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $contentNo = $req->contentno;
            $contentRate = $req->contentrate;
            if ($jwt) {
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
                    if ($contentRate <= 10) {
                        $message = ContentRate($userNo, $contentNo, $contentRate);
                        $res->code = 100;
                        $res->message = $message;
                    } else {
                        $res->user = $userInfo;
                        $res->code = 500;
                        $res->message = "평점을 제대로 입력하세요";
                    }
                }
            } else {

                $res->code = 200;
                $res->message = "로그인이 필요한 서비스 입니다.";
            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
    * API No. 18
    * API Name : 만화 검색
    * 마지막 수정 날짜 : 19.04.01
    */

        case "comicSearch":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
           // $encode = $vars['input'];
            $input = urldecode($vars['input']);
            //$input = iconv("EUC-KR", "UTF-8", $input);

            if ($jwt) {
                // jwt 유효성 검사
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
                }
            } else {
            }

            $data = comicSearch($input);
            $res->code = 100;
            $res->data = $data;
            $res->message = $input;
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
        * API No. 19
        * API Name : 댓글 싫어요 누르기
        * 마지막 수정 날짜 : 19.04.02
        */
        case "commentDislike":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $commentNo = $req->commentno;
            // jwt 유효성 검사
            if ($jwt) {
                if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                    $res->isSuccess = FALSE;
                    $res->code = 205;
                    $res->message = "유효하지 않은 토큰입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                } else {
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
                    $message = CommentDislike($userNo, $commentNo);

                    $res->code = 100;
                }
            } else {
                $res->code = 200;
                $res->message = "로그인이 필요한 서비스 입니다.";
            }
            $res->message = $message;
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}