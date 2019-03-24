<?php
  // Initialize the session
  session_start();

  // Check if the user is logged in, if not then redirect user to login page
  if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
      header("location: login.php");
      exit;
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
          <div class="col-sm-2"></div>
          <div class="col-sm-8">
            <?php
            // Include config file
            require_once "config.php";

              if(isset($_POST['iban'])){
                if(strlen($_POST['iban']) == 14){

                  $sql = "SELECT * FROM transactions WHERE iban_sender IN (SELECT iban FROM accounts WHERE id = " . $_SESSION['id'] . " AND iban = '" . $_POST['iban'] . "')";
                  $result = $link->query($sql);

                  if ($result->num_rows > 0) {
                      // output data of each row
                      echo "<h1>Afschriften</h1><table class='table'>
                                <tr>
                                    <th>Amount</th>
                                    <th>Time</th>
                                    <th>Location</th>
                                    <th>Recipient</th>
                                </tr>";
                      while($row = $result->fetch_assoc()) {
                          echo("<tr>
                                    <td>- ".$row["amount"]."</td>
                                    <td>".$row["timestamp"]."</td>
                                    <td>".$row["location"]."</td>
                                    <td>");
                                    if(isset($row["iban_recipient"])){
                                        echo($row["iban_recipient"]);
                                      }else{
                                        echo("Withdraw");
                                    }
                                    echo("</td></tr>");
                      }
                      echo "</table>";
                  } else {
                      echo "0 results";
                  }



                  $sql = "SELECT * FROM transactions WHERE iban_recipient IN (SELECT iban FROM accounts WHERE id = " . $_SESSION['id'] . " AND iban = '" . $_POST['iban'] . "')";
                  $result = $link->query($sql);

                  if ($result->num_rows > 0) {
                      // output data of each row
                      echo "<h1>Bijschriften</h1><table class='table'>
                                <tr>
                                    <th>Amount</th>
                                    <th>Time</th>
                                    <th>Location</th>
                                    <th>Recipient</th>
                                </tr>";
                      while($row = $result->fetch_assoc()) {
                          echo "<tr>
                                    <td>+ ".$row["amount"]."</td>
                                    <td>".$row["timestamp"]."</td>
                                    <td>".$row["location"]."</td>
                                    <td>".$row["iban_recipient"]."</td>
                                </tr>";
                      }
                      echo "</table>";
                  } else {
                      echo "0 results";
                  }
                  $link->close();
                }else{
                  echo "IBAN not correct";
                }
              }else{
                  echo("<form action='transactions.php' method='post'>
                      <div class='form-group'>
                      <label>Account</label><select class='form-control' name='iban'>");
                      $sql = "SELECT iban FROM accounts WHERE id IN (SELECT id FROM users WHERE id = '" . $_SESSION['id'] . "') ";
                      $result = mysqli_query($link, $sql);

                      while($row = mysqli_fetch_array($result)){
                            echo("<option value='" . $row['iban'] . "'>" . $row['iban'] . "</option>");
                        }
                        echo("</select>");

                        mysqli_close($link);

                        echo("
                        </div>
                        <div class='form-group'>
                            <input type='submit' class='btn btn-primary' value='Submit'>
                        </div>
                    </form> ");
              }
            ?>
          </div>
          <div class="col-sm-2"></div>
        </div>
      </div>
      </main>
      <footer class="container">
        <hr>
        <p>&copy; Monarch Douglas Bank 2018-2019</p>
      </footer>
    </body>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
</html>
