<?php
/**
 * 前台阿里图床
 */
?>
<?php
$ali_configs = get_settings('tle_weibo_tuchuang');
try{
	?>
	<html>
	<head>
		<meta charset="UTF-8">
		<title>阿里图床</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta name="format-detection" content="telephone=no">
		<meta name="renderer" content="webkit">
		<meta http-equiv="Cache-Control" content="no-siteapp"/>
		<meta name="author" content="同乐儿">
		<meta name="referrer" content="never" />
		<link rel="alternate icon" href="<?=$ali_configs['tle_aliprefix'];?>HTB1RdVVVbvpK1RjSZFq763XUVXae.png" type="image/png" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/layer/2.3/layer.js"></script>
	</head>
	<body>
	<div id="alifile_webimg_container" onclick="alifile_file.click()" style="margin:5px 0px;position: relative; border: 2px dashed #e2e2e2; background-image:url('<?=$instance['tle_webaliimgbg']?$instance['tle_webaliimgbg']:$ali_configs['tle_aliprefix']."HTB1RdVVVbvpK1RjSZFq763XUVXae.png";?>'); text-align: center; cursor: pointer;height: 100%;">
		<p id="alifile_webimg_upload" style="height: <?=$instance['tle_webaliimgheight']?$instance['tle_webaliimgheight']:"100";?>px;line-height:<?=$instance['tle_webaliimgheight']?$instance['tle_webaliimgheight']:"100";?>px;position: relative;font-size:20px; color:#d3d3d3;">阿里图床</p> 
		<input type="file" id="alifile_file" style="display:none" accept="image/*" multiple /> 
	</div>
	<script>
	var tle_aliprefix="<?=$ali_configs['tle_aliprefix'];?>";
	var alifile_webimgdiv = document.getElementById('alifile_webimg_upload');
	var alifile_file = document.getElementById('alifile_file');
	alifile_file.addEventListener('change', function() {
		inputFileHandlerAli();
	}, false);
	function inputFileHandlerAli(){
		var file = alifile_file.files;
		upLoadAli(file);
	}
	function upLoadAli(file){
		var upLoadFlag = true;
		if(upLoadFlag === false){
			layer.msg('正在上传中……请稍后……');
			return;
		}
		if(!file){
			layer.msg('不要上传图片了吗……');
			return;
		}
		
		upLoadFlag = false;
		alifile_webimgdiv.innerHTML = '上传中……';
		var j=0;for(var i = 0; i < file.length; i++){
			var imageData = new FormData();
			imageData.append("file", file[i]);
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
					if(j==0){
						j++;
						layer.confirm('<font color="green"><small>上传结果</small><br /><small id="aliimgurl"><a href="'+url+'" target="_blank">'+url+'<br /></a></small></font><textarea id="aliimgcode" style="width:100%;margin: 0 auto;" rows="2" onfocus="this.select();"><img src="'+url+'" alt="'+data.data.title+'" /></textarea>', {
							btn: ['关闭']
						},function(index){
							layer.close(index);
						});
						document.getElementById('alifile_webimg_container').style.backgroundImage = "url("+url+")";
					}else{
						$("#aliimgurl").append('<a href="'+url+'" target="_blank">'+url+'</a><br />');
						$("#aliimgcode").val($("#aliimgcode").val()+'<img src="'+url+'" alt="'+data.data.title+'" />');
					}
				},
				error: function (data) {
					if(j==0){
						j++;
						layer.confirm('<font color="green"><small>上传结果</small><br /><small id="aliimgurl">上传失败<br /></small></font><textarea id="aliimgcode" style="width:100%;margin: 0 auto;" rows="2" onfocus="this.select();">上传失败\r\n</textarea>', {
							btn: ['关闭']
						},function(index){
							layer.close(index);
						});
					}else{
						$("#aliimgurl").append("上传失败<br />");
						$("#aliimgcode").val("上传失败\r\n");
					}
				}
			});
		}
		upLoadFlag = true;
		alifile_webimgdiv.innerHTML = '阿里图床';
	}
	</script>
	</body>
	</html>
	<?php
}catch(Exception $e){}
?>