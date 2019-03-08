<?php

/* not working (yet)
function username($username){
  $sql = "SELECT id FROM users WHERE username = ?";

  if($stmt = mysqli_prepare($link, $sql)){
    // Bind variables to the prepared statement as parameters
    mysqli_stmt_bind_param($stmt, "s", $param_username);

    // Set parameters
    $param_username = $username;

    // Attempt to execute the prepared statement
    if(mysqli_stmt_execute($stmt)){
        mysqli_stmt_store_result($stmt);

        if(mysqli_stmt_num_rows($stmt) == 1){
            return true; //username exists
        } else{
          return false;
        }
    } else{
        return false;
    }
  }
}

?>
