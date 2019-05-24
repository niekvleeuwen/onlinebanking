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

  // Processing form data when form is submitted
  if($_SERVER["REQUEST_METHOD"] == "POST"){
      $err = "";
      // Check if nuid is empty
      if(empty($_POST["nuid"])){
          $err = "Please enter a nuid.";
      } else{
          $nuid = $_POST["nuid"];
      }

      $status = $_POST["status"];

      if(!$err){
        require 'api/functions.php';
        if($status == 1){
          if(blockcard($nuid) == 1){
              $stat = "Gelukt!";
          }else{
            $err = 'Oops! Something went wrong. Please try again later.';
          }
        }else if($status == 0){
          if(reset_pin_attempts($nuid) == 1){
              $stat = "Gelukt!";
          }else{
            $err = 'Status not entered or 1 digit.';
          }
        }else{
          $err = 'Oops! Something went wrong. Please try again later.';
        }
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
            <?php
              if(!isset($link) || $link == false){
                // Include config file
                require 'config.php';
              }

              //Omschrijven naar een query zonder SQL injectie mogelijkheden
              $sql = "SELECT username, iban, nuid, balance, pin_attempts FROM accounts, users WHERE users.id = accounts.id";
              $result = mysqli_query($link, $sql);

              if (mysqli_num_rows($result) > 0) {
                  echo "<table class='table'><thead>
                        <tr>
                          <th scope='col'>Name</th>
                          <th scope='col'>IBAN</th>
                          <th scope='col'>NUID</th>
                          <th scope='col'>Balance</th>
                          <th scope='col'>Status</th>
                        </tr>
                      </thead><tbody>"; // start a table tag in the HTML
                  while($row = mysqli_fetch_assoc($result)) {
                      echo "<tr>
                                <td>" . $row['username'] ."</td>
                                <td>" . $row['iban'] ."</td>
                                <td>" . $row['nuid'] ."</td>
                                <td>
                                    €" . $row['balance'] . "
                                </td>
                                ";
                                if($row['pin_attempts'] > 2){
                                    echo("<td>
                                            <form action='' method='POST'>
                                                  <input type='hidden' name='nuid' value='" . $row['nuid'] ."'>
                                                  <input type='hidden' name='status' value=0>
                                                  <input class='btn btn-danger' type='submit' value='unblock >>'>
                                            </form>
                                          </td>
                                  ");
                                }else{
                                  echo("<td>
                                          <form action='' method='POST'>
                                                <input type='hidden' name='nuid' value='" . $row['nuid'] ."'>
                                                <input type='hidden' name='status' value=1>
                                                <input class='btn btn-primary' type='submit' value='block >>'>
                                          </form>
                                        </td>
                                ");
                                }
                          echo "</tr>";
                  }
                  echo "</tbody></table>"; //Close the table in HTML
              } else {
                  echo "<div class='center'>Error</div>";
              }
            ?>
          </div>
          <div class="col-sm-3"></div>
        </div>
        <div class="row">
          <div class="col-sm-3"></div>
          <div class="col-sm-6">
          <div class="wrapper">
              <h2>Create a bankaccount</h2>
              <p>Please fill this form to create a bankaccount.</p>
              <form action="addacount.php" method="post">
                  <div class="form-group">
                      <label>Account *</label>
                      <select class='form-control' name='id'>
                        <?php
                          $sql = "SELECT id,username FROM users";
                          $result = mysqli_query($link, $sql);

                          while($row = mysqli_fetch_array($result)){
                                echo("<option value='" . $row['id'] . "'>" . $row['username'] . "</option>");
                            }
                            echo("</select>");
                        ?>
                    </div>
                    <div class="form-group">
                        <label>Pin *</label>
                        <input type="password" maxlength="4" name="pin" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>NUID</label>
                        <input type="text" maxlength="8" name="nuid" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Submit">
                    </div>
                    <p>* is required</p>
              </form>
          </div>
          <div class="col-sm-3"></div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-3"></div>
        <div class="col-sm-6">
          <div class="wrapper">
              <h2>Delete a bankaccount</h2>
              <p>Please fill this form to create a bankaccount.</p>
              <form action='functions/deleteacc.php' method='post'><select class='form-control' name='iban'>
                <?php
                    //verkrijg de informatie uit de tabel
                    $sql = "SELECT username, iban, balance FROM accounts, users WHERE accounts.id = users.id";
                    $result = mysqli_query($link, $sql);

                    while($row = mysqli_fetch_array($result)){
                      echo("<option value='" . $row['iban'] . "'>" . $row['iban'] . " van " . $row['username'] . "</option>");
                    }
                    echo("</select>");

                    mysqli_close($link);
                ?>
              <br>
              <input type='submit' name='Delete' class='btn btn-danger btn-send' value='Delete'>
            </form>
          </div>
        </div>
        <div class="col-sm-3"></div>
      </div>
      <footer class="container">
        <hr>
        <p>&copy; Monarch Douglas Bank 2018-2019</p>
      </footer>
      </main>
    </body>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</html>
