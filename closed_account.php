<?php
include("includes/header.php");

if(isset($_POST['cancel'])){
	header("Location: settings.php");
}

if(isset($_POST['closed_account'])){
	$query = $con->prepare("UPDATE users SET user_active = 'no' WHERE username=:userLoggedIn");
	$query->bindParam(":userLoggedIn", $userLoggedIn);
	$query->execute();
	session_destroy();
	header("Location: register.php");

}

?>

<div class="main_column column">
	<h4>Closed Account</h4>
	<p>
		Are you sure you want to close your account?
	</p>

	<form action="closed_account.php" method="POST">		
		<input type="submit" name="closed_account" id="closed_account" value="Yes Closed Account" class="danger">
		<input type="submit" name="cancel" id="update_details" value="No" class="warning">

	</form>
	<p> 
		<br>
		<br>
		Attention: If you close account, your account will be hide from other user.<br>
		But you can re-active your account any time by simply log in your account.
	</p>

</div>