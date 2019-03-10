<?php
  header('Content-Type: application/json');

  // Initialize the session
  session_start();

  /* This page is only for logged in users */

  // Check if the user is logged in, if not then redirect user to login page
  if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
      header("location: ../index.php");
      exit;
  }

  require_once "../config.php";

  $nuid_length = 8;
  $pin_length = 4;

  $nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid'])); //remove whitespaces
  $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces

  if(isset($nuid) && strlen($nuid) == $nuid_length){
      if(isset($pin) && strlen($pin) == $pin_length){
          include '../functions/iban_generator.php';

          //first get the balance and id from the user
          $sql = "INSERT INTO accounts (id, iban, nuid, pin) VALUES (?, ?, ?, ?)";
          $stmt->bind_param("isss", $param_id, $param_iban, $param_nuid, $param_pin);

          $param_id = $_SESSION['id'];
          $param_iban = ibanGenerator("MD", "USSR");
          $param_nuid = $nuid;
          $param_pin = $pin;

          if (!$stmt->execute()) {
              $response = array('status' => '0', 'iban' => $param_iban);
          }else{
              $response = array('status' => '1', 'error' => 'Oops! Something went wrong. Please try again later.');
          }
          $stmt->close();
      }else{
          $response = array('status' => '1', 'error' => 'PIN not entered or correct.');
      }
  }else{
      $response = array('status' => '1', 'error' => 'NUID not entered or correct.');
  }

  $link->close();
  
  echo(json_encode($response));
?>
