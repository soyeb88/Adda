<?php
include("includes/header.php");

if(isset($_GET['id'])){
	$id = $_GET['id'];
}
else{
	$id = 0;
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

	<div class="posts_area">
		<?php
			$post = new Post($con ,$userLoggedIn);
			$post->getSinglePost($id);
		?>
	</div>

</div>