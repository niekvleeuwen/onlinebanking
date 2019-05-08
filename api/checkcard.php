<?php
    header('Content-Type: application/json');
    $nuid_length = 8;
    $pin_length = 4;

    $nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid'])); //remove whitespaces

    if(isset($nuid) && strlen($nuid) == $nuid_length){
            require_once "functions.php";
            if(checkcard($nuid) == true){ //check if the pin is correct
                $response = array('status' => '0');
            }else{
                $response = array('status' => '1', 'error' => 'Card is unkown.');
            }
    }else{
        $response = array('status' => '1', 'error' => 'NUID not entered or 8 digits.');
    }

    echo(json_encode($response));
?>
