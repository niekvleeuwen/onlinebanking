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

    function checksaldo($nuid, $pin){
      require "../config.php";
      // prepare and bind
      $stmt = $link->prepare("SELECT balance FROM accounts WHERE nuid = ? AND pin = ?");
      $stmt->bind_param("ss", $param_nuid, $param_pin);

      // set parameters and execute
      $param_nuid = $nuid;
      $param_pin = $pin;

      if (!$stmt->execute()) {
          echo(json_encode(array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.')));
          exit();
      }

      $stmt->bind_result($balance);
      $stmt->fetch();
      $stmt->close();

      return $balance;
  }
?>
