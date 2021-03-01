<?php
require "../dbconnect/connection.php";

$userId = $_POST["userId"];
$postIndex =$_POST["postIndex"];

//DB insert
if ($conn)
{

    $sql_post = "UPDATE diary_page SET post_status = '0' WHERE user_id = '$userId' and id = '$postIndex'";

        if (mysqli_query($conn, $sql_post))
        {
           echo "삭제하였습니다.";
        }
        else
        {
           echo "DB Fail";
        }
  

}
else
{
    echo "DB Connection Error";
}


mysqli_close($conn);
?>
