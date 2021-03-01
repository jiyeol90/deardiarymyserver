<?php
require "../dbconnect/connection.php";

//$_POST 는 헤더에 포함된 POST data를 파싱한 결과를 가지지만,
//file_get_contents('php://input'); 는 파싱하기전 POST data를 가져온다.
$data = json_decode(file_get_contents('php://input'),true);    //POST로 받은 값을 json형식으로 decode

$userId = $data['id'];
//print_r($userId);

if ($conn) {
         
    $sqlCheckId = "SELECT * FROM user WHERE user_id = '$userId'";
    $userIdQuery = mysqli_query($conn, $sqlCheckId);

    if (mysqli_num_rows($userIdQuery) > 0) {

        $row = $userIdQuery->fetch_array(MYSQLI_ASSOC);

        $userinfo_arr = array(
            "id" => $row['id'],
            "user_id" => $row['user_id'],
            "user_name" => $row['user_name']
        );

        echo json_encode($userinfo_arr, JSON_UNESCAPED_UNICODE); //한글을 유니코드 형태로 자동으로 변환
    } else {
        echo "Invalid ID";
    }

    
 
} else {
 echo "Connection Error";
}



?>
