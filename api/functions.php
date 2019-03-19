<?php
    function update_saldo($balance, $iban){
      require "../config.php";
      $sql = "UPDATE accounts SET balance = $balance WHERE iban = '$iban'";

      if ($link->query($sql) === TRUE) {
          return 1;
      } else {
          return null;
      }
    }

    function checksaldo($nuid, $pin, $iban){
      require "../config.php";

      // prepare and bind
      if(isset($iban)){
        //check balance with iban (no pin verification)
        $stmt = $link->prepare("SELECT balance, iban FROM accounts WHERE iban = ? AND pin = ?");
        $stmt->bind_param("s", $param_iban,);
        $param_iban = $iban;
      }else{
        //check balance with nuid
        $stmt = $link->prepare("SELECT balance, iban FROM accounts WHERE nuid = ? AND pin = ?");
        $stmt->bind_param("ss", $param_nuid, $param_pin);
        // set parameters and execute
        $param_nuid = $nuid;
        $param_pin = $pin;
      }

      if (!$stmt->execute()) {
          echo(json_encode(array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.')));
          exit();
      }

      $stmt->bind_result($balance, $iban);
      $stmt->fetch();
      $stmt->close();

      return array('balance' => $balance, 'iban' => $iban);
    }

    function checkiban($iban){
      require "../config.php";
      // Prepare a select statement
      $sql = "SELECT iban FROM accounts WHERE iban = ?";

      if($stmt = mysqli_prepare($link, $sql)){
          // Bind variables to the prepared statement as parameters
          mysqli_stmt_bind_param($stmt, "s", $param_iban);

          // Set parameters
          $param_iban = $iban;

          // Attempt to execute the prepared statement
          if(mysqli_stmt_execute($stmt)){
              /* store result */
              mysqli_stmt_store_result($stmt);

              if(mysqli_stmt_num_rows($stmt) == 1){
                  return $iban;
              } else {
                return null;
              }
          } else{
              echo(json_encode(array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.')));
          }
      }

      // Close statement
      mysqli_stmt_close($stmt);
    }
?>
