<?php
  // Initialize the session
  session_start();

  // Check if the user is logged in, if not then redirect user to login page
  if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
      header("location: login.php");
      exit;
  }

  // Include config file
  require_once "config.php";
  include 'functions/iban_generator.php';

  // Define variables and initialize with empty values
  $pin = $nuid = "";
  $pin_err = $nuid_err = $stat = $err = "";

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
            ?>
          </div>
          <div class="col-sm-3"></div>
        </div>
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6">
              <p>
                  <a onclick="show_addaccount()" class="btn btn-info">Add bankaccount</a>
                  <a href="" class="btn btn-warning">Disable bankaccount</a>
                  <a onclick="show_delaccount()" class="btn btn-danger">Delete bankaccount</a>
              </p>
            </div>
            <div class="col-sm-3"></div>
        </div>
        <div class="row">
            <div class="center">
              <div id="addaccount">
                <hr>
                  <div class="wrapper" align="center">
                      <h2>Add a bankaccount</h2>
                      <p>Please fill in this form to create a bankaccount.</p>
                      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                          <div class="form-group <?php echo (!empty($pin_err)) ? 'has-error' : ''; ?>">
                              <label>PIN</label>
                              <input type="text" maxlength="4" name="pin" class="form-control" value="<?php echo $pin; ?>">
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
            </div>
        </div>
        <div class="row">
          <div id="delaccount">
            <hr>
            <div class="col-sm-4"></div>
            <div class="col-sm-4">
              <div class="wrapper" align="left">
                      <h2>Delete a bankaccount</h2>
                      <p>Please fill in this form to delete a bankaccount.</p>
                      <form action='functions/deleteacc.php' method='post'><select class='form-control' name='iban'>
                        <?php
                            //verkrijg de informatie uit de tabel
                            $sql = "SELECT iban, balance FROM accounts WHERE id IN (SELECT id FROM users WHERE id = '" . $_SESSION['id'] . "') ";
                            $result = mysqli_query($link, $sql);

                            while($row = mysqli_fetch_array($result)){
                                echo("<option value='" . $row['iban'] . "'>" . $row['iban'] . "</option>");
                            }
                            echo("</select>");

                            mysqli_close($link);
                        ?>
                        <br><br>
                        <input type='submit' name='Delete' class='btn btn-danger btn-send' value='Delete'>
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
    </body>
    <script>
      <?php
        //check if there are form errors, otherwise hide the form
        if(empty($pin_err) && empty($nuid_err)){
          echo("
              x = document.getElementById('addaccount');
              x.style.display = 'none'; //hide by default
          ");
        }
      ?>

      //hide delaccount by deafault
      x = document.getElementById('delaccount');
      x.style.display = 'none'

      function show_addaccount() {
        var x = document.getElementById("addaccount");
        if (x.style.display === "none") {
          x.style.display = "block";
        } else {
          x.style.display = "none";
        }
      }

      function show_delaccount() {
        var x = document.getElementById("delaccount");
        if (x.style.display === "none") {
          x.style.display = "block";
        } else {
          x.style.display = "none";
        }
      }
    </script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
</html>
