<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
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
              // Include config file
              require_once "config.php";

              //Omschrijven naar een query zonder SQL injectie mogelijkheden
              $sql = "SELECT iban, balance FROM accounts WHERE id IN (SELECT id FROM users WHERE username = '" . $_SESSION['username'] . "') ";
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
          <div class="col-sm-2"></div>
          <div class="col-sm-8" align='center'>
                <a href="accounts.php" class="btn btn-info" role="button">Manage accounts</a>
                <a href="transfer.php" class="btn btn-info" role="button">Transfer</a>
                <a href="reset-password.php" class="btn btn-secondary" role="button">Change your Password</a>
                <a href="logout.php" class="btn btn-danger" role="button">Sign Out of Your Account</a>
          </div>
          <div class="col-sm-2"></div>
        </div>
      </main>
    </body>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</html>
