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

  // Include config file
  require_once "../config.php";

  $nuid_length = 8;
  $pin_length = 4;
  $username_length = 50;


  $nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid'])); //remove whitespaces
  $pin = str_replace(' ', '', htmlspecialchars($_POST['pin'])); //remove whitespaces
  $username = htmlspecialchars($_POST['username']);

  if(isset($nuid) && strlen($nuid) == $nuid_length){
      if(isset($pin) && strlen($pin) == $pin_length){
          /*
          include '../functions/iban_generator.php';

          // Prepare an insert statement
          $sql = "INSERT INTO accounts (id, iban, nuid, pin) VALUES (?, ?, ?, ?)";

          if($stmt = mysqli_prepare($link, $sql)){

              // Bind variables to the prepared statement as parameters
              mysqli_stmt_bind_param($stmt, "isss", $param_id, $param_iban, $param_nuid, $param_pin);

              // Set parameters
              $param_id = $_SESSION['id'];
              $param_iban = ibanGenerator("MD", "USSR");
              $param_nuid = $nuid;
              $param_pin = $pin;

              // Attempt to execute the prepared statement
              if(mysqli_stmt_execute($stmt)){
                  $response = array('iban' => $iban);
              } else{
                $response = array('error' => 'Oops! Something went wrong. Please try again later.');
              }
          }

          // Close statement
          mysqli_stmt_close($stmt);

          //Close connection
          mysqli_close($link);
          */
          $response = array('iban' => 'Test');
      }else{
          $response = array('error' => 'PIN not entered/correct. PIN is 4 characters long');
      }
  }else{
      $response = array('error' => 'NUID not entered/correct. NUID is 8 characters long');
  }

  echo json_encode($response);
?>
