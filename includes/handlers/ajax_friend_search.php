<?php
include("../../config/config.php");
include("../classes/User.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query);

if(strpos($query, "_") !== false){
	$userReturned = $con->prepare("SELECT * FROM users WHERE username LIKE :query AND user_active = 'yes' LIMIT 8");
	$query = $query . '%';
	$userReturned->bindParam(":query", $query);
	$userReturned->execute();
}
else if(count($names) == 2){
	$first_name = '%' . $names[0] . '%';
	$last_name = '%' . $names[1] . '%';
	$userReturned = $con->prepare("SELECT * FROM users WHERE (first_name LIKE :first_name AND last_name LIKE :last_name) AND user_active = 'yes' LIMIT 8");
	$userReturned->bindParam(":first_name", $first_name);
	$userReturned->bindParam(":last_name", $last_name);
	$userReturned->execute();
}
else{
	$first_name = '%' . $names[0] . '%';
	$userReturned = $con->prepare("SELECT * FROM users WHERE (first_name LIKE :first_name OR last_name LIKE :first_name) AND user_active = 'yes' LIMIT 8");
	$userReturned->bindParam(":first_name", $first_name);
	$userReturned->execute();
}


if($query != ""){
	while($row = $userReturned->fetch(PDO::FETCH_ASSOC)){

		$user = new User($con, $userLoggedIn);

		if($row['username'] != $userLoggedIn){
			$mutual_friends = $user->getMutualFriends($row['username']) . " friends in common";
		}
		else{
			$mutual_friends = "";
		}

		if($user->getMutualFriends($row['username'])){
			echo "<div class='resultDisplay'>
					<a href='messages.php?u=" . $row['username'] ."' style='color: #000'>
						<div class='liveSearchProfile'>
							<img src= '" . $row['profile_pic'] . "'> 
						</div>

						<div class='liveSearchText'>"
							. $row['first_name'] . " " . $row['last_name'] . "
							<p style='margin:0'>". $row['username'] ."</p>
							<p id='grey'>" . $mutual_friends . "</p>
						</div>
					</a>
				</div>";
		}
	}
}

?>