<?php
require "../dbconnect/connection.php";


$roomId = $_POST['roomId'];
$userId = $_POST['userId'];
$friendId = $_POST['friendId'];
$contentType = $_POST['contentType'];
$content = $_POST['content'];

$result_message;
//DB insert
if ($conn)
{
    //트랜잭션 시작
    // 트랜잭션 시작 (MYSQLI_TRANS_START_READ_ONLY 도 가능)
    // 데이터 읽기만 할 때와 수정, 삭제할 때를 구분하여 지정함
    mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);

    //1. 처음 생성한 Room을 테이블에 INSERT 한다
    //2. 생성한 방에 유저(나) 가 참여했다는 participate_in 테이블에 INSERT 한다. (생성할때 상대방도 생성해 준다.######)

    $sql_room = "INSERT INTO chatting_room(id, room_owner_id, user_count, created_date) VALUES ('$roomId', '$userId', 1, now())";

    $result_make_room = mysqli_query($conn, $sql_room);

    // if($result_make_room) {
    //     echo "방 생성은 성공";
    //     exit();
    // }
    
   
    //$sql_load = "select id, user_id, text_content, img_src, hashtag, hit_view, hit_like, created_date from diary_page where user_id = $index";
    //포스트 정보 가져오기
    $sql_participate = "INSERT INTO participate_in(room_id, user_id, invited_date, user_status) 
                        VALUES ('$roomId', '$userId', now(), 0), ('$roomId', '$friendId', now(), 0)";
    
    
    $result_participate = mysqli_query($conn, $sql_participate);
  
    
    if($result_make_room && $result_participate) {

            $result_message = $contentType.' 을 성공하였습니다.';

            //트랜잭션 완료후 커밋
            mysqli_commit($conn);
        
        echo $result_message;
        
    }else {
        //트랜잭션 실패시 롤백
        mysqli_rollback($conn);
        echo "ROLLBACK";
    }
}
    
else {
        
        echo "DB Fail";
}

mysqli_close($conn);
?>