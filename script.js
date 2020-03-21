$(document).ready(function(){
	
	// Is user connected ? -- function.php <<is_logged>>
		// If yes go to lobby
		// If no show inscription menu
	$.ajax({
		url:"function.php",
		type:"get",
		data:{
			"function" : "is_logged"
		},
		success:function(data)
		{
			data = JSON.parse(data);
			if(data["response"])
			{
				go_to_lobby(data["login"]);
			}
			else
			{
				$("#connect-form").css("display","block");
			}
		}
	});
	
	$("#connect").click(function(e){
		e.preventDefault();
		$.ajax(
		{
			type:"POST",
			url:"function.php",
			data:{ "function" : "sign_up", login: $("#login").val(), email:$("#mail").val() },
			success:function(data){
				$("#connect-form").css("display","none");
				
				go_to_lobby(data);
			}
		});
	});
	
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
		lobby_wait = setInterval(fill_lobby,1000);
		
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
				url:"function.php",
				data:{ "function":"lobby_user_ready", "is_ready":ready },
				success:function(data)
				{
					if(ready == 1)
					{
						$("#curUsr").next().css("opacity","1");
						console.log($("#curUsr").next()[0]);
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
			url:"function.php",
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
			url:"function.php",
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
							userTag = $("<p class='userTag'></p>");
							userTag.text(usr["login"]);
							$("#lobby").append(userZone.append(userTag));
							userZone.append($("<img src='assets/ready.png' class='readyLogo'/>"));
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
			}
		});
	}
	
	function send_player_to_game()
	{
		$.ajax({
			type:"post",
			url:"send_to_game.php",
			success:function(data)
			{
				console.log(data);
			}
			
			
		});
	}
	
	
	
});