<?php
require "../dbconnect/connection.php";

$userId = $_POST['userId'];
$postId = $_POST['postId'];

//하트를 clicke / unclick 했을때 정보
$clicked;
$like_status;

if (isset($_POST['clicked']))
{
    $clicked = $_POST['clicked'];
}

// echo $clicked;
// exit();


/*
1. 테이블에 내가 좋아요를 누른적이 있었는지 확인한다. 없었으면 -> insert 있었으면 -> update
2. 
*/

$resultArr = array();

//DB insert
if ($conn)
{

    //먼저 테이블에 내가 좋아요를 누른적이 있었는지 확인한다. 없었으면 -> insert 있었으면 -> update
    $sql_like_check = "SELECT count(*) like_cnt
                        FROM like_list
                        WHERE like_list.user_id = '$userId' AND like_list.post_id = '$postId'";

    $check_result = mysqli_query($conn, $sql_like_check);
    //print_r($check_esult);
    if ($check_result)
    {

        // echo '들어옴';
        // exit();
        //$rowCnt = mysqli_num_rows($result);
        $row = mysqli_fetch_array($check_result, MYSQLI_ASSOC);

        // print_r($row);
        // echo $row['like_cnt'];
        // exit();
        //좋아요를 처음 누를경우 -> insert
        if ($row['like_cnt'] === '0')
        {
            //좋아요를 처음 누를 경우는 clicked 가 '0' 인 경우밖에 없다. -> 처음 에는 누르는 선택지만 존재한다.
            if ($clicked === '1')
            {
                // echo '들어옴';
                // exit();
                $sql_like_insert = "INSERT INTO like_list (user_id, post_id, like_status) VALUES ('$userId', '$postId', '1')";
                $insert_result = mysqli_query($conn, $sql_like_insert);
                // echo '들어옴';
                // exit();
                if ($insert_result)
                {
                    //echo "좋아요가 Insert 되었습니다.";
                    //array_push($resultArr, $row);
                    // $resultArr = [
                    //     'like_cnt' => '1', // 이렇게 하면 안된다.
                    //     'like_status' => '1'
                    // ];
                    // print_r($resultArr);
                    $sql_like_cnt = "SELECT count(*) like_cnt
                                FROM like_list
                                WHERE like_list.post_id = '$postId' AND like_list.like_status = '1'";

                    $sql_like_cnt_result = mysqli_query($conn, $sql_like_cnt);

                    if ($sql_like_cnt_result)
                    {
                        $row = mysqli_fetch_array($sql_like_cnt_result, MYSQLI_ASSOC);

                        $resultArr = ['like_cnt' => $row['like_cnt'], 'like_status' => '1'];
                    }

                }
            }
           
        }
        // else
        // {
        //     $sql_like_cnt = "SELECT count(*) like_cnt
        //     FROM like_list
        //     WHERE like_list.post_id = '$postId' AND like_list.like_status = '1'";

        //     $sql_like_cnt_result = mysqli_query($conn, $sql_like_cnt);

        //     if ($sql_like_cnt_result)
        //     {
        //         $row = mysqli_fetch_array($sql_like_cnt_result, MYSQLI_ASSOC);

        //         $resultArr = ['like_cnt' => $row['like_cnt'], 'like_status' => '0'];
        //     }
        // }

        //좋아요를 누른적이 있는경우 -> update
        if ($clicked === '0')
        {

            $sql_like_update = "UPDATE like_list SET like_status = '0' WHERE user_id = '$userId' AND post_id = '$postId'";

            $decrease_like_result = mysqli_query($conn, $sql_like_update);

            $like_status = '0';

        }
        else if ($clicked === '1')
        {
            $sql_like_update = "UPDATE like_list SET like_status = '1' WHERE user_id = '$userId' AND post_id = '$postId'";

            $increase_like_result = mysqli_query($conn, $sql_like_update);

            $like_status = '1';

        } else { // 포스트 페이지에 들어왔을때 (좋아요를 했던 적이있는 포스팅) 처음 조회하는 부분 -> 중요하다

            $sql_like_status = "SELECT like_status 
            FROM like_list
            WHERE like_list.post_id = '$postId' AND like_list.user_id = '$userId'";

            $like_status_result = mysqli_query($conn, $sql_like_status);

            if($like_status_result) {
                $row = mysqli_fetch_array($like_status_result, MYSQLI_ASSOC);

                $like_status = $row['like_status'];
            }

        }

        if ($decrease_like_result || $increase_like_result || $like_status_result)
        {

            $sql_like_cnt = "SELECT count(*) like_cnt
                    FROM like_list
                    WHERE like_list.post_id = '$postId' AND like_list.like_status = '1'";

            $sql_like_cnt_result = mysqli_query($conn, $sql_like_cnt);

            if ($sql_like_cnt_result)
            {
                $row = mysqli_fetch_array($sql_like_cnt_result, MYSQLI_ASSOC);

                $resultArr = ['like_cnt' => $row['like_cnt'], 'like_status' => $like_status];
            }

        }

        // $arr = array();
        // for($i = 0; $i < $rowCnt; $i++) {
        //     $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        //     $arr[$i] = $row;
        // }
        print_r(json_encode($resultArr, JSON_PRETTY_PRINT));

    }

}

else
{
    echo "DB Fail";
}

mysqli_close($conn);
?>
