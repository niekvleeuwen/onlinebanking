<?php
    header('Content-Type: application/json');
    $iban_length = 14;
    $pin_length = 4;

    $iban = str_replace(' ', '', htmlspecialchars($_POST['iban'])); //remove whitespaces
    $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces

    if(isset($iban) && strlen($iban) == $iban_length){
        if(isset($pin) && strlen($pin) == $pin_length){
            require_once "functions.php";
            $data = checksaldo($pin, $iban);
            $balance = $data['balance'];
            if(isset($balance)){
                $response = array('status' => '0', 'balance' => $balance);
            }else{
                $response = array('status' => '1', 'error' => 'Card or Pin not correct.');
            }
        }else{
            $response = array('status' => '1', 'error' => 'PIN not entered or correct.');
        }
    }else{
        $response = array('status' => '1', 'error' => 'Iban not entered or correct.');
    }

    echo(json_encode($response));
?>
