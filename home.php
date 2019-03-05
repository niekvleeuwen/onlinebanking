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
    <link rel="shortcut icon" href="img/favicon.ico"/>
</head>
<body>
    <div class="page-header">
        <h1>Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Welcome to our Bank.</h1>
    </div>
    <div class="row">
        <div class="col-sm-3"></div>
        <div class="col-sm-6">
          <?php
            // Include config file
            require_once "config.php";

            $sql = "SELECT iban, balance FROM users";
            $result = mysqli_query($link, $sql);


            echo "<table class='table'><thead>
                    <tr>
                      <th scope='col'>IBAN</th>
                      <th scope='col'>Balance</th>
                    </tr>
                  </thead><tbody>"; // start a table tag in the HTML

            while($row = mysqli_fetch_array($result)){   //Creates a loop to loop through results
            echo "<tr><td>" . $row['iban'] . "</td><td>" . $row['balance'] . "</td></tr>";  //$row['index'] the index here is a field name
            }

            echo "</tbody></table>"; //Close the table in HTML

            mysqli_close($link); //Make sure to close out the database connection
          ?>
        </div>
        <div class="col-sm-3"></div>
      </div>
      <div class="row">
        <div class="buttons">
          <p>
              <a href="reset-password.php" class="btn btn-warning">Reset Your Password</a>
              <a href="logout.php" class="btn btn-danger">Sign Out of Your Account</a>
          </p>
        </div>
      </div>
    </body>
</html>