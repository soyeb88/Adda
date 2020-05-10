<?php
require 'config/config.php';
require 'includes/form_handler/register_form_handler.php';
require 'includes/form_handler/login_handler.php';
require 'includes/form_handler/reset_password_handler.php';


?>


<!DOCTYPE html>
<html>
<head>
	<title>Welcome to FreeLand</title>
	<link rel="stylesheet" type="text/css" href="Assets/css/register_style.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="Assets/js/register.js">
</script>
</head>
<body>

	<?php
	if(isset($_POST['register_button'])){
		echo '
			<script>

			$(document).ready(function(){
				$(".first").hide();
				$(".second").show();
			});

			</script>
		';
	}

	if(isset($_POST['forget_password'])){
		echo '
			<script>

			$(document).ready(function(){
				$(".login_box").hide();
				$(".reset_password_box").show();
			});

			</script>
		';
	}

	?>

	<div class="wrapper">
		<div class="login_box">

			<div class="login_header">
				<h1> FreeLand </h1>
				Login or Sign up below!
			</div>
			
			<div class="first">
				
				<form action="register.php" method="POST">
					<input type="email" name="log_email" placeholder="Email Address" value ="<?php 
							if(isset($_SESSION['log_email'])){
								echo $_SESSION['log_email'];
							}
						?>" required>
					<br>
					<input type="password" name="log_password" placeholder="Password">
					<br>
					<input type="submit" name="login_button" value="Login">

					<br>

					<?php if(in_array("Email and Password does't mactch! ", $error_array)) 
							echo "Email and Password does't mactch! "; 
					?>

					<a href="#" id="reset_form">Forgot Passsword?</a>
					<br>
					<a href="#" id="signup">Need an account? Register here!</a>

				</form>

			</div>

			<div class="second">				
				<form accept="register.php" method="POST">
					<input type="text" name="reg_fname" placeholder="First Name" value = "<?php 
						if(isset($_SESSION['reg_fname'])){
							echo $_SESSION['reg_fname'];
						}
					?>" required>
					<br>
					<?php if(in_array("Your first name must be betwenn 2 and 25 characters <br>", $error_array)) echo "Your first name must be betwenn 2 and 25 characters <br>"; ?>

					<input type="text" name="reg_lname" placeholder="Last Name"value = "<?php 
						if(isset($_SESSION['reg_lname'])){
							echo $_SESSION['reg_lname'];
						}
					?>" required>
					<br>
					<?php if(in_array("Your last name must be between 2 and 25 characters <br>", $error_array)) echo "Your last name must be between 2 and 25 characters <br>"; ?>
					<input type="email" name="reg_email" placeholder="Email" value = "<?php 
						if(isset($_SESSION['reg_email'])){
							echo $_SESSION['reg_email'];
						}
					?>" required>
					<br>
					<?php if(in_array("Email has already used! <br>", $error_array)) echo "Email has already used! <br>";
						  else if(in_array("Invalid format <br>", $error_array)) echo "Invalid format <br>";
						  else if(in_array("email doesn't match! <br>", $error_array)) echo "email doesn't match! <br>";
					?>
					<input type="email" name="reg_email2" placeholder="Confirm Email" value = "<?php 
						if(isset($_SESSION['reg_email2'])){
							echo $_SESSION['reg_email2'];
						}
					?>" required>
					<br>
					<input type="password" name="reg_password" placeholder="Password" required>
					<br>
					<input type="password" name="reg_password2" placeholder="Confirm Password" required>
					<br>
					<?php if(in_array("Your password do not match <br>", $error_array)) echo "Your password dosn't match <br>";
						  else if(in_array("Your password can only contain characters and numbers <br>", $error_array)) echo "Your password can only contain characters and numbers <br>";
						  else if(in_array("Your password name must be between 5 and 30 characters", $error_array)) echo "Your password name must be between 5 and 30 characters<br>";
					?>
					<input type="submit" name="register_button" value="Register">
					<br>

					<?php if(in_array("<span style='color:#14C800;'>You successfully register your account! Please go to log in page</span><br>", $error_array)) 
						echo "<span style='color:#14C800;'>You successfully register your account! Please go to log in page</span><br>"; 
					?>
					<a href="#" id="signin">Already have an account? Sign in here!</a>
				</form>
			</div>			
		</div>

		<div class ="reset_password_box">
	        
	        <form method="post">

	            <div class="reset_password_header">
	                <h2>Reset Your Password</h2>
	            </div>
	                <input type="text" name="email" placeholder="Type your email address" required>
	                <br>
	                <input type="submit" name="forget_password">
	            <br>
	            <?php if(in_array("Please check your email", $error_array)) echo "<p class='checkEmail'>Please check your email</p>";
	                   else if(in_array("Email doesn't exists in our database. Please register for a new account.", $error_array)) echo "<p class='checkEmail'>Email doesn't exists in our database. Please register for a new account.</p>";
	            ?>
	            <a href="#" id="signup2">Not match email address? Register here! or contact with administration</a>
	        </form>
    	</div>
	</div>

</body>
</html>