<?php
require "../dbconnect/connection.php";

//$_POST 는 헤더에 포함된 POST data를 파싱한 결과를 가지지만,
$myId = $_POST['myId'];
$friendId = $_POST['friendId'];

//친구리스트를 받을 변수
$friendsList;

//나를 포함한 방안의 모든 멤버들
$memberList;

//1:1 채팅방인지 단체채팅방인지 구분해준다.
$isOneonOne = true;

//친구리스트를 가지고 있는 경우
if (strpos($friendId, '/') !== false)
{

    $friendsList = explode('/', $friendId);

    $memberList = $friendsList;
    array_push($memberList, $myId);

    // print_r($memberList);
    // exit();
    // echo '친구리스트 명수 : '. count($friendsList).' => ';
    // for($i = 0; $i < count($friendsList); $i++) {
    //     echo '친구 ['.$i.'] :'.$friendsList[$i]. ' ';
    // }
    $isOneonOne = false;
    //exit();
    
}

$result_message;
//DB insert
if ($conn)
{

    /////////////////////////////////////////////////////1:1방
    if($isOneonOne) {
    //이미 참여했던 방인지 조회한다.
    //1. 먼저 나와 상대가 참여한 방을 조회한다. (나와 , 상대, 다른사람도 있는 방이 있다면 그 방도 조회된다.)
    $sql_search_room = "SELECT room_id , count(user_id) AS roomType FROM participate_in WHERE user_id IN ('$myId', '$friendId') GROUP BY room_id HAVING roomType = 2";

    $roomSearchResult = mysqli_query($conn, $sql_search_room);
    $rowCnt = mysqli_num_rows($roomSearchResult);

    if ($rowCnt > 0)
    {

        $roomIdArray = array();

        for ($i = 0;$i < $rowCnt;$i++)
        {
            $row = mysqli_fetch_array($roomSearchResult, MYSQLI_ASSOC);

            array_push($roomIdArray, $row["room_id"]);
        }

        //print_r($roomIdArray);
        for ($i = 0; $i < count($roomIdArray); $i++)
        {

            //2. 해당 방 번호로 참여한 인원수를 조회하고 2명이면 나와 상대만 있는 방이므로 탐색완료.
            $sql_match_room = "SELECT count(user_id) AS roomType FROM participate_in WHERE room_id = '$roomIdArray[$i]'";

            $result = mysqli_query($conn, $sql_match_room);
            $participant = mysqli_fetch_assoc($result);

            //1:1방일 경우
            if ($participant['roomType'] == 2)
            {
                // roomId@[방번호] 를 보낸다.
                echo 'roomId@' . $roomIdArray[$i];

                //0. 채팅방에 입장하면 user의 participate_in 테이블의 user_status 를 update 해준다.
                $sql_participate_in_status_update = "UPDATE participate_in SET user_status = 0 WHERE user_id = '$myId' AND room_id = '$roomIdArray[$i]'";
                $participateResult = mysqli_query($conn, $sql_participate_in_status_update);

                //update를 실패할 경우 Early Return
                if (!$participateResult)
                {
                    echo "user_status update fail";
                    exit();
                }

                exit();
            }

            echo $roomIdArray[$i] . ' 번 방에 참여한 인원 : ' . $participant['roomType'] . '   ';
        }

    }
    //해당 방이 없을때
    else
    {

        //가장 마지막 방번호를 주거나 테이블에 방이 없으면 없다고 알려주어야 한다.
        $sql_last_room = "SELECT max(id) AS lastroomId FROM chatting_room order by id desc limit 1"; //마지막 room ID 가져오기 없을시 NULL반환
        $result = mysqli_query($conn, $sql_last_room);

        $existornot = mysqli_fetch_assoc($result);

        //print_r($existornot);
        if ($existornot['lastroomId'] === null)
        {
            $result_message = 'noRoom@';
        }
        else
        {
            $result_message = 'lastRoomId@' . $existornot['lastroomId'];
        }

    }

    echo $result_message;
    }
    /////////////////////////////////////////////////////단체방
    else {
    
    $roomMember = count($friendsList) + 1; // 방안의 멤버 = 친구들의 수 + 자기자신

    // echo '단체방이고 멤버수는 나포함 해서'. $roomMember.'  명이다.';
    // exit();
       
        //이미 참여했던 방인지 조회한다.
    //1. 먼저 나와 상대가 참여한 방을 조회한다. (나와 , 상대, 다른사람도 있는 방이 있다면 그 방도 조회된다.)
    $sql_search_room = "SELECT room_id , count(user_id) AS roomType FROM participate_in GROUP BY room_id HAVING roomType = $roomMember";

    $roomSearchResult = mysqli_query($conn, $sql_search_room);
    $rowCnt = mysqli_num_rows($roomSearchResult);

    if ($rowCnt > 0)
    {

        $roomIdArray = array();

        for ($i = 0;$i < $rowCnt;$i++)
        {
            $row = mysqli_fetch_array($roomSearchResult, MYSQLI_ASSOC);

            array_push($roomIdArray, $row["room_id"]);
        }

    //    print_r($roomIdArray);
    //    exit();
        for ($i = 0; $i < count($roomIdArray); $i++)
        {

           
            $sql_match_room = "SELECT user_id FROM participate_in WHERE room_id = '$roomIdArray[$i]'";

            $result = mysqli_query($conn, $sql_match_room);
            $memberCnt = mysqli_num_rows($result);

            // echo $memberCnt.'/'.$roomMember;
            // exit();

            if($memberCnt == $roomMember) {

                $flag = true;
                while($row = mysqli_fetch_assoc($result)) {
                    // echo $row['user_id'].'===>';
                    // print_r($memberList);
                    if(!in_array($row['user_id'], $memberList)) {
                        $flag = false;
                    }
                }
                if($flag) {
                    echo 'roomId@' . $roomIdArray[$i];

                    $sql_participate_in_status_update = "UPDATE participate_in SET user_status = 0 WHERE user_id = '$myId' AND room_id = '$roomIdArray[$i]'";
                    $participateResult = mysqli_query($conn, $sql_participate_in_status_update);
    
                    //update를 실패할 경우 Early Return
                    if (!$participateResult)
                    {
                        echo "user_status update fail";
                        exit();
                    }

                    exit();
                }
            
            }




            //////////////////////////////////////////////////////////////////////////////

            //단체방일 경우
            // if ($participant['roomType'] == $roomMember)
            // {
            //     //해당 인원이 모두 방 안의 인원들과 같은지 확인



            //     // roomId@[방번호] 를 보낸다.
            //     echo 'roomId@' . $roomIdArray[$i];

            //     //0. 채팅방에 입장하면 user의 participate_in 테이블의 user_status 를 update 해준다.
            //     $sql_participate_in_status_update = "UPDATE participate_in SET user_status = 0 WHERE user_id = '$myId' AND room_id = '$roomIdArray[$i]'";
            //     $participateResult = mysqli_query($conn, $sql_participate_in_status_update);

            //     //update를 실패할 경우 Early Return
            //     if (!$participateResult)
            //     {
            //         echo "user_status update fail";
            //         exit();
            //     }

            //     exit();
            // }

            echo $roomIdArray[$i] . ' 번 방에 참여한 인원 : ' . $participant['roomType'] . '   ';
        }

    }
    //해당 방이 없을때
    else
    {
        // echo '단체방에 대한 해당 방이 없다.';
        // exit();

        //가장 마지막 방번호를 주거나 테이블에 방이 없으면 없다고 알려주어야 한다.
        $sql_last_room = "SELECT max(id) AS lastroomId FROM chatting_room order by id desc limit 1"; //마지막 room ID 가져오기 없을시 NULL반환
        $result = mysqli_query($conn, $sql_last_room);

        $existornot = mysqli_fetch_assoc($result);

        //print_r($existornot);
        if ($existornot['lastroomId'] === null)
        {
            $result_message = 'noRoom@';
        }
        else
        {
            $result_message = 'lastRoomId@' . $existornot['lastroomId'];
        }

    }

    echo $result_message;
    }
    /////////////////////////////////////////////////
}

else
{

    echo "DB Fail";
}

mysqli_close($conn);
?>
