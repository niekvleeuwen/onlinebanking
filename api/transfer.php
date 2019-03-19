<?php
    header('Content-Type: application/json');

    require_once "../config.php";

    $nuid_length = 8;
    $pin_length = 4;
    $iban_length = 14;

    // $_POST['nuid'] = "B8C5E3K8";
    // $_POST['pin'] = "1111";
    // $_POST['amount'] = 10;
    // $_POST['iban_recipient'] = "SU95USSR909335";
    // $_POST['iban_sender'] = "SU66USSR721677";

    $nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid'])); //remove whitespaces
    $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces
    $amount = htmlspecialchars($_POST['amount']);
    $iban_recipient = htmlspecialchars($_POST['iban_recipient']);
    if(isset($iban_sender)){
        $iban_sender = htmlspecialchars($_POST['iban_sender']);
    }

    if(isset($nuid) && strlen($nuid) == $nuid_length){
        if(isset($pin) && strlen($pin) == $pin_length){
          if(isset($iban_recipient) && strlen($iban_recipient) == $iban_length){
            include_once "functions.php";
            //Check if the IBAN is valid
            if(checkiban($iban_recipient) !== null){
              if(isset($amount)){
                  //for the transfer you can set a deticated IBAN to use if a user had multiple accounts
                  if(isset($iban_sender)){
                    $data = checksaldo(null, $pin, $iban_sender);
                    $balance_sender = $data['balance'];
                  }else{
                    //if the user hasn't sent iban_sender as a parameter we use the nuid and pin
                    $data = checksaldo($nuid, $pin, null);
                    $balance_sender = $data['balance'];
                    $iban_sender = $data['iban'];
                  }
                  //chek if balance is enough to withdraw amount
                  if(isset($balance_sender)){
                    if($amount <= $balance_sender){
                      require_once "../api/functions.php";
                      if(update_saldo($balance_sender - $amount, $iban_sender) !== null){ //insert the new balance of the sender
                          if(add_saldo($amount, $iban_recipient) !== null){ //insert the new balance of the recipient
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
