<?php 
	require 'config/config.php'; 
?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="Assets/css/style.css">
</head>
<body>

	<style>

		*{
			font-family: Ariel, Helvetica, Sans-serif;
		}
		body{
			background-color: #fff;
		}

		form{
			position: absolute;
    		top: 0px;
		}
	</style>

	<?php
		include("includes/classes/User.php");
		include("includes/classes/Post.php");
		include("includes/classes/Notification.php");

		if(isset($_SESSION['username'])){
			$userLoggedIn = $_SESSION['username'];
			$username_statement = "SELECT * FROM users WHERE username = :username";
			$username_query = $con->prepare($username_statement);
			$username_query->bindParam(":username", $userLoggedIn);
			$username_query->execute();

			$username = $username_query->fetch(PDO::FETCH_ASSOC); 
		}
		else{
			header("Location: register.php");
		}
	?>

	<?php 
		//get id of post
		if(isset($_GET['post_id'])){
			$post_id = $_GET['post_id'];
	}

	$get_likes = $con->prepare("SELECT likes, added_by from posts where id = :post_id");
	$get_likes->bindParam(":post_id", $post_id);
	$get_likes->execute();
	$row = $get_likes->fetch(PDO::FETCH_ASSOC);
	$total_likes = $row['likes'];
	$user_liked = $row['added_by']; 

	$user_detailed_query = $con->prepare("SELECT * from users where username = :user_liked");
	$user_detailed_query->bindParam(":user_liked", $user_liked);
	$user_detailed_query->execute();
	$row = $user_detailed_query->fetch(PDO::FETCH_ASSOC);

	$user_total_liked = $row['num_likes'];
	//Like Button	
	if(isset($_POST['like_button'])){
		
		$total_likes++;
		$query = $con->prepare("UPDATE posts SET likes = :likes where id = :post_id");
		$query->bindParam(":likes", $total_likes);
		$query->bindParam(":post_id", $post_id);
		$query->execute();

		$user_total_liked++;
		$user_likes = $con->prepare("UPDATE users SET num_likes = :num_likes where username = :user_liked");
		$user_likes->bindParam(":num_likes", $user_total_liked);
		$user_likes->bindParam(":user_liked", $user_liked);
		$user_likes->execute();


		$insert_user = $con->prepare("INSERT INTO likes(username, post_id) VALUES(:username, :post_id)");
		$insert_user->bindParam(":username", $userLoggedIn);
		$insert_user->bindParam(":post_id", $post_id);
		$insert_user->execute();

		//insert Notification
		if($user_to != $userLoggedIn){
			$notification = new Notification($con, $userLoggedIn);
			$notification->insertNotification($post_id, $user_liked, 'like');
		}

	}

	//Unlike Button
	if(isset($_POST['unlike_button'])){
		$total_likes--;
		$query = $con->prepare("UPDATE posts SET likes = :likes where id = :post_id");
		$query->bindParam(":likes", $total_likes);
		$query->bindParam(":post_id", $post_id);
		$query->execute();

		$user_total_liked--;
		$user_likes = $con->prepare("UPDATE users SET num_likes = :num_likes where username = :user_liked");
		$user_likes->bindParam(":num_likes", $total_likes);
		$user_likes->bindParam(":user_liked", $user_liked);
		$user_likes->execute();

		$insert_user = $con->prepare("DELETE FROM likes WHERE username = :username AND post_id = :post_id");
		$insert_user->bindParam(":username", $userLoggedIn);
		$insert_user->bindParam(":post_id", $post_id);
		$insert_user->execute();

	}


	$check_query = $con->prepare("SELECT * from likes where username = :username and post_id = :post_id");
	$check_query->bindParam(":username", $userLoggedIn);
	$check_query->bindParam(":post_id", $post_id);
	$check_query->execute();
	$check_query->fetch(PDO::FETCH_ASSOC);
	$num_rows = $check_query->rowCount();


	if($num_rows>0){
		echo '<form action="likes.php?post_id=' . $post_id . '" method= "POST">
					<input type="submit" class="comment_like" name="unlike_button" value="Unlike">
					<div class="like_value">
					' . $total_likes . ' Likes
					</div>
			  </form>';
	}
	else{
		echo '<form action="likes.php?post_id=' . $post_id . '" method= "POST">
				<input type="submit" class="comment_like" name="like_button" value="Like">
				<div class="like_value">
				' . $total_likes . ' Likes
				</div>
			</form>';
	}

	?> 

</body>
</html>