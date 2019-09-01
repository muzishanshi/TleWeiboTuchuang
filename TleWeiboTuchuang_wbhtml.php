<?php
$weibo_configs = get_settings('tle_weibo_tuchuang');
$isMultiple="multiple";
if($weibo_configs['tle_weibo_issave']=="y"){
	$isMultiple="";
}
echo '<script>var tle_weibo_tuchuang_post_url="' . admin_url('options-general.php?page=tle-weibo-tuchuang&t=uploadWBTC') . '";</script>';
?>
<div id="tle_weibo_tuchuang_post" style="width:auto;height:100px;border:3px dashed silver;line-height:100px; text-align:center; font-size:20px; color:#d3d3d3;cursor:pointer;">点击此区域上传图片</div>
<input type="file" <?=$isMultiple;?> id="tle_weibo_tuchuang_input" style="position: absolute;display: block;top:0;left:0;bottom:0;right:0;opacity: 0;-moz-opacity: 0;filter:alpha(opacity=0);cursor:pointer;" />
<script>
/*$(function(){*/
	var input = document.getElementById('tle_weibo_tuchuang_input');
	/*
	var div = document.getElementById('tle_weibo_tuchuang_post');
	document.ondragenter = document.ondrop = document.ondragover = function(e){
		e.preventDefault();
		div.style.display = 'block';
	}
	div.ondragenter = function(e){
		div.innerHTML = '松开开始上传';
		e.preventDefault();
	}
	div.ondragleave = function(e){
		div.innerHTML = '离开取消上传';
		e.preventDefault();
	}
	div.ondragover = function(e){
		e.preventDefault();
	}
	var dropHandler = function(e){
		var file;
		e.preventDefault();
		file = e.dataTransfer.files && e.dataTransfer.files;
		upLoad(file);
	}
	document.body.addEventListener('drop', function(e) {
		dropHandler(e);
	}, false);
	*/
	function upLoad(file){
		var xhr = new XMLHttpRequest();
		var data;
		var upLoadFlag = true;
		if(upLoadFlag === false){
			alert('正在上传中……请稍后……');
			return;
		}
		if(!file){
			alert('不选择图片上传了吗……');
			return;
		}
		data = new FormData();
		for (var i=0;i<file.length;i++){
			if(file[i] && file[i].type.indexOf('image') === -1){
				alert('这不是一张图片……请重新选择……');
				return;
			}
			data.append('tle_weibo_tuchuang['+i+']', file[i]);
		}
		xhr.open("POST", tle_weibo_tuchuang_post_url);
		xhr.send(data);
		upLoadFlag = false;
		document.getElementById('tle_weibo_tuchuang_post').innerHTML = '正在上传中……请稍后……';
		xhr.onreadystatechange = function(){
			if(xhr.readyState == 4 && xhr.status == 200){
				upLoadFlag = true;
				document.getElementById('tle_weibo_tuchuang_post').innerHTML = '点击此区域上传图片';
				tinyMCE.activeEditor.execCommand('mceInsertContent', 0, "\n"+xhr.responseText+"\n");
			}
		}
	}
	
	function inputFileHandler(){
		var file = input.files;
		upLoad(file);
	}

	input.addEventListener('change', function() {
		inputFileHandler();
	}, false);
/*});*/
</script>