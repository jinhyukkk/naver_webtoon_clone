<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 20.08.29
         */
        case "createJwt":
            http_response_code(200);

////            accessToken으로 회원정보 입력
//            $token = "YOUR_ACCESS_TOKEN";
//            $header = "Bearer ".$token; // Bearer 다음에 공백 추가
//            $url = "https://openapi.naver.com/v1/nid/me";
//            $is_post = false;
//            $ch = curl_init();
//            curl_setopt($ch, CURLOPT_URL, $url);
//            curl_setopt($ch, CURLOPT_POST, $is_post);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            $headers = array();
//            $headers[] = "Authorization: ".$header;
//            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//            $response = curl_exec ($ch);
//            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//            echo "status_code:".$status_code."<br>";
//            curl_close ($ch);
//            if($status_code == 200) {
//                echo $response;
//            } else {
//                echo "Error 내용:".$response;
//            }
//            // 1) 로그인 시 email, password 받기
//            if (!isValidUser($req->userId, $req->pwd)) { // JWTPdo.php 에 구현
//                $res->isSuccess = FALSE;
//                $res->code = 201;
//                $res->message = "유효하지 않은 아이디 입니다";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                return;
//            }
            if (!isset($req->email)) { // JWTPdo.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 이메일 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            if (!isEmailExist($req->email)) { // JWTPdo.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 이메일 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            // 2) JWT 발급
            // Payload에 맞게 다시 설정 요함, 아래는 Payload에 userIdx를 넣기 위한 과정

            $userIdx = getUserIdxByEMAIL($req->email);  // JWTPdo.php 에 구현
            $jwt = getJWT($userIdx, JWT_SECRET_KEY); // function.php 에 구현
            $res->result = new stdClass();
            $res->result->jwt = $jwt;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "jwt 발급 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 2
         * API Name : JWT 유효성 검사 테스트 API -> 자동로그인
         * 마지막 수정 날짜 : 20.11.07
         */
        case "validateJwt":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // 1) JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // 2) JWT Payload 반환
            http_response_code(200);
            $res->userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "자동로그인 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
