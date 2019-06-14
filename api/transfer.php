<?php
    header('Content-Type: application/json');

    require_once "../config.php";

    $iban_length = 8;
    $pin_length = 4;
    $iban_length = 14;

    $iban = str_replace(' ', '', htmlspecialchars($_POST['iban'])); //remove whitespaces
    $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces
    $amount = htmlspecialchars($_POST['amount']);
    $iban_recipient = htmlspecialchars($_POST['iban_recipient']);

    //check if a location is available, otherwise set the location to unkown
    if(isset($location)){
        $location = htmlspecialchars($_POST['location']);
    }else{
        $location = "Unkown";
    }

    if(isset($iban) && strlen($iban) == $iban_length){
        if(isset($pin) && strlen($pin) == $pin_length){
          if(isset($iban_recipient) && strlen($iban_recipient) == $iban_length){
            include_once "functions.php";
            //Check if the IBAN is valid
            if(checkiban($iban_recipient) !== null){
              if(isset($amount)){
                  //if the user hasn't sent iban_sender as a parameter we use the iban and pin
                  $data = checksaldo($iban, $pin);
                  $balance_sender = $data['balance'];
                  $iban_sender = $data['iban'];
                  //chek if balance is enough to withdraw amount
                  if(isset($balance_sender)){
                    if($amount <= $balance_sender){
                      require_once "../api/functions.php";
                      if(update_saldo($balance_sender - $amount, $iban_sender) !== null){ //insert the new balance of the sender
                          if(add_saldo($amount, $iban_recipient) !== null){ //insert the new balance of the recipient
                              transaction($iban_sender, $iban_recipient, $amount, $location);
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
        $response = array('status' => '1', 'error' => 'Iban not entered or correct.');
    }

    //close connection
    $link->close();

    echo(json_encode($response));
?>
