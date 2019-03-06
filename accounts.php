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
include 'functions/iban_generator.php';

// Define variables and initialize with empty values
$pin = $nuid = "";
$pin_err = $nuid_err = $stat = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

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

    // Validate nuid
    if(empty(trim($_POST["nuid"]))){
        $nuid_err = "Please enter a NUID.";
    } else{
        // Prepare a select statement
        $sql = "SELECT nuid FROM accounts WHERE nuid = ?";

        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_nuid);

            // Set parameters
            $param_nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid']));

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);

                if(mysqli_stmt_num_rows($stmt) == 1){
                    $nuid_err = "This nuid is already taken.";
                } else {
                  if(strlen($param_nuid) == 8){
                     $nuid = str_replace(' ', '', htmlspecialchars($_POST['nuid']));
                  }
                }
            } else{
                $err = "Oops! Something went wrong. Please try again later.";
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }

    // Check input errors before inserting in database
    if(empty($pin_err) && empty($nuid_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO accounts (id, iban, nuid, pin) VALUES (?, ?, ?, ?)";

        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "isss", $param_id, $param_iban, $param_nuid, $param_pin);

            // Set parameters
            $param_id = $_SESSION['id'];
            $param_iban = ibanGenerator("SU", "USSR");
            $param_nuid = $nuid;
            $param_pin = $pin;

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $stat = "Succes!";
            } else{
                $err = "Something went wrong. Please try again later. <br />";
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);
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
</head>
<body>
    <div class="page-header">
        <h1>Accounts</h1>
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

            //Omschrijven naar een query zonder SQL injectie mogelijkheden
            $sql = "SELECT iban, balance FROM accounts WHERE id IN (SELECT id FROM users WHERE id = '" . $_SESSION['id'] . "') ";
            $result = mysqli_query($link, $sql);

            if (mysqli_num_rows($result) > 0) {
                echo "<table class='table'><thead>
                      <tr>
                        <th scope='col'>IBAN</th>
                        <th scope='col'>Balance</th>
                      </tr>
                    </thead><tbody>"; // start a table tag in the HTML
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr><td>" . $row['iban'] . "</td><td>€" . $row['balance'] . "</td></tr>";
                }

                echo "</tbody></table>"; //Close the table in HTML
            } else {
                echo "<div class='center'>You do not have any bank accounts</div>";
            }

            mysqli_close($link);
          ?>
        </div>
        <div class="col-sm-3"></div>
      </div>
      <br />
      <div class="row">
        <div class="center">
          <p>
              <a onclick="show_addaccount()" class="btn btn-info">Add bankaccount</a>
              <a href="" class="btn btn-warning">Disable bankaccount</a>
              <a href="" class="btn btn-danger">Delete bankaccount</a>
              <a href="index.php" class="btn btn-info">Home</a>
          </p>
        </div>
      </div>
      <div class="row">
          <div class="center">
            <div id="addaccount">
              <hr>
              <div class="col-sm-4"></div>
              <div class="col-sm-4">
                <div class="wrapper" align="left">
                    <h2>Add a bankaccount</h2>
                    <p>Please fill this form to create a bankaccount.</p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group <?php echo (!empty($pin_err)) ? 'has-error' : ''; ?>">
                            <label>PIN</label>
                            <input type="text" name="pin" class="form-control" value="<?php echo $pin; ?>">
                            <span class="help-block"><?php echo $pin_err; ?></span>
                        </div>
                        <div class="form-group <?php echo (!empty($nuid_err)) ? 'has-error' : ''; ?>">
                            <label>NUID</label>
                            <input type="text" name="nuid" class="form-control" value="<?php echo $nuid; ?>">
                            <span class="help-block"><?php echo $nuid_err; ?></span>
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
    </body>
    <script>
      <?php
        if(empty($pin_err) && empty($nuid_err)){ //check if there are form errors, otherwise hide the form
          echo("
              x = document.getElementById('addaccount');
              x.style.display = 'none'; //hide by default
          ");
        }
      ?>
      function show_addaccount() {
        var x = document.getElementById("addaccount");
        if (x.style.display === "none") {
          x.style.display = "block";
        } else {
          x.style.display = "none";
        }
      }
    </script>
</html>
