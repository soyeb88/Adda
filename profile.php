<?php 
include("includes/header.php");

$message_obj = new Message($con, $userLoggedIn); 
	
	if(isset($_GET['profile_username'])){
		$username = $_GET['profile_username'];
		$user_details_query = $con->prepare("SELECT * from users where username = :username");
		$user_details_query->bindParam(":username", $username);
		$user_details_query->execute();
		$user_array = $user_details_query->fetch(PDO::FETCH_ASSOC);

		$num_friends = (substr_count($user_array['friends_array'] ,",")) - 1;
	}

	
	if(isset($_POST['remove_friend'])){
		$user = new User($con, $userLoggedIn);
		$user->removeFriend($username);
	}	

	if(isset($_POST['add_friend'])){
		$user = new User($con, $userLoggedIn);
		$user->sendRequest($username);
	}

	if(isset($_POST['receive_friend'])){
		header("Location: requests.php");
	}

	if(isset($_POST['post_message'])){
		if(isset($_POST['message_body'])){
			$body = $_POST['message_body'];
			$body = strip_tags($body); //remove html tags			
			$body = str_replace('\r\n', '\n', $body);
			$body = nl2br($body);

			$date = date("Y-m-d H:i:s");

			$message_obj->sendMessage($username, $body, $date);


		}
	}

	$link = '#profileTabs a[href="#messages_div"]';

	echo "<script>
			$(function(){
				$('" . $link . "').tab('show');
			});
		 </script>";
?>	

	<div class="profile_left">
		<img src="<?php echo $user_array['profile_pic']; ?>">

		<div class="profile_info">
			<p> <?php echo "Posts: " . $user_array['num_posts']; ?> </p>
			<p> <?php echo "Likes: " . $user_array['num_likes']; ?> </p>
			<p> <?php echo "Friends: " . $num_friends; ?> </p>
		</div>	

		<form action="<?php echo $username; ?>" method="POST">
			<?php 

				$profile_user_obj = new User($con, $username);

				if($profile_user_obj->isClosed()){
					header("Location: user_closed.php");
				} 

				$logged_in_user_obj = new User($con, $userLoggedIn);

				if($profile_user_obj != $logged_in_user_obj){
					if($logged_in_user_obj->isFriend($username)){
						echo "<input type='submit' name='remove_friend' class='danger' value='Remove Friend'><br>";
					}
					
					else if($logged_in_user_obj->didReceiveRequest($username)){
						echo "<input type='submit' name='receive_friend' class='warning' value='Response to Request'><br>";
					}
					else if($logged_in_user_obj->didSentRequest($username)){
						echo "<input type='submit' name='' class='default' value='Request Sent'><br>";
					}
					else  
						echo "<input type='submit' name='add_friend' class='success' value='Add Friend'><br>";
				}				
			?>
			
		</form>


		<input type="submit" class="default" data-toggle="modal" data-target="#post_form" value="Post Something">

		<?php 
			if ($userLoggedIn != $username) {
				echo "<div class='profile_info_bottom'>";
					echo $logged_in_user_obj->getMutualFriends($username) . " Mutual Friends";
				echo "</div>";
			}
		?>

	</div>

	<div class="main_column column">

		<ul class="nav nav-tabs" id="profileTabs" role="tablist">
		  <li class="nav-item">
		    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home">Home</a>
		  </li>
		  <li class="nav-item">
		    <a class="nav-link" id="messages-tab" data-toggle="tab" href="#messages" role="tab" aria-controls="messages">Messages</a>
		  </li>

		</ul>

		<div class="tab-content">
		  <div class="tab-pane active" id="home" role="tabpanel" aria-labelledby="home-tab">
		  	<div class="post_area"></div>
			<img class="loading_1" id="loading" src="Assets/Images/icons/loading.gif">
		  </div>

		  <div class="tab-pane" id="messages" role="tabpanel" aria-labelledby="messages-tab">
		  	<?php 

				echo "<h4>You and <a href='" . $username . "'>". $profile_user_obj->getFirstAndLastName() . "</a></h4><hr><br>";
				echo "<div class='loaded_messages' id='scroll_messages'>";
					echo $message_obj->getMessage($username);
				echo "</div>";
			?>

			<div class="message_post">
				<form action="" method="POST">
					<textarea name='message_body' id='message_textarea' placeholder='Write your message ...'></textarea>
					<input type='submit'name='post_message' class='info' id='message_submit' value='sent'>
				</form>

			</div>

			<script>					
				var div = document.getElementById("scroll_messages");
				div.scrollTop = div.scrollHeight;
			</script>
		  </div>

		</div>
	</div>


	<!--Source Code: https://getbootstrap.com/docs/4.3/components/modal/ -->
	<!-- Modal -->
	<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">

	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLabel">Post Something!</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>

	      <div class="modal-body">
	        <p>This will appear on the user's profile page and also their newsfeed for your friends to see </p>

	        <form class="profile_post" action="" method="POST">

	        	<div class="form-group">

	        		<textarea class="form-control" name="post_body"></textarea>
	        		<input type="hidden" name="user_from" value="<?php echo $userLoggedIn; ?>">
	        		<input type="hidden" name="user_to" value="<?php echo $username; ?>">

	        	</div>
	        	

	        </form>
	      
	      </div>


	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	        <button type="button" class="btn btn-primary" class="post_button" id="submit_profile_post">Post</button>
	      </div>
	    </div>
	  </div>

	  <script>
			var userLoggedIn = '<?php echo $userLoggedIn; ?>';
			var profileUsername = '<?php echo $username; ?>';

			$(document).ready(function(){
				$('#loading').show();


				//Original ajax request for loading first posts
				$.ajax({
					url: "includes/handlers/ajax_load_profile_posts.php",
					type:"POST",
					data:"page=1&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
					cache: false,

					success: function(data){
						$('#loading').hide();
						$('.post_area').html(data);
					}
				});

				$(window).scroll(function() {
					var height = $('.post_area').height(); //Div containing posts
					var scroll_top = $(this).scrollTop();
					var page = $('.post_area').find('.nextPage').val();
					var noMorePosts = $('.post_area').find('.noMorePosts').val();

					if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {
						$('#loading').show();

						var ajaxReq = $.ajax({
							url: "includes/handlers/ajax_load_profile_posts.php",
							type: "POST",
							data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
							cache:false,

							success: function(response) {
								$('.post_area').find('.nextPage').remove(); //Removes current .nextpage 
								$('.post_area').find('.noMorePosts').remove(); //Removes current .nextpage 

								$('#loading').hide();
								$('.post_area').append(response);
							}
						});

					} //End if 

					return false;

				}); //End (window).scroll(function(
				
			});

		</script>

	</div>

</body>
</html>