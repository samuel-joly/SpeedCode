<?php
	session_start();
	$stmt = new PDO("mysql:host=localhost;dbname=speedCode", "root","");
	
	if(isset($_POST["function"]) || isset($_GET["function"]))
	{
		$selector = $_POST["function"] ?? $_GET["function"];
		
		switch ($selector)
		{
			case "sign_up" :
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
				
			break;
			
			
			case "is_logged":
				if(isset($_SESSION["id"]))
				{
					$res = $stmt->query("SELECT id_exercice FROM working WHERE id_utilisateur =".$_SESSION["id"])->fetch();
					if(!empty($res))
					{
						if(isset($_SESSION["end_exo"]))
						{
							$exo = $stmt->query("SELECT * FROM exercice_problem WHERE id = ".($_SESSION["end_exo"]+1))->fetch(PDO::FETCH_ASSOC);
							if(!empty($exo))
							{
								echo json_encode(["response"=>"next_exo"]);
								unset($_SESSION["end_exo"]);								
							}
							else
							{
								echo json_encode(["response"=>"leaderboard"]);
							}
							
						}
						else
						{
							echo json_encode(["exo"=>$stmt->query("SELECT * FROM exercice_problem WHERE id = ".$res[0])->fetch(PDO::FETCH_ASSOC), "response"=>"exo"]);
							
						}
						return 0;
					}
					
					echo json_encode(["response"=>true, "login"=>$_SESSION["login"]]);
				}
				else
				{
					echo json_encode(["response"=>false, "login"=>false]);
				}
			break;
			
			
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
						$data = $stmt->query("SELECT *, login FROM lobby INNER JOIN utilisateurs 
						ON lobby.id_utilisateur = utilisateurs.id")->fetchAll(PDO::FETCH_ASSOC);
						echo json_encode($data);
					}
				}
				else
				{
					echo "<p class='info-msg'>Une erreure est survenue</p>";
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
				$res = $stmt->query("SELECT * FROM lobby")->fetchAll();
				
				for($i=0;$i<count($res);$i++)
				{
					if($res[$i]["ready"] == "0")
					{
						echo 0;
						return 0;
					}
				}
				$stmt->query("DELETE FROM lobby");
				echo 1;
				
				for($i=0;$i<count($res);$i++)
				{
					$stmt->query("INSERT INTO working(`id`,`id_utilisateur`,`id_exercice`) 
					VALUES(NULL, ".$res[$i][1]." ,0)");
				}
				
			break;
			
			
			case "next_level":
				$id_exo = $stmt->query("SELECT id_exercice FROM working WHERE id_utilisateur = ".$_SESSION["id"])->fetch()[0];
				$id_exo++;
				$exo = $stmt->query("SELECT * FROM exercice_problem WHERE id = ".$id_exo)->fetch(PDO::FETCH_ASSOC);
				if(!empty($exo))
				{
					$stmt->query("UPDATE working SET id_exercice = ".$id_exo." WHERE id_utilisateur = ".$_SESSION["id"]);					
				}
				else
				{
					echo json_encode(["exo_end"=>true]);
					return 0;
				}
				
				echo json_encode($exo);
			break;
			
			case "is_exo_started":
				$id_exo = $stmt->query("SELECT id_exercice FROM working WHERE id_utilisateur = ".$_SESSION["id"])->fetch()[0];
				$time_exo = $stmt->query("SELECT time FROM exercice_problem WHERE id =".$id_exo)->fetch()[0];

				if(isset($_SESSION["exo_started"]))
				{
					$time_passed = time() - $_SESSION["exo_started"];
					$time_left = ($time_exo*60) - $time_passed;
					
					if($time_left <= 1)
					{
						unset($_SESSION["exo_started"]);
						echo json_encode(["response"=>"end"]);
					}
					else
					{
						echo json_encode(["time"=>round($time_left/60)]);						
					}
				}
				else
				{
					$_SESSION["exo_started"] = time();
					echo json_encode(["time"=>round($time_exo)]);
				}
			break;
			
			default :
				echo "No function found for : ".$selector."";
		}

	}
	else
	{
		echo "No POST / GET detected<br/>";
	}
	






?>