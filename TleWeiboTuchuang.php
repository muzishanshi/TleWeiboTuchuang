<?php
/* 
Plugin Name: TleWeiboTuchuang
Plugin URI: https://github.com/muzishanshi/TleWeiboTuchuang
Description:  TleWeiboTuchuang是微博图床修改版，因原插件api失效，所以开发此插件，并将微博授权方式改为微博小号，自动利用cookie上传，更加方便，在文章发布页面增加微博上传功能，使用微博作为图床，只需一个微博小号即可实现。
Version: 1.0.1
Author: 二呆
Author URI: http://www.tongleer.com
License: 
*/
add_action('submitpost_box', 'tle_weibo_tuchuang_post_box');
function tle_weibo_tuchuang_post_box(){
    add_meta_box('tle_weibo_tuchuang_div', __('微博图床'), 'tle_weibo_tuchuang_post_html', 'post', 'side');
}
function tle_weibo_tuchuang_post_html(){
	echo '<script>var tle_weibo_tuchuang_post_url="' . admin_url('options-general.php?page=tle-weibo-tuchuang&t=upload') . '";</script>';
   ?>
   <div id="tle_weibo_tuchuang_post" style="width:auto;height:100px;border:3px dashed silver;line-height:100px; text-align:center; font-size:20px; color:#d3d3d3;cursor:pointer;">将图片拖拽到此区域上传</div>
   <input type="file" id="tle_weibo_tuchuang_input" style="position: absolute;display: block;top:0;left:0;bottom:0;right:0;opacity: 0;-moz-opacity: 0;filter:alpha(opacity=0);cursor:pointer;" />
   <script>
   window.onload = function(){
	var div = document.getElementById('tle_weibo_tuchuang_post');
	var input = document.getElementById('tle_weibo_tuchuang_input');
	
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
		if(file && file.type.indexOf('image') === -1){
			alert('这不是一张图片……请重新选择……');
			return;
		}
		data = new FormData();
		data.append('tle_weibo_tuchuang', file);
		xhr.open("POST", tle_weibo_tuchuang_post_url);
        xhr.send(data);
		upLoadFlag = false;
		div.innerHTML = '正在上传中……请稍后……';
		xhr.onreadystatechange = function(){
			if(xhr.readyState == 4 && xhr.status == 200){
				upLoadFlag = true;
				div.innerHTML = '将图片拖拽到此区域上传';
				tinyMCE.activeEditor.execCommand('mceInsertContent', 0, "\n"+xhr.responseText+"\n");
			}
		}
    }
	
	var dropHandler = function(e){
		var file;
		e.preventDefault();
		file = e.dataTransfer.files && e.dataTransfer.files[0];
		upLoad(file);
	}
	var inputFileHandler = function(){
		var file = input.files[0];
		upLoad(file);
	}
	document.body.addEventListener('drop', function(e) {
		dropHandler(e);
	}, false);
	
	input.addEventListener('change', function() {
		inputFileHandler();
	}, false);
   }
   </script>
   <?php
}

if(isset($_GET['t'])){
    if($_GET['t'] == 'config'){
        update_option('tle_weibo_tuchuang', array('tle_weibouser' => $_REQUEST['tle_weibouser'], 'tle_weibopwd' => $_REQUEST['tle_weibopwd']));
    }
    
    if($_GET['t'] == 'upload'){
        $weibo_configs = get_settings('tle_weibo_tuchuang');
        if(!isset($weibo_configs['tle_weibouser']) || !isset($weibo_configs['tle_weibopwd'])){
            echo '请先配置微博小号';
        }else{
            if(!class_exists('Sinaupload')){
                require_once plugin_dir_path(__FILE__) . 'Sinaupload.php';
            }
            $Sinaupload=new Sinaupload('');
			$cookie=$Sinaupload->login($weibo_configs['tle_weibouser'],$weibo_configs['tle_weibopwd']);
			$result=$Sinaupload->upload($_FILES['tle_weibo_tuchuang']['tmp_name']);
			$arr = json_decode($result,true);
			echo '<img src="https://ws3.sinaimg.cn/large/' . $arr['data']['pics']['pic_1']['pid'] . '.jpg" alt="' . $_FILES['tle_weibo_tuchuang']['tmp_name'] . '" />';
            exit;
        }
    }
}

add_action('admin_menu', 'tle_weibo_tuchuang_menu');
function tle_weibo_tuchuang_menu(){
    add_options_page('微博图床', '微博图床', 'manage_options', 'tle-weibo-tuchuang', 'tle_weibo_tuchuang_options');
}
function tle_weibo_tuchuang_options(){
    $weibo_configs = get_settings('tle_weibo_tuchuang');
	?>
	<div class="wrap">
		<h2>微博图床设置:</h2>
		作者：<a href="http://tongleer.com" title="微博图床">二呆</a><br />
		<?php
		$version=file_get_contents('http://api.tongleer.com/interface/TleWeiboTuchuang.php?action=update&version=1');
		echo $version;
		?>
		<form method="get" action="">
			<p>
				<input type="text" name="tle_weibouser" placeholder="微博小号用户名" value="<?=$weibo_configs['tle_weibouser'];?>" />
			</p>
			<p>
				<input type="password" name="tle_weibopwd" placeholder="微博小号密码" value="<?=$weibo_configs['tle_weibopwd'];?>" />
			</p>
			<p>
				<input type="hidden" name="t" value="config" />
				<input type="hidden" name="page" value="tle-weibo-tuchuang" />
				<input type="submit" value="修改" />
			</p>
		</form>
	</div>
	<?php
}
?>