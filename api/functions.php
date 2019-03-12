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
        $stmt = $link->prepare("SELECT balance, iban FROM accounts WHERE iban = ?");
        $stmt->bind_param("s", $param_iban,);
        $param_iban = $iban;
      }else{
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
?>
