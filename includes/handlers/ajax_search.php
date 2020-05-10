<?php
include("../../config/config.php");
include("../../includes/classes/User.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query);


//if query contains an underscore, assume user is searching for usernames
$query_like = $query . "%";
if(strpos($query, '_') !== false){
	$userReturnedQuery = $con->prepare("SELECT * FROM users WHERE username LIKE :query AND user_active = 'yes' LIMIT 8");
	$userReturnedQuery->bindParam(":query" ,$query_like);
	$userReturnedQuery->execute();
}
//if there are two words, assume that there are first name and last name respectively
else if(count($names) == 2){
	$first_name = $names[0] . "%";
	$last_name = $names[1] . "%";
	$userReturnedQuery = $con->prepare("SELECT * FROM users WHERE (first_name LIKE :first_name AND last_name LIKE :last_name) AND user_active = 'yes' LIMIT 8");
	$userReturnedQuery->bindParam(":first_name" ,$first_name);
	$userReturnedQuery->bindParam(":last_name" ,$last_name);
	$userReturnedQuery->execute();
}
//If any user search on the base of first name or last name
else {
	$name = $names[0] . "%";
	$userReturnedQuery = $con->prepare("SELECT * FROM users WHERE (first_name LIKE :name OR last_name LIKE :name) AND user_active = 'yes' LIMIT 8");
	$userReturnedQuery->bindParam(":name" ,$name);
	$userReturnedQuery->execute();
}

if($query != ""){
	while($row = $userReturnedQuery->fetch(PDO::FETCH_ASSOC)){
		$user = new User($con, $userLoggedIn);

		if($row['username'] != $userLoggedIn){
			$mutual_friends = $user->getMutualFriends($row['username']) . " friends in mutual";
		}
		else{

			$mutual_friends = "";
		}
		

		echo "<div class='resultDisplay'>
				<a href='" . $row['username'] . "' style='color: #14858D'>
					<div class='liveSearchProfilePic'>
						<img src='" . $row['profile_pic'] . "''>
					</div>

					<div class='liveSearchText'>
						" . $row['first_name'] . " " . $row['last_name'] . "
						<p>" . $row['username'] . "</p>
						<p id='grey'>" . $mutual_friends . "</p>
					</div>
				</a>
			 </div>";
	}
}

?>