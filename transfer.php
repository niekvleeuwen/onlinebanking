<?php
  // Initialize the session
  session_start();

  // Check if the user is logged in, if not then redirect him to login page
  if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
      header("location: login.php");
      exit;
  }

  // Include config file
  require_once "config.php";
  require_once "api/functions.php";

  // Define variables and initialize with empty values
  $pin = $amount = $iban_sender = $iban_recipient = "";
  $stat = $err = "";

  // Processing form data when form is submitted
  if($_SERVER["REQUEST_METHOD"] == "POST"){

      // Validate iban sender
      if(empty(trim($_POST["iban_sender"]))){
          $err = "Please enter a IBAN.";
      } else{
        if(checkiban($_POST['iban_sender']) == null){
            $err = "IBAN doesn't exist.";
        }else{
            if(strlen($_POST['iban_sender']) == 14){
              $iban_sender = $_POST['iban_sender'];
            }else{
              $err = "IBAN not correct.";
            }
        }
      }

      // Validate iban recipient
      if(empty(trim($_POST["iban_recipient"]))){
          $err = "Please enter a IBAN.";
      } else{
        if(checkiban($_POST['iban_recipient']) == null){
            $err = "IBAN doesn't exist.";
        }else{
            if(strlen($_POST['iban_recipient']) == 14){
                $iban_recipient = $_POST['iban_recipient']; //iban will be checked in api call
            }else{
                $err = "IBAN not correct.";
            }

        }
      }

      // Validate amount
      if(empty(trim($_POST["amount"]))){
        $err = "Please enter a amount.";
      } else{
        if(is_numeric($_POST["amount"])){
            $amount= trim($_POST["amount"]);
        }else{
          $err = "Please enter numbers as amount (use decimal point)";
        }
      }

      // Validate pin
      if(empty(trim($_POST["pin"]))){
          $err= "Please enter a pin.";
      } else{
          if(strlen($_POST["pin"]) == 4){
            if(is_numeric($_POST["pin"])){
                $pin = trim($_POST["pin"]);
                if(checkpin($iban_sender, $pin) == null){
                  $err = "Pin not correct.";
                }
            }else{
                $err = "Please enter numbers as pin";
            }
          }else{
                $err = "Please enter a pin of 4 characters.";
          }
      }

      if($err == ""){
        //get the users balance
        $data = checksaldo($pin, $iban_sender);
        $balance_sender = $data['balance'];
        if(isset($balance_sender)){
          if($amount <= $balance_sender){ //check if the balance is sufficient to transfer the amount
            if(update_saldo($balance_sender - $amount, $iban_sender) !== null){ //insert the new balance of the sender
                if(add_saldo($amount, $iban_recipient) !== null){ //insert the new balance of the recipient
                    if(transaction($iban_sender, $iban_recipient, $amount, "Online Banking") !== null){ //insert the transaction record in the database
                      $stat = "The transfer is complete";
                    }else{
                      $stat = "The transfer is complete, but isn't logged! Contact customer support please.";
                    }
                }else{
                  $err = "Oops! Something went wrong. Please try again later.";
                }
            }else{
                $err = "Oops! Something went wrong. Please try again later.";
            }
          }else{
            $err = "Not enough funds to withdraw.";
          }
        }else{
            $err = "Pin not correct.";
        }
      }
  }
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Monarch Douglas Bank</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link href="css/style.css" rel="stylesheet">
  </head>
  <body>
    <?php
      include 'menu.php';
    ?>
    <main role="main">
      <div class="jumbotron">
        <div class="container">
          <h1 class="display-3">Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b></h1>
          <p>Welcome to the Monarch Douglas Bank</p>
        </div>
      </div>
      <div class="container">
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
          <div class="col-sm-4"></div>
          <div class="col-sm-4">
            <div class="wrapper" align="left">
                <h2>Transfer Money</h2>
                <p>Please fill in this form to tranfer money.</p>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
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
                          </div>
                          <div class="form-group">
                              <label>IBAN Recipient</label>
                              <input type="text" name="iban_recipient" class="form-control" value="<?php echo $iban_recipient; ?>">
                          </div>
                          <div class="form-group">
                              <label>Amount</label>
                              <input type="text" maxlength="10" name="amount" class="form-control" value="<?php echo $amount; ?>">
                          </div>
                          <div class="form-group">
                              <label>PIN</label>
                              <input type="password" maxlength="4" name="pin" class="form-control" value="<?php echo $pin; ?>">
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
    </main>
    <footer class="container">
      <hr>
      <p>&copy; Monarch Douglas Bank 2018-2019</p>
    </footer>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
    </body>
</html>
