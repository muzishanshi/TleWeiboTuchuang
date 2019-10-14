<?php
$ali_configs = get_settings('tle_weibo_tuchuang');
?>
<div id="tle_imgpool_ali_post" style="width:auto;height:100px;border:3px dashed silver;line-height:100px; text-align:center; font-size:20px; color:#d3d3d3;cursor:pointer;">点击此区域上传图片</div>
<input type="file" multiple id="tle_imgpool_ali_input" style="position: absolute;display: block;top:0;left:0;bottom:0;right:0;opacity: 0;-moz-opacity: 0;filter:alpha(opacity=0);cursor:pointer;" />
<script>
/*$(function(){*/
	var tle_aliprefix="<?=$ali_configs["tle_aliprefix"];?>";
	var aliInput = document.getElementById('tle_imgpool_ali_input');
	/*
	var aliDiv = document.getElementById('tle_imgpool_ali_post');
	document.ondragenter = document.ondrop = document.ondragover = function(e){
		e.preventDefault();
		aliDiv.style.display = 'block';
	}
	aliDiv.ondragenter = function(e){
		aliDiv.innerHTML = '松开开始上传';
		e.preventDefault();
	}
	aliDiv.ondragleave = function(e){
		aliDiv.innerHTML = '离开取消上传';
		e.preventDefault();
	}
	aliDiv.ondragover = function(e){
		e.preventDefault();
	}
	var dropHandlerAli = function(e){
		var file;
		e.preventDefault();
		file = e.dataTransfer.files && e.dataTransfer.files;
		upLoadAli(file);
	}
	document.body.addEventListener('drop', function(e) {
		dropHandlerAli(e);
	}, false);
	*/
	function upLoadAli(file){
		var upLoadFlag = true;
		if(upLoadFlag === false){
			alert('正在上传中……请稍后……');
			return;
		}
		if(!file){
			alert('不选择图片上传了吗……');
			return;
		}
		for(var i = 0; i < file.length; i++){
			var imageData = new FormData();
			imageData.append("file", file[i]);
			tleImgpoolAliXmlHttp=new XMLHttpRequest();
			tleImgpoolAliXmlHttp.open("POST","https://www.tongleer.com/api/web/?action=weiboimg&type=ali",true);
			tleImgpoolAliXmlHttp.send(imageData);
			upLoadFlag = false;
			document.getElementById("tle_imgpool_ali_post").innerHTML="正在上传中"+file[i].name;
			tleImgpoolAliXmlHttp.onreadystatechange=function () {
				upLoadFlag = true;
				document.getElementById("tle_imgpool_ali_post").innerHTML="点击此区域上传图片";
				if (tleImgpoolAliXmlHttp.readyState ==4 && tleImgpoolAliXmlHttp.status ==200){
					var data=JSON.parse(tleImgpoolAliXmlHttp.responseText);
					var url=tle_aliprefix+data.data.src.substring(data.data.src.lastIndexOf("/")+1);
					tinyMCE.activeEditor.execCommand('mceInsertContent', 0, "\n<img src='"+url+"' alt='' />\n");
				}else{
					tinyMCE.activeEditor.execCommand('mceInsertContent', 0, "\n此图片上传失败：'"+data.code+"'\n");
				}
			}
			/*
			$.ajax({
				url: "https://www.tongleer.com/api/web/?action=weiboimg",
				type: 'POST',
				data: imageData,
				cache: false,
				contentType: false,
				processData: false,
				dataType: 'json',
				success: function (data) {
					var url=tle_aliprefix+data.data.src.substring(data.data.src.lastIndexOf("/")+1);
					tinyMCE.activeEditor.execCommand('mceInsertContent', 0, "\n<img src='"+url+"' alt='' />\n");
				},
				error: function (data) {
					tinyMCE.activeEditor.execCommand('mceInsertContent', 0, "\n此图片上传失败：'"+data.code+"'\n");
				}
			});
			*/
		}
	}
	
	var inputFileHandlerAli = function(){
		var file = aliInput.files;
		upLoadAli(file);
	}

	aliInput.addEventListener('change', function() {
		inputFileHandlerAli();
	}, false);
/*});*/
</script>