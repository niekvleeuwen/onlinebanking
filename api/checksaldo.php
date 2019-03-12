<?php
    header('Content-Type: application/json');

    $_POST['nuid'] = "B8C5E3K8";
    $_POST['pin'] = "1111";

    $nuid_length = 8;
    $pin_length = 4;

    $nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid'])); //remove whitespaces
    $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces

    if(isset($nuid) && strlen($nuid) == $nuid_length){
        if(isset($pin) && strlen($pin) == $pin_length){
            require_once "../api/functions.php";
            $balance = checksaldo($nuid, $pin);
            if(isset($balance)){
                $response = array('status' => '0', 'balance' => $balance);
            }else{
                $response = array('status' => '1', 'error' => 'Card or Pin not correct.');
            }
        }else{
            $response = array('status' => '1', 'error' => 'PIN not entered or correct.');
        }
    }else{
        $response = array('status' => '1', 'error' => 'NUID not entered or correct.');
    }

    echo(json_encode($response));
?>
