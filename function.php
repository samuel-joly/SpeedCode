<?php
	session_start();
	$stmt = new PDO("mysql:host=localhost;dbname=speedCode","root","");
	
	
	if(isset($_POST["function"]) || isset($_GET["function"]))
	{
		$selector = $_POST["function"] ?? $_GET["function"];
		
		if($selector == "sign_up")
		{
			if(strlen($_POST["login"]) > 1 && strlen($_POST["email"]) > 4)
			{
				$usr = $stmt->query("SELECT * FROM utilisateurs WHERE login = '".$_POST["login"]."'")->fetch(PDO::FETCH_ASSOC);
				if(empty($usr))
				{
					$stmt->query("INSERT INTO utilisateurs (id, login, date, email) VALUES(NULL, '".$_POST["login"]."', CURRENT_TIMESTAMP(), '".$_POST["email"]."')");			
					$_SESSION["id"] = $stmt->query("SELECT id FROM utilisateurs WHERE login = '".$_POST["login"]."'")->fetch()[0];
					$_SESSION["login"] = $usr["login"];
					echo $_POST["login"];
				}
				else
				{
					$_SESSION["id"] = $usr["id"];
					$_SESSION["login"] = $usr["login"];
					echo $usr["login"];
				}
			}
			else
			{
				echo "false";
			}
			
			$selector = true;
		}
		else if($selector == "is_logged")
		{
			if(isset($_SESSION["id"]))
			{
				echo json_encode(["response"=>true, "login"=>$_SESSION["login"]]);
			}
			else
			{
				echo json_encode(["response"=>false, "login"=>false]);
			}
			
			$selector = true;
		}
		else if($selector == "connect_to_lobby")
		{
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
			$selector = true;
			
			
		}
		else if($selector == "lobby_user_ready")
		{
			if(isset($_SESSION["id"]))
			{
				$res = $stmt->query("UPDATE lobby SET ready = ".$_POST["is_ready"]." WHERE id_utilisateur = '".$_SESSION["id"]."'");
			}
			else
			{
				echo "<p class='info-msg'>Une erreure est survenue</p>";
			}
			
			$selector = true;
		}
		else if($selector == "fill_lobby")
		{
			if(isset($_SESSION["login"]))
			{
				if(isset($_POST["login"]))
				{					
					$data = $stmt->query("SELECT *, login FROM lobby INNER JOIN utilisateurs 
					ON lobby.id_utilisateur = utilisateurs.id")->fetchAll(PDO::FETCH_ASSOC);
					echo json_encode($data);
				}
			}
			else
			{
				echo "<p class='info-msg'>Une erreure est survenue</p>";
			}
			$selector = true;
		}
		
		if($selector != true)
		{
			echo "No function found for :<b>".$selector."</b>";
		}
	}
	else
	{
		echo "No POST / GET detected";
	}
	







?>