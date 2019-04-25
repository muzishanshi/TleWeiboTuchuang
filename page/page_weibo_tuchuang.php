<?php
/**
 * 前台微博图床
 */
define('PATH', dirname(dirname(__FILE__)).'/');
require_once(PATH . '../../../wp-blog-header.php');  
?>
<?php

try{
	$weibo_configs = get_settings('tle_weibo_tuchuang');
	$isMultiple="multiple";
	if($weibo_configs['tle_weibo_issave']=="y"){
		$isMultiple="";
	}
	?>
	<html>
	<head>
		<meta charset="UTF-8">
		<title>微博图床</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta name="format-detection" content="telephone=no">
		<meta name="renderer" content="webkit">
		<meta http-equiv="Cache-Control" content="no-siteapp"/>
		<meta name="author" content="同乐儿">
		<meta name="referrer" content="never" />
		<link rel="alternate icon" href="<?=$weibo_configs['tle_weiboprefix'];?>ecabade5ly1fxpiemcap1j200s00s744.jpg" type="image/png" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/layer/2.3/layer.js"></script>
	</head>
	<body>
	<div id="weibofile_webimg_container" onclick="weibofile_file.click()" style="margin:5px 0px;position: relative; border: 2px dashed #e2e2e2; background-image:url('<?=$weibo_configs['tle_webimgbg']?$weibo_configs['tle_webimgbg']:$weibo_configs['tle_weiboprefix']."ecabade5ly1fxp3dil4pxj21hc0u0wn1.jpg";?>'); text-align: center; cursor: pointer;height: 100%;">
		<p id="weibofile_webimg_upload" style="height: <?=$weibo_configs['tle_webimgheight']?$weibo_configs['tle_webimgheight']:"100";?>px;line-height:<?=$weibo_configs['tle_webimgheight']?$weibo_configs['tle_webimgheight']:"100";?>px;position: relative;font-size:20px; color:#d3d3d3;">微博图床</p> 
		<input type="file" id="weibofile_file" style="display:none" accept="image/*" <?=$isMultiple;?> /> 
		<input type="hidden" id="tle_weibo_issave" value="<?=$weibo_configs['tle_weibo_issave'];?>"/>
	</div>
	<script>
	var weibofile_webimgdiv = document.getElementById('weibofile_webimg_upload');
	var weibofile_file = document.getElementById('weibofile_file');
	weibofile_file.addEventListener('change', function() {
		inputFileHandler();
	}, false);
	function inputFileHandler(){
		var file = weibofile_file.files;
		upLoad(file);
	}
	function upLoad(file){
		var xhr = new XMLHttpRequest();
		var data;
		var upLoadFlag = true;
		if(upLoadFlag === false){
			layer.msg('正在上传中……请稍后……');
			return;
		}
		if(!file){
			layer.msg('不要上传图片了吗……');
			return;
		}
		data = new FormData();
		data.append('action', 'imageUpload');
		for (var i=0;i<file.length;i++){
			if(file[i] && file[i].type.indexOf('image') === -1){
				layer.msg('这不是一张图片……请重新选择……');
				return;
			}
			data.append('webimgupload['+i+']', file[i]);
		}
		xhr.open("POST", "<?=admin_url('options-general.php?page=tle-weibo-tuchuang&t=uploadWBTCByForeground');?>");
		xhr.send(data);
		upLoadFlag = false;
		weibofile_webimgdiv.innerHTML = '上传中……';
		xhr.onreadystatechange = function(){
			if(xhr.readyState == 4 && xhr.status == 200){
				upLoadFlag = true;
				var data=JSON.parse(xhr.responseText);
				if(data.status=="noset"){
					weibofile_webimgdiv.innerHTML = data.msg;
				}else if(data.status=="disable"){
					weibofile_webimgdiv.innerHTML = data.msg;
				}else if(data.status=="ok"){
					weibofile_webimgdiv.innerHTML = '微博图床';
					layer.confirm('<small><font color="green">'+data.msg+'<br />'+data.hrefs+'</font></small><textarea style="width:100%;margin: 0 auto;" rows="2" onfocus="this.select();">'+data.codes+'</textarea>', {
						btn: ['关闭']
					},function(index){
						layer.close(index);
					});
					var urls=data.urls.split("<br />");
					document.getElementById('weibofile_webimg_container').style.backgroundImage = "url("+urls[0]+")";
				}
			}
		}
	}
	</script>
	</body>
	</html>
	<?php
}catch(Exception $e){}
?>