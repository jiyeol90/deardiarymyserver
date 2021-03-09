<?php
require "../dbconnect/connection.php";

//$_POST 는 헤더에 포함된 POST data를 파싱한 결과를 가지지만,
//file_get_contents('php://input'); 는 파싱하기전 POST data를 가져온다.
$data = json_decode(file_get_contents('php://input'),true);    //POST로 받은 값을 json형식으로 decode

$userId = $data['userId'];
$postId = $data['postId'];
$comment_cnt = $data['comment_cnt'];

//DB insert
if ($conn)
{
    //트랜잭션 시작
    // 트랜잭션 시작 (MYSQLI_TRANS_START_READ_ONLY 도 가능)
    // 데이터 읽기만 할 때와 수정, 삭제할 때를 구분하여 지정함
    mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);

    //조회수 증가
    //포스트를 조회할경우 
    if(!isset($comment_cnt)) {

    $sql_view = "UPDATE diary_page SET hit_view = hit_view + 1 WHERE id = '$postId'";

    $result_view = mysqli_query($conn, $sql_view);
    
    //댓글 갯수만 업데이트 할 경우
    } else {
        $result_view = true;
    } 

    //$sql_load = "select id, user_id, text_content, img_src, hashtag, hit_view, hit_like, created_date from diary_page where user_id = $index";
    //포스트 정보 가져오기
    $sql_load = "SELECT count(c.id) comment_cnt, dp.user_id, dp.text_content, 
                dp.img_src, dp.hashtag, dp.hit_view, dp.hit_like, dp.click_like, dp.created_date 
                FROM diary_page dp LEFT JOIN comment c 
                ON dp.id = c.post_id
                WHERE dp.id = '$postId'";
    
    
    $result_load = mysqli_query($conn, $sql_load);

    if($result_view && $result_load) {

            $arr = array();
  
            $row = mysqli_fetch_array($result_load, MYSQLI_ASSOC);

            $arr['comment_cnt'] = $row['comment_cnt'];
            $arr['user_id'] = $row['user_id'];
            $arr['text_content'] = $row['text_content'];
            $arr['img_src'] = $row['img_src'];
            $arr['hashtag'] = $row['hashtag'];
            $arr['hit_view'] = $row['hit_view'];
            $arr['hit_like'] = $row['hit_like'];
            $arr['click_like'] = $row['click_like'];
            $arr['created_date'] = $row['created_date'];

            //트랜잭션 완료후 커밋
            mysqli_commit($conn);
        
        print_r(json_encode($arr, JSON_PRETTY_PRINT));
        
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