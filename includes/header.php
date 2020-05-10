<?php
	require 'config/config.php';
	include("includes/classes/User.php");
	include("includes/classes/Post.php");
	include("includes/classes/Message.php");
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
	<title>FreeLand</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<!-- Production version -->
	<script src="https://unpkg.com/@popperjs/core@2"></script>
	<script src="Assets/js/bootstrap.js"></script> 
	<script src="Assets/js/bootbox.min.js"></script> 
	<script src="https://kit.fontawesome.com/a076d05399.js"></script>
	<script src="Assets/js/demo.js"></script> 
	<script src="Assets/js/jquery.Jcrop.js"></script> 
	<script src="Assets/js/jcrop_bits.js"></script> 
	<!-- css-->
	<link rel="stylesheet" type="text/css" href="Assets/css/bootstrap.css"> 
	<link rel="stylesheet" type="text/css" href="Assets/css/style.css">
	<link rel="stylesheet" type="text/css" href="Assets/css/jquery.Jcrop.css">
</head>
<body>   

<!-- https://getbootstrap.com/docs/4.4/components/dropdowns/ -->

	<div class="top_bar">
		<div class="logo">
			<a href="index.php">FreeLand</a>
		</div>

		<div class="search">

			<form action="search.php" method="GET" name="search_form">
				<input type="text" onkeyup="getLiveSearchUser(this.value, '<?php echo $userLoggedIn; ?>')" name="q" placeholder="search..." autocomplete="off" id="search_text_input">

				<div class="button_holder">
					<img src="Assets/Images/icons/grey-magnifier.png">
				</div>
			</form>
	
			<div class="search_results">
				
			</div>

			<div class="search_results_footer_empty">
				
			</div>
			
		</div>

		<nav>
			<?php
				//unread messages
				$messages = new Message($con, $userLoggedIn);
				$num_messages = $messages->getUnreadNumber();

				//unread notifications
				$notifications = new Notification($con, $userLoggedIn);
				$num_notifications = $notifications->getUnreadNumber();


				//friend requests notifications
				$user_obj = new User($con, $userLoggedIn);
				$num_requests = $user_obj->getNumberOfFriendRequests();
			?>

			<a href="<?php echo $username['username']; ?>">
				<?php echo $username["first_name"]; ?>
			</a>
			<a href="index.php">
				<i class="fas fa-home" style="font-size:24px;"></i>
			</a>
			<a href="javascript:void(0);" onclick="getDropDownData('<?php echo $userLoggedIn; ?>', 'message')">
				<i class="fas fa-envelope" style="font-size:24px;"></i>
				<?php
				if($num_messages > 0){
					echo "<span class='notification_badge' id='unread_message'>" . $num_messages . "</span>";
				}
				?>
			</a>
			<a href="javascript:void(0);" onclick="getDropDownData('<?php echo $userLoggedIn; ?>', 'notification')">
				<i class="fas fa-bell" style="font-size:24px;"></i>
				<?php
				if($num_notifications > 0){
					echo "<span class='notification_badge' id='unread_notifications'>" . $num_notifications . "</span>";
				}
				?>
			</a>
			<a href="requests.php">
				<i class="fas fa-users" style="font-size:24px;"></i>
				<?php
				if($num_requests > 0){
					echo "<span class='notification_badge' id='unread_requests'>" . $num_requests . "</span>";
				}
				?>
			</a>
			<a href="settings.php">
				<i class="fas fa-cog" style="font-size:24px;"></i>
			</a>
			<a href="includes/handlers/log_out.php">
				<i class="fas fa-sign-out-alt" style="font-size:24px;"></i>
			</a>
		</nav>

		<div class="dropdown_data_window" style="height:0px; border:none;"></div>
		<input type="hidden" id="drop_down_data_type" value="">
	</div>

	<script>
		var userLoggedIn = '<?php echo $userLoggedIn; ?>';

		$(document).ready(function(){

			$('.dropdown_data_window').scroll(function() {
				var inner_height = $('.dropdown_data_window').innerHeight(); //Div containing data
				var scroll_top = $('.dropdown_data_window').scrollTop();
				var page = $('.dropdown_data_window').find('.nextPageDropDownData').val();
				var noMoreData = $('.dropdown_data_window').find('.noMoreDropDownData').val();

				if ((scroll_top + inner_height >= $('.dropdown_data_window')[0].scrollHeight) && noMoreData == 'false') {
					var pageName; //Holds name of page to sent ajax request
					var data_type = $('#drop_down_data_type').val();

					if(data_type == "notification"){
						pageName = "ajax_load_notification.php";
					}
					else if(data_type = "message"){
						pageName = "ajax_load_messages.php";
					}

					var ajaxReq = $.ajax({
						url: "includes/handlers/" + pageName,
						type: "POST",
						data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
						cache:false,

						success: function(response) {
							$('.dropdown_data_window').find('.nextPageDropDownData').remove(); //Removes current .nextpage 
							$('.dropdown_data_window').find('.noMoreDropDownData').remove(); //Removes current .nextpage 
							$('.dropdown_data_window').append(response);
						}
					});

				} //End if 

				return false;

			}); //End (window).scroll(function(
			
		});

	</script>

	<div class="wrapper">
