<?php	

class Notification{
	private $con;
	private $user_obj;

	public function __construct($con, $user){
		$this->con = $con;
		$this->user_obj =  new User($con, $user);
	}

	public function getUnreadNumber(){
		$userLoggedIn = $this->user_obj->getUsername();

		$query = $this->con->prepare("SELECT * FROM notifications WHERE viewed = 'no' AND user_to = :userLoggedIn");
		$query->bindParam(":userLoggedIn" ,$userLoggedIn);
		$query->execute();

		return $query->rowCount();
	}

	public function getNotification($data, $limit){

		$page = $data['page'];
		if($page == 1)
			$start = 0;
		else
			$start = ($page - 1) * $limit;

		$userLoggedIn = $this->user_obj->getUsername();

		$set_viewed_query = $this->con->prepare("UPDATE notifications SET viewed = 'yes' WHERE user_to = :userLoggedIn");
		$set_viewed_query->bindParam(":userLoggedIn", $userLoggedIn);
		$set_viewed_query->execute();

		$return_string = "";

		$query = $this->con->prepare("SELECT * FROM notifications WHERE user_to = :userLoggedIn ORDER BY id DESC");
		$query->bindParam(":userLoggedIn", $userLoggedIn);
		$query->execute();

		if ($query->rowCount() == 0) {
			echo "You have no notifications!";
			return;
		}

		$num_iteration = 0; //number of messages checked 
		$count = 1;	//Number of messages posted 

		while($row = $query->fetch(PDO::FETCH_ASSOC)) {

			if($num_iteration++ < $start)
				continue;

			if($count > $limit)
				break;
			else
				$count++;

			$user_from = $row['user_from'];

			$user_data_query = $this->con->prepare("SELECT * FROM users WHERE username = :user_from");
			$user_data_query->bindParam(":user_from", $user_from);
			$user_data_query->execute();
			$user_data = $user_data_query->fetch(PDO::FETCH_ASSOC);

			$date_time_now = date("Y-m-d H:i:s");
			$start_date = new DateTime($row['date_added']); 	//Time of Post
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



			$opened = $row['opened'];
			$style = ($row['opened']) == 'no' ? "background-color: #DDEDFF;" : "";

			$return_string .= "<a href='" . $row['link'] . "'> 
								<div class='resultDisplay resultDisplayNotification' style='" . $style . "'>
									<div class='notificationsProfilePic'>
										<img src='" . $user_data['profile_pic'] . "'>
									</div>
								
									<p class='timestamp_smaller' id='grey'> " . $time_message . "</p>" . $row['message'] . "
								</div>
							   </a>";
		}

		//If posts were loaded
		if($count > $limit)
			$return_string .= "<input type='hidden' class='nextPageDropDownData' value='" . ($page + 1) . "'><input type='hidden' class='noMoreDropDownData' value='false'>";
		else
			$return_string .= "<input type='hidden' class='noMoreDropDownData' value='true'><p style='text-align: center'> No more Notification to load</p>";

		return $return_string;

	}

	public function insertNotification($post_id, $user_to, $type){

		$userLoggedIn = $this->user_obj->getUsername();
		$userLoggedInName = $this->user_obj->getFirstAndLastName();

		$date_time = date("Y-m-d H:i:s");

		switch($type) {
			case 'comment':
				$message = $userLoggedInName . " commented on your post";
				break;
			
			case 'like':
				$message = $userLoggedInName . " liked your post";
				break;
			case 'profile_post':
				$message = $userLoggedInName . " posted on your profile";
				break;
			case 'comment_non_owner':
				$message = $userLoggedInName . " commented on a post your commented on";
				break;
			case 'profile_comment':
				$message = $userLoggedInName . " commented on your profile post";
				break;
		}

		$link = "post.php?id=" . $post_id;

		$insert_query = $this->con->prepare("INSERT INTO notifications(user_to, user_from, message, link, date_added, opened, viewed) VALUES (:user_to, :user_from, :message, :link, :date_added, 'no', 'no')");
		$insert_query->bindParam(":user_to", $user_to);
		$insert_query->bindParam(":user_from", $userLoggedIn);
		$insert_query->bindParam(":message", $message);
		$insert_query->bindParam(":link", $link);
		$insert_query->bindParam(":date_added", $date_time);
		$insert_query->execute();

	}
}

?>