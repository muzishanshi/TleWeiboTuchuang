<?php
/* 
Plugin Name: TleWeiboTuchuang
Plugin URI: https://github.com/muzishanshi/TleWeiboTuchuang
Description:  TleWeiboTuchuang（TleImgPool图池）插件源于新浪图床(已使用微博官方api实现)，而后扩展了阿里图床等功能，因技术有限，若存在bug欢迎邮件反馈，方能逐步升级。
Version: 1.0.10
Author: 二呆
Author URI: http://www.tongleer.com
License: 
*/
global $wpdb;
define("TLE_WEIBO_TUCHUANG_VERSION",10);
if(!class_exists('Sinaupload')){
	require_once plugin_dir_path(__FILE__) . 'libs/Sinaupload.php';
}

if(isset($_GET['t'])){
	/*设置参数*/
    if($_GET['t'] == 'updateWBTCConfig'){
		$ali_configs = get_settings('tle_weibo_tuchuang');
        update_option('tle_weibo_tuchuang', array('tle_weibouser' => $_REQUEST['tle_weibouser'], 'tle_weibopwd' => $_REQUEST['tle_weibopwd'], 'tle_weibo_issave' => $_REQUEST['tle_weibo_issave'],'tle_weiboprefix'=>$_REQUEST['tle_weiboprefix'],'tle_aliprefix'=>$ali_configs["tle_aliprefix"],'tle_weibo_https'=>$_REQUEST['tle_weibo_https']));
    }
	if($_GET['t'] == 'updateALTCConfig'){
		$weibo_configs = get_settings('tle_weibo_tuchuang');
        update_option('tle_weibo_tuchuang', array('tle_aliprefix' => $_REQUEST['tle_aliprefix'],'tle_weibouser' => $weibo_configs['tle_weibouser'],'tle_weibopwd' => $weibo_configs['tle_weibopwd'],'tle_weibo_issave' => $weibo_configs['tle_weibo_issave'],'tle_weiboprefix'=>$weibo_configs['tle_weiboprefix'],'tle_weibo_https'=>$weibo_configs['tle_weibo_https']));
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
				if($weibo_configs["tle_weibo_https"]=="y"){
					$original_pic=str_replace("http://","https://",$arr["original_pic"]);
				}else{
					$original_pic=$arr["original_pic"];
				}
				echo '<img src="'.$original_pic.'" alt="' . $_FILES['tle_weibo_tuchuang']['name'][0] . '" />';
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
	/*转换阿里图床链接*/
	if($_GET['t']=='updateALTCLinks'){
		$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
		if(!empty($action)){
			switch ($action) {
				case 'updateALTCLinks':
					$request = new WP_Http;
					$ali_configs = get_settings('tle_weibo_tuchuang');
					$postid = isset($_POST['postid']) ? addslashes($_POST['postid']) : '';
					$post_content = get_post($postid)->post_content;
					$tle_aliprefix=str_replace("/","\/",$ali_configs['tle_aliprefix']);
					$tle_aliprefix=str_replace(".","\.",$tle_aliprefix);
					preg_match_all( "/<(img|IMG).*?src=[\'|\"](?!".$tle_aliprefix.")(.*?)[\'|\"].*?[\/]?>/", $post_content, $submatches );
					foreach($submatches[2] as $url){
						$result = $request->request('https://www.tongleer.com/api/web/?action=weiboimg&imgurl='.$url);
						$arr=json_decode($result["body"],true);
						if(isset($arr['data']["src"])){
							$imgurl=$ali_configs['tle_aliprefix'].basename($arr['data']["src"]);
							$post_content=str_replace($url,$imgurl,$post_content);
							
							if(strpos($url,get_bloginfo("url"))!== false){
								$path=str_replace(get_bloginfo("url"),"",$url);
								$oldpath=plugin_dir_path(__FILE__)."../../..".$path;
								@unlink($oldpath);
							}
						}
					}
					$wpdb->update($wpdb->prefix."posts",array('post_content'=>$post_content),array("ID"=>$postid));
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
					$time=time();
					$uploaddir=date("Y",$time)."/".date("m",$time)."/";
					if(!is_dir(dirname(__FILE__)."/../../uploads/".$uploaddir)){
						mkdir (dirname(__FILE__)."/../../uploads/".$uploaddir, 0777, true );
					}
					$postid = isset($_POST['postid']) ? addslashes($_POST['postid']) : '';
					$post_content = get_post($postid)->post_content;
					$blogurl=str_replace("/","\/",get_bloginfo("url"));
					$blogurl=str_replace(".","\.",$blogurl);
					preg_match_all( "/<(img|IMG).*?src=[\'|\"](?!".$blogurl.")(.*?)[\'|\"].*?[\/]?>/", $post_content, $localmatches );
					foreach($localmatches[2] as $url){
						$basename=basename($url);
						if(strpos($basename,"?")!== false){
							$basenames=explode("?",$basename);
							$basename=$basenames[0];
						}
						$uploadfile=$time.$basename.".png";
						$html = file_get_contents($url);
						file_put_contents(dirname(__FILE__)."/../../uploads/".$uploaddir.$uploadfile, $html);
						$imgurl=home_url()."/wp-content/uploads/".$uploaddir.$uploadfile;
						$post_content=str_replace($url,$imgurl,$post_content);
						
						$postRow = $wpdb->get_row( "SELECT * FROM `" . $wpdb->prefix . "posts` where ID=".$postid);
						$imgData = array(
							'post_author' => $postRow->post_author,
							'post_date' => date('Y-m-d H:i:s',$time),
							'post_date_gmt' => date('Y-m-d H:i:s',$time),
							'post_title' => basename($uploadfile,".png"),
							'post_status'=>'inherit',
							'ping_status'=>'closed',
							'post_name'=>basename($uploadfile,".png"),
							'post_modified'=>date('Y-m-d H:i:s',$time),
							'post_modified_gmt'=>date('Y-m-d H:i:s',$time),
							'post_parent'=>$postid,
							'guid'=>home_url().'/wp-content/uploads/'.$uploaddir.$uploadfile,
							'post_type'=>'attachment',
							'post_mime_type'=>'image/png'
							
						);
						$wpdb->insert($wpdb->prefix."posts",$imgData);
						$imgDataId=$wpdb->insert_id;
						$imgMetaData = array(
							'post_id' => $imgDataId,
							'meta_key' => '_wp_attached_file',
							'meta_value' => $uploaddir.$uploadfile
							
						);
						$wpdb->insert($wpdb->prefix."postmeta",$imgMetaData);
						$imgFileData=array(
							"width"=>0,
							"height"=>0,
							"file"=>$uploaddir.$uploadfile,
							"sizes"=>array(),
							"image_meta"=>array(
								"aperture"=>"0",
								"credit"=>"",
								"camera"=>"",
								"caption"=>"",
								"created_timestamp"=>"0",
								"copyright"=>"",
								"focal_length"=>"0",
								"iso"=>"0",
								"shutter_speed"=>"0",
								"title"=>"",
								"orientation"=>"1",
								"keywords"=>array()
							)
						);
						$imgMetaData = array(
							'post_id' => $imgDataId,
							'meta_key' => '_wp_attachment_metadata',
							'meta_value' => serialize($imgFileData)
							
						);
						$wpdb->insert($wpdb->prefix."postmeta",$imgMetaData);
					}
					$result=$wpdb->update($wpdb->prefix."posts",array('post_content'=>$post_content),array("ID"=>$postid));
					$json=json_encode(array("status"=>"ok","msg"=>"本地化成功"));
					echo $json;
					break;
			}
			exit;
		}
	}
	/*批量转换图片链接*/
	if($_GET['t']=='imgpool_conv'){
		$imgpool_conv_type = isset($_POST['imgpool_conv_type']) ? addslashes($_POST['imgpool_conv_type']) : '';
		if(!empty($imgpool_conv_type)){
			switch ($imgpool_conv_type) {
				case 'local':
					/*批量本地化*/
					$uploaddir=date("Y")."/".date("m")."/";
					if(!is_dir(dirname(__FILE__)."/../../uploads/".$uploaddir)){
						mkdir (dirname(__FILE__)."/../../uploads/".$uploaddir, 0777, true );
					}
					$domain = isset($_GET['imgpool_conv_domain']) ? addslashes($_GET['imgpool_conv_domain']) : '';
					$imgpool_postid = isset($_POST['imgpool_postid']) ? array_map('intval', $_POST['imgpool_postid']) : array();
					foreach($imgpool_postid as $id){
						$post_content = get_post($id)->post_content;
						$domain=str_replace("/","\/",$domain);
						$domain=str_replace(".","\.",$domain);
						preg_match_all( "/<(img|IMG).*?src=[\'|\"](?!".$domain.")(.*?)[\'|\"].*?[\/]?>/", $post_content, $localmatches );
						foreach($localmatches[2] as $url){
							$basename=basename($url);
							if(strpos($basename,"?")!== false){
								$basenames=explode("?",$basename);
								$basename=$basenames[0];
							}
							$uploadfile=time().$basename.".png";
							$html = file_get_contents($url);
							file_put_contents(dirname(__FILE__)."/../../uploads/".$uploaddir.$uploadfile, $html);
							$imgurl=home_url()."/wp-content/uploads/".$uploaddir.$uploadfile;
							$post_content=str_replace($url,$imgurl,$post_content);
						}
						$result=$wpdb->update($wpdb->prefix."posts",array('post_content'=>$post_content),array("ID"=>$id));
					}
					$json=json_encode(array("status"=>"ok","msg"=>"本地化成功"));
					echo $json;
					break;
				case 'ali':
					/*批量阿里图床*/
					$request = new WP_Http;
					$ali_configs = get_settings('tle_weibo_tuchuang');
					$domain = isset($_GET['imgpool_conv_domain']) ? addslashes($_GET['imgpool_conv_domain']) : '';
					$imgpool_postid = isset($_POST['imgpool_postid']) ? array_map('intval', $_POST['imgpool_postid']) : array();
					foreach($imgpool_postid as $id){
						$post_content = get_post($id)->post_content;
						$domain=str_replace("/","\/",$domain);
						$domain=str_replace(".","\.",$domain);
						preg_match_all( "/<(img|IMG).*?src=[\'|\"](?!".$domain.")(.*?)[\'|\"].*?[\/]?>/", $post_content, $submatches );
						foreach($submatches[2] as $url){
							$result = $request->request('https://www.tongleer.com/api/web/?action=weiboimg&imgurl='.$url);
							$arr=json_decode($result["body"],true);
							if(isset($arr['data']["src"])){
								$imgurl=$domain.basename($arr['data']["src"]);
								$imgurl=str_replace("\\","",$imgurl);
								$post_content=str_replace($url,$imgurl,$post_content);
								
								if(strpos($url,get_bloginfo("url"))!== false){
									$path=str_replace(get_bloginfo("url"),"",$url);
									$oldpath=plugin_dir_path(__FILE__)."../../..".$path;
									@unlink($oldpath);
								}
							}
						}
						$wpdb->update($wpdb->prefix."posts",array('post_content'=>$post_content),array("ID"=>$id));
					}
					$json=json_encode(array("status"=>"ok","msg"=>"转换成功"));
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
	if( current_user_can( 'manage_options' ) ) {
		add_filter('manage_post_posts_columns', 'tle_weibo_tuchuang_add_post_columns');
		add_action('manage_posts_custom_column', 'tle_weibo_tuchuang_render_post_columns', 10, 2);
	}
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
			//转换微博图床链接
			/*
			$weibo_configs = get_settings('tle_weibo_tuchuang');
			$tle_weiboprefix=str_replace("/","\/",$weibo_configs['tle_weiboprefix']);
			$tle_weiboprefix=str_replace(".","\.",$tle_weiboprefix);
			preg_match_all( "/<(img|IMG).*?src=[\'|\"](?!".$tle_weiboprefix.")(.*?)[\'|\"].*?[\/]?>/", $post_content, $submatches );
			if(count($submatches[2])>0){
				echo '
					<a href="javascript:;" class="tle_weibo_tuchuang_convert_id" id="tle_weibo_tuchuang_convert_id'.$id.'" data-id="'.$id.'">转换微博</a>
				';
			}else{
				echo '无需转换微博';
			}
			*/
			//转换阿里图床链接
			$ali_configs = get_settings('tle_weibo_tuchuang');
			$tle_aliprefix=str_replace("/","\/",$ali_configs['tle_aliprefix']);
			$tle_aliprefix=str_replace(".","\.",$tle_aliprefix);
			preg_match_all( "/<(img|IMG).*?src=[\'|\"](?!".$tle_aliprefix.")(.*?)[\'|\"].*?[\/]?>/", $post_content, $submatches );
			if(count($submatches[2])>0){
				echo '
					<a href="javascript:;" class="tle_imgpool_ali_convert_id" id="tle_imgpool_ali_convert_id'.$id.'" data-id="'.$id.'">转换阿里</a>
				';
			}else{
				echo '无需转换阿里';
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
	/*
	wp_register_script( 'tle_weibo_tuchuang_jquery', 'https://libs.baidu.com/jquery/1.11.1/jquery.min.js');  
	wp_enqueue_script( 'tle_weibo_tuchuang_jquery' );
	wp_register_script( 'tle_weibo_tuchuang_js', plugins_url('js/tle_weibo_tuchuang.js',__FILE__) );  
	wp_enqueue_script( 'tle_weibo_tuchuang_js' );
	*/
	?>
	<script src="https://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>
	<script>
	$(function(){
		/*
		$(".tle_weibo_tuchuang_convert_id").each(function(){
			var id=$(this).attr("id");
			$("#"+id).click( function () {
				$.post("admin.php?page=tle-weibo-tuchuang&t=updateWBTCLinks",{action:"updateWBTCLinks",postid:$(this).attr("data-id")},function(data){
					var data=JSON.parse(data);
					if(data.status=="noneconfig"){
						alert(data.msg);
					}
					window.location.reload();
				});
			});
		});
		*/
		$(".tle_imgpool_ali_convert_id").each(function(){
			var id=$(this).attr("id");
			$("#"+id).click( function () {
				$.post("admin.php?page=tle-weibo-tuchuang&t=updateALTCLinks",{action:"updateALTCLinks",postid:$(this).attr("data-id")},function(data){
					var data=JSON.parse(data);
					if(data.status=="noneconfig"){
						alert(data.msg);
					}
					window.location.reload();
				});
			});
		});
		$(".tle_weibo_tuchuang_local_id").each(function(){
			var id=$(this).attr("id");
			$("#"+id).click( function () {
				$.post("admin.php?page=tle-weibo-tuchuang&t=localWBTCLinks",{action:"localWBTCLinks",postid:$(this).attr("data-id")},function(data){
					window.location.reload();
				});
			});
		});
		$.post("admin.php?page=tle-weibo-tuchuang&t=updateWBTCVersion",{version:$("#versionCodeWBTC").attr("data-code")},function(data){
			$("#versionCodeWBTC").html(data);
		});
	});
	</script>
	<?php
}
function tle_weibo_tuchuang_add_link( $actions, $plugin_file ) {
  static $plugin;
  if (!isset($plugin))
    $plugin = plugin_basename(__FILE__);
  if ($plugin == $plugin_file) {
      $settings = array('settings' => '<a href="admin.php?page=tle-weibo-tuchuang">' . __('Settings') . '</a>');
      $site_link  = array('version'=>'<span id="versionCodeWBTC" data-code="'.TLE_WEIBO_TUCHUANG_VERSION.'"></span>','contact' => '<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=diamond0422@qq.com" target="_blank">反馈</a>','support' => '<a href="https://www.tongleer.com/api/web/pay.png" target="_blank">打赏</a>','club' => '<a href="http://club.tongleer.com" target="_blank">论坛</a>');
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
		<a href="javascript:;" class="weibo-upload"><input type="file" id="inputAliFile" multiple />阿里图床</a>
		<script>
		var aliInputBtn = document.getElementById("inputAliFile");
		var inputFileHandler3 = function(){
			var alifile = aliInputBtn.files;
			upLoadAli(alifile);
		}
		aliInputBtn.addEventListener("change", function() {
			inputFileHandler3();
		}, false);
		var input2 = document.getElementById("inputWeiboFile");
		var inputFileHandler2 = function(){
			var file2 = input2.files;
			upLoad(file2);
		}
		input2.addEventListener("change", function() {
			inputFileHandler2();
		}, false);
		</script>
	';
}
add_action('add_meta_boxes', 'tle_imgpool_post_box');
function tle_imgpool_post_box(){
	add_meta_box('tle_imgpool_ali_div', __('阿里图床'), 'tle_imgpool_ali_post_html', 'post', 'side');
    add_meta_box('tle_imgpool_weibo_div', __('微博图床'), 'tle_imgpool_weibo_post_html', 'post', 'side');
}
function tle_imgpool_ali_post_html(){
	include "TleWeiboTuchuang_alihtml.php";
}
function tle_imgpool_weibo_post_html(){
	include "TleWeiboTuchuang_wbhtml.php";
}

add_action('admin_menu', 'tle_weibo_tuchuang_menu');
function tle_weibo_tuchuang_menu(){
    add_options_page('微博图床', '微博图床', 'manage_options', 'tle-weibo-tuchuang', 'tle_weibo_tuchuang_options');
}
function tle_weibo_tuchuang_options(){
	include "TleWeiboTuchuang_setting.php";
}
/*前台微博图床小工具*/
add_action( 'widgets_init', 'tle_weibo_tuchuang_foreground' );
function tle_weibo_tuchuang_foreground() {
	register_widget( 'tle_weibo_tuchuang_foreground' );
}
class tle_weibo_tuchuang_foreground extends WP_Widget {
	function tle_weibo_tuchuang_foreground() {
		$widget_ops = array( 'classname' => 'tle_weibo_tuchuang_foreground', 'description' => '显示前台微博图床' );
		$this->WP_Widget( 'tle_weibo_tuchuang_foreground', '微博图床', $widget_ops, $control_ops );
	}
	function widget( $args, $instance ) {
		include "page/page_weibo_tuchuang.php";
	}
	function form($instance) {
		?>
		<p>
			<label>
				背景：
				<input style="width:100%;" id="<?php echo $this->get_field_id('tle_webwbimgbg'); ?>" name="<?php echo $this->get_field_name('tle_webwbimgbg'); ?>" type="text" value="<?php echo $instance['tle_webwbimgbg']; ?>" />
			</label>
		</p>
		<p>
			<label>
				高度：
				<input style="width:100%;" id="<?php echo $this->get_field_id('tle_webimgwbheight'); ?>" name="<?php echo $this->get_field_name('tle_webimgwbheight'); ?>" type="text" value="<?php echo $instance['tle_webimgwbheight']; ?>" />
			</label>
		</p>
		<?php
	}
}
/*前台阿里图床小工具*/
add_action( 'widgets_init', 'tle_imgpool_ali_foreground' );
function tle_imgpool_ali_foreground() {
	register_widget( 'tle_imgpool_ali_foreground' );
}
class tle_imgpool_ali_foreground extends WP_Widget {
	function tle_imgpool_ali_foreground() {
		$widget_ops = array( 'classname' => 'tle_imgpool_ali_foreground', 'description' => '显示前台阿里图床' );
		$this->WP_Widget( 'tle_imgpool_ali_foreground', '阿里图床', $widget_ops, $control_ops );
	}
	function widget( $args, $instance ) {
		include "page/page_ali_tuchuang.php";
	}
	function form($instance) {
		?>
		<p>
			<label>
				背景：
				<input style="width:100%;" id="<?php echo $this->get_field_id('tle_webaliimgbg'); ?>" name="<?php echo $this->get_field_name('tle_webaliimgbg'); ?>" type="text" value="<?php echo $instance['tle_webaliimgbg']; ?>" />
			</label>
		</p>
		<p>
			<label>
				高度：
				<input style="width:100%;" id="<?php echo $this->get_field_id('tle_webimgaliheight'); ?>" name="<?php echo $this->get_field_name('tle_webimgaliheight'); ?>" type="text" value="<?php echo $instance['tle_webimgaliheight']; ?>" />
			</label>
		</p>
		<?php
	}
}
?>