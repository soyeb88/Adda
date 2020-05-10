<?php
include("includes/header.php");

if(isset($_GET['q'])){
	$query = $_GET['q'];
}
else{
	$query = "";
}

if(isset($_GET['type'])){
	$type = $_GET['type'];
}
else{
	$type = "name";
}
?>

<div class="main_column column" id="main_column">

	<?php
	if($query == ""){
		echo "You must type something in the search box.";
	}
	else{		
		//if query contains an underscore, assume user is searching for usernames
		$query_like = $query . "%";
		if($type == "username"){
			$userReturnedQuery = $con->prepare("SELECT * FROM users WHERE username LIKE :query AND user_active = 'yes' LIMIT 8");
			$userReturnedQuery->bindParam(":query" ,$query_like);
			$userReturnedQuery->execute();
		}
		//if there are two words, assume that there are first name and last name respectively
		else{
			$names = explode(" ", $query);
			if(count($names) == 3){
				$first_name = $names[0] . "%";
				$last_name = $names[2] . "%";
				$userReturnedQuery = $con->prepare("SELECT * FROM users WHERE (first_name LIKE :first_name AND last_name LIKE :last_name) AND user_active = 'yes'");
				$userReturnedQuery->bindParam(":first_name" ,$first_name);
				$userReturnedQuery->bindParam(":last_name" ,$last_name);
				$userReturnedQuery->execute();
				}
			//If any user search on the base of first name or last name
			else if(count($names) == 2){
				$first_name = $names[0] . "%";
				$last_name = $names[1] . "%";
				$userReturnedQuery = $con->prepare("SELECT * FROM users WHERE (first_name LIKE :first_name AND last_name LIKE :last_name) AND user_active = 'yes'");
				$userReturnedQuery->bindParam(":first_name" ,$first_name);
				$userReturnedQuery->bindParam(":last_name" ,$last_name);
				$userReturnedQuery->execute();
			}
			else{
				$name = $names[0] . "%";
				$userReturnedQuery = $con->prepare("SELECT * FROM users WHERE (first_name LIKE :name OR last_name LIKE :name) AND user_active = 'yes'");
				$userReturnedQuery->bindParam(":name" ,$name);
				$userReturnedQuery->execute();
			}
		}

		//if results were found 
		if($userReturnedQuery->rowCount() == 0){
			echo "We can't find anyone with " . $type . " like: " . $query;
		}
		else{
			echo $userReturnedQuery->rowCount() . " results found: <br> <br>";
		}

		echo "<p id='grey'>try searching for: </p>";	
		echo "<a href='search.php?q=" . $query . "&type=name'>Name</a> " . "&nbsp; &nbsp;" . " <a href='search.php?q=" . $query . "&type=username'>Usernames</a> <br><br><hr id='search_hr'>";

		while($row = $userReturnedQuery->fetch(PDO::FETCH_ASSOC)){
			$user_obj = new User($con, $username['username']);

			$button = "";
			$mutual_friends = "";

			if($username['username'] != $row['username']){

				//generate button depending on friendship status
				if($user_obj->isFriend($row['username'])){
					$button = "<input type='submit' name='". $row['username'] ."' class='danger' value='Remove Friend'>";
				}
				else if($user_obj->didReceiveRequest($row['username'])){
					$button = "<input type='submit' name='". $row['username'] ."' class='warning' value='Response to request'>";
				}
				else if($user_obj->didSentRequest($row['username'])){
					$button = "<input type='submit' name='". $row['username'] ."' class='default' value='Request Sent'>";
				}
				else{
					$button = "<input type='submit' name='". $row['username'] ."' class='success' value='Add Friend'>";
				}

				$mutual_friends = $user_obj->getMutualFriends($row['username']) . " friends in common";

				//Button form
				if(isset($_POST[$row['username']])){
					if($user_obj->isFriend($row['username'])){
						$user_obj->removeFriend($row['username']);
						header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
					}
					else if($user_obj->didReceiveRequest($row['username'])){
						header("Location: requests.php");
					}
					else if($user_obj->didSentRequest($row['username'])){

					}
					else{	
						$user_obj->sendRequest($row['username']);
						header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
					}
				}

			}


			echo "<div class='search_result'>		
					<div class='searchPageFriendButtons'>
						<form action='' method='POST'>
							" . $button . "
							<br>
						</form>
					</div>
					<div class='result_profile_pic'>
						<a href='" . $row['username'] . "'><img src='" . $row['profile_pic'] . "' style='height:100px;'></a>
					</div>
					<a href='" . $row['username'] . "'>" . $row['first_name'] . " " . $row['last_name'] . "
						<p id='grey'>" . $row['username'] . "</p>
					</a>
					<br>
					" . $mutual_friends . " <br>
				</div>
				<hr id='search_hr'>";
		} //End while loop
	}
	?>	

</div>