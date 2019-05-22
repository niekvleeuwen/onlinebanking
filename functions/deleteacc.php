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

    // Include config file
    require_once "../config.php";
    $iban = $stat = $err = "";

    // Validate iban
    if(empty(trim($_POST["iban"]))){
        $err = "Please enter a IBAN.";
    } else{
      if(strlen($_POST["iban"]) == 14){
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
                  mysqli_stmt_bind_result($stmt, $id, $balance);
                  if(mysqli_stmt_fetch($stmt)){
                      if($id == $_SESSION['id']){
                        if($balance == 0){
                          // Close first statement
                          mysqli_stmt_close($stmt);
                          //Drop table
                          $sql = "DELETE FROM accounts WHERE iban= ?";

                          if($stmt = mysqli_prepare($link, $sql)){
                            //Bind variables to the prepared statement as parameters
                            mysqli_stmt_bind_param($stmt, "s", $iban);
                            // Set parameters
                            $iban = htmlspecialchars($_POST['iban']);
                            // Attempt to execute the prepared statement
                            if(mysqli_stmt_execute($stmt)){
                                $stat = "Your account is successfully deleted!";
                            }else{
                              setError("");
                            }
                          }else{
                            setError("");
                          }

                        }else{
                          setError("There is still money on the account. Please transfer the money first.");
                        }
                      }else{
                          setError("");
                      }
                    }else{
                        setError("");
                    }
                  }else{
                    setError("");
                  }
              } else{
                setError("");
              }
              // Close statement
              mysqli_stmt_close($stmt);
          }else{
            setError("Please enter a correct IBAN.");
          }
    }

    function setError($err_msg){
      global $err; //use global so variable is stored outside scope
      if($err_msg == ""){
        $err = "Oops! Something went wrong. Please try again later.";
      }else{
        $err = $err_msg;
      }
    }
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
                <a href="../admin.php" id="back" name="back" class="btn btn-primary">Back</a>
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
