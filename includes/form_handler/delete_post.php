<?php
require '../../config/config.php';

	if(isset($_GET['post_id']))
		$post_id = $_GET['post_id'];

	if(isset($_POST['result'])){

		if(isset($_POST['result']) == 'true'){
			$query = $con->prepare("UPDATE posts SET deleted = 'yes' WHERE id = :post_id");

			$query->bindParam(":post_id", $post_id);
			$query->execute();
		}
	}
	

?>