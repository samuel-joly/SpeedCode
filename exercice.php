<?php

	session_start();
	$stmt = new PDO("mysql:host=localhost;dbname=speedCode", "root","");
	
	if(isset($_POST["function"]) || isset($_GET["function"]))
	{
		$selector = $_POST["function"] ?? $_GET["function"];
		
		switch ($selector)
		{
			
			case "set_level":
				if(isset($_SESSION["id"]))
				{
					$exo = $stmt->query("SELECT * FROM exercice_problem WHERE id = ".$_POST["id"])->fetch(PDO::FETCH_ASSOC);
					
					if(!empty($exo))
					{
						$stmt->query("UPDATE working SET id_exercice = ".$_POST["id"]." WHERE id_utilisateur = ".$_SESSION["id"]);
					}
					else
					{
						echo json_encode(["exo_end"=>true]);
						return 0;
					}
					
					echo json_encode(["exo_end"=>false , "exo"=>$exo]);					
				}
			break;
			
			
			case "is_exo_started":
				if(isset($_SESSION["id"]))
				{
					$id_exo = $stmt->query("SELECT id_exercice FROM working WHERE id_utilisateur = ".$_SESSION["id"])->fetch()[0];
					$time_exo = $stmt->query("SELECT time FROM exercice_problem WHERE id = ".$id_exo)->fetch()[0];

					if(isset($_SESSION["exo_started"]))
					{
						$time_passed = time() - $_SESSION["exo_started"];
						$time_left = ($time_exo*60) - $time_passed;
						
						if($time_left <= 2)
						{
							unset($_SESSION["exo_started"]);
							echo json_encode(["response"=>"end"]);
							$stmt->query("UPDATE working SET ");
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
				}
			break;
			
			default :
				echo "No function found for : ".$selector."";
		}
		
	}



?>