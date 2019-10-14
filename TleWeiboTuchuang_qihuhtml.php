<?php
$qihu_configs = get_settings('tle_weibo_tuchuang');
?>
<div id="tle_imgpool_qihu_post" style="width:auto;height:100px;border:3px dashed silver;line-height:100px; text-align:center; font-size:20px; color:#d3d3d3;cursor:pointer;">点击此区域上传图片</div>
<input type="file" multiple id="tle_imgpool_qihu_input" style="position: absolute;display: block;top:0;left:0;bottom:0;right:0;opacity: 0;-moz-opacity: 0;filter:alpha(opacity=0);cursor:pointer;" />
<script>
/*$(function(){*/
	var tle_qihuprefix="<?=$qihu_configs["tle_qihuprefix"];?>";
	var qihuInput = document.getElementById('tle_imgpool_qihu_input');
	/*
	var qihuDiv = document.getElementById('tle_imgpool_qihu_post');
	document.ondragenter = document.ondrop = document.ondragover = function(e){
		e.preventDefault();
		qihuDiv.style.display = 'block';
	}
	qihuDiv.ondragenter = function(e){
		qihuDiv.innerHTML = '松开开始上传';
		e.preventDefault();
	}
	qihuDiv.ondragleave = function(e){
		qihuDiv.innerHTML = '离开取消上传';
		e.preventDefault();
	}
	qihuDiv.ondragover = function(e){
		e.preventDefault();
	}
	var dropHandlerQihu = function(e){
		var file;
		e.preventDefault();
		file = e.dataTransfer.files && e.dataTransfer.files;
		upLoadQihu(file);
	}
	document.body.addEventListener('drop', function(e) {
		dropHandlerQihu(e);
	}, false);
	*/
	function upLoadQihu(file){
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
			tleImgpoolQihuXmlHttp=new XMLHttpRequest();
			tleImgpoolQihuXmlHttp.open("POST","https://www.tongleer.com/api/web/?action=weiboimg&type=qihu",true);
			tleImgpoolQihuXmlHttp.send(imageData);
			upLoadFlag = false;
			document.getElementById("tle_imgpool_qihu_post").innerHTML="正在上传中"+file[i].name;
			tleImgpoolQihuXmlHttp.onreadystatechange=function () {
				upLoadFlag = true;
				document.getElementById("tle_imgpool_qihu_post").innerHTML="点击此区域上传图片";
				if (tleImgpoolQihuXmlHttp.readyState ==4 && tleImgpoolQihuXmlHttp.status ==200){
					var data=JSON.parse(tleImgpoolQihuXmlHttp.responseText);
					var url=tle_qihuprefix+data.data.src.substring(data.data.src.lastIndexOf("/")+1);
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
					var url=tle_qihuprefix+data.data.src.substring(data.data.src.lastIndexOf("/")+1);
					tinyMCE.activeEditor.execCommand('mceInsertContent', 0, "\n<img src='"+url+"' alt='' />\n");
				},
				error: function (data) {
					tinyMCE.activeEditor.execCommand('mceInsertContent', 0, "\n此图片上传失败：'"+data.code+"'\n");
				}
			});
			*/
		}
	}
	
	var inputFileHandlerQihu = function(){
		var file = qihuInput.files;
		upLoadQihu(file);
	}

	qihuInput.addEventListener('change', function() {
		inputFileHandlerQihu();
	}, false);
/*});*/
</script>