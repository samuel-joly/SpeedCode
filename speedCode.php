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
			$stmt = new PDO("mysql:host=localhost;dbname=speedCode", "root","");
			// var_dump($_SESSION);
			// unset($_SESSION["id"]);
			// unset($_SESSION["exo_started"]);
			// unset($_SESSION["groupe"]);
			// unset($_SESSION["login"]);
			// unset($_SESSION["exo-end"]);
			// unset($_SESSION["end_exo"]);
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
						<label for="mail">Email :</label>
						<input type="mail" id="mail"/>
					</div>
					
					<div id='party_type'>
						<span>
							<input type='radio' name="party_type" value='solo' id='solo' /><label for='solo'>Singleplayer</label>
						</span>
						
						<span>
							<input type='radio' name="party_type" value='multi' id='multi' /><label for='multi'>Multiplayer</label>
						</span>
					</div>
					
					<table id='party_zone'>
					
						<tr id='host-zone'>
							<td>
								<input type='radio' name='host_zone' id='host'/>
								<label for='host'>Host party N°</label>
							</td>
							
							<td id='group_id'></td>
						</tr>
					
						<tr id='join-zone'>
						
							<td>
								<input type='radio' name='host_zone' value='join' id='join'/>
								<label for='join'>Join party N°</label>
							</td>
							
							<td id='host_id'>
								<input type='text' id='id_host' maxlength='3' required/>
							</td>
						</tr>

					</table>
					
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
					echo "<p style='color:red; font-weight:bold;'>Wrong type, only .zip and .rar accepted</p>";
				}				
			}
		}
	}
?>	