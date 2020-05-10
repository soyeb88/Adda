<?php

//user: soyebb@gmail.com pass: Dhaka866

$fname=""; //First Name
$lname=""; //Last Name
$em=""; //Email
$em2=""; //Confirm Email
$password=""; //Password
$password2=""; //Confrim Password
$date=""; //Sign up date 
$error_array= array(); //Holds error messages

if(isset($_POST['register_button'])){

	//Registration Form Values

	//First Name
	$fname = strip_tags($_POST['reg_fname']); //remove html taqs
	$fname = str_replace(' ', '', $fname); //remove spaces
	$fname = ucfirst(strtolower($fname)); //uppercase first letter
	$_SESSION['reg_fname'] = $fname; //store first name into session variavle

	//Last Name
	$lname = strip_tags($_POST['reg_lname']); //remove html taqs
	$lname = str_replace(' ', '', $lname); //remove spaces
	$lname = ucfirst(strtolower($lname)); //uppercase first letter
	$_SESSION['reg_lname'] = $lname; //store last name into session variavle

	//Email
	$em = strip_tags($_POST['reg_email']); //remove html taqs
	$em = str_replace(' ', '', $em); //remove spaces
	$em = ucfirst(strtolower($em)); //uppercase first letter
	$_SESSION['reg_email'] = $em; //store eamil1 into session variavle

	//Email-2
	$em2 = strip_tags($_POST['reg_email2']); //remove html taqs
	$em2 = str_replace(' ', '', $em2); //remove spaces
	$em2 = ucfirst(strtolower($em2)); //uppercase first letter
	$_SESSION['reg_email2'] = $em2; //store eamil2 into session variavle

	//Password
	$password = strip_tags($_POST['reg_password']); //remove html taqs
	$password2 = strip_tags($_POST['reg_password2']); //remove html taqs

	//date
	$date = date("Y-m-d"); //current date

	if($em == $em2){
		//Check if email is valid format
		if(filter_var($em, FILTER_VALIDATE_EMAIL)){
			
			$em = filter_var($em, FILTER_VALIDATE_EMAIL);


			$countEmail_SQL = "SELECT COUNT(*) as total FROM users WHERE email = :em ";

			$countEmail_Query = $con->prepare($countEmail_SQL);
			$countEmail_Query->bindParam(":em", $em);
			$countEmail_Query->execute();

			$row = $countEmail_Query->fetch(PDO::FETCH_ASSOC);

			if($row["total"] > 0){
				array_push($error_array, "Email has already used! <br>");
			}

		}
		else{
			array_push($error_array, "Invalid format <br>");
		}
	}
	else{
		array_push($error_array, "email doesn't match! <br>");
	}

	if(strlen($fname) > 25 || strlen($fname) < 2){

		array_push($error_array, "Your first name must be betwenn 2 and 25 characters <br>");
	}

	if(strlen($lname) > 25 || strlen($lname) < 2){

		array_push($error_array, "Your last name must be between 2 and 25 characters <br>");
	}

	if($password != $password2){

		array_push($error_array, "Your password do not match <br>");
	}
	else{
		if(preg_match('/[^A-Za-z0-9]/', $password)){
			array_push($error_array, "Your password can only contain characters and numbers <br>"); 
		}
	}

	if(strlen($password) > 30 || strlen($password) < 5){

		array_push($error_array, "Your password name must be between 5 and 30 characters");
	}

	if(empty($error_array)){
		$password = md5($password); //Encrypt password before sending to the database

		$username = strtolower($fname . "_" . $lname);

		$username_SQL = "SELECT COUNT(*) as total from users WHERE username = :username";
		$check_username_query = $con->prepare($username_SQL);
		$check_username_query->bindParam(":username", $username);
		$check_username_query->execute();

		$count_username = $check_username_query->fetch(PDO::FETCH_ASSOC);

		$i = 0;

		//if username exists add number to username 
		while($count_username["total"] != 0){
			$i++; //add 1 to i
			$username = strtolower($fname . "_" . $lname) . "_" . $i;
			
			$username_SQL = "SELECT username from users WHERE username = :username";
			$check_username_query = $con->prepare($username_SQL);
			$check_username_query->bindParam(":username", $username);
			$check_username_query->execute();

			$count_username = $check_username_query->fetch(PDO::FETCH_ASSOC);
		}

		//Profile Picture assignment 
		$rand = rand(1, 2);

		if($rand == 1) 
			$profile_pic = "Assets/Images/profile_pics/defaults/head_alizarin.png";
		else if($rand == 2)
			$profile_pic = "Assets/Images/profile_pics/defaults/head_emerald.png";

		$insertData_SQL = "INSERT INTO users(first_name, last_name, username, email, password, signup_date, profile_pic, user_active, friends_array) VALUES(:fname, :lname, :username, :em,:password, :dateT, :profile_pic, 'yes', ',')";
		
		$insert_query = $con->prepare($insertData_SQL);
		$insert_query->bindParam(":fname", $fname);
		$insert_query->bindParam(":lname", $lname);
		$insert_query->bindParam(":username", $username);
		$insert_query->bindParam(":em", $em);
		$insert_query->bindParam(":password", $password);
		$insert_query->bindParam(":dateT", $date);
		$insert_query->bindParam(":profile_pic", $profile_pic);
		$insert_query->execute();

		array_push($error_array, "<span style='color:#14C800;'>You successfully register your account! Please go to log in page</span><br>");

		//clear session variable
		$_SESSION['reg_fname'] = "";
		$_SESSION['reg_lname'] = "";
		$_SESSION['reg_email'] = "";
		$_SESSION['reg_email2'] = "";
	}

}

?>