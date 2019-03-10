<?php
    header('Content-Type: application/json');

    // Include config file
    require_once "../config.php";

    $nuid_length = 8;
    $pin_length = 4;

    $nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid'])); //remove whitespaces
    $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces
    $amount = htmlspecialchars($_POST['amount']);

    if(isset($nuid) && strlen($nuid) == $nuid_length){
        if(isset($pin) && strlen($pin) == $pin_length){
          if(isset($balance)){
              // prepare and bind
              $stmt = $link->prepare("SELECT iban, balance FROM accounts WHERE nuid = ? AND pin = ?");
              $stmt->bind_param("ss", $param_nuid, $param_pin);

              // set parameters and execute
              $param_nuid = $nuid;
              $param_pin = $pin;

              if (!$stmt->execute()) {
                  $response = array('error' => 'Oops! Something went wrong. Please try again later.');
              }
              // bind result variables
              $stmt->bind_result($iban, $balance);

              // fetch value
              $stmt->fetch();

              //close connection
              $stmt->close();

              //chek if balance is enough to withdraw Amount
              if($balance <= $amount){
                // prepare and bind
                $stmt = $link->prepare("UPDATE accounst SET balance = ? WHERE iban = ?");
                $stmt->bind_param("ss", $param_newbalance, $param_iban);

                // set parameters and execute
                $param_newbalance = $balance - $amount;
                $param_iban = $iban;

                if (!$stmt->execute()) {
                    $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
                }else{
                  $response = array('status' => '0');
                }

                //close connection
                $stmt->close();

              }else{
                  $response = array('status' => '1', 'error' => 'Not enough funds to withdraw.');
              }
          }else{
              $response = array('status' => '1', 'error' => 'Amount not entered.');
          }
        }else{
            $response = array('status' => '1', 'error' => 'PIN not entered or correct.');
        }
    }else{
        $response = array('status' => '1', 'error' => 'NUID not entered or correct.');
    }

    //close connection
    $link->close();

    echo json_encode($response);
?>
