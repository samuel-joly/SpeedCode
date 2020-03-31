$(document).ready(function(){
	const player_nb = 1;
	
	$.ajax({
		url:"user.php",
		type:"get",
		data:{
			"function" : "is_logged"
		},
		success:function(data)
		{
			data = JSON.parse(data);
			switch(data["response"])
			{
				case true:
					go_to_lobby(data["login"]);				
				break;
				
				case "exo":
					display_exercice(data["exo"]);
				break;
				
				case "next_exo":
					set_level(data["exo"]);
				break;
				
				case "leaderboard":
					display_leaderboard();
				break;
				
				case "wait_team":
					console.log("ATTEND");
					$.ajax({
						url:"exercice.php",
						type:"post",
						data:{"function":"set_level","id":data["exo"]},
						
					});
					
					var wait_team = setInterval(function(){
						$.ajax({
							url:"lobby.php",
							type:"get",
							data:{"function":"is_group_ready"},
							success:function(data)
							{
								console.log(data);
								data = JSON.parse(data);
								if(data["team_ready"] == true)
								{
									set_level(data["exo"]);
									clearInterval(wait_team);
								}
							}
							
						})
						
					}, 1000);
				break;
				
				default:
					$("#connect-form").css("display","block");				
				break;
			}
		}
	});
	
	$("#connect").click(function(e){
		e.preventDefault();
		
		if($("#login").val() == "" || (document.getElementsByName("party_type")[1].checked == false && document.getElementsByName("party_type")[0].checked == false))
		{
			return 0;
		}
		
		if(document.getElementsByName("party_type")[1].checked)
		{
			if(document.getElementsByName("host_zone")[1].checked)
			{
				if($("id_host").val() == "")
				{
					$("main").append("<p>Empty ID</p>");
					return 0;
				}
				else
				{
					group_id = $("#id_host").val();
				}
			}
		}
		else if(document.getElementsByName("party_type")[0].checked)
		{
			group_id = false;
		}
		else
		{
			return 0;
		}
		
		
		$.ajax(
		{
			type:"post",
			url:"user.php",
			data:{ "function" : "sign_up", login: $("#login").val(), email:$("#mail").val(), group:group_id },
			success:function(data){
				data = JSON.parse(data);
				$("#connect-form").css("display","none");
				if(data["response"] == false)
				{
					go_to_lobby(data["login"]);					
				}
				else if(data["response"] == "group_is_playing")
				{
					$("main").append("<p style='color:red'>Your group is already in game.</p>");
				}
				else
				{
					next_level(1);
				}
			}
		});
	});
	
	$(document.getElementsByName("party_type")[1]).click(function(){
		
		if($("#party_zone").css("display") == "none")
		{
			$("#party_zone").css("display","flex");
		}
	});
	
	$(document.getElementsByName("party_type")[0]).click(function(){
		$("#party_zone").css("display","none");
	});
	
	group_id = Math.floor(Math.random()*100)+100;
	$(document.getElementsByName("host_zone")[0]).click(function(){
		is_group_id_available(group_id);
		
		if($("#group_id").css("display") == "none")
		{
			$("#group_id").css("display","block");		
		}
		
		if($("#host_id").css("display") != "none")
		{
			$("#host_id").css("display","none");
		}
		
	});
	
	$(document.getElementsByName("host_zone")[1]).click(function(){
		if($("#group_id").css("display") != "none")
		{
			$("#group_id").css("display","none");
		}
		
		if($("#host_id").css("display") == "none")
		{
			$("#host_id").css("display","block");
		}
	});
	
	var lobby_wait;
	function go_to_lobby(login)
	{
		lobby = $("<div id='lobby'/></div>");
		
		userTag = $("<div class='usrBox'> <p class='usrTag' id='curUsr'>"+login+"</p><img src='assets/ready.png'class='readyLogo'/></div>");
		lobby.append(userTag);
		
		ready = $("<button id='readyBtn'>ready</button>");
		
		$("main").append(lobby);
		$("main").append(ready);
		
		connect_lobby(login)
		fill_lobby();
		lobby_wait = setInterval(fill_lobby,500);
		
		$.ajax({
			type:"post",
			url:"lobby.php",
			data : {"function" : "is_user_ready", "login":login},
			success:function(data)
			{
				if(data == 1)
				{
					$("#readyBtn").text("Not Ready");	
					$("#readyBtn").css("background","#ec9f2b");
					$("#readyBtn").css("border-color","#ec2b2b");
					ready = 1;
				}
				else
				{
					$("#readyBtn").text("Ready");
					$("#readyBtn").css("background","#52ec2b");
					$("#readyBtn").css("border-color","#b0fb9d");
					ready = 0;
				}
			}
			
		});
		
		$("#readyBtn").click(function() {
			if($(this).text() == "Ready")
			{
				$(this).text("Not Ready");	
				$(this).css("background","#ec9f2b");
				$(this).css("border-color","#ec2b2b");
				ready = 1;
			}
			else
			{
				$(this).text("Ready");
				$(this).css("background","#52ec2b");
				$(this).css("border-color","#b0fb9d");
				ready = 0;
			}
			
			$.ajax({
				type:"post",
				url:"lobby.php",
				data:{ "function":"lobby_user_ready", "is_ready":ready },
				success:function(data)
				{
					if(ready == 1)
					{
						$("#curUsr").next().css("opacity","1");
					}
					else
					{
						$("#curUsr").next().css("opacity","0");
					}
				}
				
			});
			
		});
	}
	
	function connect_lobby(login)
	{
		$.ajax({
			type:"post",
			url:"lobby.php",
			data:{"function":"connect_to_lobby" , "login":login},
			success:function(data){
				$("main").prepend(data);
			}
		});
	}
	
	function fill_lobby()
	{
		parent = $(".usrBox");
		childNames = [];
		
		for(i=0;i<parent.length;i++)
		{
			childs = $(parent[i]).children();
			childNames.push(childs[0].innerHTML);
		}
	
		$.ajax({
			type:"post",
			url:"lobby.php",
			data:{
				"function": "fill_lobby",
				login:childNames
			},
			success:function(data){
				data = JSON.parse(data);
				for(usr of data)
				{
					if(usr["login"] != "")
					{
						peer = childNames.indexOf(usr["login"]);
						userZone = $("<div class='usrBox'></div>");
						if( peer == -1)
						{
							userTag = $("<p class='usrTag'></p>");
							userTag.text(usr["login"]);
							$("#lobby").append(userZone.append(userTag));
							userZone.append($("<img src='assets/ready.png' class='readyLogo'/>"));
							
							if(usr["ready"] == 1)
							{
								$(userTag.next()).css("opacity","1");
							}
							else
							{
								$(userTag.next()).css("opacity","0");
							}
						}
						else
						{
							if(usr["ready"] == 1)
							{
								$($(parent[peer]).children()[1]).css("opacity", "1");				
							}
							else
							{
								$($(parent[peer]).children()[1]).css("opacity","0");
							}
						}
					}
				}
				ready_to_play();
			}
		});
	}
	
	function ready_to_play()
	{
		if ($(".usrBox").length >= player_nb)
		{
			$.ajax({
				type:"get",
				url:"lobby.php",
				data:{ "function" : "is_lobby_ready"},
				success:function(data)
				{
					if(data == 1)
					{
						clearInterval(lobby_wait);
						info = $("<h1 id='lobby-ready'></h1>");
						$("main").append(info);
						var count = 5;
						ready_wait = setInterval(function(){
							if(count == 0)
							{
								clearInterval(ready_wait);
								$("#lobby").remove();
								$(".info-msg").remove();
								$("#readyBtn").remove();
								$("#lobby-ready").remove();
								
								set_level(1);
							}
							$("#lobby-ready").text("Début dans "+count+"s");
							count--;
						}, 1000);
					}
				}			
			});
		}
	}
	
	function set_level(level_id)
	{
		$.ajax({
			type:"post",
			url:"exercice.php",
			data:{ "function" : "set_level", "id":level_id},
			success:function(data){
				data = JSON.parse(data);
				if(data["exo_end"] == false)
				{
					display_exercice(data["exo"]);					
				}
				else
				{
					display_leaderboard();
				}
			}
		});
	}
	
	function display_exercice(exercice)
	{
		
		$("#exo").remove();
		exo = $("<form method='post' action='' enctype='multipart/form-data' id='exo'></form>");
		exo.append("<table id='exo-title-zone'>"+
						"<tr> <td>"+exercice["titre"]+"</td>"+
						"<td id='exo-time'>"+exercice["time"]+" min left</td>"+
						"<td>"+exercice["language"]+"</td>"+
						"</tr>"+
					"</table>");
		exo.append("<p id='exo-desc'>"+exercice["description"]+"</p>");
		files = "<a href='"+exercice["zip"]+"' id='exo-file-img'><p>Download exercice</p><img src='assets/zip.png'/></a>";
		exo.append(files);
		file_zone  = $("<span></span>");
		file_input = $("<input type='file' name='exo-file' id='zip'/> ");
		
		send_btn = $("<input type ='submit' name='send-exo' id='exo-send' value='Envoyer'/>");
		
		file_zone.append(file_input);
		exo.append(file_zone);
		exo.append(send_btn);
		
		$("main").append(exo);
		
		exo_timer = setInterval(function(){
			
			$.ajax({
				type:"post",
				url:"exercice.php",
				data:{"function":"is_exo_started"},
				success:function(data){
					data = JSON.parse(data);
					if(data["response"] != "end")
					{
						$("#exo-time").text(data["time"]-1+" min left");						
					}
					else
					{
						$("main").append("<h3 id='end-exo'>End of the exercice, send what you made and go to next level</h3>");						
						clearInterval(exo_timer);
					}
				}
				
			});
		}, 100)
	}
	
	function is_group_id_available(group_id)
	{
		$.ajax({
			type:"post",
			url:"user.php",
			data:{"function":"is_group_id_available", "id":group_id},
			success:function(data){
				if(data != "false")
				{
					$("#group_id").text(data);
				}
				else
				{
					is_group_id_available(Math.floor(Math.random()*100)+100);					
				}
			}		
		});		
	}
	
	function display_leaderboard()
	{
		$("main").children().remove();
	}
});