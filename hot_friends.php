<?php
/*
Plugin Name: Hot Friends
Plugin URI: http://www.thinkagain.cn/archives/985.html
Description: Generate Hot Friends list / cloud by ranking links in blogroll based on its associated comment number or other ways specified by admin.
Author: ThinkAgain
Version: 1.4.1
Author URI: http://www.thinkagain.cn/archives/985.html
*/
/*
[1.4.1]		2008-12-08: Fixed bug to open hot friends link in current / new window according to the configuration of associated link. 
[1.4]		2008-12-07:	Add avatar supports.
[1.3.2] 	2008-10-25:	Changed file name of functions.php to hot_friends_functions.php to avoid conflict with some themes (just in case).
[1.3.1] 	2008-09-26:	fixed bug to avoid zero division in case of all links have same comment counts.
[1.3]		2008-09-09:	 Add options for customizing output style of hot friends cloud.
					Add options for language pack selection in dashboard.
					Several bugs fixed.
[1.2.3] 	2008-08-27:	Add new option that allows admin to specify the link category which commenter will be automatically added into.
[1.2.2] 	2008-08-17:	Fixed code to make character correctly displayed in mail subject.
[1.2.1] 	2008-08-16: 	Fixed code to exclude admin from automatically adding to blogroll. 
[1.2]   	2008-08-16: 	Fixed function bug of automatically add friends. Thanks <a href="http://www.girlqiqi.cn/">QiQi</a>.
[1.1]   	2008-08-16: 	Revised import/export links to opml file code. Thanks <a href="http://photozero.net/">Neekey</a>. Tested up to 2.6.1. 
[1.0]   	2008-08-15: 	1.0 released.
*/
$options = get_option( 'hot_friends_options');
if($options['language'] == 0){
	load_textdomain('HotFriendstext', dirname(__FILE__) . "/hotfriends-en_US.mo");
}elseif($options['language'] == 1){
	load_textdomain('HotFriendstext', dirname(__FILE__) . "/hotfriends-it_IT.mo");
}elseif($options['language'] == 2){
	load_textdomain('HotFriendstext', dirname(__FILE__) . "/hotfriends-zh_CN.mo");
}
include_once( 'hot_friends_functions.php' );
function hot_friends(){
	$hf_options= get_option( 'hot_friends_options');
	$hot_friends_display = $hf_options['hot_friends_display'];
	$hf_cache = get_option('hot_friends_cache');
	if (empty($hf_cache)){
		hot_friends_update();
		$hf_cache = get_option('hot_friends_cache');
	}
	$type = 'list';
	switch($hot_friends_display){
		case 'link':
			$cached_friend = $hf_cache['list'];
			echo create_hot_friends($cached_friend,$type);
			break;
		case 'avatar':
			echo create_hot_friends_avatars($type);
			break;
	}
}
function hot_friends_cloud(){
	$hf = get_option('hot_friends_cache');
	$type = "cloud";
	if ($hf){
		$cached_friend = $hf['cloud'];
		echo create_hot_friends($cached_friend,$type);
	}else{
		hot_friends_update();
		$hf = get_option('hot_friends_cache');
		$cached_friend = $hf['cloud'];
		echo create_hot_friends($cached_friend,$type);
		//echo "updated";
	}
}
function init_hot_friends(){
	$hf_options= get_option( 'hot_friends_options');
	$init_options = hot_friends_init_options();
	if ($hf_options) {
		$diff = array_diff_assoc($init_options,$hf_options);
		$hf_options = array_merge($diff,$hf_options);
		update_option('hot_friends_options', $hf_options);
	}else{
		add_option( 'hot_friends_options', $init_options);
	}
	$hf_cache = get_option( 'hot_friends_cache');
	if(!$hf_cache) add_option( 'hot_friends_cache', $hf_cache);
	hot_friends_update();
	if (!wp_next_scheduled('hot_friend_schedule_hook')) {
		wp_schedule_event( time(), $hf_options['schedule'], 'hot_friend_schedule_hook' );
	}
	hot_friends_add_author();
}
function create_hot_friends($cached_friend,$type="list"){
	$output = "";
	$count =1;
	$hf_options= get_option( 'hot_friends_options');
	$number = $hf_options['number'] ;
	$display_comment_count =$hf_options['display_comment_count'];
	if(($hf_options['type'] == 'random')and ($type != "cloud")){
		shuffle($cached_friend);
		$output = '<ul class="hot_friend">';
		foreach ($cached_friend as $friend){
			if ($count <= $number){
				$name=$friend['name'];
				$url=$friend['url'];
				$title = 'title="';
				if($display_comment_count){
					$comment_count =$friend['comment'];
					$title .= $comment_count.__('Comments ','HotFriendstext');
				}
				if($hf_options['display_description']){
					$title .=$friend['description'];
				}
				$target = $friend['link_target'];
				if($target){
					$target = " target='$target' ";
				}
				$title .='"'; 
				$output .= '<li>';
				$output .= '<a '.$target.$title.' href="'.$url.'">'.$name."</a>";
				$output .= '</li>';
				$count ++;
			}
		}
		$output .= '</ul>';
		return $output;
	}
	switch($type){
		case "list":
			$output = '<ul class="hot_friend">';
			foreach ($cached_friend as $friend){
				$name=$friend['name'];
				$url=$friend['url'];
				$title = 'title="';
				if($display_comment_count){
					$comment_count =$friend['comment'];
					$title .= $comment_count.__('Comments ','HotFriendstext');
				}
				if($hf_options['display_description']){
					$title .=$friend['description'];
				}
				$title .='"';
				$target = $friend['link_target'];
				if($target){
					$target = " target='$target' ";
				}
				$output .= '<li>';
				$output .= '<a '. $target.$title.' href="'.$url.'">'.$name."</a>";
				$output .= '</li>';
			}
			$output .= '</ul>';
			break;
		case "cloud":
			foreach ($cached_friend as $key => $row){
				$comment[$key]  = $row['comment'];
			}
			array_multisort($comment, SORT_DESC, $cached_friend);
			$first = $cached_friend[0];
			$last = $cached_friend[count($cached_friend)-1];
			extract($first,EXTR_OVERWRITE);
			$maxcount = $comment;
			extract($last,EXTR_OVERWRITE);
			$mincount = $comment;
			shuffle($cached_friend);
			$separator = $hf_options['separator'] ;
			$tempoutput = array();
			foreach ($cached_friend as $key=>$friend){
				$name=$friend['name'];
				$url=$friend['url'];
				$comment_count =$friend['comment'];
				$title = '';
				if($display_comment_count){
					$title .= $comment_count.__('Comments ','HotFriendstext');
				}
				if($hf_options['display_description']){
					$title .=$friend['description'];
				}
				$target = $friend['link_target'];
				$fontsize =hot_friends_fontsize($mincount,$maxcount,$comment_count);
				if($maxcount != $mincount){
					$lineheight = ($comment_count-$mincount)/($maxcount-$mincount).'em';
				}else{
					$lineheight = ($comment_count-$mincount).'em';
				}
				$color = hot_friends_fontcolor($mincount,$maxcount,$comment_count);
				$tempoutput["$key"] = "<a style=\"font-size:{$fontsize}pt;color:{$color}\" title=\"$title\" target=\"{$target}\" href=\"{$url}\">{$name}</a>";
			}
			$output = implode($separator,$tempoutput);
			break;
	}
	return $output;
}
function create_hot_friends_avatars($type){
	$hf_options= get_option( 'hot_friends_options');
	$display_description = $hf_options['display_description'];
	$display_comment_count = $hf_options['display_comment_count'];
	$size = $hf_options['avatar_size'];
	$margin = 'margin:'.$hf_options['avatar_css'];
	$number = $hf_options['number'];
	$hf_cache = get_option('hot_friends_cache');
	if($type == 'list' and $hf_options['type'] == 'random'){
		$cached_friend = ($hf_cache['cloud']);
		shuffle($cached_friend);
		$cached_friend = array_slice($cached_friend,0,$number);
	}elseif($type == 'list'){
		$cached_friend = $hf_cache['list'];
	}elseif($type == 'cloud'){
		$cached_friend = $hf_cache['cloud'];
		shuffle($cached_friend);
	}
	if($cached_friend){
		foreach($cached_friend as $friend){
			$url = $friend['url'];
			$title ='';
			if($display_comment_count){
				$comment_count =$friend['comment'];
				$title = $comment_count.__('Comments ','HotFriendstext');
			}
			if($display_description){
				$title .= $friend['description'];
			}
			$target = $friend['link_target'];
			if($target){
				$target = "target='$target'";
			}
			$alt = $friend['name'];
			$avatar = $friend['avatar'];
			$avatar = "<img style=\"{$margin}\" height=\"{$size}\" width=\"{$size}\" src=\"{$avatar}\" alt=\"{$alt}\" />";
			$output .= "<span><a href=\"{$url}\" {$target} title=\"{$title}\">{$avatar}</a></span>";
		}
	}
	return $output;
}
function hot_friends_update(){
	global $wpdb;
	$hf_options= get_option( 'hot_friends_options');
	$number = $hf_options['number'];
	$avatar_size = $hf_options['avatar_size'];
	$ht_cache = array();
	$cached_list = array();
	$cached_cloud = array();
	$templist = array();
	$tempcloud = array();
	$typelist = "list";
	$typecloud = "cloud";
	$get_friend = "SELECT * FROM $wpdb->links WHERE $wpdb->links.link_visible='Y'";
	$friends = $wpdb->get_results($get_friend);
	foreach ($friends as $friend){
		$templist['name'] = $friend->link_name;
		$templist['url']=$friend->link_url;
		$templisturl = hot_friends_trim_url($friend->link_url);
		$templist['description'] = $friend->link_description;
		$templist['link_target'] = $friend->link_target;
		$templist['comment'] = hot_friends_get_count($templisturl,$typelist);
		$templist['email'] = hot_friends_get_email($templisturl);
		$templist['avatar'] = hot_friends_get_avatar($templist['email'],$avatar_size);
		$cached_list[$friend->link_id] = $templist;
		
		$tempcloud['name'] = $friend->link_name;
		$tempcloud['url']=$friend->link_url;
		$tempcloudurl = hot_friends_trim_url($friend->link_url);
		$tempcloud['description'] = $friend->link_description;
		$tempcloud['link_target'] = $friend->link_target;
		$tempcloud['comment'] = hot_friends_get_count($tempcloudurl,$typecloud);
		$tempcloud['email'] = hot_friends_get_email($tempcloudurl);
		$tempcloud['avatar'] = hot_friends_get_avatar($tempcloud['email'],$avatar_size);
		$cached_cloud[$friend->link_id] = $tempcloud;
	}
	$hf_options= get_option( 'hot_friends_options');
	if (($hf_options['exist_always_display']) and ($hf_options['link_lists'] !='')){
		$hf_cache['list'] = hot_friends_update_alternative($cached_list);
	}else{
		foreach ($cached_list as $key => $row){
			$comment[$key]  = $row['comment'];
		}
		array_multisort($comment, SORT_DESC, $cached_list);
		$temp = array_slice($cached_list,0,$number);
		$hf_cache['list'] = $temp;
	}
	$hf_cache['cloud'] = $cached_cloud;
	update_option( 'hot_friends_cache', $hf_cache);
}
function hot_friends_update_alternative($cached_list){
	global $wpdb;
	$hf_options= get_option( 'hot_friends_options');
	$link_lists = $hf_options['link_lists'];
	$avatar_size = $hf_options['avatar_size'];
	$link_lists = explode(',',$link_lists);
	$temp = array();
	$always_friends = array();
	foreach($link_lists as $link){
		$url = hot_friends_trim_url($link);
		$friend = $wpdb->get_row("SELECT * FROM $wpdb->links WHERE $wpdb->links.link_url LIKE '%$url%'");
		$temp['name'] = $friend->link_name;
		$temp['url']=$friend->link_url;
		$temp['description'] = $friend->link_description;
		$temp['link_target'] = $friend->link_target;
		$temp['comment'] = hot_friends_get_count($url,'list');
		$temp['email'] = hot_friends_get_email($temp['url']);
		$temp['avatar'] = hot_friends_get_avatar($temp['email'],$avatar_size);
		$always_friends[$friend->link_id] = $temp;
	}
	$total_friends = $cached_list;
	$remain_friends = array_diff_assoc($total_friends,$always_friends);
	$number = $hf_options['number'];
	$remain_number = $number - count($always_friends);
	foreach ($remain_friends as $key => $row){
		$comment[$key]  = $row['comment'];
	}
	array_multisort($comment, SORT_DESC, $remain_friends);
	$remain_friends = array_slice($remain_friends,0,$remain_number);
	$cache = array_merge($always_friends,$remain_friends);
	return $cache;
}
function hot_friends_get_count($url,$type){
	global $wpdb;
	$hf_options= get_option( 'hot_friends_options');
	$temp = $hf_options['type'];
	switch($temp){
		case "weekly":
			$period = 3600*24*7;
			break;
		case "monthly":
			$period = 3600*24*30;
			break;
		case "total":
			$period = 0;
			break;
		case "random":
			$period = 0;
	}
	switch($type){
		case "cloud":
			$get_count_sql = "SELECT count(*) FROM $wpdb->comments WHERE $wpdb->comments.comment_author_url like '%$url%' and $wpdb->comments.comment_approved = '1' ";
			$count =$wpdb->get_var($get_count_sql);
			break;
		case "list";
			$starttime = time() - $period;
			$startdate = date('y-m-d',$starttime);
			if (($temp == "random")||($temp == "total")){
				$get_count_sql = "SELECT count(*) FROM $wpdb->comments WHERE $wpdb->comments.comment_author_url like '%$url%' and $wpdb->comments.comment_approved = '1' ";
			}else{	
				$get_count_sql = "SELECT count(*) FROM $wpdb->comments WHERE $wpdb->comments.comment_date>'$startdate' and $wpdb->comments.comment_approved = '1' and $wpdb->comments.comment_author_url like '%$url%'";
			}
			$count =$wpdb->get_var($get_count_sql);
			break;
	}
	return $count;
}
function hot_friends_query_comment_number($type,$data,$period){
	global $wpdb;
	if($type=='url'){
		$url = hot_friends_trim_url($data);
		$sql = "SELECT count(*) FROM $wpdb->comments WHERE $wpdb->comments.comment_author_url like '%$url%'";
	}elseif($type=='name'){
		$name = $data;
		$sql = "SELECT count(*) FROM $wpdb->comments WHERE $wpdb->comments.comment_author like '%$name%'";
	}else{
		return;
	}
	switch($period){
		case "weekly comments":
			$time = time() - 3600*24*7;
			$date = date('y-m-d',$time);
			//echo $data.'<hr>';
			$sql .= "AND $wpdb->comments.comment_date > '$date' AND $wpdb->comments.comment_approved = '1' ";
			break;
		case "monthly comments":
			$time = time() - 3600*24*30;
			$date = date('y-m-d',$time);
			$sql .= "AND $wpdb->comments.comment_date > '$date' AND $wpdb->comments.comment_approved = '1' ";
			break;
		case "total comments":
			$sql .= "AND $wpdb->comments.comment_approved = '1' ";
			break;
	}
	$count =$wpdb->get_var($sql);
	return $count;
}
function hot_friends_trim_url($url){
	preg_match("/^(http:\/\/)?([^\/]+)/i", $url, $matches);
	$host = str_replace("www.","",$matches[2]);
	return $host;
}
function hot_friends_fontsize($mincount,$maxcount,$current){
	$hf_options= get_option( 'hot_friends_options');
	$maxfontsize = $hf_options['font_size_max'];
	$minfontsize = $hf_options['font_size_min'];
	$scope = $maxcount-$mincount;
	if($scope==0) $scope = 1;
	$step = ($maxfontsize-$minfontsize)/$scope;
	$fontsize = $minfontsize + ($current-$mincount)*$step;
	return $fontsize;
}
function hot_friends_fontcolor($mincount,$maxcount,$current){
	$hf_options= get_option( 'hot_friends_options');
	$font_color_max = $hf_options['font_color_max'];
	$font_color_min = $hf_options['font_color_min'];
	if(($font_color_min == "") or ($font_color_max == "")) return $color="";
	$min_r = hexdec(substr($font_color_min, 1, 2));
	$min_g = hexdec(substr($font_color_min, 3, 2));
	$min_b = hexdec(substr($font_color_min, 5, 2));
	$max_r = hexdec(substr($font_color_max, 1, 2));
	$max_g = hexdec(substr($font_color_max, 3, 2));
	$max_b = hexdec(substr($font_color_max, 5, 2));
	if($maxcount == $mincount){
		$scope =1;
	}else{
		$scope = $maxcount-$mincount;
	}
	$step_r = ($max_r-$min_r)/$scope;
	$step_g = ($max_g-$min_g)/$scope;
	$step_b = ($max_b-$min_b)/$scope;
	$r = dechex(intval((($current - $mincount) * $step_r) + $min_r));
	$g = dechex(intval((($current - $mincount) * $step_g) + $min_g));
	$b = dechex(intval((($current - $mincount) * $step_b) + $min_b));
	if (strlen($r) == 1) $r = "0" . $r;
	if (strlen($g) == 1) $g = "0" . $g;
	if (strlen($b) == 1) $b = "0" . $b;
	$color = "#$r$g$b";
	return $color;
}
//widget
function widget_hot_friends() {
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;
	function widget_hot_friends_module($args) {	
		extract($args);
		$options = get_option('widget_hot_friends');
		$title = $options['title'];
		if ( empty($title) )
			$title = 'Hot Friends';
		echo $before_widget . $before_title . $title . $after_title;
		hot_friends();
		echo $after_widget;
	}
	
function widget_hot_friends_control() {			
	$options = $newoptions = get_option('widget_hot_friends');
	if ( $_POST["hot_friends-submit"] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["hot_friends-title"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_hot_friends', $options);
	}
	$title = attribute_escape($options['title']);
	$number = $options['number'];
	$display_comment_count = $options['display_comment_count'];
	?>
	<p>
		<label for="hot_friends-title"><?php _e('Title:','HotFriendstext'); ?> <input style="width: 250px;" id="hot_friends-title" name="hot_friends-title" type="text" value="<?php echo $title; ?>" /></label>
		<br/>
		<?php _e('Please configure Hot friends via ','HotFriendstext');?><a href="<?php echo get_option('siteurl');?>/wp-admin/options-general.php?page=hot_friends.php">Hot Friends option page.</a>
	</p>
	<input type="hidden" id="hot_friends-submit" name="hot_friends-submit" value="1" />
	<?php
		}
	register_sidebar_widget('Hot Friends', 'widget_hot_friends_module');
	register_widget_control('Hot Friends', 'widget_hot_friends_control');
	}
	add_action('plugins_loaded', 'widget_hot_friends');
function hot_friends_deactivation(){
	global $wpdb;
	$remove_options_sql1 = "DELETE FROM $wpdb->options WHERE $wpdb->options.option_name='hot_friends_options'";
	$remove_options_sql2= "DELETE FROM $wpdb->options WHERE $wpdb->options.option_name='widget_hot_friends'";
	$remove_options_sql3= "DELETE FROM $wpdb->options WHERE $wpdb->options.option_name='hot_friends_cache'";
	$wpdb->query($remove_options_sql1);
	$wpdb->query($remove_options_sql2);
	$wpdb->query($remove_options_sql3);
	wp_clear_scheduled_hook('hot_friend_schedule_hook');
}
//automatically add commentor to blogroll list while his total comments over specified number.
function hot_friends_verify_friend($cid){
	if (current_user_can('switch_themes')) return;
	$hf = get_option('hot_friends_options');
	if(!$hf['auto_add_friend']) return;
	global $wpdb;
	$bound = $hf['limit_comment_count'];
	$cid = (int)$cid;
	$data = $wpdb->get_row("SELECT comment_author_email,comment_author,comment_author_url FROM $wpdb->comments WHERE $wpdb->comments.comment_ID = '$cid' AND $wpdb->comments.comment_approved='1'");
	$friends = $wpdb->get_col("SELECT link_url FROM $wpdb->links");
	$email = $data->comment_author_email;
	$url = $data->comment_author_url;
	$urltrim = hot_friends_trim_url($url);
	$name = $data->comment_author;
	for($i=0;$i<count($friends);$i++){
		$b[$i] = hot_friends_trim_url($friends[$i]);
	}
	if ((!in_array($urltrim,$b)) and (hot_friends_get_count($urltrim,"cloud")>= $bound)){
		$description = $name;
		hot_friends_add_friend($name,$url,$email,$description);
	}else{
		return;
	}
}
function hot_friends_add_option() {
	if (function_exists('hot_friends_options_page')) {
		add_options_page('Hot Friends Options', 'Hot Friends Options',8, basename(__FILE__), 'hot_friends_options_page');
	}//add_options_page(page_title, menu_title, access_level/capability, file, [function]);
}
register_activation_hook(__FILE__, 'init_hot_friends');
register_deactivation_hook(__FILE__,'hot_friends_deactivation');
add_action( 'hot_friend_schedule_hook', 'hot_friends_update' );
add_action('comment_post', create_function('$cid', 'return hot_friends_verify_friend($cid);'));
add_action('admin_menu', 'hot_friends_add_option');
?>