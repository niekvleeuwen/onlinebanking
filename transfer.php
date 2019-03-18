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
$pin = $amount = $iban_sender = $iban_recipient = "";
$pin_err = $amount_err = $iban_recipient_err = $iban_sender_err = $stat = $err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate iban sender
    if(empty(trim($_POST["iban_sender"]))){
        $iban_sender_err = "Please enter a IBAN.";
    } else{
      $iban_sender = $_POST['iban_sender']; //iban will be checked in api call
    }

    // Validate iban recipient
    if(empty(trim($_POST["iban_recipient"]))){
        $iban_recipient_err = "Please enter a IBAN.";
    } else{
      $iban_recipient = $_POST['iban_recipient']; //iban will be checked in api call
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
    $url = 'https://bank.niekvanleeuwen.nl/api/transfer.php';
    $data = array('nuid' => $nuid, 'pin' => $pin, 'iban_sender' => $iban_sender, 'iban_recipient' => $iban_recipient , 'amount' => $amount);

    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $json_decoded = json_decode($result);

    $status = $json_decoded->status;
    if($status == 0){
      $stat = "De overschrijving is voltooid!";
    }else{
      $err = $json_decoded->error;
    }
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
                      <div class="form-group <?php echo (!empty($iban_sender_err)) ? 'has-error' : ''; ?>">
                          <label>Account</label>
                          <?php
                              echo("<select class='form-control' name='iban_sender'>");
                              $sql = "SELECT iban FROM accounts WHERE id IN (SELECT id FROM users WHERE id = '" . $_SESSION['id'] . "') ";
                              $result = mysqli_query($link, $sql);

                              while($row = mysqli_fetch_array($result)){
                                  echo("<option value='" . $row['iban'] . "'>" . $row['iban'] . "</option>");
                              }
                              echo("</select>");

                              mysqli_close($link);
                          ?>
                          <span class="help-block"><?php echo $iban_sender_err; ?></span>
                        </div>
                        <div class="form-group <?php echo (!empty($iban_recipient_err)) ? 'has-error' : ''; ?>">
                            <label>IBAN Recipient</label>
                            <input type="text" name="iban_recipient" class="form-control" value="<?php echo $iban_recipient; ?>">
                            <span class="help-block"><?php echo $iban_recipient_err; ?></span>
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
      <hr>
      <div class="row">
        <div class="center">
          <p>
              <a href="home.php" class="btn btn-info">Home</a>
              <a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a>
          </p>
        </div>
      </div>
    </body>
</html>
