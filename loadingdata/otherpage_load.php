<?php
require "../dbconnect/connection.php";

//주의할 것
//userId는 내가 클릭한 상대방의 아이디
//myId는 누른 나의 아이디
$userId = $_POST["userId"]; //user_id
$myId = $_POST["myId"];



/*
 * ## 3 가지 정보를 조회한다 ##
 * 
 * 1. 유저의 프로필 사진과 프로필 문구
 * 2. 유저가 포스팅한 게시물의 수
 * 3. 유저와 나의 친구관계 (기존의 mypage_load 와 다른점)
 * 4. 각 포스팅의 정보 (인덱스, 이미지 경로, 게시일자)
 * 
 */
if ($conn)
{
    //1. 유저의 프로필 사진과 프로필 문구
    //### 유저의 status(회원가입상태 : 탈퇴, 가입, 휴면)은 로그인시 처리해 주므로 조회시에는 필요 없다.
    $sql_profile = "SELECT user_profile, user_text FROM user WHERE user_id = '$userId'";

    $result = mysqli_query($conn, $sql_profile);
    $rowCnt = mysqli_num_rows($result);

    $upperPageArry = array();

    if($rowCnt > 0) {

            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

            if(!isset($row['user_profile'])) { //DB의 NULL값을 가져오면 
                $upperPageArry['user_profile'] = "default";
            } else {
                $upperPageArry['user_profile'] = $row['user_profile'];
            }

        
            if(!isset($row['user_text'])) { //DB의 NULL값을 가져오면 
                $upperPageArry['user_text'] = "default";
            } else {
                $upperPageArry['user_text'] = $row['user_text'];
            }

            // $upperPageArry['user_profile'] = $row['user_profile'];
            // $upperPageArry['user_text'] = $row['user_text'];
      
    } 


    //2. 유저가 포스팅한 게시물의 수
    $sql_cnt = "SELECT count(*) postCnt FROM diary_page WHERE user_id = '$userId' and post_status = 1";
    //$sql_load = "select id, user_id, text_content, img_src, hashtag, hit_view, hit_like, created_date from diary_page where user_id = $index";

    $result = mysqli_query($conn, $sql_cnt);
    $rowCnt = mysqli_num_rows($result);
 
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $upperPageArry['postCnt'] = $row['postCnt'];
  

    //print_r(json_encode($cntArr, JSON_PRETTY_PRINT));
    $sql_friend_or_not = "SELECT count(*)friendOrNot FROM follow WHERE user_id = '$myId' AND friend_id = '$userId' AND follow_status = 1";
    
    $result = mysqli_query($conn, $sql_friend_or_not);
    
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    $upperPageArry['friendOrNot'] = $row['friendOrNot'];

    //3. 각 포스팅의 정보 (인덱스, 이미지 경로, 게시일자)
    $sql_load = "SELECT id, user_id, img_src , created_date FROM diary_page WHERE user_id = '$userId' and post_status = 1";
    
    $result = mysqli_query($conn, $sql_load);

    $lowerPageArr = array();

    if($result) {

        $rowCnt = mysqli_num_rows($result);

       //$arr = array();

        for($i = 0; $i < $rowCnt; $i++) {
        
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

            $lowerPageArr[$i] = $row;
        }

        $resultArray = array(
            $upperPageArry,
            $lowerPageArr 
        );

        print_r(json_encode($resultArray, JSON_PRETTY_PRINT));
       
    }

}

else {
        echo "DB Fail";
}

mysqli_close($conn);
?>