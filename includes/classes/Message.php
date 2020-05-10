<?php	

class Message{
	private $con;
	private $user_obj;

	public function __construct($con, $user){
		$this->con = $con;
		$this->user_obj =  new User($con, $user);
	}

	public function getMostRecentUser(){
		$userLoggedIn = $this->user_obj->getUsername();

		$query = $this->con->prepare("SELECT user_to, user_from FROM messages WHERE  user_to = :user_to OR user_from = :user_from ORDER BY id DESC LIMIT 1");
		$query->bindParam(":user_to", $userLoggedIn);
		$query->bindParam(":user_from", $userLoggedIn);
		$query->execute();

		if($query->rowCount() == 0)
			return false;

		$row = $query->fetch(PDO::FETCH_ASSOC);
		$user_to = $row['user_to']; 
		$user_from = $row['user_from'];

		if($user_to != $userLoggedIn){
			return $user_to;
		}
		else{
			return $user_from;
		}
	}

	public function sendMessage($user_to, $body, $date){
		if($body != ""){
			$userLoggedIn = $this->user_obj->getUsername();
			$query = $this->con->prepare("INSERT INTO messages(user_to, user_from, body, date_added, opened, viewed, deleted) VALUES(:user_to, :user_from, :body, :date_added, 'no', 'no', 'no')");
			$query->bindParam(":user_to", $user_to);
			$query->bindParam(":user_from", $userLoggedIn);
			$query->bindParam(":body", $body);
			$query->bindParam(":date_added", $date);
			$query->execute();
		}
	}

	public function getMessage($other_user){
		$userLoggedIn = $this->user_obj->getUsername();
		$query = $this->con->prepare("UPDATE messages SET opened = 'yes' Where user_to = :userLoggedIn AND user_from = :other_user");
		$query->bindParam(":other_user", $other_user);
		$query->bindParam(":userLoggedIn", $userLoggedIn);
		$query->execute();

		$get_messages_query = $this->con->prepare("SELECT * FROM messages WHERE (user_to = :userLoggedIn AND user_from = :other_user) OR (user_to = :other_user AND user_from = :userLoggedIn)");
		$get_messages_query->bindParam(":other_user", $other_user);
		$get_messages_query->bindParam(":userLoggedIn", $userLoggedIn);
		$get_messages_query->execute();

		while ($row = $get_messages_query->fetch(PDO::FETCH_ASSOC)) {
			$user_to = $row['user_to'];
			$user_from = $row['user_from'];
			$body = $row['body'];

			$div_top = ($user_to == $userLoggedIn) ? "<div class='message' id='green'>" : "<div class='message' id='blue'>";

			$data = $data . $div_top . $body . "</div><br><br>";

		}

		return $data;
	}

	public function getLatestDetails($userLoggedIn, $user_to){
		$details_array = array();

		$query = $this->con->prepare("SELECT user_to, body, date_added FROM messages WHERE (user_to = :userLoggedIn AND user_from = :user_to) OR (user_to = :user_to AND user_from = :userLoggedIn) ORDER BY id DESC LIMIT 1");
		$query->bindParam(":userLoggedIn", $userLoggedIn);
		$query->bindParam(":user_to", $user_to);
		$query->execute();

		$row = $query->fetch(PDO::FETCH_ASSOC);
		$sent_by = ($row['user_to'] == $userLoggedIn) ? "They said: " : "You said: ";

		//Timeframe
		$date_time_now = date("Y-m-d H:i:s");
		$start_date = new DateTime($row['date_added']); //Time of Post
		$end_date = new DateTime($date_time_now);   	//Current time
		$interval = $start_date->diff($end_date);		//difference between dates

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

		array_push($details_array, $sent_by);
		array_push($details_array, $row['body']);
		array_push($details_array, $time_message);

		return $details_array;
	}

	public function getConvos(){
		$userLoggedIn = $this->user_obj->getUsername();
		$return_string = "";
		$convos = array();


		$query = $this->con->prepare("SELECT user_to, user_from FROM messages WHERE user_to = :userLoggedIn OR user_from = :userLoggedIn");
		$query->bindParam(":userLoggedIn", $userLoggedIn);
		$query->execute();

		while($row = $query->fetch(PDO::FETCH_ASSOC)){

			$user_to_push = ($row['user_to'] != $userLoggedIn) ? $row['user_to']: $row['user_from'];

			if(!in_array($user_to_push, $convos)){
				array_push($convos, $user_to_push);
			}
		}

		foreach ($convos as $username) {
			$user_found_obj = new User($this->con, $username);
			$latest_message_details = $this->getLatestDetails($userLoggedIn, $username);
			$dots = (strlen($latest_message_details[1]) >= 12) ? "...": "";
			$split = str_split($latest_message_details[1], 12);
			$split = $split[0] . $dots;

			$return_string .= "<a href='messages.php?u=$username'> 
								<div class='user_found_messages'>
							   		<img src='" . $user_found_obj->getProfilePic() . "' style='border-radius: 5px; margin-right: 5px;'>" . $user_found_obj->getFirstAndLastName() . 
							   		"<span class='timestamp_smaller' id='grey'> " . $latest_message_details[2] . "</span>
							   		<p id='grey' style='margin:0;'>" . $latest_message_details[0] . $split . "</p> 
							   	</div>
							   </a>";
		}

		return $return_string;

	}

	public function getDropDownConvos($data, $limit){

		$page = $data['page'];
		if($page == 1)
			$start = 0;
		else
			$start = ($page - 1) * $limit;

		$userLoggedIn = $this->user_obj->getUsername();

		$set_viewed_query = $this->con->prepare("UPDATE messages SET viewed = 'yes' WHERE user_to = :userLoggedIn");
		$set_viewed_query->bindParam(":userLoggedIn", $userLoggedIn);
		$set_viewed_query->execute();

		$return_string = "";
		$convos = array();


		$query = $this->con->prepare("SELECT user_to, user_from FROM messages WHERE user_to = :userLoggedIn OR user_from = :userLoggedIn ORDER BY id DESC");
		$query->bindParam(":userLoggedIn", $userLoggedIn);
		$query->execute();

		while($row = $query->fetch(PDO::FETCH_ASSOC)){

			$user_to_push = ($row['user_to'] != $userLoggedIn) ? $row['user_to']: $row['user_from'];

			if(!in_array($user_to_push, $convos)){
				array_push($convos, $user_to_push);
			}
		}

		$num_iteration = 0; //number of messages checked 
		$count = 1;	//Number of messages posted 

		foreach ($convos as $username) {

			if($num_iteration++ < $start)
				continue;

			if($count > $limit)
				break;
			else
				$count++;

			$is_unread_query = $this->con->prepare("SELECT opened FROM messages WHERE user_from = :userLoggedIn AND user_to = :username ORDER BY id DESC");
			$is_unread_query->bindParam(":userLoggedIn",$userLoggedIn);
			$is_unread_query->bindParam(":username",$username);
			$is_unread_query->execute();

			$row = $is_unread_query->fetch(PDO::FETCH_ASSOC);
			$style = ($row['opened']) == 'no' ? "background-color= #DDEDFF;" : "";



			$user_found_obj = new User($this->con, $username);
			$latest_message_details = $this->getLatestDetails($userLoggedIn, $username);
			$dots = (strlen($latest_message_details[1]) >= 12) ? "...": "";
			$split = str_split($latest_message_details[1], 12);
			$split = $split[0] . $dots;

			$return_string .= "<a href='messages.php?u=$username'> 
								<div class='user_found_messages' style='" . $style . "'>
							   		<img src='" . $user_found_obj->getProfilePic() . "' style='border-radius: 5px; margin-right: 5px;'>" . $user_found_obj->getFirstAndLastName() . 
							   		"<span class='timestamp_smaller' id='grey'> " . $latest_message_details[2] . "</span>
							   		<p id='grey' style='margin:0;'>" . $latest_message_details[0] . $split . "</p> 
							   	</div>
							   </a>";
		}
		//If posts were loaded
		if($count > $limit)
			$return_string .= "<input type='hidden' class='nextPageDropDownData' value='" . ($page + 1) . "'><input type='hidden' class='noMoreDropDownData' value='false'>";
		else
			$return_string .= "<input type='hidden' class='noMoreDropDownData' value='true'><p style='text-align: center'> No more message to load</p>";

		return $return_string;
	}

	public function getUnreadNumber(){
		$userLoggedIn = $this->user_obj->getUsername();
		$query = $this->con->prepare("SELECT * FROM messages WHERE viewed = 'no' AND user_to = :userLoggedIn");
		$query->bindParam(":userLoggedIn" ,$userLoggedIn);
		$query->execute();

		return $query->rowCount();
	}

}

?>