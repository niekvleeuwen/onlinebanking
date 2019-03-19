<?php
    header('Content-Type: application/json');

    require_once "../config.php";

    $nuid_length = 8;
    $pin_length = 4;
    $iban_length = 14;

    $nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid'])); //remove whitespaces
    $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces
    $amount = htmlspecialchars($_POST['amount']);
    $iban_recipient = htmlspecialchars($_POST['iban_recipient']);
    $iban_sender = htmlspecialchars($_POST['iban_sender']);

    if(isset($nuid) && strlen($nuid) == $nuid_length){
        if(isset($pin) && strlen($pin) == $pin_length){
          if(isset($iban_recipient) && strlen($iban_recipient) == $iban_length){
            include_once "functions.php";
            if(checkiban($iban_recipient) !== null){
              if(isset($amount)){
                  if(isset($iban_sender)){
                    $data = checksaldo(null, $pin, $iban_sender);
                    $balance_sender = $data['balance'];
                  }else{
                    //if the user hasn't sent iban_sender as a parameter we use the nuid and pin
                    $data = checksaldo($nuid, $pin, null);
                    $balance_sender = $data['balance'];
                    $iban_sender = $data['iban'];
                  }

                  //second get the balance from the recipient
                  $data = checksaldo(null, $pin, $iban_recipient);
                  $balance_recipient = $data['balance'];

                  //chek if balance is enough to withdraw amount
                  if(isset($balance_sender)){
                    if($amount <= $balance_sender){
                      require_once "../api/functions.php";
                      if(update_saldo($balance_sender - $amount, $iban_sender) !== null){ //insert the new balance of the sender
                          if(update_saldo($balance_recipient + $amount, $iban_recipient) !== null){ //insert the new balance of the recipient
                              $response = array('status' => '0', 'amount' => $amount, 'iban' => $iban_recipient);
                          }else{
                            $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
                          }
                      }else{
                          $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
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
              $response = array('status' => '1', 'error' => 'Iban not recognised.');
            }
          }else{
              $response = array('status' => '1', 'error' => 'Iban not entered or correct.');
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
