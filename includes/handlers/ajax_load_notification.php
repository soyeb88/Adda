<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Notification.php");

echo "hello";

$limit = 7;
$notification = new Notification($con, $_REQUEST['userLoggedIn']);

echo $notification->getNotification($_REQUEST, $limit);

?>