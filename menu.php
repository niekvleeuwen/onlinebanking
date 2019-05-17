<?php
// Initialize the session
if(session_id() == '' || !isset($_SESSION)) {
    session_start();
}
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    include('functions/menu.php');
}else{
    if(!isset($_SESSION["admin"]) || $_SESSION["admin"] !== 1){
      include('functions/menu_loggedin.php');
    }else{
        include('functions/menu_admin.php');
    }
}
?>
