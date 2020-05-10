<?php 
	require 'config/config.php'; 

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

<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="Assets/css/style.css">
</head>
<body>

	<style type="text/css">
		*{
			background-color: #e2ecec;
			font-size: 12px;
			font-family: Arial, Helvetica, Sans-serif; 
		}
	</style>

	<script>
		function toggle(){
			var element = document.getElementById("comment_section");

			if(element.style.display == "block"){
				element.style.display == "none";
			}
			else{
				element.style.display = "block";
			}
		}
	</script>

	<?php 
	//get id of post
	if(isset($_GET['post_id'])){
		$post_id = $_GET['post_id'];
	} 

	$user_query = $con->prepare("SELECT added_by, user_to from posts where id = :post_id");
	$user_query->bindParam(":post_id", $post_id);
	$user_query->execute();

	$row = $user_query->fetch(PDO::FETCH_ASSOC);

	$posted_to = $row['added_by'];
	$user_to = $row['user_to'];

	if(isset($_POST['postComment' . $post_id])){
		$post_body = $_POST['post_body'];
		$post_body = strip_tags($post_body);
		$date_time_now = date("Y-m-d H:i:s");

		$insert_post = $con->prepare("INSERT INTO comments(post_body, posted_by, posted_to, date_added, removed, post_id) 
							Values(:post_body, :post_by, :posted_to, :date_added, 'no', :post_id)");
		
		$insert_post->bindParam(":post_body",$post_body);
		$insert_post->bindParam(":post_by", $userLoggedIn);
		$insert_post->bindParam(":posted_to", $posted_to);
		$insert_post->bindParam(":date_added", $date_time_now);
		$insert_post->bindParam(":post_id", $post_id);
		$insert_post->execute();

		//insert Notification
		if($user_to != $userLoggedIn){
			$notification = new Notification($con, $userLoggedIn);
			$notification->insertNotification($post_id, $posted_to, "comment");
		}
		
		if($user_to != "none" && $user_to != $userLoggedIn){
			$notification = new Notification($con, $userLoggedIn);
			$notification->insertNotification($post_id, $user_to, "profile_comment");
		}

		//notification posted by non-owner
		$get_comments = $con->prepare("SELECT * FROM comments WHERE post_id = :post_id");
		$get_comments->bindParam(":post_id", $post_id);
		$get_comments->execute();

		$notification_user = array();

		while($row = $get_comments->fetch(PDO::FETCH_ASSOC)){
			if($row['posted_by'] != $posted_to && $row['posted_by'] != $user_to && $row['posted_by'] != $userLoggedIn && !in_array($row['posted_by'], $notification_user)){
				$notification = new Notification($con, $userLoggedIn);
				$notification->insertNotification($post_id, $row['posted_by'], "comment_non_owner");

				array_push($notification_user, $row['posted_by']);
			}
		}

		echo "<p>Comment Posted! </p>";

	}


	?>

	<form action="comment_frame.php?post_id=<?php echo $post_id; ?>" id="comment_form" name="postComment<?php echo $post_id; ?>" method="POST">

		<textarea name="post_body"></textarea>
		<input type="submit" name="postComment<?php echo $post_id; ?>" value="Post">

	</form>

	<!-- Load Comment -->

	<?php
		$get_comments = $con->prepare("SELECT * from comments where post_id = :post_id order by id ASC" );
		$get_comments -> bindParam(":post_id", $post_id); 
		$get_comments->execute();

		$count = $get_comments->rowCount();

		if($count != 0){

			while($comments = $get_comments->fetch(PDO::FETCH_ASSOC)){

				$comment_body = $comments['post_body'];
				$posted_by = $comments['posted_by'];
				$posted_to = $comments['posted_to'];
				$date_added = $comments['date_added'];
				$removed = $comments['removed'];

				//Timeframe
				$date_time_now = date("Y-m-d H:i:s");
				$start_date = new DateTime($date_added); 	//Time of Post
				$end_date = new DateTime($date_time_now);   //Current time
				$interval = $start_date->diff($end_date);	//difference between dates

				if($interval->y >= 1){
					if($interval->y == 1)
						$time_message = $interval->y . " year ago"; //1 year ago
					else
						$time_message = $interval->y . " years ago"; //1+ year ago
				}
				else if($interval->m >= 1){
					if($interval->d == 0)
						$days = " ago"; 
					else if($interval->d == 1)
						$days = $interval->d . " day ago"; //1 day ago
					else
						$days = $interval->d . " days ago"; //1+ days ago

					if($interval->m == 1)
						$time_message = $interval->m . " month" . $days; //1 month ago # days or 1 day ago 
					else
						$time_message = $interval->m . " months" . $days; //# months ago # days or 1 day ago 
				}
				else if($interval->d >= 1){
					if($interval->d == 1)
						$time_message = " Yesterday"; 
					else
						$time_message = $interval->d . " days ago"; //1+ days ago
				}
				else if($interval->h >= 1){
					if($interval->h == 1)
						$time_message = $interval->h . " hour ago"; //1 hour ago
					else
						$time_message = $interval->h . " hours ago"; //1+ hour ago
				}
				else if($interval->i >= 1){
					if($interval->i == 1)
						$time_message = $interval->i . " minute ago"; //1 minute ago
					else 
						$time_message = $interval->i . " minutes ago"; //1+ minutes ago
				}
				else {
					if($interval->s < 30)
						$time_message = "just now";
					else{
						$time_message = $interval->s . " seconds ago";
					}
				}

				$user_obj = new User($con, $posted_by);

				?>
				<div class="comment_section">

					<a href="<?php echo $posted_by ?>" target= "_parent">
						<img src="<?php echo $user_obj->getProfilePic() ?>" title="<?php echo $posted_by; ?>" style="float:left;" height="30">
					</a>
					<a href="<?php echo $posted_by ?>" target= "_parent">
						<b><?php echo $user_obj->getFirstAndLastName(); ?></b>
					</a>
					&nbsp; &nbsp; &nbsp; &nbsp; <?php echo $time_message . "<br>" . $comment_body; ?>
					<br>
		
				</div>
	<?php
			}
		}

		else{
			echo "<center><br><br> No Comments to Show! </center>";
		}
	?>


</body>
</html>