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
					$response = false;

					$_POST["login"] = htmlspecialchars($_POST["login"]);
					$_POST["email"] = htmlspecialchars($_POST["email"]);
					
					$usr = $stmt->query("SELECT * FROM utilisateurs WHERE login = '".$_POST["login"]."'")->fetch(PDO::FETCH_ASSOC);
					
					if(empty($usr))
					{
						$stmt->query("INSERT INTO utilisateurs (id, login, date, email, groupe)
						VALUES(NULL, '".$_POST["login"]."', CURRENT_TIMESTAMP(), '".$_POST["email"]."', 0)");
						$_SESSION["id"] = $stmt->query("SELECT id FROM utilisateurs WHERE login = '".$_POST["login"]."'")->fetch()[0];
						$_SESSION["login"] = $usr["login"];
					}
					else
					{
						$_SESSION["id"] = $usr["id"];
						$_SESSION["login"] = $usr["login"];
					}
					
					if($_POST["group"] > 99)
					{
						$cur_group = $stmt->query("SELECT id_utilisateur FROM working INNER JOIN utilisateurs ON id_utilisateur = utilisateurs.id WHERE 
						utilisateurs.groupe =".$_POST["group"])->fetchAll();
						if(empty($cur_group))
						{
							$stmt->query("UPDATE utilisateurs SET groupe = ".$_POST["group"]." WHERE id = ".$_SESSION["id"]);
							$_SESSION["groupe"] = $_POST["group"];							
						}
						else
						{
							$response = "group_is_playing";
						}
					}
					else
					{
						$stmt->query("INSERT INTO working(id,id_utilisateur, id_exercice) VALUES(NULL,".$_SESSION["id"].", 0)");
						$response = true;
					}
					
					echo json_encode(["login"=>$_SESSION["login"], "response"=>$response]);
				}
				else
				{
					echo $selector." error.";
				}
				
			break;
			
			case "is_logged":
				if(isset($_SESSION["id"]))
				{ 
					$res = $stmt->query("SELECT id_exercice FROM working WHERE id_utilisateur =".$_SESSION["id"])->fetch();
					if(!empty($res))
					{
						if(isset($_SESSION["groupe"]))
						{
							if(isset($_SESSION["end_exo"]))
							{
								$exo = $stmt->query("SELECT * FROM exercice_problem WHERE id = 
									".($_SESSION["end_exo"]+1))->fetch(PDO::FETCH_ASSOC);
								if(!empty($exo))
								{
									$current_group_exercice = $stmt->query("SELECT id_exercice FROM working INNER JOIN 
										utilisateurs ON id_utilisateur = utilisateurs.id WHERE utilisateurs.groupe = 
											".$_SESSION["groupe"]." AND utilisateurs.id != 
												".$_SESSION["id"])->fetchAll(PDO::FETCH_ASSOC);
									$check = true;
									foreach($current_group_exercice as $exo_group)
									{
										if($exo_group["id_exercice"] < $res[0]+1)
										{
											$check = false;
											break;
										}
									}
									
									if($check)
									{
										echo json_encode(["response"=>"next_exo", "exo"=>$res[0]+1]);									
										unset($_SESSION["end_exo"]);								
									}
									else
									{
										echo json_encode(["response"=>"wait_team", "exo"=>$res[0]+1]);
									}									
								}
								else
								{
									unset($_SESSION["end_exo"]);
									unset($_SESSION["exo_started"]);
									$stmt->query("DELETE FROM working WHERE id_utilisateur = ".$_SESSION["id"]);
									echo json_encode(["response"=>"leaderboard"]);
								}
								return 0;
							}
						}

						if(isset($_SESSION["end_exo"]))
						{
							$exo = $stmt->query("SELECT * FROM exercice_problem WHERE id = ".($_SESSION["end_exo"]+1))->fetch(PDO::FETCH_ASSOC);
							if(!empty($exo))
							{
								echo json_encode(["response"=>"next_exo", "exo"=>$exo["id"]]);
								unset($_SESSION["end_exo"]);								
							}
							else
							{
								unset($_SESSION["end_exo"]);
								unset($_SESSION["exo_started"]);
								$stmt->query("DELETE FROM working WHERE id_utilisateur = ".$_SESSION["id"]);
								echo json_encode(["response"=>"leaderboard"]);
							}
							
						}
						else
						{
							echo json_encode(["exo"=>$stmt->query("SELECT * FROM exercice_problem WHERE id = ".$res[0])->fetch(PDO::FETCH_ASSOC), "response"=>"exo"]);
						}
					}
					else
					{
						echo json_encode(["response"=>true, "login"=>$_SESSION["login"]]);						
					}
					
				}
				else
				{
					echo json_encode(["response"=>false, "login"=>false]);
				}
			break;
			
			case "is_group_id_available":
				if(isset($_POST["id"]))
				{
					$res = $stmt->query("SELECT groupe FROM utilisateurs WHERE groupe = ".$_POST["id"])->fetchAll();
					if(empty($res))
					{
						echo $_POST["id"];
					}
					else
					{
						echo "false";
					}
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