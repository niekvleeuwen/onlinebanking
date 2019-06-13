<?php
    //this function is used to update the saldo of the iban to the balance given as paramater
    function update_saldo($balance, $iban){
        require "config.php";
        $sql = "UPDATE accounts SET balance = $balance WHERE iban = '$iban'";
        if ($link->query($sql) === TRUE) {
            return 1;
        } else {
            return null;
        }
    }

    //this function is used to add a amount to the IBAN given as a paramter
    function add_saldo($amount, $iban){
      require "config.php";
      //prepare and bind
      $stmt = $link->prepare("SELECT balance, iban FROM accounts WHERE iban = ?");
      $stmt->bind_param("s", $param_iban);
      $param_iban = $iban;
      if (!$stmt->execute()) {
          echo(json_encode(array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.')));
          exit();
      }
      $stmt->bind_result($balance, $iban);
      $stmt->fetch();
      $stmt->close();
      $new_balance = $balance + $amount;
      $sql = "UPDATE accounts SET balance = $new_balance WHERE iban = '$iban'";
      if ($link->query($sql) === TRUE) {
          return 1;
      } else {
          return null;
      }
    }

    //this function is used to get the balance from a user using the pin and a IBAN or NUID
    function checksaldo($nuid, $pin, $iban){
        require "config.php";
        //prepare and bind
        if(isset($iban)){
          //check balance with iban (with pin verification)
          $pin_attempts = checkpin_iban($iban, $pin); //check if the pin is correct
          if(isset($pin_attempts)){
            $stmt = $link->prepare("SELECT balance, iban FROM accounts WHERE iban = ?");
            $stmt->bind_param("s", $param_iban);
            $param_iban = $iban;
          }else{
            echo(json_encode(array('status' => '1', 'error' => 'Pin is not correct')));
            exit();
          }
        }else{
          //check balance with iban (with pin verification)
          $pin_attempts = checkpin($nuid, $pin); //check if the pin is correct
          if(isset($pin_attempts)){
            //check balance with nuid and pin
            $stmt = $link->prepare("SELECT balance, iban FROM accounts WHERE nuid = ?");
            $stmt->bind_param("s", $param_nuid);
            // set parameters and execute
            $param_nuid = $nuid;
          }else{
            echo(json_encode(array('status' => '1', 'error' => 'Pin is not correct')));
            exit();
          }
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

    //this function is used to check if a card is valid
    function checkcard($nuid){
      require "config.php";
      // Prepare a select statement
      $sql = "SELECT nuid FROM accounts WHERE nuid = ?";
      if($stmt = mysqli_prepare($link, $sql)){
          // Bind variables to the prepared statement as parameters
          mysqli_stmt_bind_param($stmt, "s", $param_nuid);
          // Set parameters
          $param_nuid = $nuid;
          // Attempt to execute the prepared statement
          if(mysqli_stmt_execute($stmt)){
              mysqli_stmt_store_result($stmt);
              if(mysqli_stmt_num_rows($stmt) == 1){
                  return true;
              } else {
                return false;
              }
          } else{
              echo(json_encode(array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.')));
          }
      }
      // Close statement
      mysqli_stmt_close($stmt);
    }

    //this function is used to get the balance from a user using the pin and a IBAN or NUID
    function get_pin_attempts($nuid){
        require "config.php";
        $stmt = $link->prepare("SELECT pin_attempts FROM accounts WHERE nuid = ?");
        $stmt->bind_param("s", $param_nuid);
        $param_nuid = $nuid;
        if (!$stmt->execute()) {
            echo(json_encode(array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.')));
            exit();
        }
        $stmt->bind_result($pin_attempts);
        $stmt->fetch();
        $stmt->close();
        return $pin_attempts;
    }

    //this function is used to check a pin
    function checkpin($nuid, $pin){
      require "config.php";
      //check pin
      $stmt = $link->prepare("SELECT pin, pin_attempts FROM accounts WHERE nuid = ?");
      $stmt->bind_param("s", $param_nuid);
      $param_nuid = $nuid;
      if (!$stmt->execute()) {
          echo(json_encode(array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.')));
          exit();
      }
      $stmt->bind_result($pin_hash, $pin_attempts);
      $stmt->fetch();
      $stmt->close();
      if(password_verify($pin, $pin_hash)){
        return $pin_attempts;
      }else{
        return null;
      }
    }

    //this function is used to check a pin with a iban
    function checkpin_iban($iban, $pin){
      require "config.php";
      //check pin
      $stmt = $link->prepare("SELECT pin, pin_attempts FROM accounts WHERE iban = ?");
      $stmt->bind_param("s", $param_iban);
      $param_iban = $iban;
      if (!$stmt->execute()) {
          echo(json_encode(array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.')));
          exit();
      }
      $stmt->bind_result($pin_hash, $pin_attempts);
      $stmt->fetch();
      $stmt->close();
      if(password_verify($pin, $pin_hash)){
        return $pin_attempts;
      }else{
        return null;
      }
    }

    //this function resets the pin attempts
    function reset_pin_attempts($nuid){
      require "config.php";
      //set pin attempts to zero
      $sql = "UPDATE accounts SET pin_attempts = 0 WHERE nuid = '$nuid'";
      if ($link->query($sql) === TRUE) {
          return 1;
      } else {
          return null;
      }
    }

    //this function blocks the card
    function blockcard($nuid){
      require "config.php";
      //set pin attempts to zero
      $sql = "UPDATE accounts SET pin_attempts = 3 WHERE nuid = '$nuid'";
      if ($link->query($sql) === TRUE) {
          return 1;
      } else {
          return null;
      }
    }

    //this function adds 1 pin attempt
    function add_pin_attempt($nuid){
      require "config.php";
      $sql = "UPDATE accounts SET pin_attempts = pin_attempts + 1 WHERE nuid = '$nuid'";
      if ($link->query($sql) === TRUE) {
          return 1;
      } else {
          return null;
      }
    }

    //this function is used to check if a IBAN is valid
    function checkiban($iban){
        require "config.php";
        // Prepare a select statement
        $sql = "SELECT iban FROM accounts WHERE iban = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_iban);
            // Set parameters
            $param_iban = $iban;
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
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

    //this function is used to insert a transaction record in the transaction mysql_list_tables
    function transaction($iban_sender, $iban_recipient, $amount, $location){
      require "config.php";
      $sql = "INSERT INTO transactions (iban_sender, iban_recipient, amount, location) VALUES (?, ?, ?, ?)";


      $sql = "INSERT INTO transactions (iban_sender, iban_recipient, amount, location) VALUES ('$iban_sender', '$iban_recipient', $amount, '$location')";
      if ($link->query($sql) === TRUE) {
          return true;
      } else {
          return null;
      }

      mysqli_stmt_close($stmt);
      mysqli_close($link);
    }

    function atm($ten, $twenty, $fifty, $atm_id){
      require "config.php";
      $sql = "UPDATE atm SET bill_10 = bill_10 + $ten, bill_20 = bill_20 + $twenty,bill_50 = bill_50 + $fifty WHERE atm_id = $atm_id";
      if ($link->query($sql) === TRUE) {
          return true;
      } else {
          return false;
      }
    }

    function remote_transaction($bank_code, $acc_number, $pin, $amount){
      require "config.php";
      //this is what the noob bank expects
      //["ABNA", "withdraw", 300400, 1234, 199.50]

      $command = "[\"" . $bank_code  . "\", \"withdraw\", " . $acc_number .", " . $pin . ", " . $amount . "]";
      $sql = "INSERT INTO noob (command) VALUES ('$command')";

      if ($link->query($sql) === TRUE) {
          return true;
      } else {
          return false;
      }
    }
?>
