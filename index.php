<?php 
include("includes/header.php");


if(isset($_POST['post'])){

	$upLoadOk = 1;
	$imageName = $_FILES['fileToUpload']['name'];
	$errorMessage = "";

	if($imageName != ""){
		$targetDir = "Assets/Images/posts/";
		$imageName = $targetDir . uniqid() . basename($imageName); 
		$imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);

		if($_FILES['fileToUpload']['name'] > 10000000){
			$errorMessage = "Your file is too much big";
			$upLoadOk = 0;
		}

		if(strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg"){
			$errorMessage = "Sorry only allowed jped, png jpg types files";
			$upLoadOk = 0;
		}

		if($upLoadOk){
			if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)){
				//image Upload okey
			}
			else{
				//image didn't Upload 
				$upLoadOk = 0;
			}
		}
	}

	if($upLoadOk){
		$post = new Post($con, $userLoggedIn);
		$post->submitPost($_POST['post_text'], 'none', $imageName);
		header("Location: index.php");
	}
	else{
		echo "<div style='text-align:center;' class='alert alert-danger'>
				$errorMessage
			  </div>";
	}
}
?>

		<div class="user_details column">
			<a href="<?php echo $username['username']; ?>"><img src="<?php echo $username['profile_pic']?>"></a>	
			
			<div class= "user_details_left_right">
			
				<a href="<?php echo $username['username']; ?>">
					<?php 
						echo $username['first_name'] . " " . $username['last_name'] . "<br>"; 
					?>
				</a>
				
				<?php	
					echo "Posts: " . $username['num_posts'] . "<br>" . "Likes: " . $username['num_likes'];
				?>
			</div>	
		</div>

		<div class="main_column column">
			<form class="post_form" action="index.php" method="POST" enctype="multipart/form-data">
				<input type="file" name="fileToUpload" id="fileToUpload">
				<textarea name="post_text" id="post_text" placeholder="Post your thoughts..."></textarea>
				<input type="submit" name="post" id="post_button" value="Post">
				<hr>
			</form>

			<div class="post_area"></div>
			<img class="loading_1" id="loading" src="Assets/Images/icons/loading.gif">
		</div>

		<div class="user_details column">
			
			<h4>Popular Word</h4>

			<div class="trends">
				<?php
				$query = $con->prepare("SELECT * FROM trends ORDER BY hits LIMIT 9");
				$query->execute();

				foreach ($query as $row) {
					

					$word = $row['title'];
					$word_dot = (strlen($word) >= 14) ? "..." : "";

					$trimmed_word = str_split($word, 14);
					$trimmed_word = $trimmed_word[0];

					echo "<div style='padding:1px'>";
					echo $trimmed_word . $word_dot;
					echo "<br></div>";
				}

				?>
			</div>		

		</div>

		<script>
			var userLoggedIn = '<?php echo $userLoggedIn; ?>';

			$(document).ready(function(){
				$('#loading').show();


				//Original ajax request for loading first posts
				$.ajax({
					url: "includes/handlers/ajax_load_posts.php",
					type:"POST",
					data:"page=1&userLoggedIn=" + userLoggedIn,
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
							url: "includes/handlers/ajax_load_posts.php",
							type: "POST",
							data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
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