<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
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
                    echo "<tr><td>" . $row['iban'] . "</td><td>" . $row['balance'] . "</td></tr>";
                }

                echo "</tbody></table>"; //Close the table in HTML
            } else {
                echo "You do not have any bank accounts";
            }

            mysqli_close($link);
          ?>
        </div>
        <div class="col-sm-3"></div>
      </div>
      <br />
      <div class="row">
        <div class="buttons">
          <p
              <a href="" class="btn btn-info">Add bankaccount</a>>
              <a href="" class="btn btn-warning">Disable bankaccount</a>
              <a href="" class="btn btn-danger">Delete bankaccount</a>
          </p>
        </div>
      </div>
    </body>
</html>
