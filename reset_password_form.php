<?php 
  require 'config/config.php';
  require 'includes/form_handler/register_form_handler.php';
  require 'includes/form_handler/reset_password_handler.php';
?>

<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <link rel="stylesheet" type="text/css" href="Assets/css/register_style.css">
</head>
<body>

  <div class="login_box">


    <div class="login_header">
      <h1> Reset Password </h1>
    </div>

    <form method="post">
      <input type="password" name="reset_password" placeholder="Type New Password" required>
      <br>
      <input type="password" name="reset_password2" placeholder="Type again New Password" required>
      <br>
      <input type="submit" name="password_reset_button" value="Submit">

       <br>
      <?php if(in_array("Your password does not match <br>", $error_array)) echo "<p style='color:green'>Your password doesn't match</p>";
      ?>
    </form>
    

  </div>

</body>
</html>

