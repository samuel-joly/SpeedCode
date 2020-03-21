<!DOCTYPE html>

<html lang="fr">
	<head>
		<title>Speed Code V1</title>
		<meta charset="utf-8"/>
		<link rel="stylesheet" type="text/css" href="doNotTouch.css"/>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		<script src="script.js"></script>
	</head>

	<body>
		<header>
			<?php session_start();
				// unset($_SESSION["id"]);
			?>
		</header>

		<main>
			<?php if(!isset($_SESSION["id"])) { ?>
				<form action="" method="post" id="connect-form">
					<div>
						<label for="login">Login :</label>
						<input type="text" id="login"/>
					</div>
					<div>
						<label for="mail">Email: </label>
						<input type="mail" id="mail"/>
					</div>
					
					<input type="submit" value="Commencer" id="connect"/>
				</form>	
			<?php } ?>
		</main>

		<footer>
		</footer>
	</body>
</html>



