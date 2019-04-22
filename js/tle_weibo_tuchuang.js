$(function(){
	$(".tle_weibo_tuchuang_convert_id").each(function(){
		var id=$(this).attr("id");
		$("#"+id).click( function () {
			$.post("admin.php?page=tle-weibo-tuchuang&t=updateWBTCLinks",{action:"updateWBTCLinks",postid:$(this).attr("data-id")},function(data){
				var data=JSON.parse(data);
				if(data.status=="noneconfig"){
					alert(data.msg);
				}
				window.location.reload();
			});
		});
	});
	$(".tle_weibo_tuchuang_local_id").each(function(){
		var id=$(this).attr("id");
		$("#"+id).click( function () {
			$.post("admin.php?page=tle-weibo-tuchuang&t=localWBTCLinks",{action:"localWBTCLinks",postid:$(this).attr("data-id")},function(data){
				window.location.reload();
			});
		});
	});
	$.post("admin.php?page=tle-weibo-tuchuang&t=updateWBTCVersion",{version:$("#versionCodeWBTC").attr("data-code")},function(data){
		$("#versionCodeWBTC").html(data);
	});
});