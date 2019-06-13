<?php
    header('Content-Type: application/json');

    require_once "../config.php";

    $iban_length = 14;
    $pin_length = 4;

    $iban = str_replace(' ', '', htmlspecialchars($_POST['iban'])); //remove whitespaces
    $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces
    $amount = htmlspecialchars($_POST['amount']);
    $location = htmlspecialchars($_POST['location']);
    //check if a location is available, otherwise set the location to unkown
    if(!isset($location)){
        $location = "Unkown";
    }

    if(isset($iban) && strlen($iban) == $iban_length){
        if(isset($pin) && strlen($pin) == $pin_length){
          if(isset($amount)){
              if($amount > 0 && $amount < 500){
                require_once "../api/functions.php";
                //first get the target bank code
                $bank_code = substr($iban, 4, 4);

                //second get the target account number
                $acc_number = substr($iban, 8, 14);

                if(remote_transaction($bank_code, $acc_number, $pin, $amount) == true){
                  $response = array('status' => '0');
                }else{
                  $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
                }
              }else{
                $response = array('status' => '1', 'error' => 'Amount not between 0 - 500.');
              }
          }else{
              $response = array('status' => '1', 'error' => 'Amount not entered.');
          }
        }else{
            $response = array('status' => '1', 'error' => 'PIN not entered or correct.');
        }
    }else{
        $response = array('status' => '1', 'error' => 'iban not entered or correct.');
    }

    echo(json_encode($response));
?>
