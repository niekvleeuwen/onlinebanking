<?php
    header('Content-Type: application/json');

    require_once "../config.php";

    $nuid_length = 8;
    $pin_length = 4;

    $nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid'])); //remove whitespaces
    $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces
    $amount = htmlspecialchars($_POST['amount']);
    $location = htmlspecialchars($_POST['location']);
    //check if a location is available, otherwise set the location to unkown
    if(!isset($location)){
        $location = "Unkown";
    }

    if(isset($nuid) && strlen($nuid) == $nuid_length){
        if(isset($pin) && strlen($pin) == $pin_length){
          if(isset($amount)){
              require_once "../api/functions.php";
              //first get the balance and iban from the sender
              $data = checksaldo($nuid, $pin, null);
              $balance = $data['balance'];
              $iban = $data['iban'];

              //chek if balance is enough to withdraw amount
              if(isset($balance)){
                if($amount <= $balance){
                  //insert the new balance
                  $new_balance = $balance - $amount;
                  if(update_saldo($new_balance, $iban) !== null){
                    transaction($iban, null, $amount, $location);
                    $response = array('status' => '0', 'balance' => $new_balance); //sent amount back for conformation
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
