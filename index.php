<!DOCTYPE html>
<html lang="en">
<head>
	<?php include "includes/head.php"?>
</head>
<body>

	 <nav class="navbar navbar-inverse navbar-fixed-top">
        <?php include "includes/nav.php"?>
    </nav>
	
<div class="container">


	<div class="jumbotron">
		<h1 class="text-center"> <?php display_message(); ?></h1>
	</div>

<?php
    $sql = "SELECT * FROM users";
    $result = query($sql);
    confirm($result);
    $row = fetch_array($result);

    echo $row["username"];
?>
	
</div> <!--Container-->
</body>
</html>