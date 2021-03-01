<?php

 //환경변수를 변경해주고 환경변수의 값을 가져온다. 
 //지정한 지시어를 값을 가져온다. host , user, pw 는 지정되어있지만 
 //dbname은 내가 설정해주어야 한다. => apache 환경변수에 설정해 주었다.
 $conn = mysqli_connect(ini_get("mysqli.default_host"), ini_get("mysqli.default_user"), ini_get("mysqli.default_pw"), getenv('DB_NAME'));

?>