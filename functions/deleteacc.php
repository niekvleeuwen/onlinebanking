<?php
    // Initialize the session
    session_start();

    // Check if the user is logged in, if not then redirect him to login page
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: index.php");
        exit;
    }

    // Include config file
    require_once "../config.php";

    /*STEPS*/
    //Firts check if account is Empty

    $iban = "";
    $err = "";

    // Validate iban
    if(empty(trim($_POST["iban"]))){
        $err = "Please enter a IBAN.";
    } else{
      // Prepare a select statement
      $sql = "SELECT id, balance FROM accounts WHERE iban = ?";

      if($stmt = mysqli_prepare($link, $sql)){
          // Bind variables to the prepared statement as parameters
          mysqli_stmt_bind_param($stmt, "s", $param_iban);

          // Set parameters
          $param_iban = htmlspecialchars($_POST['iban']);

          // Attempt to execute the prepared statement
          if(mysqli_stmt_execute($stmt)){
              /* store result */
              mysqli_stmt_store_result($stmt);

              while($row = mysqli_fetch_array($result)){
                  $id = $row['id'];
                  $balance = $row['balance'];
              }

              if($id == $_SESSION['id']){
                  if($balance == 0){
                    // Prepare a select statement
                    $sql = "DELETE FROM accounts WHERE iban = ?";

                    if($stmt = mysqli_prepare($link, $sql)){
                        // // Bind variables to the prepared statement as parameters
                        // mysqli_stmt_bind_param($stmt, "s", $param_iban);
                        //
                        // // Set parameters
                        // $param_iban = htmlspecialchars($_POST['iban']);

                        // Attempt to execute the prepared statement
                        if(mysqli_stmt_execute($stmt)){
                            $stat = "AAccount is successfully deletedccount is successfully deleted";
                        }else{
                          $err = "Oops! Something went wrong. Please try again later.";
                        }
                      }
                  }else{
                    $err = "There is still money on the account. Please transfer the money first.";
                  }
              }else{
                $err = "Oops! Something went wrong. Please try again later.";
              }
          } else{
              $err = "Oops! Something went wrong. Please try again later.";
          }
      }

      // Close statement
      mysqli_stmt_close($stmt);


        if(strlen($_POST["iban"]) == 14){
            $iban = trim($_POST["iban"]);
        }else{
            $iban_err = "Please enter a correct IBAN.";
        }
    }
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Delete Accounts</title>
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
      <link rel="stylesheet" href="css/home.css">
  </head>
  <body>
      <div class="page-header">
          <div class="text-center">
            <h1>Delete Account</h1>
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
        </div>
    </body>
</html>
