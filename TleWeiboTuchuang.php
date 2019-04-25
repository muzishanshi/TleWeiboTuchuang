<?php
/* 
Plugin Name: TleWeiboTuchuang
Plugin URI: https://github.com/muzishanshi/TleWeiboTuchuang
Description:  新浪微博图床插件支持微博授权和非授权两种方式，并提供前台图传和远程连接、本地链接、微博图床链接之间的转换：1、非授权方式自动利用cookie上传，在文章发布页面增加微博上传功能，使用微博作为图床，更加方便，只需一个微博小号即可实现。（因微博验证或其他权限问题可能会失败几次，可多尝试几个微博小号多上传两次即可。）2、授权方式是利用分享功能可保存至自己的微博相册。
Version: 1.0.6
Author: 二呆
Author URI: http://www.tongleer.com
License: 
*/
global $wpdb;
define("TLE_WEIBO_TUCHUANG_VERSION",6);
if(!class_exists('Sinaupload')){
	require_once plugin_dir_path(__FILE__) . 'libs/Sinaupload.php';
}

if(isset($_GET['t'])){
	/*设置参数*/
    if($_GET['t'] == 'updateWBTCConfig'){
        update_option('tle_weibo_tuchuang', array('tle_weibouser' => $_REQUEST['tle_weibouser'], 'tle_weibopwd' => $_REQUEST['tle_weibopwd'], 'tle_webimgbg' => $_REQUEST['tle_webimgbg'], 'tle_webimgheight' => $_REQUEST['tle_webimgheight'], 'tle_weibo_issave' => $_REQUEST['tle_weibo_issave'],'tle_weiboprefix'=>$_REQUEST['tle_weiboprefix']));
    }
    /*编辑文章中上传*/
    if($_GET['t'] == 'uploadWBTC'){
		$weibosync_configs = get_settings('tle_weibo_sync');
        $weibo_configs = get_settings('tle_weibo_tuchuang');
		if($weibo_configs['tle_weibo_issave']=="y"){
			date_default_timezone_set('Asia/Shanghai');
			if (file_exists(dirname(__FILE__).'/../TleWeiboSyncV2/libs/saetv2.ex.class.php')) {
				if(!class_exists('SaeTOAuthV2')&&!class_exists('SaeTClientV2')){
					require_once plugin_dir_path(__FILE__) . '../TleWeiboSyncV2/libs/saetv2.ex.class.php';
				}
			}
			if (file_exists(dirname(__FILE__).'/../TleWeiboSyncV2/sinav2_token_conf.php')) {
				include( dirname(__FILE__).'/../TleWeiboSyncV2/sinav2_token_conf.php' );
			}
			if (!defined('SINAV2_ACCESS_TOKEN')){
				echo "请先启用微博同步插件并授权：https://github.com/muzishanshi/TleWeiboSyncV2";
				exit;
			}
			$time=time();
			$utfname=$time."_".$_FILES["tle_weibo_tuchuang"]["name"][0];
			$gbkname = iconv("utf-8", "gbk", $utfname);
			move_uploaded_file($_FILES["tle_weibo_tuchuang"]["tmp_name"][0], dirname(__FILE__).'/'.$gbkname);
			$img=plugins_url()."/TleWeiboTuchuang/".$utfname;
			/* 修改了下风格，并添加文章关键词作为微博话题，提高与其他相关微博的关联率 */
			$string1 = '【新浪图床】';
			$string2 = '来源：'.get_bloginfo('url');
			/* 微博字数控制，避免超标同步失败 */
			$postData = $string1.mb_strimwidth("",0, 140,'...').$string2;
			$c = new SaeTClientV2( $weibosync_configs["weiboappkey"] , $weibosync_configs["weiboappsecret"] , SINAV2_ACCESS_TOKEN );
			$arr=$c->share($postData,$img);
			@unlink(dirname(__FILE__).'/'.$gbkname);
			if(isset($arr["original_pic"])){
				echo '<img src="' . $arr["original_pic"] . '" alt="' . $_FILES['tle_weibo_tuchuang']['name'][0] . '" />';
			}
		}else{
			if(!isset($weibo_configs['tle_weibouser']) || !isset($weibo_configs['tle_weibopwd'])){
				echo '请先配置微博小号';
			}else{
				for($i=0,$j=count($_FILES["tle_weibo_tuchuang"]["name"]);$i<$j;$i++){
					$Sinaupload=new Sinaupload('');
					$cookie=$Sinaupload->login($weibo_configs['tle_weibouser'],$weibo_configs['tle_weibopwd']);
					$result=$Sinaupload->upload($_FILES['tle_weibo_tuchuang']['tmp_name'][$i]);
					$arr = json_decode($result,true);
					echo '<img src="'.$weibo_configs['tle_weiboprefix'] . $arr['data']['pics']['pic_1']['pid'] . '.jpg" alt="' . $_FILES['tle_weibo_tuchuang']['name'][$i] . '" />';
				}
			}
		}
		exit;
    }
	/*转换微博图床链接*/
	if($_GET['t']=='updateWBTCLinks'){
		$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
		if(!empty($action)){
			switch ($action) {
				case 'updateWBTCLinks':
					$weibo_configs = get_settings('tle_weibo_tuchuang');
					if(!isset($weibo_configs['tle_weibouser']) || !isset($weibo_configs['tle_weibopwd'])){
						$json=json_encode(array("status"=>"noneconfig","msg"=>"请先配置微博小号"));
						echo $json;
						exit;
					}
					$postid = isset($_POST['postid']) ? addslashes($_POST['postid']) : '';
					$post_content = get_post($postid)->post_content;
					$tle_weiboprefix=str_replace("/","\/",$weibo_configs['tle_weiboprefix']);
					$tle_weiboprefix=str_replace(".","\.",$tle_weiboprefix);
					preg_match_all( "/<(img|IMG).*?src=[\'|\"](?!".$tle_weiboprefix.")(.*?)[\'|\"].*?[\/]?>/", $post_content, $submatches );
					$savepath=dirname(__FILE__)."/x.jpg";
					foreach($submatches[2] as $url){
						$html = file_get_contents($url);
						file_put_contents($savepath, $html);
						$Sinaupload=new Sinaupload('');
						$cookie=$Sinaupload->login($weibo_configs['tle_weibouser'],$weibo_configs['tle_weibopwd']);
						$result=$Sinaupload->upload($savepath);
						$arr = json_decode($result,true);
						if(isset($arr['data']['pics']['pic_1']['pid'])){
							$imgurl="".$weibo_configs['tle_weiboprefix'].$arr['data']['pics']['pic_1']['pid'].".jpg";
							$post_content=str_replace($url,$imgurl,$post_content);
							
							if(strpos($url,get_bloginfo("url"))!== false){
								$path=str_replace(get_bloginfo("url"),"",$url);
								$oldpath=plugin_dir_path(__FILE__)."../../..".$path;
								@unlink($oldpath);
							}
						}
					}
					$result=$wpdb->update($wpdb->prefix."posts",array('post_content'=>$post_content),array("ID"=>$postid));
					@unlink($savepath);
					$json=json_encode(array("status"=>"ok","msg"=>"转换成功"));
					echo $json;
					break;
			}
			exit;
		}
	}
	/*本地化微博图床链接*/
	if($_GET['t']=='localWBTCLinks'){
		$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
		if(!empty($action)){
			switch ($action) {
				case 'localWBTCLinks':
					$postid = isset($_POST['postid']) ? addslashes($_POST['postid']) : '';
					$post_content = get_post($postid)->post_content;
					$blogurl=str_replace("/","\/",get_bloginfo("url"));
					$blogurl=str_replace(".","\.",$blogurl);
					preg_match_all( "/<(img|IMG).*?src=[\'|\"](?!".$blogurl.")(.*?)[\'|\"].*?[\/]?>/", $post_content, $localmatches );
					foreach($localmatches[2] as $url){
						$uploadfile="uploads/".date("Y")."/".date("m")."/".time().basename($url);
						$html = file_get_contents($url);
						file_put_contents(dirname(__FILE__)."/../../".$uploadfile, $html);
						$imgurl=plugins_url()."/../".$uploadfile;
						$post_content=str_replace($url,$imgurl,$post_content);
					}
					$result=$wpdb->update($wpdb->prefix."posts",array('post_content'=>$post_content),array("ID"=>$postid));
					$json=json_encode(array("status"=>"ok","msg"=>"本地化成功"));
					echo $json;
					break;
			}
			exit;
		}
	}
	/*前台上传*/
	if($_GET['t'] == 'uploadWBTCByForeground'){
		$weibo_configs = get_settings('tle_weibo_tuchuang');
		if($weibo_configs['tle_weibo_issave']=="y"){
			date_default_timezone_set('Asia/Shanghai');
			if (file_exists(dirname(__FILE__).'/../TleWeiboSyncV2/libs/saetv2.ex.class.php')) {
				if(!class_exists('SaeTOAuthV2')&&!class_exists('SaeTClientV2')){
					require_once plugin_dir_path(__FILE__) . '../TleWeiboSyncV2/libs/saetv2.ex.class.php';
				}
			}
			if (file_exists(dirname(__FILE__).'/../TleWeiboSyncV2/sinav2_token_conf.php')) {
				include( dirname(__FILE__).'/../TleWeiboSyncV2/sinav2_token_conf.php' );
			}
			if (!defined('SINAV2_ACCESS_TOKEN')){
				echo "请先启用微博同步插件并授权：https://github.com/muzishanshi/TleWeiboSyncV2";
				exit;
			}
			$time=time();
			$utfname=$time."_".$_FILES["webimgupload"]["name"][0];
			$gbkname = iconv("utf-8", "gbk", $utfname);
			move_uploaded_file($_FILES["webimgupload"]["tmp_name"][0], dirname(__FILE__).'/'.$gbkname);
			$img=plugins_url()."/TleWeiboTuchuang/".$utfname;
			/* 修改了下风格，并添加文章关键词作为微博话题，提高与其他相关微博的关联率 */
			$string1 = '【新浪图床】';
			$string2 = '来源：'.get_bloginfo('url');
			/* 微博字数控制，避免超标同步失败 */
			$postData = $string1.mb_strimwidth("",0, 140,'...').$string2;
			$c = new SaeTClientV2( $weibosync_configs["weiboappkey"] , $weibosync_configs["weiboappsecret"] , SINAV2_ACCESS_TOKEN );
			$arr=$c->share($postData,$img);
			@unlink(dirname(__FILE__).'/'.$gbkname);
			if(isset($arr["original_pic"])){
				$urls=$arr["original_pic"];
				$hrefs="<a style='text-decoration:none;' href='".$urls."' target='_blank' title='".$_FILES['webimgupload']['name'][0]."'>".$urls."</a>";
				$codes="<a href='".$urls."' target='_blank' title='".$_FILES['webimgupload']['name'][0]."'><img src='".$urls."' alt='".$_FILES['webimgupload']['name'][0]."' /></a>";
				$json=json_encode(array("status"=>"ok","msg"=>"上传结果","urls"=>$urls,"hrefs"=>$hrefs,"codes"=>$codes));
				echo $json;
				
			}
		}else{
			if(!isset($weibo_configs['tle_weibouser']) || !isset($weibo_configs['tle_weibopwd'])){
				$json=json_encode(array("status"=>"noset","msg"=>"请先配置微博小号"));echo $json;exit;
			}
			$urls="";
			$hrefs="";
			$codes="";
			for($i=0,$j=count($_FILES["webimgupload"]["name"]);$i<$j;$i++){
				$Sinaupload=new Sinaupload('');
				$cookie=$Sinaupload->login($weibo_configs['tle_weibouser'],$weibo_configs['tle_weibopwd']);
				$result=$Sinaupload->upload($_FILES['webimgupload']['tmp_name'][$i]);
				$arr = json_decode($result,true);
				$urls.="".$weibo_configs['tle_weiboprefix'].$arr['data']['pics']['pic_1']['pid'].".jpg<br />";
				$hrefs.="<a style='text-decoration:none;' href='".$weibo_configs['tle_weiboprefix'].$arr['data']['pics']['pic_1']['pid'].".jpg' target='_blank' title='".$_FILES['webimgupload']['name'][$i]."'>".$weibo_configs['tle_weiboprefix'].$arr['data']['pics']['pic_1']['pid'].".jpg</a><br />";
				$codes.="<a href='".$weibo_configs['tle_weiboprefix'].$arr['data']['pics']['pic_1']['pid'].".jpg' target='_blank' title='".$_FILES['webimgupload']['name'][$i]."'><img src='".$weibo_configs['tle_weiboprefix'].$arr['data']['pics']['pic_1']['pid'].".jpg' alt='".$_FILES['webimgupload']['name'][$i]."' /></a>\r\n";
			}
			$json=json_encode(array("status"=>"ok","msg"=>"上传结果","urls"=>$urls,"hrefs"=>$hrefs,"codes"=>$codes));
			echo $json;
		}
		exit;
	}
	/*版本检测*/
	if($_GET['t']=='updateWBTCVersion'){
		$version = isset($_POST['version']) ? addslashes($_POST['version']) : '';
		$version=file_get_contents('https://www.tongleer.com/api/interface/TleWeiboTuchuang.php?action=update&version='.$version);
		echo $version;
		exit;
	}
}

add_action( 'admin_init', 'tle_weibo_tuchuang_admin_init' );
function tle_weibo_tuchuang_admin_init() {
    add_filter('manage_post_posts_columns', 'tle_weibo_tuchuang_add_post_columns');
	add_action('manage_posts_custom_column', 'tle_weibo_tuchuang_render_post_columns', 10, 2);
	add_action( 'admin_enqueue_scripts', 'tle_weibo_tuchuang_scripts' );
	add_filter( 'plugin_action_links', 'tle_weibo_tuchuang_add_link', 10, 2 );
}
function tle_weibo_tuchuang_add_post_columns($columns) {
    $columns['weibo_tuchuang_price_name'] = '微博图床';
    return $columns;
}
function tle_weibo_tuchuang_render_post_columns($column_name, $id) {
    switch ($column_name) {
    case 'weibo_tuchuang_price_name':
		$post_content = get_post($id)->post_content;
		preg_match_all( "/<(img|IMG).*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/", $post_content, $matches );
		if(count($matches[2])>0){
			//转换微博图传链接
			$weibo_configs = get_settings('tle_weibo_tuchuang');
			$tle_weiboprefix=str_replace("/","\/",$weibo_configs['tle_weiboprefix']);
			$tle_weiboprefix=str_replace(".","\.",$tle_weiboprefix);
			preg_match_all( "/<(img|IMG).*?src=[\'|\"](?!".$tle_weiboprefix.")(.*?)[\'|\"].*?[\/]?>/", $post_content, $submatches );
			if(count($submatches[2])>0){
				echo '
					<a href="javascript:;" class="tle_weibo_tuchuang_convert_id" id="tle_weibo_tuchuang_convert_id'.$id.'" data-id="'.$id.'">转换</a>
				';
			}else{
				echo '无需转换';
			}
			//图片本地化
			$blogurl=str_replace("/","\/",get_bloginfo("url"));
			$blogurl=str_replace(".","\.",$blogurl);
			preg_match_all( "/<(img|IMG).*?src=[\'|\"](?!".$blogurl.")(.*?)[\'|\"].*?[\/]?>/", $post_content, $localmatches );
			if(count($localmatches[2])>0){
				echo '
					<a href="javascript:;" class="tle_weibo_tuchuang_local_id" id="tle_weibo_tuchuang_local_id'.$id.'" data-id="'.$id.'">本地化</a>
				';
			}else{
				echo '无需本地化';
			}
		}else{
			echo '未包含图片';
		}
		break;
    }
}
function tle_weibo_tuchuang_scripts(){
	wp_register_script( 'tle_weibo_tuchuang_jquery', 'https://libs.baidu.com/jquery/1.11.1/jquery.min.js');  
	wp_enqueue_script( 'tle_weibo_tuchuang_jquery' );
	wp_register_script( 'tle_weibo_tuchuang_js', plugins_url('js/tle_weibo_tuchuang.js',__FILE__) );  
	wp_enqueue_script( 'tle_weibo_tuchuang_js' );
}
function tle_weibo_tuchuang_add_link( $actions, $plugin_file ) {
  static $plugin;
  if (!isset($plugin))
    $plugin = plugin_basename(__FILE__);
  if ($plugin == $plugin_file) {
      $settings = array('settings' => '<a href="admin.php?page=tle-weibo-tuchuang">' . __('Settings') . '</a>');
      $site_link  = array('version'=>'<span id="versionCodeWBTC" data-code="'.TLE_WEIBO_TUCHUANG_VERSION.'"></span><br />','contact' => '<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=diamond0422@qq.com" target="_blank">联系</a>','support' => '<a href="https://www.tongleer.com" target="_blank">官网</a>','club' => '<a href="http://club.tongleer.com" target="_blank">论坛</a>');
      $actions  = array_merge($settings, $actions);
      $actions  = array_merge($site_link, $actions);
  }
  return $actions;
}

add_action('media_buttons', 'add_my_media_button');
function add_my_media_button() {
	$weibo_configs = get_settings('tle_weibo_tuchuang');
	$isMultiple="multiple";
	if($weibo_configs['tle_weibo_issave']=="y"){
		$isMultiple="";
	}
    echo '
		<style>
			.weibo-upload {
				padding: 4px 10px;
				height: 20px;
				line-height: 20px;
				position: relative;
				cursor: pointer;
				color: #888;
				background: #fafafa;
				border: 1px solid #ddd;
				border-radius: 4px;
				overflow: hidden;
				display: inline-block;
				*display: inline;
				*zoom: 1
			}
			.weibo-upload  input {
				position: absolute;
				font-size: 100px;
				right: 0;
				top: 0;
				opacity: 0;
				filter: alpha(opacity=0);
				cursor: pointer
			}
			.weibo-upload:hover {
				color: #444;
				background: #eee;
				border-color: #ccc;
				text-decoration: none
			}
		</style>
		<a href="javascript:;" class="weibo-upload"><input type="file" id="inputWeiboFile" '.$isMultiple.' />微博图床</a>
	';
}
add_action('add_meta_boxes', 'tle_weibo_tuchuang_post_box');
function tle_weibo_tuchuang_post_box(){
    add_meta_box('tle_weibo_tuchuang_div', __('微博图床'), 'tle_weibo_tuchuang_post_html', 'post', 'side');
}
function tle_weibo_tuchuang_post_html(){
	$weibo_configs = get_settings('tle_weibo_tuchuang');
	$isMultiple="multiple";
	if($weibo_configs['tle_weibo_issave']=="y"){
		$isMultiple="";
	}
	echo '<script>var tle_weibo_tuchuang_post_url="' . admin_url('options-general.php?page=tle-weibo-tuchuang&t=uploadWBTC') . '";</script>';
   ?>
   <div id="tle_weibo_tuchuang_post" style="width:auto;height:100px;border:3px dashed silver;line-height:100px; text-align:center; font-size:20px; color:#d3d3d3;cursor:pointer;">将图片拖拽到此区域上传</div>
   <input type="file" <?=$isMultiple;?> id="tle_weibo_tuchuang_input" style="position: absolute;display: block;top:0;left:0;bottom:0;right:0;opacity: 0;-moz-opacity: 0;filter:alpha(opacity=0);cursor:pointer;" />
   <script>
   window.onload = function(){
	var div = document.getElementById('tle_weibo_tuchuang_post');
	var input = document.getElementById('tle_weibo_tuchuang_input');
	var input2 = document.getElementById('inputWeiboFile');
	
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
		file = e.dataTransfer.files && e.dataTransfer.files;
		upLoad(file);
	}
	var inputFileHandler = function(){
		var file = input.files;
		upLoad(file);
	}
	var inputFileHandler2 = function(){
		var file2 = input2.files;
		upLoad(file2);
	}
	document.body.addEventListener('drop', function(e) {
		dropHandler(e);
	}, false);
	
	input.addEventListener('change', function() {
		inputFileHandler();
	}, false);
	$("#inputWeiboFile").change(function(){
		inputFileHandler2();
	});
   }
   </script>
   <?php
}

add_action('admin_menu', 'tle_weibo_tuchuang_menu');
function tle_weibo_tuchuang_menu(){
    add_options_page('微博图床', '微博图床', 'manage_options', 'tle-weibo-tuchuang', 'tle_weibo_tuchuang_options');
}
function tle_weibo_tuchuang_options(){
    $weibo_configs = get_settings('tle_weibo_tuchuang');
	?>
	<div class="wrap">
		<h2>微博图床设置</h2>
		<form method="get" action="">
			<p>
				是否保存到微博相册(需开启<a href="https://github.com/muzishanshi/TleWeiboSyncV2" target="_blank">微博同步插件</a>)：
				<input type="radio" name="tle_weibo_issave" value="n" <?=isset($weibo_configs['tle_weibo_issave'])?($weibo_configs['tle_weibo_issave']=="n"?"checked":""):"checked";?> />否
				<input type="radio" name="tle_weibo_issave" value="y" <?=isset($weibo_configs['tle_weibo_issave'])?($weibo_configs['tle_weibo_issave']=="y"?"checked":""):"";?> />是
			</p>
			<p>
				<input type="text" name="tle_weibouser" placeholder="微博小号用户名" value="<?=$weibo_configs['tle_weibouser'];?>" />
			</p>
			<p>
				<input type="password" name="tle_weibopwd" placeholder="微博小号密码" value="<?=$weibo_configs['tle_weibopwd'];?>" />
			</p>
			<p>
				<input type="text" name="tle_weiboprefix" placeholder="图片链接前缀" value="<?=$weibo_configs['tle_weiboprefix']?$weibo_configs['tle_weiboprefix']:"https://ws3.sinaimg.cn/large/";?>" />
			</p>
			<p>
				<input type="text" name="tle_webimgbg" placeholder="前台图床背景" value="<?=$weibo_configs['tle_webimgbg'];?>" />
			</p>
			<p>
				<input type="number" name="tle_webimgheight" placeholder="前台图床高度" value="<?=$weibo_configs['tle_webimgheight'];?>" />
			</p>
			<p>
				<input type="hidden" name="t" value="updateWBTCConfig" />
				<input type="hidden" name="page" value="tle-weibo-tuchuang" />
				<input type="submit" value="修改" />
			</p>
			<p>
				特别注意：<br />
				1、在微博同步插件中，微博开放平台的安全域名要与网站域名一致；<br />
				2、保存到微博相册时，如果频繁会禁用当前微博的接口，所以每次只能上传一张图片；<br />
				3、不保存到微博相册时，设置微博小号后可多尝试多上传几次，上传成功尽量不要将此微博小号登录微博系的网站、软件，可以登录，但不确定会不会上传失败，上传失败了再重新上传2次同样可以正常上传，如果小号等级过低，可尝试微博大号，微博账号不能有手机、二维码验证权限，插件可正常使用，无需担心。
			</p>
		</form>
	</div>
	<?php
}
/*前台图床小工具*/
add_action( 'widgets_init', 'tle_weibo_tuchuang_foreground' );
function tle_weibo_tuchuang_foreground() {
	register_widget( 'tle_weibo_tuchuang_foreground' );
}
class tle_weibo_tuchuang_foreground extends WP_Widget {
	function tle_weibo_tuchuang_foreground() {
		$widget_ops = array( 'classname' => 'tle_weibo_tuchuang_foreground', 'description' => '显示前台图床' );
		$this->WP_Widget( 'tle_weibo_tuchuang_foreground', '微博图床', $widget_ops, $control_ops );
	}
	function widget( $args, $instance ) {
		include "page/page_weibo_tuchuang.php";
	}
	function form($instance) {}
}
?>