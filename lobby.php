<?php
	session_start();
	$stmt = new PDO("mysql:host=localhost;dbname=speedCode", "root","");


	if(isset($_POST["function"]) || isset($_GET["function"]))
	{
		$selector = $_POST["function"] ?? $_GET["function"];
		
		switch ($selector)
		{
			case "connect_to_lobby":
				if(isset($_SESSION["id"]))
				{
					if(empty($stmt->query("SELECT * FROM lobby WHERE id_utilisateur = ".$_SESSION["id"])->fetch()))
					{	
						if($stmt->query("INSERT INTO lobby(id,id_utilisateur,ready) VALUES(NULL, '".$_SESSION["id"]."', 0)"))
						{
							echo "<p class='info-msg'>Vous etes connectés, en attente d'autre joueurs...</p>";
						}
						else
						{
							echo "<p class='info-msg'>Une erreure est survenue</p>";
						}		
					}
					else
					{
						echo "<p class='info-msg'>Vous etes dans le lobby en attente d'autre joueurs...</p>";
					}				
				}
			break;
			

			case "lobby_user_ready":
				if(isset($_SESSION["id"]))
				{
					$res = $stmt->query("UPDATE lobby SET ready = ".$_POST["is_ready"]." WHERE id_utilisateur = '".$_SESSION["id"]."'");
				}
				else
				{
					echo "<p class='info-msg'>Une erreure est survenue</p>";
				}
			break;
			
			
			case "fill_lobby":
				if(isset($_SESSION["login"]))
				{
					if(isset($_POST["login"]))
					{					
						$usr_group = $stmt->query("SELECT groupe FROM utilisateurs WHERE id =".$_SESSION["id"])->fetch()[0];
						$data = $stmt->query("SELECT *, login FROM lobby INNER JOIN utilisateurs ON lobby.id_utilisateur = utilisateurs.id 
						WHERE utilisateurs.groupe = ".$usr_group)->fetchAll(PDO::FETCH_ASSOC);
						echo json_encode($data);
					}
				}
				else
				{
					echo $selector." error";
				}
				
			break;
			
			
			case "is_user_ready":
				if(isset($_POST["login"]))
				{
					echo $stmt->query("SELECT ready FROM lobby INNER JOIN utilisateurs
					ON lobby.id_utilisateur = utilisateurs.id WHERE utilisateurs.login = '".$_POST["login"]."'")->fetch()[0];
				}
				else
				{
					echo "login not found";
				}
			break;
			
			
			case "is_lobby_ready":
				$res = $stmt->query("SELECT * FROM lobby")->fetchAll(PDO::FETCH_ASSOC);
				$return_value = 0;
				for($i=0;$i<count($res);$i++)
				{
					if($res[$i]["ready"] == "0")
					{
						echo $return_value;
						return 0;
					}
				}
				$return_value = 1;
				
			
				$stmt->query("INSERT INTO working(`id`,`id_utilisateur`,`id_exercice`) 
				VALUES(NULL, ".$_SESSION["id"]." ,0)");
			
				if($return_value)
				{
					$stmt->query("DELETE FROM lobby WHERE id_utilisateur =".$_SESSION["id"]);
				}
				echo $return_value;
				
			break;
			
			case "is_group_ready":
				if(isset($_SESSION["id"]))
				{
					$current_exo = $stmt->query("SELECT id_exercice FROM working WHERE id_utilisateur = 
					".$_SESSION["id"])->fetch()[0];
					$group_exo = $stmt->query("SELECT id_exercice FROM working INNER JOIN utilisateurs ON 
					id_utilisateur = utilisateurs.id WHERE utilisateurs.groupe = ".$_SESSION["groupe"]." AND 
					utilisateurs.id != ".$_SESSION["id"])->fetchAll(PDO::FETCH_ASSOC);
					foreach($group_exo as $exo)
					{
						if($exo["id_exercice"] < $current_exo)
						{
							echo json_encode(["team_ready"=>false]);
							return 0;
							break;
						}
					}
					
					echo json_encode(["team_ready"=>true, "exo"=>$current_exo]);
				}
			break;
			
			default :
				echo "No function found for : ".$selector."";			
		}
		
	}


?>
