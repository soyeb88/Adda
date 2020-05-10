<?php
include("includes/header.php");
?>

<div class="main_column column" id="main_column">
	
	<h4>Friend Requests</h4>

	<?php

	$query = $con->prepare("SELECT * from friend_requests where user_to = :user_to");
	$query->bindParam(":user_to", $userLoggedIn);
	$query->execute();

	if($query->rowCount() == 0){
		echo "You have no request at this time!";
	}
	else{
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			$user_from = $row['user_from'];

			$user_from_obj = new User($con, $user_from);


			echo $user_from_obj->getFirstAndLastName() . " sent a friend request!"; 

			$user_from_friend_array = $user_from_obj->getFriendArray();

			if(isset($_POST['accept_request' . $user_from])){

				$user_from_comma = $user_from . ",";
				
				$add_friend_query = $con->prepare("UPDATE users SET friends_array = CONCAT(friends_array, :user_from) where username = :userLoggedIn");
				$add_friend_query->bindParam(":userLoggedIn", $userLoggedIn);
				$add_friend_query->bindParam(":user_from", $user_from_comma);
				$add_friend_query->execute();

				$user_to_comma = $userLoggedIn . ",";

				$add_friend_query = $con->prepare("UPDATE users SET friends_array = CONCAT(friends_array, :userLoggedIn) where username = :user_from");
				$add_friend_query->bindParam(":userLoggedIn", $user_to_comma);
				$add_friend_query->bindParam(":user_from", $user_from);
				$add_friend_query->execute();

				$delete_query = $con->prepare("DELETE FROM friend_requests where user_to = :userLoggedIn and user_from = :user_from");
				$delete_query->bindParam(":userLoggedIn", $userLoggedIn);
				$delete_query->bindParam(":user_from", $user_from);
				$delete_query->execute();
				

				echo "You are now friends!";
				header("Location: requests.php");
			}
			if(isset($_POST['ignore_request' . $user_from])){

					$delete_query = $con->prepare("DELETE FROM friend_requests where user_to = :userLoggedIn and user_from = :user_from");
					$delete_query->bindParam(":userLoggedIn", $userLoggedIn);
					$delete_query->bindParam(":user_from", $user_from);
					$delete_query->execute();
				

					echo "Request ignore!";
					header("Location: requests.php");
			}

			?>

			<form action="requests.php" method="POST">
				<input type="submit" name="accept_request<?php echo $user_from; ?>" id="accept_button" value="Accept">
				<input type="submit" name="ignore_request<?php echo $user_from; ?>" id="ignore_button" value="Ignore">		
			</form>

			<?php
		} 

	}
	?>

</div>


</body>