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
          $stmt = $link->prepare("SELECT balance, iban FROM accounts WHERE iban = ? AND pin = ?");
          $stmt->bind_param("ss", $param_iban, $param_pin);
          $param_iban = $iban;
          $param_pin = $pin;
        }else{
          //check balance with nuid and pin
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


    function checkpin($nuid, $pin){
      require "config.php";
      //check pin
      $stmt = $link->prepare("SELECT pin_attempts FROM accounts WHERE nuid = ? AND pin = ?");
      $stmt->bind_param("ss", $param_nuid, $param_pin);
      $param_nuid = $nuid;
      $param_pin = $pin;

      if (!$stmt->execute()) {
          echo(json_encode(array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.')));
          exit();
      }

      $stmt->bind_result($pin_attempts);
      $stmt->fetch();
      $stmt->close();
      return $pin_attempts;
    }

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
      if($stmt = mysqli_prepare($link, $sql)){
          // Bind variables to the prepared statement as parameters
          mysqli_stmt_bind_param($stmt, "ssis", $param_iban_seneder, $param_iban_recipient, $param_amount, $param_location);
          // Set parameters
          $param_iban_seneder = $iban_sender;
          $param_iban_recipient = $iban_recipient;
          $param_amount = $amount;
          $param_location = $location;
          // Attempt to execute the prepared statement
          if(mysqli_stmt_execute($stmt)){
              return true;
          } else{
              return null;
          }
      }

      mysqli_stmt_close($stmt);
      mysqli_close($link);
    }
?>
