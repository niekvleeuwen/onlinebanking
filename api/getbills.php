<?php
    header('Content-Type: application/json');
    $auth_length = 14;

    $auth = str_replace(' ', '', htmlspecialchars($_POST['auth'])); //remove whitespaces
    if(isset($auth) && strlen($auth) == $auth_length){
          require_once "functions.php";
            $data = getbills();
            if(isset($data)){
                $response = array('status' => '0', 'ten' => $data['bill_10'], 'twenty' => $data['bill_20'], 'fifty' => $data['bill_50']);
            }else{
                $response = array('status' => '1', 'error' => 'Auth not correct.');
            }
    }else{
        $response = array('status' => '1', 'error' => 'Auth not entered or correct.');
    }

    echo(json_encode($response));
?>
