<?php

/*******************
   Update Details
********************/

if(isset($_POST['update_details'])){
	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$email = $_POST['email'];

	$email_check = $con->prepare("SELECT * FROM users WHERE email = :email");
	$email_check->bindParam(":email", $email);
	$email_check->execute();

	$row = $email_check->fetch(PDO::FETCH_ASSOC);
	$matched_user = $row['username'];

	if($matched_user == "" OR $matched_user == $userLoggedIn){
		$message = "Update Details! <br><br>";

		$query = $con->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email WHERE username = :userLoggedIn");
		$query->bindParam(":first_name", $first_name);
		$query->bindParam(":last_name", $last_name);
		$query->bindParam(":email", $email);
		$query->bindParam(":userLoggedIn", $userLoggedIn);
		$query->execute();

	}
	else{
		$message = "This email is already in use!";
	}
}
else
	$message = "";

/*******************
   Update Password
********************/
if(isset($_POST['update_passwords'])){
	$old_password = strip_tags($_POST['old_password']);
	$new_password_1 = strip_tags($_POST['new_password_1']);
	$new_password_2 = strip_tags($_POST['new_password_2']);

	$password_query = $con->prepare("SELECT password FROM users WHERE username = :userLoggedIn");
	$password_query->bindParam(":userLoggedIn", $userLoggedIn);
	$password_query->execute();

	$row = $password_query->fetch(PDO::FETCH_ASSOC);
	$db_password = $row['password'];

	if(md5($old_password) == $db_password){
		if($new_password_1 == $new_password_2){
			if(strlen($new_password_1) <= 4){
				$password_message = "Your password must be greater than 4!<br><br>";
			}
			else{
				$new_password_md = 	md5($new_password_1);			
				$query = $con->prepare("UPDATE users SET password = :new_password_md WHERE username = :userLoggedIn");
				$query->bindParam(":new_password_md", $new_password_md);
				$query->bindParam(":userLoggedIn", $userLoggedIn);
				$query->execute();
				$password_message = "Update Password! <br><br>";
			}		
		}
		else{
			$password_message = "The new two passwords are not matched!";
		}
	}
	else{
		$password_message = "The old password is incorrect!";
	}
}
else
	$password_message = "";



/*******************
   Closed Account
********************/

if(isset($_POST['close_account'])){
	header("Location: closed_account.php");
}

?>