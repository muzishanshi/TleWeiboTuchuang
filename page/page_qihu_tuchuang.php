<?php
/**
 * 前台360图床
 */
define('PATH', dirname(dirname(__FILE__)).'/');
require_once(PATH . '../../../wp-blog-header.php');  
?>
<?php
$weibo_configs = get_settings('tle_weibo_tuchuang');
$qihu_configs = get_settings('tle_weibo_tuchuang');
try{
	?>
	<html>
	<head>
		<meta charset="UTF-8">
		<title>360图床</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta name="format-detection" content="telephone=no">
		<meta name="renderer" content="webkit">
		<meta http-equiv="Cache-Control" content="no-siteapp"/>
		<meta name="author" content="同乐儿">
		<meta name="referrer" content="never" />
		<link rel="alternate icon" href="<?=$qihu_configs['tle_qihuprefix'];?>HTB1RdVVVbvpK1RjSZFq763XUVXae.png" type="image/png" />
		<?php
		if(function_exists("is_single")){
			if(@$weibo_configs['isEnableJQuery']=="y"){
				echo '<script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js"></script>';
			}
		}else{
			echo '<script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js"></script>';
		}
		?>
		<script src="https://www.tongleer.com/api/web/include/layui/layui.js"></script>
	</head>
	<body>
	<div id="qihufile_webimg_container" onclick="qihufile_file.click()" style="margin:5px 0px;position: relative; border: 2px dashed #e2e2e2; background-image:url('<?=$instance['tle_webqihuimgbg']?$instance['tle_webqihuimgbg']:"https://ae01.alicdn.com/kf/HTB1jpKrVXzqK1RjSZFC762bxVXa1.png";?>'); text-align: center; cursor: pointer;height: 100%;">
		<p id="qihufile_webimg_upload" style="height: <?=$instance['tle_webqihuimgheight']?$instance['tle_webqihuimgheight']:"100";?>px;line-height:<?=$instance['tle_webqihuimgheight']?$instance['tle_webqihuimgheight']:"100";?>px;position: relative;font-size:20px; color:#d3d3d3;">360图床</p> 
		<input type="file" id="qihufile_file" style="display:none" accept="image/*" multiple /> 
	</div>
	<script>
	var tle_qihuprefix="<?=$qihu_configs['tle_qihuprefix'];?>";
	var qihufile_webimgdiv = document.getElementById('qihufile_webimg_upload');
	var qihufile_file = document.getElementById('qihufile_file');
	qihufile_file.addEventListener('change', function() {
		inputFileHandlerQihu();
	}, false);
	function inputFileHandlerQihu(){
		var file = qihufile_file.files;
		upLoadQihu(file);
	}
	function upLoadQihu(file){
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
				qihufile_webimgdiv.innerHTML = '上传中'+file[i].name;
				$.ajax({
					url: "https://www.tongleer.com/api/web/?action=weiboimg&type=qihu",
					type: 'POST',
					data: imageData,
					cache: false,
					contentType: false,
					processData: false,
					dataType: 'json',
					success: function (data) {
						upLoadFlag = true;
						qihufile_webimgdiv.innerHTML = '360图床';
						var url=tle_qihuprefix+data.data.src.substring(data.data.src.lastIndexOf("/")+1);
						if(j==0){
							j++;
							layer.confirm('<font color="green"><small>上传结果</small><br /><small id="qihuimgurl"><a href="'+url+'" target="_blank">'+url+'<br /></a></small></font><textarea id="qihuimgcode" style="width:100%;margin: 0 auto;" rows="2" onfocus="this.select();"><img src="'+url+'" alt="'+data.data.title+'" /></textarea>', {
								btn: ['关闭']
							},function(index){
								layer.close(index);
							});
							document.getElementById('qihufile_webimg_container').style.backgroundImage = "url("+url+")";
						}else{
							$("#qihuimgurl").append('<a href="'+url+'" target="_blank">'+url+'</a><br />');
							$("#qihuimgcode").val($("#qihuimgcode").val()+'<img src="'+url+'" alt="'+data.data.title+'" />');
						}
					},
					error: function (data) {
						upLoadFlag = true;
						qihufile_webimgdiv.innerHTML = '360图床';
						if(j==0){
							j++;
							layer.confirm('<font color="green"><small>上传结果</small><br /><small id="qihuimgurl">上传失败<br /></small></font><textarea id="qihuimgcode" style="width:100%;margin: 0 auto;" rows="2" onfocus="this.select();">上传失败\r\n</textarea>', {
								btn: ['关闭']
							},function(index){
								layer.close(index);
							});
						}else{
							$("#qihuimgurl").append("上传失败<br />");
							$("#qihuimgcode").val("上传失败\r\n");
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