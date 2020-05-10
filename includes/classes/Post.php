<?php	
	class Post{
		private $con;
		private $user_obj;

		public function __construct($con, $user){
			$this->con = $con;
			$this->user_obj =  new User($con, $user);
		}

		public function submitPost($body, $user_to, $imageName){

			$body = strip_tags($body); //remove html tags
			
			$body = str_replace('\r\n', '\n', $body);
			$body = nl2br($body);

			$check_empty = preg_replace('/\s+/', '', $body);

			if($check_empty != "" || $imageName !=""){

				$body_array = preg_split("/\s+/", $body);

				foreach($body_array as $key => $value) {

					if(strpos($value, "https://www.youtube.com/watch?v=") !== false) {

						$link = preg_split("!&!", $value);
						$value = preg_replace("!watch\?v=!", "embed/", $link[0]);
						$value = "<br><iframe width='420' height='315' src='" . $value ."'></iframe><br>";
						$body_array[$key] = $value;

					}

				}
				$body = implode(" ", $body_array);


				//Current date and time
				$date_added = date("Y-m-d H:i:s");
				//get username
				$added_by = $this->user_obj->getUsername();

				//if user is on own profile, user_to = "none"
				if($user_to == $added_by){
					$user_to = "none";
				}

				//insert post
				$insertPostData_SQL = "INSERT INTO posts(body, added_by, user_to, date_added, user_active, deleted, likes, images) 
									VALUES(:body, :added_by, :user_to, :date_added,'yes', 'no', '0', :imageName)";
		
				$insert_post_query = $this->con->prepare($insertPostData_SQL);
				$insert_post_query->bindParam(":body", $body);
				$insert_post_query->bindParam(":added_by", $added_by);
				$insert_post_query->bindParam(":user_to", $user_to);
				$insert_post_query->bindParam(":date_added", $date_added);
				$insert_post_query->bindParam(":imageName", $imageName);
				$insert_post_query->execute();

				$returned_id = $this->con->lastInsertId();

				//insert notification
				if($user_to != 'none'){
					$notification = new Notification($this->con, $added_by);
					$notification->insertNotification($returned_id, $user_to, "profile_post");
				}

				//update post count for user
				$num_posts = $this->user_obj->getNumPosts();
				$num_posts++;
				$update_post_count_query = $this->con->prepare("UPDATE users SET num_posts = :num_posts WHERE username = :added_by");

				$update_post_count_query->bindParam(":added_by", $added_by);
				$update_post_count_query->bindParam(":num_posts", $num_posts);
				$update_post_count_query->execute();

				$stopWords = "a about above across after again against all almost alone along already
			 also although always among am an and another any anybody anyone anything anywhere are 
			 area areas around as ask asked asking asks at away b back backed backing backs be became
			 because become becomes been before began behind being beings best better between big 
			 both but by c came can cannot case cases certain certainly clear clearly come could
			 d did differ different differently do does done down down downed downing downs during
			 e each early either end ended ending ends enough even evenly ever every everybody
			 everyone everything everywhere f face faces fact facts far felt few find finds first
			 for four from full fully further furthered furthering furthers g gave general generally
			 get gets give given gives go going good goods got great greater greatest group grouped
			 grouping groups h had has have having he her here herself high high high higher
		     highest him himself his how however i im if important in interest interested interesting
			 interests into is it its itself j just k keep keeps kind knew know known knows
			 large largely last later latest least less let lets like likely long longer
			 longest m made make making man many may me member members men might more most
			 mostly mr mrs much must my myself n necessary need needed needing needs never
			 new new newer newest next no nobody non noone not nothing now nowhere number
			 numbers o of off often old older oldest on once one only open opened opening
			 opens or order ordered ordering orders other others our out over p part parted
			 parting parts per perhaps place places point pointed pointing points possible
			 present presented presenting presents problem problems put puts q quite r
			 rather really right right room rooms s said same saw say says second seconds
			 see seem seemed seeming seems sees several shall she should show showed
			 showing shows side sides since small smaller smallest so some somebody
			 someone something somewhere state states still still such sure t take
			 taken than that the their them then there therefore these they thing
			 things think thinks this those though thought thoughts three through
	         thus to today together too took toward turn turned turning turns two
			 u under until up upon us use used uses v very w want wanted wanting
			 wants was way ways we well wells went were what when where whether
			 which while who whole whose why will with within without work
			 worked working works would x y year years yet you young younger
			 youngest your yours z lol haha omg hey ill iframe wonder else like 
             hate sleepy reason for some little yes bye choose";

             $stopWords = preg_split("/[\s,]+/", $stopWords);

             $no_punctuation = preg_replace("/[^a-zA-Z 0-9]+/", "",  $body);

	             if(strpos($no_punctuation, "height") === false && strpos($no_punctuation, "width") === false 
	             	&& strpos($no_punctuation, "http") === false){
	             	
	             	$no_punctuation = preg_split("/[\s,]+/", $no_punctuation);

	             	foreach ($stopWords as $value) {
	             		foreach ($no_punctuation as $key => $value2) {
	             			
	             			if(strtolower($value) == $value2){
	             				$no_punctuation[$key] = "";
	             			}

	             		}
	             	}

	             	foreach ($no_punctuation as $value) {
	             		$this->calculateTrend(ucfirst($value));
	             	}
	             }             
			}
		}

		public function calculateTrend($term){

			if($term != ''){
				$query = $this->con->prepare("SELECT * FROM trends WHERE title=:term");
				$query->bindParam(":term", $term);
				$query->execute();

				if($query->rowCount() == 0){
					$insert_query = $this->con->prepare("INSERT INTO trends(title, hits) VALUES(:title, '1')");
					$insert_query->bindParam(":title", $term);
					$insert_query->execute();
				}
				else{
					$insert_query = $this->con->prepare("UPDATE trends SET hits = hits + 1 WHERE title = :term");
					$insert_query->bindParam(":term", $term);
					$insert_query->execute();
				}
			}
		}

		public function loadPostsFriend($data, $limit){

			$page = $data['page'];

			$userLoggedIn = $this->user_obj->getUsername();

			if($page==1)
				$start = 0;
			else
				$start = ($page - 1) * $limit;


			$str = ""; //String to return


			$data_query_2 = $this->con->prepare("SELECT * from posts WHERE deleted = 'no' ORDER BY id DESC");
			$data_query_2->execute();


			if($data_query_2->rowCount() > 0){

				$num_iterations = 0; //Number of results checked (not necessarily posted)
				$count = 1;

				while($row = $data_query_2->fetch(PDO::FETCH_ASSOC)){

					$id = $row['id'];
					$body = $row['body'];
					$added_by = $row['added_by'];
					$date_time = $row['date_added'];
					$imagePath = $row['images'];



					//Prepare user_to string so it could included even not posted to user
					if($row['user_to'] == "none"){
						$user_to = "";
					}
					else{
						$user_to_obj = new User($this->con, $row['user_to']);
						$user_to_name = $user_to_obj->getFirstAndLastName();
						$user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
					}



					//Check if user who posted, has their account closed
					$added_by_obj = new User($this->con, $added_by);
					if($added_by_obj->isClosed()){
						continue;
					}

					//check whether User A is firend to user B
					$user_logged_obj = new User($this->con, $userLoggedIn);
					if($user_logged_obj->isFriend($added_by)){


						if($num_iterations++ < $start)
							continue;

						//Once 10 posts have been loaded, break
						if($count > $limit){
							break;
						}
						else{
							$count++;
						}

						if($userLoggedIn==$added_by){
							$delete_button = "<button class='delete_button' id='post$id'>X</button>";
						}
						else{
							$delete_button = "";
						}

				
						$user_details_query = $this->con->prepare("SELECT first_name, last_name, profile_pic from users WHERE username = :username");
						$user_details_query->bindParam(":username", $added_by);
						$user_details_query->execute();
						$user_row = $user_details_query->fetch(PDO::FETCH_ASSOC);

						$first_name = $user_row['first_name'];
						$last_name = $user_row['last_name'];
						$profile_pic = $user_row['profile_pic'];
						
						?>

						<script>
							function toggle<?php echo $id; ?>(){
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block")
									element.style.display = "none";
								else
									element.style.display = "block";
							}
						</script>

						<?php
						//comment number Query
						$comments_check = $this->con->prepare("SELECT * FROM comments WHERE post_id=:id");
						$comments_check->bindParam("id",$id);
						$comments_check->execute();
						$comments_check_num = $comments_check->rowCount();
						//Timeframe
						$date_time_now = date("Y-m-d H:i:s");
						$start_date = new DateTime($date_time); 	//Time of Post
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

						if($imagePath != ""){
							$imageDiv = "<div class='postedImage'>
											<img src='$imagePath'> 
										 </div>";
						}
						else{
							$imageDiv = "";
						}


						
						$str .= "<div class='status_post' onClick='javascript:toggle$id()')>
									<div class='post_profile_pic'>
										<img src='$profile_pic' width='50'>
									</div>

									<div class='posted_by' style='color: #ACACAC'>
										<a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $time_message
											$delete_button
									</div>

									<div id='post_body'>
										$body
										<br>
										$imageDiv
										<br>
										<br>
									</div>
									<div class='post_comments_option'>
										<iframe src='likes.php?post_id=$id' scrolling='no'></iframe>
										Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
									</div>
								</div>

								<div class='post_comment' id='toggleComment$id' style='display:none;'>
									<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
								</div>

								<hr>";
					}
					?>

					<script>
						
						$(document).ready(function(){
							$('#post<?php echo $id; ?>').on('click', function(){
								bootbox.confirm("Are you want to delete this post?" , function(result){

									$.post("includes/form_handler/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

									if(result)
										location.reload();

								});
							});
						});

					</script>

					<?php

				} //end of while loop

				if($count > $limit)
					$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
								<input type='hidden' class='noMorePosts' value = 'false'>";
				else
					$str .= "<input type='hidden' class='noMorePosts' value = 'true'>
							<p style='text-align: center;'> No more posts to show! </p>";

			}
			echo $str;
		}

		public function loadProfilePosts($data, $limit){

			$page = $data['page'];
			$profileUser = $data['profileUsername'];

			$userLoggedIn = $this->user_obj->getUsername();

			if($page==1)
				$start = 0;
			else
				$start = ($page - 1) * $limit;


			$str = ""; //String to return


			$data_query_2 = $this->con->prepare("SELECT * from posts WHERE deleted = 'no' AND ((added_by = :profileUser AND user_to = 'none') OR user_to = :profileUser) ORDER BY id DESC");
			$data_query_2->bindParam(":profileUser", $profileUser);
			$data_query_2->execute();


			if($data_query_2->rowCount() > 0){

				$num_iterations = 0; //Number of results checked (not necessarily posted)
				$count = 1;

				while($row = $data_query_2->fetch(PDO::FETCH_ASSOC)){

					$id = $row['id'];
					$body = $row['body'];
					$added_by = $row['added_by'];
					$date_time = $row['date_added'];
					$imagePath = $row['images'];

						if($num_iterations++ < $start)
							continue;

						//Once 10 posts have been loaded, break
						if($count > $limit){
							break;
						}
						else{
							$count++;
						}

						if($userLoggedIn==$added_by){
							$delete_button = "<button class='delete_button' id='post$id'>X</button>";
						}
						else{
							$delete_button = "";
						}

				
						$user_details_query = $this->con->prepare("SELECT first_name, last_name, profile_pic from users WHERE username = :username");
						$user_details_query->bindParam(":username", $added_by);
						$user_details_query->execute();
						$user_row = $user_details_query->fetch(PDO::FETCH_ASSOC);

						$first_name = $user_row['first_name'];
						$last_name = $user_row['last_name'];
						$profile_pic = $user_row['profile_pic'];
						
						?>

						<script>
							function toggle<?php echo $id; ?>(){
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block")
									element.style.display = "none";
								else
									element.style.display = "block";
							}
						</script>

						<?php
						//comment number Query
						$comments_check = $this->con->prepare("SELECT * FROM comments WHERE post_id=:id");
						$comments_check->bindParam("id",$id);
						$comments_check->execute();
						$comments_check_num = $comments_check->rowCount();

						//Timeframe
						$date_time_now = date("Y-m-d H:i:s");
						$start_date = new DateTime($date_time); 	//Time of Post
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

						if($imagePath != ""){
							$imageDiv = "<div class='postedImage'>
											<img src='$imagePath'> 
										 </div>";
						}
						else{
							$imageDiv = "";
						}


						
						$str .= "<div class='status_post' onClick='javascript:toggle$id()')>
									<div class='post_profile_pic'>
										<img src='$profile_pic' width='50'>
									</div>

									<div class='posted_by' style='color: #ACACAC'>
										<a href='$added_by'> $first_name $last_name </a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $time_message
											$delete_button
									</div>

									<div id='post_body'>
										$body
										<br>
										$imageDiv
										<br>
										<br>
									</div>

									<div class='post_comments_option'>
										Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
										<iframe src='likes.php?post_id=$id' scrolling='no'></iframe>
									</div>
								</div>

								<div class='post_comment' id='toggleComment$id' style='display:none;'>
									<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
								</div>

								<hr>";
					?>

					<script>
						
						$(document).ready(function(){
							$('#post<?php echo $id; ?>').on('click', function(){
								bootbox.confirm("Are you want to delete this post?" , function(result){

									$.post("includes/form_handler/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

									if(result)
										location.reload();

								});
							});
						});

					</script>

					<?php

				} //end of while loop

				if($count > $limit)
					$str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
								<input type='hidden' class='noMorePosts' value = 'false'>";
				else
					$str .= "<input type='hidden' class='noMorePosts' value = 'true'>
							<p style='text-align: center;'> No more posts to show! </p>";

			}
			echo $str;
		}

		public function getSinglePost($post_id){

			$userLoggedIn = $this->user_obj->getUsername();

			$post_id_link = "%=" . $post_id;

			$opened_query = $this->con->prepare("UPDATE notifications SET opened='yes' WHERE user_to = :userLoggedIn AND link LIKE :post_id");
			$opened_query->bindParam(":userLoggedIn", $userLoggedIn);
			$opened_query->bindParam(":post_id", $post_id_link);
			$opened_query->execute();


			$str = ""; //String to return


			$data_query_2 = $this->con->prepare("SELECT * from posts WHERE deleted = 'no' AND id = :post_id ORDER BY id DESC");
			$data_query_2->bindParam(":post_id", $post_id);
			$data_query_2->execute();


			if($data_query_2->rowCount() > 0){

				$row = $data_query_2->fetch(PDO::FETCH_ASSOC);

					$id = $row['id'];
					$body = $row['body'];
					$added_by = $row['added_by'];
					$date_time = $row['date_added'];



					//Prepare user_to string so it could included even not posted to user
					if($row['user_to'] == "none"){
						$user_to = "";
					}
					else{
						$user_to_obj = new User($this->con, $row['user_to']);
						$user_to_name = $user_to_obj->getFirstAndLastName();
						$user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
					}



					//Check if user who posted, has their account closed
					$added_by_obj = new User($this->con, $added_by);
					if($added_by_obj->isClosed()){
						return;
					}

					//check whether User A is firend to user B
					$user_logged_obj = new User($this->con, $userLoggedIn);
					if($user_logged_obj->isFriend($added_by)){


						if($userLoggedIn==$added_by){
							$delete_button = "<button class='delete_button' id='post$id'>X</button>";
						}
						else{
							$delete_button = "";
						}

				
						$user_details_query = $this->con->prepare("SELECT first_name, last_name, profile_pic from users WHERE username = :username");
						$user_details_query->bindParam(":username", $added_by);
						$user_details_query->execute();
						$user_row = $user_details_query->fetch(PDO::FETCH_ASSOC);

						$first_name = $user_row['first_name'];
						$last_name = $user_row['last_name'];
						$profile_pic = $user_row['profile_pic'];
						
						?>

						<script>
							function toggle<?php echo $id; ?>(){
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block")
									element.style.display = "none";
								else
									element.style.display = "block";
							}
						</script>

						<?php
						//comment number Query
						$comments_check = $this->con->prepare("SELECT * FROM comments WHERE post_id=:id");
						$comments_check->bindParam("id",$id);
						$comments_check->execute();
						$comments_check_num = $comments_check->rowCount();
						//Timeframe
						$date_time_now = date("Y-m-d H:i:s");
						$start_date = new DateTime($date_time); 	//Time of Post
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


						
						$str .= "<div class='status_post' onClick='javascript:toggle$id()')>
									<div class='post_profile_pic'>
										<img src='$profile_pic' width='50'>
									</div>

									<div class='posted_by' style='color: #ACACAC'>
										<a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $time_message
											$delete_button
									</div>

									<div id='post_body'>
										$body
										<br>
									</div>
									<div class='post_comments_option'>
										Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
										<iframe src='likes.php?post_id=$id' scrolling='no'></iframe>
									</div>
								</div>

								<div class='post_comment' id='toggleComment$id' style='display:none;'>
									<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
								</div>

								<hr>";


					?>

					<script>
						
						$(document).ready(function(){
							$('#post<?php echo $id; ?>').on('click', function(){
								bootbox.confirm("Are you want to delete this post?" , function(result){

									$.post("includes/form_handler/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

									if(result)
										location.reload();

								});
							});
						});

					</script>

					<?php
						}
					else{
						echo "<p>You cannot see this post because you are not friends with this user.</p>";
						return;
					}

			}
			else{
				echo "<p>No post found. If you clicked a link, it may be broken.</p>";
						return;
			}
			echo $str;
		}
	}
?>