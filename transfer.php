<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$pin = $amount = $iban = "";
$pin_err = $amount_err = $iban_err = $stat = $err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate iban
    if(empty(trim($_POST["iban"]))){
        $iban_err = "Please enter a IBAN.";
    } else{
      $iban = $_POST['iban']; //iban will be checked in api call
      /*
        include_once "api/functions.php";
        if(checkiban($_POST["iban"]) !== null){
          $iban = $_POST['iban'];
        }else{
          $iban_err = "Iban is not valid";
        }
      */
    }

    // Validate amount
    if(empty(trim($_POST["amount"]))){
        $amount_err = "Please enter a amount.";
    } else{
      if(is_numeric($_POST["amount"])){
          $amount= trim($_POST["amount"]);
      }else{
          $amount_err = "Please enter numbers as amount";
      }
    }

    // Validate pin
    if(empty(trim($_POST["pin"]))){
        $pin_err = "Please enter a pin.";
    } else{
        if(strlen($_POST["pin"]) == 4){
          if(is_numeric($_POST["pin"])){
              $pin = trim($_POST["pin"]);
          }else{
              $pin_err = "Please enter numbers as pin";
          }
        }else{
              $pin_err = "Please enter a pin of 4 characters.";
        }
    }

    //user is already logged in (so the user is verified and has authorization) so we need to get the pin and nuid of the current user for the api call
    $stmt = $link->prepare("SELECT pin, nuid FROM accounts WHERE id = ?");
    $stmt->bind_param("s", $param_id,);
    $param_id = $_SESSION["id"];

    if (!$stmt->execute()) {
        echo("Oops! Something went wrong. Please try again later.");
        exit();
    }

    $stmt->bind_result($pin, $nuid);
    $stmt->fetch();
    $stmt->close();

    //we need to make a call to the api here
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,"api/transfer.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,
                "nuid=$nuid&pin=$pin&amount=$amount&iban=$iban");

    // In real life you should use something like:
    // curl_setopt($ch, CURLOPT_POSTFIELDS,
    //          http_build_query(array('postvar1' => 'value1')));

    // Receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);

    curl_close ($ch);

    var_dump($server_output);

    /* Further processing ...
    if ($server_output == "OK") { ... } else { ... } */
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="css/home.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="page-header">
        <h1>Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>.</h1><p class="text-muted">Welcome to the Monarch Douglas Bank</p>
    </div>
      <div class="row">
          <div class="center">
            <div id="addaccount">
              <div class="col-sm-4"></div>
              <div class="col-sm-4">
                <div class="wrapper" align="left">
                    <h2>Transfer Money</h2>
                    <p>Please fill in this form to tranfer money.</p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group <?php echo (!empty($iban_err)) ? 'has-error' : ''; ?>">
                            <label>IBAN</label>
                            <input type="text" name="iban" class="form-control" value="<?php echo $iban; ?>">
                            <span class="help-block"><?php echo $iban_err; ?></span>
                        </div>
                        <div class="form-group <?php echo (!empty($amount_err)) ? 'has-error' : ''; ?>">
                            <label>Amount</label>
                            <input type="text" maxlength="10" name="amount" class="form-control" value="<?php echo $amount; ?>">
                            <span class="help-block"><?php echo $amount_err; ?></span>
                        </div>
                        <div class="form-group <?php echo (!empty($pin_err)) ? 'has-error' : ''; ?>">
                            <label>PIN</label>
                            <input type="password" maxlength="4" name="pin" class="form-control" value="<?php echo $pin; ?>">
                            <span class="help-block"><?php echo $pin_err; ?></span>
                        </div>
                        <div class="form-group">
                            <input type="submit" class="btn btn-primary" value="Submit">
                        </div>
                    </form>
                </div>
              </div>
              <div class="col-sm-4"></div>
            </div>
          </div>
      </div>
      <br />
      <div class="row">
        <div class="center">
          <p>
              <a href="accounts.php" class="btn btn-info">Manage accounts</a>
              <a href="home.php" class="btn btn-info">Home</a>
              <a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a>
          </p>
        </div>
      </div>
    </body>
</html>
