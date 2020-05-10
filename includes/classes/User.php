<?php
	
	class User{
		private $con;
		private $user;

		public function __construct($con, $user){
			$this->con = $con;

			$username_query = $this->con->prepare("SELECT * from users WHERE username = :username");
			$username_query->bindParam(":username", $user);
			$username_query->execute();
			$this->user = $username_query->fetch(PDO::FETCH_ASSOC);
		}

		public function getUsername(){
			return $this->user['username'];
		}

		public function getNumberOfFriendRequests(){
			$username = $this->user['username'];
			$query = $this->con->prepare("SELECT * from friend_requests WHERE user_to = :username");
			$query->bindParam(":username", $username);
			$query->execute();
			return $query->rowCount();
		}

		public function getNumPosts(){
			$username = $this->user['username'];
			$query = $this->con->prepare("SELECT num_posts from users WHERE username = :username");
			$query->bindParam(":username", $username);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			return $row['num_posts'];
		}

		public function getFirstAndLastName(){
			$username = $this->user['username'];
			$query = $this->con->prepare("SELECT first_name, last_name from users WHERE username = :username");
			$query->bindParam(":username", $username);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			return $row['first_name'] . " " . $row['last_name'];
		}

		public function getFriendArray(){

			$username = $this->user['username'];
			$query = $this->con->prepare("SELECT friends_array from users WHERE username = :username");
			$query->bindParam(":username", $username);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			return $row['friends_array'];		
		}

		public function getProfilePic(){
			$username = $this->user['username'];
			$query = $this->con->prepare("SELECT profile_pic from users WHERE username = :username");
			$query->bindParam(":username", $username);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);
			return $row['profile_pic'];
		}

		public function isClosed(){
			$username = $this->user['username'];
			$query = $this->con->prepare("SELECT user_active from users WHERE username = :username");
			$query->bindParam(":username", $username);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);

			if($row['user_active'] == 'no'){
				return true;
			}
			else{
				return false;
			}
		}

		public function isFriend($username_to_check){
			$username_comma = "," . $username_to_check . ",";

			if((strstr($this->user['friends_array'], $username_comma)) || $this->user['username'] == $username_to_check){								
				return true;
			}
			else{
				return false;
			}
		}

		
		public function didReceiveRequest($username_from){
			$user_to = $this->user['username'];
			$check_request_query = $this->con->prepare("SELECT * From friend_requests where user_to = :user_to and user_from = :user_from");
			$check_request_query->bindParam(":user_to", $user_to);
			$check_request_query->bindParam(":user_from", $username_from);
			$check_request_query->execute();

			if($check_request_query->rowCount() > 0){
				return true;
			}
			else{
				return false;
			}
		}

		public function didSentRequest($username_to){
			$user_from = $this->user['username'];
			$check_request_query = $this->con->prepare("SELECT * From friend_requests where user_to = :user_to and user_from = :user_from");
			$check_request_query->bindParam(":user_to", $username_to);
			$check_request_query->bindParam(":user_from", $user_from);
			$check_request_query->execute();

			if($check_request_query->rowCount() > 0){
				return true;
			}
			else{
				return false;
			}
		}


		public function removeFriend($user_to_remove){
			$logged_into_user = $this->user['username'];
			$query = $this->con->prepare("SELECT friends_array from users where username = :username");
			$query->bindParam(":username", $user_to_remove);
			$query->execute();
			$row = $query->fetch(PDO::FETCH_ASSOC);

			$friend_array_username = $row['friends_array']; 

			$new_friends_array = str_replace($user_to_remove . "," , "", $this->user['friends_array']);
			$remove_friend = $this->con->prepare("UPDATE users SET friends_array = :new_friends_array  where username = :logged_into_user");
			$remove_friend->bindParam(":new_friends_array", $new_friends_array);
			$remove_friend->bindParam(":logged_into_user", $logged_into_user);
			$remove_friend->execute();

			$new_friends_array = str_replace($this->user['username'] . "," , "", $friend_array_username);
			$remove_friend = $this->con->prepare("UPDATE users SET friends_array = :new_friends_array where username = :user_to_remove");
			$remove_friend->bindParam(":new_friends_array", $new_friends_array);
			$remove_friend->bindParam(":user_to_remove", $user_to_remove);
			$remove_friend->execute();
		}

		public function sendRequest($user_to){
			$user_from = $this->user['username'];
			$query = $this->con->prepare("INSERT INTO friend_requests(user_to, user_from) VALUES(:user_to, :user_from)");
			$query->bindParam(":user_to", $user_to);
			$query->bindParam(":user_from", $user_from);
			$query->execute();
		}

		public function getMutualFriends($user_to_check){
			$mutualFriends = 0;
			$user_array = $this->user['friends_array'];
			$user_array_explode = explode(",", $user_array);

			$query = $this->con->prepare("SELECT friends_array From users Where username = :user_to_check");
			$query->bindParam(':user_to_check', $user_to_check);
			$query->execute();

			$row = $query->fetch(PDO::FETCH_ASSOC);
			$user_to_check_array = $row['friends_array'];
			$user_to_check_array_explode = explode(",", $user_to_check_array);

			foreach ($user_array_explode as $i) {

				foreach ($user_to_check_array_explode as $j) {
					if ($i == $j && $i != "") {
						$mutualFriends++;
					}
				}
			}

			return $mutualFriends;
		}		
		
	}
?>