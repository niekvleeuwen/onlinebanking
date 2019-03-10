<?php
    header('Content-Type: application/json');

    require_once "../config.php";
    
    $nuid_length = 8;
    $pin_length = 4;

    $nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid'])); //remove whitespaces
    $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces
    $amount = htmlspecialchars($_POST['amount']);

    if(isset($nuid) && strlen($nuid) == $nuid_length){
        if(isset($pin) && strlen($pin) == $pin_length){
          if(isset($amount)){
              //first get the balance and id from the user
              $stmt = $link->prepare("SELECT iban, balance FROM accounts WHERE nuid = ? AND pin = ?");
              $stmt->bind_param("ss", $param_nuid, $param_pin);

              $param_nuid = $nuid;
              $param_pin = $pin;

              if (!$stmt->execute()) {
                  $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
              }

              $stmt->bind_result($iban, $balance);
              $stmt->fetch();
              $stmt->close();

              //chek if balance is enough to withdraw amount
              if(isset($balance)){
                if($amount <= $balance){
                  //insert the new balance
                  $stmt = $link->prepare("UPDATE accounts SET balance = ? WHERE iban = ?");
                  $stmt->bind_param("is", $param_newbalance, $param_iban);

                  $param_newbalance = $balance - $amount;
                  $param_iban = $iban;

                  if (!$stmt->execute()) {
                      $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
                  }else{
                      $response = array('status' => '0', 'amount' => $amount); //sent amount back for conformation
                  }
                  $stmt->close();
              }else{
                  $response = array('status' => '1', 'error' => 'Not enough funds to withdraw.');
              }
            }else{
                $response = array('status' => '1', 'error' => 'Card or Pin not correct.');
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

    echo(json_encode($response));
?>
