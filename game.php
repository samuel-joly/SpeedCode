<?php
	
	// include("game_function.php");
	session_start();
	$stmt = new PDO("mysql:host=localhost;dbname=speedCode", "root","");
	
	if(isset($_POST["function"]) || isset($_GET["function"]))
	{
		$selector = $_POST["function"] ?? $_GET["function"];
		
		switch ($selector)
		{
			case "get_score":
				$answer_file = $stmt->query("SELECT reponse FROM exercice_problem")->fetchAll(PDO::FETCH_ASSOC);
				$table = "<table id='scoreboard'>";
				if(isset($_SESSION["groupe"]))
				{
					$groupe = $stmt->query("SELECT id, login FROM utilisateurs WHERE groupe 
						=".$_SESSION["groupe"])->fetchAll(PDO::FETCH_ASSOC);
				}
				else
				{
					$groupe = [$stmt->query("SELECT id,login FROM utilisateurs WHERE id =".$_SESSION["id"])];
				}
				
				$table .= "<tr><th>Login</th>";
				$i = 1;
				foreach($answer_file as $file)
				{
					$table .= "<th>N°".$i."</th>";
					$i++;
				}
				$table .= "</tr>";
				foreach($groupe as $utilisateur)
				{
					$table .= "<tr>";
					$table .= "<td class='login_td'>".$utilisateur["login"]."</td>";
					foreach($answer_file as $file)
					{
						$table .= "<td><a href='".$file["reponse"].$utilisateur["id"].".rar'><img src='assets/zip.png' 
							class='scoreboard_img'/></a></td>";
					}
					$table .= "</tr>";
				}
				
				
				$table .= "</table>";
				echo $table;
			break;
		}
		
	}
?>