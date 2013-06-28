<?php
	
	/* Error Reporting */
	ini_set("display_errors", 1);
	error_reporting(E_ALL);
	
	/* Inlcude main class */
	include 'classes/main.php';
	
	/* Start Genus widht default directory */
	$genus = new Genus();

?>
<!doctype html>
<html lang="en-us">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width">
		<title><?php echo $genus->title; ?></title>
		<link rel="stylesheet/less" href="css/main.development.less">
		<!--[if lt IE 9]>
		<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<?php echo $genus->body; ?>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
		<script src="js/plugins.development.js"></script>
		<script src="js/main.development.js"></script>
	</body>
</html>