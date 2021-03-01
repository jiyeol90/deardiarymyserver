<?php
require "../dbconnect/connection.php";

$postId = $_POST["postId"];

//DB insert
if ($conn)
{

    //$sql_load = "select id, user_id, text_content, img_src, hashtag, hit_view, hit_like, created_date from diary_page where user_id = $index";

    $sql_load_comment = "SELECT user.user_profile, comment.user_id, comment.content, comment.created_date 
                            From user JOIN comment
                            ON user.user_id = comment.user_id
                            WHERE comment.post_id = '$postId' and comment.comment_status = '1'";
    
    $result = mysqli_query($conn, $sql_load_comment);

    if($result) {

        $rowCnt = mysqli_num_rows($result);

        $arr = array();

        for($i = 0; $i < $rowCnt; $i++) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

            $arr[$i] = $row;
        }

        print_r(json_encode($arr, JSON_PRETTY_PRINT));
        
    }

}

else {
        echo "DB Fail";
}

mysqli_close($conn);
?>
