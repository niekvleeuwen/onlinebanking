<?php
    header('Content-Type: application/json');

    require_once "../config.php";

    $nuid_length = 8;
    $pin_length = 4;
    $iban_length = 14;

    $nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid'])); //remove whitespaces
    $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces
    $amount = htmlspecialchars($_POST['amount']);
    $iban_recipient = htmlspecialchars($_POST['iban']);

    if(isset($nuid) && strlen($nuid) == $nuid_length){
        if(isset($pin) && strlen($pin) == $pin_length){
          if(isset($iban_recipient) && strlen($iban_recipient) == $iban_length){
            if(isset($amount)){
                //first get the balance and iban from the sender
                $stmt = $link->prepare("SELECT iban, balance FROM accounts WHERE nuid = ? AND pin = ?");
                $stmt->bind_param("ss", $param_nuid, $param_pin);

                $param_nuid = $nuid;
                $param_pin = $pin;

                if (!$stmt->execute()) {
                    $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
                }

                $stmt->bind_result($iban_sender, $balance);
                $stmt->fetch();
                $stmt->close();

                //second get the balance from the recipient
                $stmt = $link->prepare("SELECT balance FROM accounts WHERE iban = ?");
                $stmt->bind_param("s", $param_iban);

                $param_iban = $iban_recipient;

                if (!$stmt->execute()) {
                    $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
                }

                $stmt->bind_result($balance_recipient);
                $stmt->fetch();
                $stmt->close();

                //chek if balance is enough to withdraw amount
                if(isset($balance)){
                  if($amount <= $balance){
                    //insert the new balance of the sender
                    $stmt = $link->prepare("UPDATE accounts SET balance = ? WHERE iban = ?");
                    $stmt->bind_param("is", $param_newbalance, $param_iban);

                    $param_newbalance = $balance - $amount;
                    $param_iban = $iban_sender;

                    if (!$stmt->execute()) {
                        $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
                    }else{
                      //insert the new balance of the recipient
                      $stmt = $link->prepare("UPDATE accounts SET balance = ? WHERE iban = ?");
                      $stmt->bind_param("is", $param_newbalance, $param_iban);

                      $param_newbalance = $balance_recipient + $amount;
                      $param_iban = $iban_recipient;

                      if (!$stmt->execute()) {
                          $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
                      }else{
                          $response = array('status' => '0', 'amount' => $amount, 'iban' => $iban_recipient); //sent amount and recipient iban back for conformation
                      }
                      $stmt->close();
                    }
                }else{
                    $response = array('status' => '1', 'error' => 'Not enough funds to withdraw.');
                }
              }else{
                  $response = array('status' => '1', 'error' => 'Card or Pin not correct.');
              }
            }else{
                $response = array('status' => '1', 'error' => 'Iban not entered or correct.');
            }
          }else{
              $response = array('status' => '1', 'error' => 'PIN not entered or correct.');
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
