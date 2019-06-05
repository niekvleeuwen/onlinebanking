<?php
  // Initialize the session
  session_start();

  // Check if the user is logged in, if not then redirect him to login page
  if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
      header("location: login.php");
      exit();
  }else{
    if($_SESSION["admin"] !== 1){
      header("location: home.php");
      exit();
    }
  }

  $err = "";
  $stat = "";


  // Include config file
  require_once 'config.php';

  // Check if pin is empty
  if(empty($_POST["pin"])){
      $err = "Please enter a pin.";
  } else{
      if(strlen($_POST["pin"]) == 4){
          $pin = $_POST["pin"];
      }else{
          $err = "Pin not correct";
      }

  }

  // Check if id is empty
  if(empty($_POST["id"])){
    $err = "Please enter a ID.";
  }else{
    if(strlen($_POST["id"]) > 4){
      $err = "ID not correct";
    }else{
      $id = $_POST["id"];
    }
  }

  if(strlen($_POST["nuid"]) == 8){
    // Prepare a select statement
    $sql = "SELECT nuid FROM accounts WHERE nuid = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_nuid);
        // Set parameters
        $param_nuid = trim($_POST["nuid"]);
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            /* store result */
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) == 1){
                $err = "This nuid is already taken.";
            } else{
                $nuid = $_POST["nuid"];
            }
        } else{
            $err =  "Oops! Something went wrong. Please try again later. 1";
        }
    }
    // Close statement
    mysqli_stmt_close($stmt);
    $nuid = $_POST["nuid"];
  }else if(strlen($_POST["nuid"] > 0)){
    $err = "NUID not correct";
  }else{
      $nuid = "NULL";
  }

  if(!$err){
    include 'functions/iban_generator.php';
    // Prepare an insert statement
    $sql = "INSERT INTO accounts (id, iban, nuid, pin) VALUES (?, ?, ?, ?)";
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "isss", $param_id, $param_iban, $param_nuid, $param_pin);
        // Set parameters
        $param_id = $id;
        $param_iban = ibanGenerator("MD", "MODO"); //generate a IBAN
        $param_nuid = $nuid;
        $param_pin = password_hash($pin, PASSWORD_DEFAULT); // Creates a password hash
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $stat = "The account has been created. The IBAN is " . $param_iban . "";
        } else{
            $err = "Oops! Something went wrong. Please try again later. ";
            echo "Prepared Statement Error: " . $link->error;
        }
    }
    // Close statement
    mysqli_stmt_close($stmt);
  }

  // Close connection
  mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <?php
      include('menu.php');
    ?>
    <main role="main">
        <div class="jumbotron">
          <div class="container">
            <h1 class="display-3">Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b></h1>
            <p>Welcome to the Monarch Douglas Bank</p>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-3"></div>
          <div class="col-sm-6">
            <?php
              if($err){
                echo("<div class='alert alert-danger' role='alert'>
                  ". $err . "
                </div>");
              }
              if($stat){
                echo("<div class='alert alert-info' role='alert'>
                  ". $stat . "
                </div>");
              }
            ?>
            </div>
            <div class="col-sm-3"></div>
        </div>
        <div class="row">
          <div class="col-sm-3"></div>
          <div class="col-sm-6">
            <div class="form-group">
              <a href="admin.php" id="back" name="back" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="col-sm-3"></div>
        </div>
        <br />
      </main>
    </body>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</html>
