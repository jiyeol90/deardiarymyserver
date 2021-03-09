<?php
require "../dbconnect/connection.php";

//$_POST 는 헤더에 포함된 POST data를 파싱한 결과를 가지지만,

$myId = $_POST['myId'];
//$friendId = $_POST['friendId'];


$result_message;
//DB insert
if ($conn)
{

    //이미 참여했던 방인지 조회한다.
    //1. 먼저 나와 상대가 참여한 방을 조회한다. (나와 , 상대, 다른사람도 있는 방이 있다면 그 방도 조회된다.)
    $sql_room_list = "SELECT 
	                    t.room_id, t.user_id, t.content_type, t.content, t.created_date 
                        FROM (
		                        SELECT * 
                                FROM message
		                        WHERE (room_id, created_date) IN (
			                        SELECT room_id, max(created_date) AS date_time
			                        FROM message GROUP BY room_id
    	                            )
	                        )t
                     WHERE room_id IN (SELECT room_id FROM participate_in WHERE user_id = '$myId')
                     ORDER BY room_id";

    $roomSearchResult = mysqli_query($conn, $sql_room_list);
    $rowCnt = mysqli_num_rows($roomSearchResult);

    if($rowCnt > 0) {
        
        $roomIdArray = array(); //roomId 에 대한 정보
        

        for($i = 0; $i < $rowCnt; $i++) {
            $row = mysqli_fetch_array($roomSearchResult, MYSQLI_ASSOC);
            //roomId 에 참여한 사람중 상대방의 프로필을 가져온다. 
            $roomId = $row['room_id'];

            $friendProfile = array();//room에 참여한 상대방 profile 정보
            $sql_friend_profile = "SELECT pi.user_id , user.user_profile 
                                   FROM participate_in pi JOIN user ON pi.user_id = user.user_id 
                                   WHERE pi.room_id = '$roomId' AND pi.user_id NOT IN ('$myId')";

           

            $friend_profile_result = mysqli_query($conn, $sql_friend_profile);
            $friend_row = mysqli_num_rows($friend_profile_result);

            for($j = 0; $j < $friend_row; $j++) {
                
                $profileRow = mysqli_fetch_array($friend_profile_result, MYSQLI_ASSOC);

                //userProfile의 값이 null 이면 default String값을 넣어준다. (NULL값을 처리하기 위해)
                if(empty($profileRow['user_profile'])) {
                    $profileRow['user_profile'] = 'default';
                }

                array_push($friendProfile,$profileRow);
   
            }
            // print_r($friendProfile);
            // echo $friendProfile[0]['user_profile']; 
            // exit();

            // while($profileRow = mysqli_fetch_assoc($friend_profile_result)) {
            //     $friendProfile = $profileRow;

            //     if(empty($profileRow['user_profile'])) {
            //         $friendProfile['user_profile'] = 'default';
            //     }
            // }

            

            // print_r($friendProfile);
            // exit();
            array_push($roomIdArray, $row);
            //roomIdArray 마지막 요소에 친구 리스트를 넣어준다.
            $roomIdArray[$i]['friend'] = $friendProfile;
            //array_push($roomIdArray,$friendProfile);

            // for($k = 0; $k < count($friendProfile); $k++) {
            //     $roomIdArray[$i]['other_id'.$j] = $friendProfile[$k]['user_id'];
            //     $roomIdArray[$i]['friend_profile'.$j] = $friendProfile[$k]['user_profile'];
            // }

            
            
           
        }
       
    }    
    print_r(json_encode($roomIdArray, JSON_PRETTY_PRINT));
    /*    
[
    {
    "room_id": "1",
    "user_id": "james",
    "content_type": "txt",
    "content": "hello",
    "created_date": "2021-03-08 06:30:14",
    "friend": [
            {
            "user_id": "jiyeol90",
            "user_profile": "default"
            }
        ]
    },
    {
    "room_id": "2",
    "user_id": "james",
    "content_type": "txt",
    "content": "where r u",
    "created_date": "2021-03-08 05:33:34",
    "friend": [
            {
            "user_id": "jar",
            "user_profile": "default"
            }
        ]
    }
]
    */
}
    
else {
        
        echo "DB Fail";
}

mysqli_close($conn);
?>