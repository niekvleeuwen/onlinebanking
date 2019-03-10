<?php
    header('Content-Type: application/json');

    // Include config file
    require_once "../config.php";
    
    $nuid_length = 8;
    $pin_length = 4;

    $nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid'])); //remove whitespaces
    $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces

    if(isset($nuid) && strlen($nuid) == $nuid_length){
        if(isset($pin) && strlen($pin) == $pin_length){
            // prepare and bind
            $stmt = $link->prepare("SELECT balance FROM accounts WHERE nuid = ? AND pin = ?");
            $stmt->bind_param("ss", $param_nuid, $param_pin);

            // set parameters and execute
            $param_nuid = $nuid;
            $param_pin = $pin;

            if (!$stmt->execute()) {
                $response = array('error' => 'Oops! Something went wrong. Please try again later.');
            }
            // bind result variables
            $stmt->bind_result($balance);

            // fetch value
            $stmt->fetch();

            if(isset($balance)){
                $response = array('status' => '0', 'balance' => $balance);
            }else{
                $response = array('status' => '1', 'error' => 'Card or Pin not correct.');
            }

            $stmt->close();
        }else{
            $response = array('status' => '1', 'error' => 'PIN not entered or correct.');
        }
    }else{
        $response = array('status' => '1', 'error' => 'NUID not entered or correct.');
    }

    //close connection
    $link->close();

    echo json_encode($response);
?>
