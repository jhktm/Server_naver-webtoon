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

        case "fcmToken":
            $userId = $req->id;
            $userPw = $req->pw;
            $token = $req->token;

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

            register($token,$userId);

            $res->code = 100;
            $res->message = "토큰 생성 및 fdm저장";
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
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
                    $res->check = CheckLike($userNo, $comicNo,1);
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
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
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
                    $userInfo = getDataByJWToken($jwt, JWT_SECRET_KEY);
                    $userId = $userInfo->userId;
                    $userNo = getNo($userId);
                    $res->check = CheckLike($userNo, $contentNo,2);


                }
            }
            $res->result = Content($contentNo);
            $res->comment = commentNumber($contentNo);
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

                }
            } else {

            }
            $data = Comment($contentNo);
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
                    $like = ComicLike($userNo, $comicNo);

                    $res->code = 100;
                    $res->like= $like;
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
            $data = firstContent($comicNo);
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
                    $like = ContentLike($userNo, $contentNo);
                    $res->code = 100;
                    $res->like = $like;
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
                    $like = CommentLike($userNo, $commentNo);

                    $res->code = 100;
                }
            } else {
                $res->code = 200;
                $res->message = "로그인이 필요한 서비스 입니다.";
            }
            $res->like = $like;
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
                    $dislike = CommentDislike($userNo, $commentNo);

                    $res->code = 100;
                }
            } else {
                $res->code = 200;
                $res->message = "로그인이 필요한 서비스 입니다.";
            }
            $res->dislike = $dislike;
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
        * API No. 20
        * API Name : 푸쉬알림
        * 마지막 수정 날짜 : 19.04.02
        */
        case "pushNow":
            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
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
                    $token = getToken($userId);
                    $myMessage = "푸쉬 성공!";
                    $message_status=sendFcm($token, $myMessage, GOOGLE_API_KEY);
                    $res->code = 100;
                    $res->message = $message_status;
                }
            } else {
                $res->code = 200;
                $res->message = "로그인이 필요한 서비스 입니다.";
            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "mailSend":
            $mail = $vars['mail'];
            $check_email = filter_var($mail, FILTER_VALIDATE_EMAIL);
            if ($check_email == true) {

                sendMail(MAIL_ADDRESS, "jihwan_kim", "메일 제목입니다.", "이메일 보내보기.",$mail, "jihwan_kim");
                $res->code = 100;
                $res->message = "이메일 전송되었습니다.";

            }else{
                $res->code = 200;
                $res->message = "잘못된 이메일 형식";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "fileUpload":
            $file_name = $_FILES['upload_file']['name'];                // 업로드한 파일명
            $file_tmp_name = $_FILES['upload_file']['tmp_name'];   // 임시 디렉토리에 저장된 파일명
            $file_size = $_FILES['upload_file']['size'];                 // 업로드한 파일의 크기
            $mimeType = $_FILES['upload_file']['type'];                 // 업로드한 파일의 MIME Type
// 첨부 파일이 저장될 서버 디렉토리 지정(원하는 경로에 맞게 수정하세요)

            $save_dir = './img/test/';



// 업로드 파일 확장자 검사 (필요시 확장자 추가)

            if($mimeType=="html" ||

                $mimeType=="htm" ||

                $mimeType=="php" ||

                $mimeType=="php3" ||

                $mimeType=="inc" ||

                $mimeType=="pl" ||

                $mimeType=="cgi" ||

                $mimeType=="txt" ||

                $mimeType=="TXT" ||

                $mimeType=="asp" ||

                $mimeType=="jsp" ||

                $mimeType=="phtml" ||

                $mimeType=="js" ||

                $mimeType=="") {

                echo("업로드할수 없는 파일형식");

                exit;

            }
            // 파일명 변경 (업로드되는 파일명을 별도로 생성하고 원래 파일명을 별도의 변수에 지정하여 DB에 기록할 수 있습니다.)
            $real_name = $file_name;     // 원래 파일명(업로드 하기 전 실제 파일명)

            $arr = explode(".", $real_name);	 // 원래 파일의 확장자명을 가져와서 그대로 적용 $file_exe

            $arr1 = $arr[0];
            $arr2 = $arr[1];
            $arr3 = $arr[2];
            $arr4 = $arr[3];
            if($arr4) {

                $file_exe = $arr4;

            } else if($arr3 && !$arr4) {

                $file_exe = $arr3;

            } else if($arr2 && !$arr3) {

                $file_exe = $arr2;

            }
            $file_time = time();

            $file_Name = "file_".$file_time.".".$file_exe;	 // 실제 업로드 될 파일명 생성	(본인이 원하는 파일명 지정 가능)

            $change_file_name = $file_Name;			 // 변경된 파일명을 변수에 지정

            $real_name = addslashes($real_name);		// 업로드 되는 원래 파일명(업로드 하기 전 실제 파일명)

            $real_size = $file_size;                         // 업로드 되는 파일 크기 (byte)

//파일을 저장할 디렉토리 및 파일명 전체 경로
            $dest_url = $save_dir . $change_file_name;
//파일을 지정한 디렉토리에 업로드
            if(!move_uploaded_file($file_tmp_name, $dest_url))

            {
                die("파일을 지정한 디렉토리에 업로드하는데 실패했습니다.");

            }

// DB에 기록할 파일 변수 (DB에 저장이 필요한 경우 아래 변수명을 기록하시면 됩니다.)

            /*

                $change_file_name : 실제 서버에 업로드 된 파일명. 예: file_145736478766.gif

                $real_name : 원래 파일명. 예: 풍경사진.gif

                $real_size : 파일 크기(byte)

            */

            $res->code = 100;
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}