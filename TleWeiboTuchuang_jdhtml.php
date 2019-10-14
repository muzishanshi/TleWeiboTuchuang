<?php
$jd_configs = get_settings('tle_weibo_tuchuang');
?>
<div id="tle_imgpool_jd_post" style="width:auto;height:100px;border:3px dashed silver;line-height:100px; text-align:center; font-size:20px; color:#d3d3d3;cursor:pointer;">点击此区域上传图片</div>
<input type="file" multiple id="tle_imgpool_jd_input" style="position: absolute;display: block;top:0;left:0;bottom:0;right:0;opacity: 0;-moz-opacity: 0;filter:alpha(opacity=0);cursor:pointer;" />
<script>
/*$(function(){*/
	var tle_jdprefix="<?=$jd_configs["tle_jdprefix"];?>";
	var jdInput = document.getElementById('tle_imgpool_jd_input');
	/*
	var jdDiv = document.getElementById('tle_imgpool_jd_post');
	document.ondragenter = document.ondrop = document.ondragover = function(e){
		e.preventDefault();
		jdDiv.style.display = 'block';
	}
	jdDiv.ondragenter = function(e){
		jdDiv.innerHTML = '松开开始上传';
		e.preventDefault();
	}
	jdDiv.ondragleave = function(e){
		jdDiv.innerHTML = '离开取消上传';
		e.preventDefault();
	}
	jdDiv.ondragover = function(e){
		e.preventDefault();
	}
	var dropHandlerJd = function(e){
		var file;
		e.preventDefault();
		file = e.dataTransfer.files && e.dataTransfer.files;
		upLoadJd(file);
	}
	document.body.addEventListener('drop', function(e) {
		dropHandlerJd(e);
	}, false);
	*/
	function upLoadJd(file){
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
			tleImgpoolJdXmlHttp=new XMLHttpRequest();
			tleImgpoolJdXmlHttp.open("POST","https://www.tongleer.com/api/web/?action=weiboimg&type=jd",true);
			tleImgpoolJdXmlHttp.send(imageData);
			upLoadFlag = false;
			document.getElementById("tle_imgpool_jd_post").innerHTML="正在上传中"+file[i].name;
			tleImgpoolJdXmlHttp.onreadystatechange=function () {
				upLoadFlag = true;
				document.getElementById("tle_imgpool_jd_post").innerHTML="点击此区域上传图片";
				if (tleImgpoolJdXmlHttp.readyState ==4 && tleImgpoolJdXmlHttp.status ==200){
					var data=JSON.parse(tleImgpoolJdXmlHttp.responseText);
					var url=tle_jdprefix+data.data.title;
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
					var url=tle_jdprefix+data.data.src.substring(data.data.src.lastIndexOf("/")+1);
					tinyMCE.activeEditor.execCommand('mceInsertContent', 0, "\n<img src='"+url+"' alt='' />\n");
				},
				error: function (data) {
					tinyMCE.activeEditor.execCommand('mceInsertContent', 0, "\n此图片上传失败：'"+data.code+"'\n");
				}
			});
			*/
		}
	}
	
	var inputFileHandlerJd = function(){
		var file = jdInput.files;
		upLoadJd(file);
	}

	jdInput.addEventListener('change', function() {
		inputFileHandlerJd();
	}, false);
/*});*/
</script>