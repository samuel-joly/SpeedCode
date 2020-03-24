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
			<?php session_start(); ?>
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

<?php
	if(isset($_SESSION["id"]))
	{
		if(isset($_POST["send-exo"]))
		{
			if(!empty($_FILES["exo-file"]))
			{
				$stmt = new PDO("mysql:host=localhost;dbname=speedCode", "root","");
				$file = $_FILES["exo-file"];
				$type = pathinfo($file["name"], PATHINFO_EXTENSION);
				if($type == "rar" || $type == "zip")
				{
					$exo = $stmt->query("SELECT id_exercice FROM working WHERE id_utilisateur = ".$_SESSION["id"])->fetch()[0];
					$exo_folder = $stmt->query("SELECT reponse FROM exercice_problem WHERE id =".$exo)->fetch()[0];
					if(!file_exists($exo_folder))
					{
						mkdir($exo_folder);
					}
					
					$newName = $exo_folder.$_SESSION["id"].".".$type;
					if(!file_exists($newName))
					{
						unset($newName);
						$newName = $exo_folder.$_SESSION["id"].".".$type;
					}
					move_uploaded_file($file["tmp_name"], $newName);
					$_SESSION["end_exo"] = $exo;
					
					header("location:speedCode.php");
				}
				else
				{
					echo "Wrong type, only .zip and .rar accepted";
				}				
			}
		}
	}
?>	