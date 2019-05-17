<?php
  // Initialize the session
  session_start();

  // Check if the user is logged in, otherwise redirect to login page
  if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
      header("location: login.php");
      exit;
  }

  // Include config file
  require_once "config.php";

  // Define variables and initialize with empty values
  $new_password = $confirm_password = "";
  $new_password_err = $confirm_password_err = "";

  // Processing form data when form is submitted
  if($_SERVER["REQUEST_METHOD"] == "POST"){

      // Validate new password
      if(empty(trim($_POST["new_password"]))){
          $new_password_err = "Please enter the new password.";
      } elseif(strlen(trim($_POST["new_password"])) < 6){
          $new_password_err = "Password must have atleast 6 characters.";
      } else{
          $new_password = trim($_POST["new_password"]);
      }

      // Validate confirm password
      if(empty(trim($_POST["confirm_password"]))){
          $confirm_password_err = "Please confirm the password.";
      } else{
          $confirm_password = trim($_POST["confirm_password"]);
          if(empty($new_password_err) && ($new_password != $confirm_password)){
              $confirm_password_err = "Password did not match.";
          }
      }

      // Check input errors before updating the database
      if(empty($new_password_err) && empty($confirm_password_err)){
          // Prepare an update statement
          $sql = "UPDATE users SET password = ? WHERE id = ?";

          if($stmt = mysqli_prepare($link, $sql)){
              // Bind variables to the prepared statement as parameters
              mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);

              // Set parameters
              $param_password = password_hash($new_password, PASSWORD_DEFAULT);
              $param_id = $_SESSION["id"];

              // Attempt to execute the prepared statement
              if(mysqli_stmt_execute($stmt)){
                  // Password updated successfully. Destroy the session, and redirect to login page
                  session_destroy();
                  header("location: login.php");
                  exit();
              } else{
                  echo "Oops! Something went wrong. Please try again later.";
              }
          }

          // Close statement
          mysqli_stmt_close($stmt);
      }

      // Close connection
      mysqli_close($link);
  }
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Reset Password</title>
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
            <h1 class="display-3">Monarch Douglas Bank</h1>
            <p>The Monarch Douglas Bank is the international bank for criminals</p>
          </div>
        </div>
        <div class="container">
          <div class="row">
            <div class="col-md-6">
              <h2>Reset Password</h2>
              <p>Please fill out this form to reset your password.</p>
              <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                  <div class="form-group <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                      <label>New Password</label>
                      <input type="password" name="new_password" class="form-control" value="<?php echo $new_password; ?>">
                      <span class="help-block"><?php echo $new_password_err; ?></span>
                  </div>
                  <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                      <label>Confirm Password</label>
                      <input type="password" name="confirm_password" class="form-control">
                      <span class="help-block"><?php echo $confirm_password_err; ?></span>
                  </div>
                  <div class="form-group">
                      <input type="submit" class="btn btn-primary" value="Submit">
                      <a class="btn btn-link" href="home.php">Cancel</a>
                  </div>
              </form>
            </div>
            <div class="col-md-6"></div>
          </div>
          <hr>
        </div>
      </main>
      <footer class="container">
        <p>&copy; Monarch Douglas Bank 2018-2019</p>
      </footer>
      <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
      <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
    </body>
  </html>
