<div class="wrap">
	<h2 class="nav-tab-wrapper" style="border-bottom: 1px solid #ccc;">
	  <a class="nav-tab" href="javascript:;" id="tab-title-setting">微博图床设置</a>
	  <a class="nav-tab" href="javascript:;" id="tab-title-log">阿里图床设置</a>
	  <a class="nav-tab" href="javascript:;" id="tab-title-convert" style="display: none;">图床转换</a>
	  <a class="nav-tab" href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=diamond0422@qq.com" target="_blank"  id="tab-title-about"><font color="red">Bug反馈</font></a>
	</h2>
	<div id="tab-setting" class="div-tab hidden" style="display: none;" >
		<h3>微博图床设置</h3>
		<form method="get" action="">
			<?php $weibo_configs = get_settings('tle_weibo_tuchuang');?>
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
				<input type="hidden" name="t" value="updateWBTCConfig" />
				<input type="hidden" name="page" value="tle-weibo-tuchuang" />
				<input type="submit" value="保存" />
			</p>
			<p>
				特别注意：<br />
				1、在微博同步插件中，微博开放平台的安全域名要与网站域名一致；<br />
				2、保存到微博相册时，如果频繁会禁用当前微博的接口，所以每次只能上传一张图片；<br />
				3、不保存到微博相册时，设置微博小号后可多尝试多上传几次，上传成功尽量不要将此微博小号登录微博系的网站、软件，可以登录，但不确定会不会上传失败，上传失败了再重新上传2次同样可以正常上传，如果小号等级过低，可尝试微博大号，微博账号不能有手机、二维码验证权限，插件可正常使用，无需担心。
			</p>
		</form>
	</div>
	<div id="tab-log" class="div-tab hidden" style="display: none;">
		<h3>阿里图床设置</h3>
		<form method="get" action="">
			<?php $ali_configs = get_settings('tle_weibo_tuchuang');?>
			<p>
				<input type="text" name="tle_aliprefix" placeholder="图片链接前缀" value="<?=$ali_configs['tle_aliprefix']?$ali_configs['tle_aliprefix']:"https://ae01.alicdn.com/kf/";?>" />
			</p>
			<p>
				<input type="hidden" name="t" value="updateALTCConfig" />
				<input type="hidden" name="page" value="tle-weibo-tuchuang" />
				<input type="submit" value="保存" />
			</p>
		</form>
	</div>
	<div id="tab-convert" class="div-tab hidden" style="display: none;">
		<h3>图床转换（每次可转换20篇）</h3>
		<div style="display: none;">
			<hr />
			<small>
				<font color="red">转换前注意：</font><br />
				1、此转换功能仅为了解决单篇转换时间长的问题，新浪图床转换阿里图床可能会请求错误。<br />
				2、此功能主要用于本地化，解决新浪图床防盗链导致的图片无法显示而需要更换的问题，同时也可本地化其他网站的图片链接。<br />
				3、如果文章内含失效图片链接，暂未考虑此问题，故如出现转换失败情况，需保持良好心态。<br />
				4、本地化转换阿里图床不支持localhost，阿里图床转换本地化可正常转换。<br />
				5、因为转换功能并未测试完全，不确保出现各种问题，所以需要测试几次再正式开始转换，但原理都是下载原图片地址，再上传新图片地址，请知悉。
			</small>
			<form id="imgpool_conv_form" method="get" action="<?=admin_url('options-general.php');?>">
				<table width="100%" border="1" cellspacing="0" cellpadding="0">
					<thead>
						<caption>
							<input type="checkbox" id="imgpool_select_all" />全选
							<input type="radio" name="imgpool_conv_type" value="ali" <?php if($_GET["imgpool_conv_type"]=="ali"){echo "checked";}?> onClick="location.href='<?= admin_url('options-general.php?page=tle-weibo-tuchuang&imgpool_conv_type=ali&imgpool_conv_domain='.$ali_configs["tle_aliprefix"]);?>';" />阿里图床
							<input type="radio" name="imgpool_conv_type" value="local" <?php if($_GET["imgpool_conv_type"]=="local"){echo "checked";}?> onClick="location.href='<?= admin_url('options-general.php?page=tle-weibo-tuchuang&imgpool_conv_type=local&imgpool_conv_domain='.get_bloginfo("url"));?>';" />本地化
							<input type="hidden" name="t" value="imgpool_conv" />
							<input type="hidden" name="page" value="tle-weibo-tuchuang" />
							<input type="submit" value="开始转换" />
						</caption>
						<tr>
							<th>选项</th>
							<th>文章</th>
						</tr>
					</thead>
					<tbody align="center">
						<?php
						global $wpdb;
						$domain=str_replace("/","\/",$_GET["imgpool_conv_domain"]);
						$domain=str_replace(".","\.",$domain);
						$rlike="<(img|IMG).*?src=[\'|\"](?!".$domain.")(.*?)[\'|\"].*?[\/]?>";
						$rows = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_content RLIKE '".$rlike."' order by ID desc LIMIT 20");
						if(!empty($_GET["imgpool_conv_type"])&&!empty($_GET["imgpool_conv_domain"])){
							if(count($rows)>0){
								foreach($rows as $val){
									?>
									<tr>
										<td><input type="checkbox" name="imgpool_postid[]" value="<?=$val->ID;?>" /></td>
										<td><?=$val->post_title;?></td>
									</tr>
									<?php
								}
							}else{
								?>
								<tr>
									<td colspan="2">暂无需要转换的文章</td>
								</tr>
								<?php
							}
						}else{
							?>
							<tr>
								<td colspan="2">首先选择转换方式，以确定转换域名。</td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
			</form>
		</div>
	</div>
	<div id="tab-about" class="div-tab hidden" style="display: none;">
		
	</div>
</div>
<script type='text/javascript'>
<?php echo "var current_tab='".$_POST['current_tab']."';";?>
jQuery(function(jQuery){
	$("#imgpool_conv_form").submit(function(){
		var formdata = $("#imgpool_conv_form").serialize();
        $.ajax({
             type: "POST",
             url: "admin.php?page=tle-weibo-tuchuang&t=imgpool_conv&imgpool_conv_domain=<?=$_GET["imgpool_conv_domain"];?>",
             data: formdata,
             success: function(data){
                var data=JSON.parse(data);
				if(data.status=="ok"){
					alert(data.msg);
				}
				window.location.reload();
             },
             error: function(data){
                alert("请求失败："+data);
             }
        });
		return false;
	});
	
    if(jQuery('div.div-tab').length){
      if(jQuery('#current_tab').length)
        current_tab = jQuery('#current_tab').first().val();      
      if(current_tab == '')
        current_tab = jQuery('div.div-tab').first()[0].id.replace('tab-','');
      var htitle    = jQuery('#tab-title-'+current_tab).parent()[0].tagName;
      jQuery('div.div-tab').hide();
      jQuery('#tab-title-'+current_tab).addClass('nav-tab-active');
      jQuery('#tab-'+current_tab).show();
      jQuery('#current_tab').val(current_tab);
      jQuery(htitle+' a.nav-tab').on('click',function(){
        var prev_tab  = current_tab;
        current_tab   = jQuery(this)[0].id.replace('tab-title-','');
        jQuery('#tab-title-'+prev_tab).removeClass('nav-tab-active');
        jQuery(this).addClass('nav-tab-active');
        jQuery('#tab-'+prev_tab).hide();
        jQuery('#tab-'+current_tab).show();
        if(jQuery('#current_tab').length){
          jQuery('#current_tab').val(current_tab);
        }
      });
    }
	return false;
});
</script>