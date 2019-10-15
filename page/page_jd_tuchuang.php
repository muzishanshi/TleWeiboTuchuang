<?php
/**
 * 前台京东图床
 */
define('PATH', dirname(dirname(__FILE__)).'/');
require_once(PATH . '../../../wp-blog-header.php');  
?>
<?php
$jd_configs = get_settings('tle_weibo_tuchuang');
try{
	?>
	<html>
	<head>
		<meta charset="UTF-8">
		<title>京东图床</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta name="format-detection" content="telephone=no">
		<meta name="renderer" content="webkit">
		<meta http-equiv="Cache-Control" content="no-siteapp"/>
		<meta name="author" content="同乐儿">
		<meta name="referrer" content="never" />
		<link rel="alternate icon" href="<?=$jd_configs['tle_jdprefix'];?>HTB1RdVVVbvpK1RjSZFq763XUVXae.png" type="image/png" />
		<script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js"></script>
		<script src="https://www.tongleer.com/api/web/include/layui/layui.js"></script>
	</head>
	<body>
	<div id="jdfile_webimg_container" onclick="jdfile_file.click()" style="margin:5px 0px;position: relative; border: 2px dashed #e2e2e2; background-image:url('<?=$instance['tle_webjdimgbg']?$instance['tle_webjdimgbg']:"https://ae01.alicdn.com/kf/HTB1jpKrVXzqK1RjSZFC762bxVXa1.png";?>'); text-align: center; cursor: pointer;height: 100%;">
		<p id="jdfile_webimg_upload" style="height: <?=$instance['tle_webjdimgheight']?$instance['tle_webjdimgheight']:"100";?>px;line-height:<?=$instance['tle_webjdimgheight']?$instance['tle_webjdimgheight']:"100";?>px;position: relative;font-size:20px; color:#d3d3d3;">京东图床</p> 
		<input type="file" id="jdfile_file" style="display:none" accept="image/*" multiple /> 
	</div>
	<script>
	var tle_jdprefix="<?=$jd_configs['tle_jdprefix'];?>";
	var jdfile_webimgdiv = document.getElementById('jdfile_webimg_upload');
	var jdfile_file = document.getElementById('jdfile_file');
	jdfile_file.addEventListener('change', function() {
		inputFileHandlerJd();
	}, false);
	function inputFileHandlerJd(){
		var file = jdfile_file.files;
		upLoadJd(file);
	}
	function upLoadJd(file){
		layui.use('layer', function(){
			var $ = layui.jquery, layer = layui.layer;
			var upLoadFlag = true;
			if(upLoadFlag === false){
				layer.msg('正在上传中……请稍后……');
				return;
			}
			if(!file){
				layer.msg('不要上传图片了吗……');
				return;
			}
			var j=0;for(var i = 0; i < file.length; i++){
				var imageData = new FormData();
				imageData.append("file", file[i]);
				upLoadFlag = false;
				jdfile_webimgdiv.innerHTML = '上传中'+file[i].name;
				$.ajax({
					url: "https://www.tongleer.com/api/web/?action=weiboimg&type=jd",
					type: 'POST',
					data: imageData,
					cache: false,
					contentType: false,
					processData: false,
					dataType: 'json',
					success: function (data) {
						upLoadFlag = true;
						jdfile_webimgdiv.innerHTML = '京东图床';
						var url=tle_jdprefix+data.data.title;
						if(data.code!=0){
							url=data.msg;
						}
						if(j==0){
							j++;
							layer.confirm('<font color="green"><small>上传结果</small><br /><small id="jdimgurl"><a href="'+url+'" target="_blank">'+url+'<br /></a></small></font><textarea id="jdimgcode" style="width:100%;margin: 0 auto;" rows="2" onfocus="this.select();"><img src="'+url+'" alt="'+data.data.title+'" /></textarea>', {
								btn: ['关闭']
							},function(index){
								layer.close(index);
							});
							document.getElementById('jdfile_webimg_container').style.backgroundImage = "url("+url+")";
						}else{
							$("#jdimgurl").append('<a href="'+url+'" target="_blank">'+url+'</a><br />');
							$("#jdimgcode").val($("#jdimgcode").val()+'<img src="'+url+'" alt="'+data.data.title+'" />');
						}
					},
					error: function (data) {
						upLoadFlag = true;
						jdfile_webimgdiv.innerHTML = '京东图床';
						if(j==0){
							j++;
							layer.confirm('<font color="green"><small>上传结果</small><br /><small id="jdimgurl">上传失败<br /></small></font><textarea id="jdimgcode" style="width:100%;margin: 0 auto;" rows="2" onfocus="this.select();">上传失败\r\n</textarea>', {
								btn: ['关闭']
							},function(index){
								layer.close(index);
							});
						}else{
							$("#jdimgurl").append("上传失败<br />");
							$("#jdimgcode").val("上传失败\r\n");
						}
					}
				});
			}
		});
	}
	</script>
	</body>
	</html>
	<?php
}catch(Exception $e){}
?>