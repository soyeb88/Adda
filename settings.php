<?php
include("includes/header.php");
include("includes/form_handler/settings_handler.php"); 
?>

<div class="main_column column">
	<h4>Account Setting</h4>
	<?php 
	echo "<img src='" . $username['profile_pic'] . "' id='small_profile_pic'>"; 
	?>
	<br>
	<a href="upload.php">Upload your profile picture!</a> 
	<br>
	<br>
	<br>
	<hr>

	<h4>Update Your Details</h4>
	<?php
	$user_data_query = $con->prepare("SELECT first_name, last_name, email FROM users WHERE username= :userLoggedIn");
	$user_data_query->bindParam(":userLoggedIn", $userLoggedIn);
	$user_data_query->execute();

	$row = $user_data_query->fetch(PDO::FETCH_ASSOC);

	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
	$email = $row['email'];

	?>

	<form action="settings.php" method="POST">
		First Name: <input type="text" name="first_name" value = "<?php echo $first_name; ?>" id='setting_input_text'> <br>
		Last Name: <input type="text" name="last_name" value = "<?php echo $last_name; ?>" id='setting_input_text'> <br>
		Email: <input type="text" name="email" value = "<?php echo $email; ?>" id='setting_input_text'> <br>
		<?php echo $message; ?> <br>
		<input type="submit" name="update_details" id="save_details" value="Update Details" class="success">
	</form> 
	<hr>

	<h4>Update Password</h4>
	<form action="settings.php" method="POST">
		Old Password: <input type="password" name="old_password" id='setting_input_text'> <br>
		New Password: <input type="password" name="new_password_1" id='setting_input_text'> <br>
		New Password Again: <input type="password" name="new_password_2" id='setting_input_text'> <br>
		<?php echo $password_message; ?> <br>
		<input type="submit" name="update_passwords" id="save_passwords" value="Update Password" class="success">
	</form>
	<hr>

	<h4>Close Account</h4>
		<form action="settings.php" method="POST">
			<input type="submit" name="close_account" id="close_account" value="Close Account"  class="danger">
		</form>
</div>