<?php
require "../dbconnect/connection.php";

//$_POST 는 헤더에 포함된 POST data를 파싱한 결과를 가지지만,

$myId = $_POST['myId'];
$friendId = $_POST['friendId'];


$result_message;
//DB insert
if ($conn)
{
    //이미 참여했던 방인지 조회한다.
    //1. 먼저 나와 상대가 참여한 방을 조회한다. (나와 , 상대, 다른사람도 있는 방이 있다면 그 방도 조회된다.)
    $sql_search_room = "SELECT room_id , count(user_id) AS roomType FROM participate_in WHERE user_id IN ('$myId', '$friendId') GROUP BY room_id HAVING roomType = 2";


    $roomSearchResult = mysqli_query($conn, $sql_search_room);
    $rowCnt = mysqli_num_rows($roomSearchResult);

    if($rowCnt > 0) {
        
        $roomIdArray = array();

    for($i = 0; $i < $rowCnt; $i++) {
        $row = mysqli_fetch_array($roomSearchResult, MYSQLI_ASSOC);

        array_push($roomIdArray, $row["room_id"]);
    }
    
        //print_r($roomIdArray); 
    for($i = 0; $i < count($roomIdArray); $i++) {

        //2. 해당 방 번호로 참여한 인원수를 조회하고 2명이면 나와 상대만 있는 방이므로 탐색완료.
        $sql_match_room = "SELECT count(user_id) AS roomType FROM participate_in WHERE room_id = '$roomIdArray[$i]'";

        $result = mysqli_query($conn, $sql_match_room);
        $participant = mysqli_fetch_assoc($result);

        //1:1방일 경우
        if($participant['roomType'] == 2) {
            echo 'roomId@'.$roomIdArray[$i];
            exit();
        }

        echo $roomIdArray[$i].' 번 방에 참여한 인원 : '. $participant['roomType']. '   ';
        }
    
    }   
    //해당 방이 없을때
     else {

        //가장 마지막 방번호를 주거나 테이블에 방이 없으면 없다고 알려주어야 한다.
        $sql_last_room = "SELECT max(id) AS lastroomId FROM chatting_room order by id desc limit 1"; //마지막 room ID 가져오기 없을시 NULL반환
        $result = mysqli_query($conn, $sql_last_room);

        $existornot = mysqli_fetch_assoc($result);

        //print_r($existornot);

        if($existornot['lastroomId'] === null) {
            $result_message = 'noRoom@';
        } else {
            $result_message = 'lastRoomId@'.$existornot['lastroomId'];
        }
       
    }

    echo $result_message;
}
    
else {
        
        echo "DB Fail";
}

mysqli_close($conn);
?>