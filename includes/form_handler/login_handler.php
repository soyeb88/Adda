<?php

if(isset($_POST['login_button'])){
	
	$email = filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL); //sanitize email

	$_SESSION['log_email'] = $email; //store email into session variable
	$password  = md5($_POST['log_password']);

	$LoginQuery_Statement = "SELECT COUNT(*) as total FROM users WHERE email = :email AND password = :password";

	$Login_Query = $con->prepare($LoginQuery_Statement);
	$Login_Query->bindParam(":email", $email);
	$Login_Query->bindParam(":password", $password);
	$Login_Query->execute();

	$LogIn_match = $Login_Query->fetch(PDO::FETCH_ASSOC);

	if($LogIn_match["total"] == 1){
		$LoginQuery_username_Statement = "SELECT username FROM users WHERE email = :email AND password = :password";
		$LoginQuery_username = $con->prepare($LoginQuery_username_Statement);
		$LoginQuery_username->bindParam(":email", $email);
		$LoginQuery_username->bindParam(":password", $password);
		$LoginQuery_username->execute();

		$LogIn_match_username = $LoginQuery_username->fetch(PDO::FETCH_ASSOC);
		$username = $LogIn_match_username["username"];

		//check user active and re-open account
		$LoginQuery_userActive_Statement = "SELECT COUNT(*) as totalActive FROM users WHERE email = :email AND user_active = 'no'";
		
		$LoginQuery_userActive = $con->prepare($LoginQuery_userActive_Statement);
		$LoginQuery_userActive->bindParam(":email", $email);
		$LoginQuery_userActive->execute();

		$LogIn_userActive_match = $LoginQuery_userActive->fetch(PDO::FETCH_ASSOC);

		if($LogIn_userActive_match["totalActive"] == 1){
			//reopen account
			$re_Open_Account_stm = "UPDATE users SET user_active = 'yes' WHERE email = :email";
			$re_Open_Account = $con->prepare($re_Open_Account_stm);
			$re_Open_Account->bindParam(":email", $email);
			$re_Open_Account->execute();
		}


		$_SESSION["username"] = $username;
		header("Location: index.php");
		exit();


	}
	else{
		array_push($error_array, "Email and Password does't mactch! ");
	}
}
?>