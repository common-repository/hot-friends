<?php
function hot_friends_options_page(){
	$options= get_option( 'hot_friends_options');
	$url=get_option('siteurl').'/wp-admin/options-general.php?page=hot_friends.php';
	if (isset($_POST['update_setting'])) {
		$options['add_author'] = $_POST['add_author'];
		if($options['add_author']){
			hot_friends_add_author();
		}else{
			hot_friends_delete_friend('thinkagain.cn');
		}
		$options['auto_add_friend'] = $_POST['auto_add_friend'];
		$options['display_comment_count'] = $_POST['display_comment_count'];
		$options['display_description'] = $_POST['display_description'];
		$options['font_size_max'] = $_POST['font_size_max'];
		$options['font_size_min'] = $_POST['font_size_min'];
		$options['font_color_max'] = trim($_POST['font_color_max']);
		$options['font_color_min'] = trim($_POST['font_color_min']);
		$options['avatar_size'] = (int)$_POST['avatar_size'];
		$options['avatar_css'] = $_POST['avatar_css'];
		$options['hot_friends_display'] = $_POST['hot_friends_display'];
		$options['limit_comment_count'] = $_POST['limit_comment_count'];
		$cat_id = hot_friends_get_cat_id($_POST['link_category']);
		if($cat_id) $options['link_category'] = $cat_id;
		$options['link_lists'] = $_POST['link_lists'];
		if($options['link_lists'] != '') {
			$options['exist_always_display']= true;
		}else{
			$options['exist_always_display']= false;
		}
		$options['mail_notification'] = $_POST['mail_notification'];
		$options['manual_update_cache'] = $_POST['manual_update_cache'];
		$options['number'] = $_POST['number'];
		//schedule
		$temp = get_option( 'hot_friends_options');
		$options['schedule'] = $temp['schedule'];
		if(	$options['schedule'] != $_POST['schedule']){
			$schedule_changed = true;
			$options['schedule'] = $_POST['schedule'];
			wp_clear_scheduled_hook( 'hot_friend_schedule_hook');
			wp_schedule_event( time(), $options['schedule'], 'hot_friend_schedule_hook' );
		}
		$options['separator'] = htmlspecialchars_decode($_POST['separator']);
		$options['type'] = $_POST['type'];
		update_option('hot_friends_options', $options);
		//update cache
		if($options['manual_update_cache']){
			hot_friends_update();
		}
		echo '<div id="message" class="updated fade"><p>';
		if(!$cat_id){
			echo __("Can not find specified link category, please check category name again.",'HotFriendstext');
		}else{
			if(	$schedule_changed ){
				echo _e("Configuration updated. Waiting for page to be reloaded...",'HotFriendstext');
				echo '<script language="javascript">setTimeout("window.open(\''.$url.'\',\'_self\');",1000);</script>';
			}else{
				echo _e("Configuration updated.",'HotFriendstext');
			}
		}
		echo '</p></div>';
	}else if (isset($_POST['set_default'])) {
		$options = hot_friends_init_options();
		update_option('hot_friends_options', $options);
		wp_clear_scheduled_hook( 'hot_friend_schedule_hook');
		wp_schedule_event( time(), $options['schedule'], 'hot_friend_schedule_hook' );
		echo '<div id="message" class="updated fade"><p>';
		echo _e("Default configuration loaded. Waiting for page to be reloaded...",'HotFriendstext');
		echo '</p></div>';
		echo '<script language="javascript">setTimeout("window.open(\''.$url.'\',\'_self\');",1000);</script>';	    
	}else if (isset($_POST['query_commentnumber_by_url'])) {
		$data = $_POST['by_url'];
		$period = $_POST['query_comment_number_by_url'];
		$result = hot_friends_query_comment_number('url',$data,$period);
		echo '<div id="message" class="updated fade"><p>';
		echo "$data ".__("has",'HotFriendstext')."$result".__("comments",'HotFriendstext');
		echo '</p></div>';
	}else if (isset($_POST['query_commentnumber_by_name'])) {
		$data = $_POST['by_name'];
		$period = $_POST['query_comment_number_by_name'];
		$result = hot_friends_query_comment_number('name',$data,$period);
		echo '<div id="message" class="updated fade"><p>';
		echo "$data ".__("has",'HotFriendstext')."$result".__("comments",'HotFriendstext');
		echo '</p></div>';
	}else if(isset($_POST['language'])) {
		$options['language'] = $_POST['language'];
		update_option('hot_friends_options', $options);
		echo '<div id="message" class="updated fade"><p>';
		echo __("Language setting updated. Waiting for page to be reloaded...",'HotFriendstext');
		echo '</p></div>';
		echo '<script language="javascript">setTimeout("window.open(\''.$url.'\',\'_self\');",1000);</script>';
	}
?>
<div class="wrap">
<h2 style="border:none">Hot Friends Options</h2>
<p style="text-align:right"><a target="_blank" href="http://wordpress.org/extend/plugins/profile/redhorsecxl"><?php _e("Try other plugins developed by author in official Wordpress plugin repository!",'HotFriendstext');?></a></p>
<form name="selectlanguage" method="post">
	<p><b><?php _e('Select Language: ','HotFriendstext');?></b>
	<input style="margin-left:10px" type="radio" name="language" value="0" <?php echo ($options['language'] == "0")?'checked="checked"':'';?> onchange="document.selectlanguage.submit();">English
	<input style="margin-left:10px" type="radio" name="language" value="1" <?php echo ($options['language'] == "1")?'checked="checked"':'';?> onchange="document.selectlanguage.submit();">Italiano
	<input style="margin-left:10px" type="radio" name="language" value="2" <?php echo ($options['language'] == "2")?'checked="checked"':'';?> onchange="document.selectlanguage.submit();">简体中文
	</p>
</form>
<form method="post">
	<div class="submit" style="text-align:right;border:none;padding-top:0px;padding-bottom:10px;border-bottom:1px solid #DADADA;">
		<input type="submit" name="set_default" value="<?php echo _e('Load default','HotFriendstext');?>" />
		<input type="submit" name="update_setting" value="<?php echo _e('Update setting','HotFriendstext');?>" />
	</div>
	<div style="padding:5px;border-bottom:1px solid #DADADA;background-color:#E4F2FD">
		<p><?php _e('Hot Friends display style: ','HotFriendstext');?>
			<label style="margin-left:10px"><input type="radio" <?php echo ($options['hot_friends_display'] == "link")?'checked="checked"':''; ?> class="tog" value="link" name="hot_friends_display"/> <?php _e('Link','HotFriendstext');?></label>
			<label style="margin-left:10px"><input type="radio" <?php echo ($options['hot_friends_display'] == "avatar")?'checked="checked"':''; ?> class="tog" value="avatar" name="hot_friends_display"/> <?php _e('Avatar','HotFriendstext');?></label>
		</p>
		<p><?php _e('Number of Hot Friends to display: ','HotFriendstext');?><input style="width: 50px;text-align: center;" name="number" type="text" value="<?php echo $options['number']; ?>" /></p>
		<p><?php _e('Rank Hot Friends based on: ','HotFriendstext');?>
			<label style="margin-left:10px"><input type="radio" <?php echo ($options['type'] == "random")?'checked="checked"':''; ?> class="tog" value="random" name="type"/> <?php _e('Random','HotFriendstext');?></label>
			<label style="margin-left:10px"><input type="radio" <?php echo ($options['type'] == "weekly")?'checked="checked"':''; ?> class="tog" value="weekly" name="type"/> <?php _e('Weekly comments number','HotFriendstext');?></label>
			<label style="margin-left:10px"><input type="radio" <?php echo ($options['type'] == "monthly")?'checked="checked"':''; ?> class="tog" value="monthly" name="type"/>  <?php _e('Monthly comments number','HotFriendstext');?></label>
			<label style="margin-left:10px"><input type="radio" <?php echo ($options['type'] == "total")?'checked="checked"':''; ?> class="tog" value="total" name="type"/>  <?php _e('Total comments number','HotFriendstext');?></label>		
		</p>
		<p><label><input name="display_comment_count" type="checkbox" class="checkbox" <?php echo ($options['display_comment_count'])?'checked="checked"':''; ?> /> <?php _e('Display comment count','HotFriendstext');?></label></p>
		<p><label><input name="display_description" type="checkbox" class="checkbox" <?php echo ($options['display_description'])?'checked="checked"':''; ?> /> <?php _e('Display description','HotFriendstext');?></label></p>
		<p><b><?php _e('Always displays following links','HotFriendstext');?></b><br/>
		<small>
			<?php _e('Note: Links displayed below will be automatically added to Hot Friends list, leave blank if you want to rank hot friends according to associated comments number (Recommended).','HotFriendstext');?>
			<br/><b><?php _e('Links must be separated by comma ","! Insensitive with "http://"','HotFriendstext');?></b>
		</small>
		<br/>
		<input style="width: 500px;height:50px;" name="link_lists" type="textarea" value="<?php echo $options['link_lists'];?>" />
		</p>
	</div>
	<div>
		<p><b><?php _e('Hot Friends avatar Options','HotFriendstext');?></b></p>
		<table style="margin-left:20px">
			<tr>
				<td style="text-align:right"><?php _e('avatar Size:','HotFriendstext');?></td>
				<td><input style="width:50px;text-align: center;" name="avatar_size" type="text" value="<?php echo $options['avatar_size']; ?>" />px.</td>
			</tr>
			<tr>
				<td><?php _e('Margin for avatar:','HotFriendstext');?></td>
				<td><input style="width:150px;text-align: center;" name="avatar_css" type="text" value="<?php echo $options['avatar_css']; ?>" /></td>
			</tr>
		</table>
		<div style="margin-left:20px">&nbsp;<small><?php _e('Note:please adjust the size and the margin of avatar according to width of sidebar. The default size and margin of avatar are (32px) and (5px 0px 0px 5px) respectively.','HotFriendstext');?></small></div>
	</div>
	<div >
		<p><b><?php _e('Hot Friends Cloud Options','HotFriendstext');?></b></p>
		<table style="margin-left:20px;">
			<tr>
				<td><?php _e('Most hot friend font size (pt) :','HotFriendstext');?></td>
				<td width="125"><input style="width: 120px;text-align: center;" name="font_size_max" type="text" value="<?php echo $options['font_size_max']; ?>" /></td>
				<td><?php _e('Color Sample:','HotFriendstext');?></td>
			</tr>
			<tr>
				<td><?php _e('Least hot friend font size (pt):','HotFriendstext');?></td>
				<td><input style="width: 120px;text-align: center;" name="font_size_min" type="text" value="<?php echo $options['font_size_min']; ?>" /></td>
				<td><?php _e('Gray: ','HotFriendstext');?>#999999&nbsp;&nbsp;</td>
				<td><span "style=background-color:#999999;width:15px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
				<td>#000000&nbsp;&nbsp;<span "style=background-color:#000000;width:15px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
			</tr>
			<tr>
				<td><?php _e('Most hot friend font color:','HotFriendstext');?></td>
				<td><input style="width: 120px;text-align: center;" name="font_color_max" type="text" value="<?php echo $options['font_color_max']; ?>" /></td>
				<td><?php _e('Blue: ','HotFriendstext');?>#0198E7&nbsp;&nbsp;</td>
				<td><span "style=background-color:#0198E7;width:15px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
				<td>#0043F7&nbsp;&nbsp;<span "style=background-color:#0043F7;width:15px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
			</tr>
			<tr>
				<td><?php _e('Least hot friend font color:','HotFriendstext');?></td>
				<td><input style="width: 120px;text-align: center;" name="font_color_min" type="text" value="<?php echo $options['font_color_min']; ?>" /></td>
				<td><?php _e('Purple: ','HotFriendstext');?>#9728A6&nbsp;&nbsp;</td>
				<td><span "style=background-color:#9728A6;width:15px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
				<td>#570144&nbsp;&nbsp;<span "style=background-color:#570144;width:15px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
			</tr>
			<tr>
				<td><?php _e('Separator:','HotFriendstext');?></td>
				<td><input style="width: 120px;text-align: center;" name="separator" type="text" value="<?php echo htmlspecialchars($options['separator']); ?>" /></td>
				<td><?php _e('Pink: ','HotFriendstext');?>#FF51D2&nbsp;&nbsp;</td>
				<td><span "style=background-color:#FF51D2;width:15px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
				<td>#E14A4F&nbsp;&nbsp;<span "style=background-color:#E14A4F;width:15px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
			</tr>
		</table>
		<span style="margin-left:20px"><small><?php _e('Note: color must be wroten in HEX format, i.e, starts with \'#\' and followed by 6 HEX numbers (0-9,A-F).','HotFriendstext');?></small></span>
		<p><b><?php _e('Current hot friends cloud syle:','HotFriendstext');?></b></p>
		<div style="border:1px dotted #333;padding:10px;margin:20px;"><?php hot_friends_cloud();?></div>
	</div>
	<div style="padding:5px;border-bottom:1px solid #DADADA;background-color:#E4F2FD">
		<p>
			<label><input name="auto_add_friend" type="checkbox" class="checkbox" <?php echo ($options['auto_add_friend'])?'checked="checked"':''; ?> /></label>
			 <?php _e('Automatically add commenter to Blogroll while his/her comments number over ','HotFriendstext');?>
			<input style="width: 25px;text-align: center;" name="limit_comment_count" type="text" value="<?php echo $options['limit_comment_count']; ?>" />
		</p>
		<ul>
			<li><?php _e('Add commenter to link category: ','HotFriendstext');?><input style="width: 100px;text-align: center;" name="link_category" type="text" value="<?php echo hot_friends_get_term_name($options['link_category']); ?>" />
				<br/><small><?php _e('Please input category name exactly. Default is Blogroll.','HotFriendstext');?>
				<?php _e('Click','HotFriendstext');?>&nbsp;<a href="<?php echo get_option('siteurl').'/wp-admin/edit-link-categories.php'?>"><?php _e('here','HotFriendstext');?></a>&nbsp;<?php _e('to manage link category.','HotFriendstext');?>
				</small>
			</li>
		</ul>
		<p><label><input name="mail_notification" type="checkbox" class="checkbox" <?php echo ($options['mail_notification'])?'checked="checked"':''; ?> /> <?php _e('Mail notification when new commenter is been automatically added to Blogroll','HotFriendstext');?></label></p>
	</div>
	<div>
		<p><?php _e('Update Schedule: ','HotFriendstext');?>
			<label style="margin-left:10px"><input type="radio" <?php echo ($options['schedule'] == "hourly")?'checked="checked"':''; ?> class="tog" value="hourly" name="schedule"/> <?php _e('Hourly','HotFriendstext');?></label>
			<label style="margin-left:10px"><input type="radio" <?php echo ($options['schedule'] == "daily")?'checked="checked"':''; ?> class="tog" value="daily" name="schedule"/> <?php _e('Daily','HotFriendstext');?></label>
			<label style="margin-left:10px"><input type="radio" <?php echo ($options['schedule'] == "hot_friends_weekly")?'checked="checked"':''; ?> class="tog" value="hot_friends_weekly" name="schedule"/> <?php _e('Weekly','HotFriendstext');?></label>
			<label style="margin-left:10px"><input type="radio" <?php echo ($options['schedule'] == "hot_friends_monthly")?'checked="checked"':''; ?> class="tog" value="hot_friends_monthly" name="schedule"/> <?php _e('Monthly','HotFriendstext');?></label>		
		</p>
		<?php if (wp_next_scheduled('hot_friend_schedule_hook')) {
			echo '<p>';
			echo _e('Current time: ','HotFriendstext');
			echo '<u>'.date('Y-m-d H:i:s',time()).'</u>, ';
			echo _e('Next update time: ','HotFriendstext');
			echo '<u>'.date('Y-m-d H:i:s',wp_next_scheduled('hot_friend_schedule_hook')).'</u>';
			echo '</p>';
		}
		?>
	</div>
	<div style="padding:5px;border-bottom:1px solid #DADADA;background-color:#E4F2FD"><p>
		<label><input name="manual_update_cache" type="checkbox" class="checkbox" <?php echo ($options['manual_update_cache'])?'checked="checked"':''; ?> /></label>
		 <b><?php _e('Manually update cache','HotFriendstext');?></b>
		 <br/><small><?php _e('Note: Be sure make this option checked to update cache in case of any change occurs, e.g, Hot Friends number or always display links changed.','HotFriendstext');?></small>
		</p>
	</div>
	<div >
		<p ><label><input name="add_author" type="checkbox" class="checkbox" <?php echo ($options['add_author'])?'checked="checked"':''; ?>/></label>
			<?php _e('Add Author to Blogroll','HotFriendstext');?>,
			<?php _e(' or ','HotFriendstext');?><a href="mailto:lovepcblog@gmail.com"><?php _e('feedback','HotFriendstext');?></a>
			<?php _e('to author.','HotFriendstext');?>
		</p>
	</div>
	<div class="submit" style="text-align:right;border:none;padding-top:0px;padding-bottom:10px;border-bottom:1px solid #DADADA;">
		<input type="submit" name="set_default" value="<?php echo _e('Load default','HotFriendstext');?>" />
		<input type="submit" name="update_setting" value="<?php echo _e('Update setting','HotFriendstext');?>" />
	</div>
	<div>
		<p><b><?php _e('Comment Number Query','HotFriendstext');?></b></p>
		<table style="margin-left:20px;">
			<tr>
				<td><?php _e('By Url: ','HotFriendstext');?></td>
				<td><input style="width: 250px;" name="by_url" type="text" value="" /></td>
				<td>
					<label style="margin-left:10px"><input type="radio" <?php echo ($options['query_comment_number_by_url'] == "weekly comments")?'checked="checked"':''; ?> class="tog" value="weekly comments" name="query_comment_number_by_url"/> <?php _e('Weekly comments','HotFriendstext');?></label>
					<label style="margin-left:10px"><input type="radio" <?php echo ($options['query_comment_number_by_url'] == "monthly comments")?'checked="checked"':''; ?> class="tog" value="monthly comments" name="query_comment_number_by_url"/> <?php _e('Monthly comments','HotFriendstext');?></label>
					<label style="margin-left:10px"><input type="radio" <?php echo ($options['query_comment_number_by_url'] == "total comments")?'checked="checked"':''; ?> class="tog" value="total comments" name="query_comment_number_by_url"/> <?php _e('Total comments','HotFriendstext');?></label>
					<input type="submit" name="query_commentnumber_by_url" value="<?php echo _e('Query','HotFriendstext');?>" />
				</td>
			</tr>
			<tr>
				<td><?php _e('By Name: ','HotFriendstext');?></td>
				<td><input style="width: 250px;" name="by_name" type="text" value="" /></td>
				<td>
					<label style="margin-left:10px"><input type="radio" <?php echo ($options['query_comment_number_by_name'] == "weekly comments")?'checked="checked"':''; ?> class="tog" value="weekly comments" name="query_comment_number_by_name"/> <?php _e('Weekly comments','HotFriendstext');?></label>
					<label style="margin-left:10px"><input type="radio" <?php echo ($options['query_comment_number_by_name'] == "monthly comments")?'checked="checked"':''; ?> class="tog" value="monthly comments" name="query_comment_number_by_name"/> <?php _e('Monthly comments','HotFriendstext');?></label>
					<label style="margin-left:10px"><input type="radio" <?php echo ($options['query_comment_number_by_name'] == "total comments")?'checked="checked"':''; ?> class="tog" value="total comments" name="query_comment_number_by_name"/> <?php _e('Total comments','HotFriendstext');?></label>
					<input type="submit" name="query_commentnumber_by_name" value="<?php echo _e('Query','HotFriendstext');?>" />	
				</td>
			</tr>
		</table>
	</div>
	<div>
		<p><b><?php _e('Import/Export to OPML file','HotFriendstext');?></b></p>
		<ul>
			<li><?php _e('Click','HotFriendstext');?>&nbsp;<a target="_blank" href="<?php echo get_option('siteurl');?>/wp-admin/link-import.php"><?php _e('here','HotFriendstext');?></a>&nbsp;<?php _e('to import links from opml file.','HotFriendstext');?></li>
			<li><?php _e('Click','HotFriendstext');?>&nbsp;<a target="_blank" href="<?php echo get_option('siteurl');?>/wp-links-opml.php"><?php _e('here','HotFriendstext');?></a>&nbsp;<?php _e('to export links to opml file.','HotFriendstext');?></li>
		</ul>
	</div>
</form>
</div>
<?php
}
function hot_friends_add_author(){
	global $wpdb;
	$result = $wpdb->get_var("SELECT count(*) FROM $wpdb->links WHERE $wpdb->links.link_url LIKE '%thinkagain.cn%'");
	if($result > 0) return;
	$linkdata["link_name"] = 'Think Again';
	$linkdata["url"] = 'http://www.thinkagain.cn/';
	$linkdata["link_description"] = 'INSPIRE THINKING, INSPIRE CREATIVITY, INSPIRE FUTURE.';
	$linkdata["link_rss"] = "http://feed.thinkagain.cn";
	$check = $wpdb->get_var("SELECT link_id FROM $wpdb->links WHERE link_url='{$url}'");
	$link = array( 'link_url' => $linkdata["url"], 'link_name' => $wpdb->escape($linkdata["link_name"]), 'link_category' => array(get_option('default_link_category')), 'link_description' => $wpdb->escape($linkdata["link_description"]), 'link_owner' => 1, 'link_image' => $linkdata["logourl"], 'link_visible' => $linkdata["approved"]==0?"Y":"N",'link_rss'=> $linkdata["link_rss"]);
	if ($check){
		//$link["link_id"]=$check;
		//return wp_update_link( $link );
		return;
	}else{
		return wp_insert_link($link);
	}
}
function hot_friends_add_friend($name,$url,$email,$description){
	global $wpdb;
	require_once("wp-admin/includes/bookmark.php");
	$hf_options = get_option( 'hot_friends_options');
	$tempurl = hot_friends_trim_url($url);
	$result = $wpdb->get_var("SELECT count(*) FROM $wpdb->links WHERE $wpdb->links.link_url LIKE '%$tempurl%'");
	if($result > 0)	return;
	$linkdata["link_name"] = $name;
	$linkdata["url"] = $url;
	$linkdata["link_description"] = $description;
	$linkdata["link_rss"] = "";
	$linkdata["link_category"] = $hf_options['link_category'];
	$link = array( 'link_url' => $linkdata["url"], 'link_name' => $wpdb->escape($linkdata["link_name"]), 'link_category' => array($linkdata["link_category"]), 'link_description' => $wpdb->escape($linkdata["link_description"]), 'link_owner' => 1, 'link_image' => $linkdata["logourl"], 'link_visible' => $linkdata["approved"]==0?"Y":"N",'link_rss'=> $linkdata["link_rss"]);
	wp_insert_link($link);
	if($hf_options['mail_notification']){
		$to=strtolower($email);
		$date = date('Y-m-d',time());
		$subject = "Congratulation you just been added to ".get_option('blogname')."'s blogroll";
		$message = "Congratulation $name! You just been added to ".get_option('blogname')."'s blogroll. <br/>";
		$message .= "Thank you for your participation. Keep in touch.<br/><br/>";
		$message .= "Your Sincerely<br/>";
		$message .= '<a href="'.get_option('siteurl').'">'.get_option('blogname').'</a><br/>';
		$message .= $date;
		hot_friends_send_email($to,$subject,$message);
		$to=strtolower(get_option('admin_email'));
		$subject = "New friend $name just been added to blogroll";
		$message = "New friend $name just been added to blogroll.<br/>";
		$message .= 'Please login to '. '<a href="'.get_option('siteurl').'/wp-admin/link-manager.php"'.'>link manager</a> to manually configure link name and other necessary info.<br/><br/>';
		$message .= $date;
		hot_friends_send_email($to,$subject,$message);
	}
}
function hot_friends_delete_friend($url){
	global $wpdb;
	$url = hot_friends_trim_url($url);
	$sql= "SELECT $wpdb->links.link_id FROM $wpdb->links WHERE $wpdb->links.link_url LIKE '%$url%'";
	$link_id = $wpdb->get_var($sql);
	wp_delete_link($link_id);
}
function hot_friends_add_schedule_options() {
	return array(
	'hot_friends_weekly' => array('interval' => 604800, 'display' => 'Once Weekly'),
	'hot_friends_monthly' => array('interval' => 2592000, 'display' => 'Once Monthly'),
	);
}
add_filter('cron_schedules', 'hot_friends_add_schedule_options');
//get commenter email from url
function hot_friends_get_email($url){
	global $wpdb;
	$url = hot_friends_trim_url($url);
	$sql = " SELECT DISTINCT $wpdb->comments.comment_author_email FROM $wpdb->comments WHERE $wpdb->comments.comment_author_url LIKE '%$url%'";
	$email= $wpdb->get_var($sql);
	return $email;
}
function hot_friends_send_email($to,$subject,$message){
	$blogname = get_option('blogname');
	$charset = get_option('blog_charset');
	$headers  = "From: $blogname \n" ;
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: text/html;charset=\"$charset\"\n";
	return wp_mail($to, $subject, $message, $headers);
}
function hot_friends_get_cat_id($name){
	global $wpdb;
	$sql = "
SELECT $wpdb->terms.term_id FROM $wpdb->terms, $wpdb->term_taxonomy
WHERE $wpdb->term_taxonomy.taxonomy = 'link_category'
AND $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id
AND $wpdb->terms.name = '$name'
	";
	$id = $wpdb->get_var($sql);
	if($id){
		return $id;
	}else{
		return false;
	}
}
function hot_friends_get_term_name($id){
	global $wpdb;
	$sql = "SELECT $wpdb->terms.name FROM $wpdb->terms WHERE $wpdb->terms.term_id = '$id'";
	$name = $wpdb->get_var($sql);
	if($name ){
		return $name;
	}else{
		return false;
	}
}
function hot_friends_get_avatar($email,$size){
	$avatar_default = get_option('avatar_default');
	if ( empty($avatar_default) ){
		$default = 'mystery';
	}else{
		$default = $avatar_default;
	}
	if ( 'mystery' == $default ){
		$default = "http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?s={$size}"; 
	}elseif ( 'blank' == $default ){
		$default = includes_url('images/blank.gif');
	}elseif ( !empty($email) && 'avatar_default' == $default ){
		$default = '';
	}elseif ( 'avatar_default' == $default ){
		$default = "http://www.gravatar.com/avatar/s={$size}";
	}elseif ( empty($email) ){
		$default = "http://www.gravatar.com/avatar/?d=$default&amp;s={$size}";
	}	
	if ( !empty($email) ) {
		$out = 'http://www.gravatar.com/avatar/';
		$out .= md5( strtolower( $email ) );
		$out .= '?s='.$size;
		$out .= '&amp;d=' . urlencode( $default );

		$rating = get_option('avatar_rating');
		if ( !empty( $rating ) )
			$out .= "&amp;r={$rating}";
		$avatar = $out;
	} else {
		$avatar = $default;
	}
	return $avatar;
}
function hot_friends_init_options(){
	$locale = get_locale();
	if($locale == "it_IT"){
		$language = 1;
	}elseif($locale == "zh_CN"){
		$language = 2;
	}else{
		$language = 0;
	}
	$options = array();
	$options['add_author'] = false;
	$options['auto_add_friend'] = false;
	$options['display_comment_count'] = false;
	$options['display_description'] = true;
	$options['exist_always_display'] = false;
	$options['font_size_max'] = 30;
	$options['font_size_min'] = 10;
	$options['font_color_max'] = '#000000'; 
	$options['font_color_min'] = '#999999';
	$options['avatar_size'] = 32;
	$options['avatar_css'] = '5px 0px 0px 5px';
	$options['hot_friends_display'] = 'link';
	$options['language'] = $language;
	$options['link_category'] = get_option('default_link_category');
	$options['limit_comment_count'] = 5;
	$options['link_lists'] = '';
	$options['mail_notification'] = true;
	$options['manual_update_cache'] = true;
	$options['number'] = 20;
	$options['query_comment_number_by_url'] = 'total comments';
	$options['query_comment_number_by_name'] = 'total comments';
	$options['schedule'] = 'hourly';
	$options['separator'] = '&nbsp;-&nbsp;';
	$options['type'] = "total";
	return $options;
}
?>