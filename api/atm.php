<?php
    header('Content-Type: application/json');

    require_once "../config.php";

    $ten_length = 2;
    $twenty_length = 2;
    $fifty_length = 2;
    $atm_id_length = 1;

    $ten = str_replace(' ', '', htmlspecialchars($_POST['ten'])); //remove whitespaces
    $twenty = str_replace(' ', '', htmlspecialchars($_POST['twenty'])); //remove whitespaces
    $fifty = htmlspecialchars($_POST['fifty']);
    $atm_id = htmlspecialchars($_POST['atm_id']);

    if(isset($ten) && strlen($ten) <= $ten_length){
        if(isset($twenty) && strlen($twenty) <= $twenty_length){
          if(isset($fifty) && strlen($fifty) <= $fifty_length){
            if(isset($atm_id) && strlen($atm_id) == $atm_id_length){
              require_once "functions.php";
              if(atm($ten, $twenty, $fifty, $atm_id) === true){
                $response = array('status' => '0');
              }else{
                $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
              }
            }else{
                $response = array('status' => '1', 'error' => 'ATM ID not entered or correct.');
            }
          }else{
              $response = array('status' => '1', 'error' => 'Fifty not entered or correct.');
          }
        }else{
            $response = array('status' => '1', 'error' => 'Twent not entered or correct.');
        }
    }else{
        $response = array('status' => '1', 'error' => 'Ten not entered or correct.');
    }

    //close connection
    $link->close();

    echo(json_encode($response));
?>
