<?php
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
    header('Content-Type: application/json');
    $iban_length = 14;
    $pin_length = 4;

    $iban = str_replace(' ', '', htmlspecialchars($_POST['iban'])); //remove whitespaces
    $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces

    if(isset($iban) && strlen($iban) == $iban_length){
        if(isset($pin) && strlen($pin) == $pin_length){
            require_once "functions.php";
            $bank_code = substr($iban, 4, 4);

            if($bank_code == "MODO"){
              $pin_attempts = checkpin($iban, $pin); //check if the pin is correct
              if(isset($pin_attempts)){
                  if($pin_attempts < 3){ //check if the card is blocked
                    //if the pin is correct, reset the pin attempts if the value is greater than 0
                    if($pin_attempts > 0){
                      reset_pin_attempts($iban);
                    }
                    $response = array('status' => '0');
                  }else{
                    $response = array('status' => '1', 'error' => 'Card is blocked.');
                  }
              }else{
                  if(add_pin_attempt($iban) !== null){ //add the pin attempt to the database
                    $current_pin_attempts = get_pin_attempts($iban);
                    if($current_pin_attempts < 3){ //check if the card is blocked or the user tries to
                        $response = array('status' => '1', 'error' => 'Pin not correct.', 'pin_attempts' =>   $current_pin_attempts);
                    }else{
                        $response = array('status' => '1', 'error' => 'Card is blocked.');
                    }
                  }else{
                    $response = array('status' => '1', 'error' => 'Card or Pin not correct.');
                  }
              }
            }else{
              //external bank
              if(remote_checkpin($bank_code, $iban, $pin) == true){
                $response = array('status' => '0');
              }else{
                $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
              }
            }
        }else{
            $response = array('status' => '1', 'error' => 'PIN not entered or 4 digits.');
        }
    }else{
        $response = array('status' => '1', 'error' => 'IBAN not entered or 14 digits.');
    }

    echo(json_encode($response));
?>
