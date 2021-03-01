<?php
require "../dbconnect/connection.php";

$postId = $_POST["postId"];
$comment = $_POST["comment"];
$userId = $_POST["userId"];

//DB insert
if ($conn)
{

    //$sql_load = "select id, user_id, text_content, img_src, hashtag, hit_view, hit_like, created_date from diary_page where user_id = $index";

    $sql_upload_comment = "INSERT INTO comment (user_id, post_id, content, created_date) 
                                VALUES ('$userId', '$postId', '$comment', now())"; 
    
    $result = mysqli_query($conn, $sql_upload_comment);

    if($result) {

        echo "Insert Success";
        
    }

}

else {
        echo "DB Fail";
}

mysqli_close($conn);
?>
