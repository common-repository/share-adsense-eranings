<?php 
/* 
Plugin Name: Adsense Revenue Sharing
Version: 1.2
Plugin URI: http://www.articlecity.info/
Description: Allows you to simply insert Google Adsense  ads inside your posts where you see fit and share your revenue with your friends and co-authors. Go to Options>Revenue Sharing to edit the options. Please make sure you read <a href="https://www.google.com/adsense/policies">Google's TOS</a> before using this plugin!
Author URI: http://www.articlecity.info/
*/ 

add_action('admin_head', 'mq_rs_cssjs');
add_action('admin_menu', 'mq_rs_maiq_add_pages');
add_filter('the_content', 'mq_rs_add_adsense'); 
add_filter('admin_footer', 'mq_rs_adsense_quicktag');
add_action('activate_adsense-revenue-sharing/adsense.php', 'mq_rs_install');
add_action('deactivate_adsense-revenue-sharing/adsense.php', 'mq_rs_uninstall');  

global $wpdb, $mq_rs_table_one, $mq_rs_table_two, $mq_rs_pub, $mq_rs_settings, $mq_rs_posts, $post, $mq_rs_db_version;
$mq_rs_table_one = $wpdb->prefix . "adsense_id";
$mq_rs_table_two = $wpdb->prefix . "adsense_settings";
$mq_rs_pub = $wpdb->get_var("SELECT aid FROM $mq_rs_table_one order by hits ASC limit 1");
$mq_rs_settings = $wpdb->get_row("SELECT * FROM $mq_rs_table_two WHERE id='1'");
$mq_rs_posts = $wpdb->get_results("SELECT ID FROM " . $wpdb->posts . " WHERE post_status='publish' ORDER BY post_date DESC LIMIT 1");

 function mq_rs_cssjs() {
	global $mq_rs_pub;
echo "<style type=\"text/css\">
#maiq {
	background: #f4f4f4;
	border: 1px solid #b2b2b2;
	color: #000;
	font: 13px Verdana, Arial, Helvetica, sans-serif;
	margin: 1px;
	padding: 3px;
	cursor:pointer;
	}
#maiq_tbl {
	width:400px; 
	background:#eaeaea;
	border:1px solid #000;
}
#maiqtd {
border-top:1px solid #000;
}
#ad_settings {
	width:400px;
	margin:0 auto;
}
#ad_code{
	height:230px;
	width:350px;
}
#error {
	color:#ff0000;
	font-weight:bold;
}
#ad_preview {
width:468px;
height:280px;
}
#text {
	display:none;
	visibility:hidden;
}
</style>
<script language=\"JavaScript\" type=\"text/javascript\"><!--
function show(id){
    if (document.getElementById){
    obj = document.getElementById(id);
    obj.style.display = '';
    obj.style.visibility = 'visible';
    }
}
function edit(a,b,c,d){
document.form1.name.value=a;
document.form1.pub.value=b;
document.form1.hits.value=c;
document.form1.id.value=d;
document.form1.maiq.name='client_update';
document.form1.maiq.value='update';
}";
?>
function onk(){
var hd = '<script type="text/javascript"><!--\n';
var ad_client = 'google_ad_client = "<?php echo $mq_rs_pub; ?>";\n';
var type = 'google_ad_type = "text_image";\n';
var format = 'google_ad_format = "'+document.form2.google_ad_format.value+'_as";\n';
var chan = 'google_ad_channel = "'+document.form2.google_ad_channel.value+'";\n';
var border = 'google_color_border = "'+document.form2.google_color_border.value+'";\n';
var bg = 'google_color_bg = "'+document.form2.google_color_bg.value+'"\n';
var link = 'google_color_link = "'+document.form2.google_color_link.value+'";\n';
var text = 'google_color_text = "'+document.form2.google_color_text.value+'";\n';
var url = 'google_color_url = "'+document.form2.google_color_url.value+'";\n';
var features= 'google_ui_features = "'+document.form2.google_ui_features.value+'";\n';
var end = '//-->\n</'+'script>\n<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">\n</'+'script>';
var show = hd+ad_client+type+format+chan+border+bg+link+text+url+features+end;
document.getElementById('ad_preview').innerHTML = '<iframe src="https://www.google.com/pagead/ads?client=<?php echo $mq_rs_pub; ?>&amp;format='+document.form2.google_ad_format.value+'_as&amp;color_border='+document.form2.google_color_border.value+'&amp;color_bg='+document.form2.google_color_bg.value+'&amp;color_link='+document.form2.google_color_link.value+'&amp;color_text='+document.form2.google_color_text.value+'&amp;color_url='+document.form2.google_color_url.value+'&amp;ui='+document.form2.google_ui_features.value+'&amp;hl=en-US" frameborder="0" width="99%" height="99%" scrolling="no"></iframe>';
}

function checkform(){
	if (document.form1.name.value == ''){
	document.getElementById('error').innerHTML = 'Name is blank!';
	document.form1.name.style.border = '1px solid #ff0000';
	document.form1.name.style.background = 'yellow';
	document.form1.name.focus();
		return false;
	}else{	
	document.form1.name.style.border = '';
	document.form1.name.style.background = '';}
 if (document.form1.pub.value == ''){
	document.getElementById('error').innerHTML = 'Pub is blank!';
	document.form1.pub.style.border = '1px solid #ff0000';
	document.form1.pub.style.background = 'yellow';
	document.form1.pub.focus()
		return false;
	}
	document.form1.pub.style.border = '';
	document.form1.pub.style.background = '';
	return true;
}
window.onload = onk;
<?php
echo"
//--></script>
";
 }
 function mq_rs_ad($float) { 
global $wpdb, $mq_rs_settings, $mq_rs_pub;
$wh = str_replace ('_as','',$mq_rs_settings->google_ad_format);
$wh = explode("x", $wh);
$adsense_code = '
<!-- Begin Google Adsense code -->
<span style="margin: 0px 6px 0px 0px; float: '.$float.';">
<script type="text/javascript"><!--
google_ad_client = "'.$mq_rs_pub.'";
google_ad_type = "text_image";
google_ad_width = '.$wh[0].';
google_ad_height = '.$wh[1].';
google_ad_format = "'.$mq_rs_settings->google_ad_format.'";
google_ad_channel = "'.$mq_rs_settings->google_ad_channel.'";
google_color_border = "'.$mq_rs_settings->google_color_border.'";
google_color_bg = "'.$mq_rs_settings->google_color_bg.'";
google_color_link = "'.$mq_rs_settings->google_color_link .'";
google_color_text = "'.$mq_rs_settings->google_color_text.'";
google_color_url = "'.$mq_rs_settings->google_color_url.'";
google_ui_features = "'.$mq_rs_settings->google_ui_features.'";
//-->
</script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>	
</span>
<!-- End Google Adsense code -->
'; 
return $adsense_code;
}

 function mq_rs_add_adsense($data) { 
global $wpdb, $mq_rs_table_one, $mq_rs_table_two, $mq_rs_settings, $mq_rs_pub, $mq_rs_posts, $post;
$tag = "<!--adsense-->";
if ($mq_rs_settings->all_pages == 'no'){
$matchCount = preg_match_all ( "/<!--adsense-->/", $data, $matches , PREG_PATTERN_ORDER );
if (is_home()){

foreach ($mq_rs_posts as $pos) {

if ($post->ID == $pos->ID){
	if( $matchCount != 0 ){ $wpdb->query("UPDATE $mq_rs_table_one SET hits = hits+1 WHERE aid = '$mq_rs_pub'"); }
	if( $matchCount <= 3 ){	return str_replace( $tag, mq_rs_ad('none'), $data );}elseif( $matchCount > 3 ){ return $data .$adsense_code; }
		}else{ return $data; } 
	}
}elseif (is_archive()){ 
foreach ($mq_rs_posts as $pos) {

if ($post->ID == $pos->ID){
	if( $matchCount != 0 ){ $wpdb->query("UPDATE $mq_rs_table_one SET hits = hits+1 WHERE aid = '$mq_rs_pub'"); }
	if( $matchCount <= 3 ){	return str_replace( $tag, mq_rs_ad('none'), $data );}elseif( $matchCount > 3 ){ return $data .$adsense_code; }
		}else{ return $data; } 
	}
 }elseif (is_search()){ return $data; }
elseif (is_feed()){ return $data;
}else{

if( $matchCount != 0 ){ $wpdb->query("UPDATE $mq_rs_table_one SET hits = hits+1 WHERE aid = '$mq_rs_pub'"); }
if( $matchCount <= 3 ){return str_replace( $tag, mq_rs_ad('none'), $data );

}elseif( $matchCount > 3 ){
return $data .mq_rs_ad('none');
		}
	}

}else{
if ($mq_rs_settings->position == 'top'){
$cdata = mq_rs_ad('none'). $data;
}elseif($mq_rs_settings->position == 'top-left'){
 $cdata = mq_rs_ad('left'). $data;
}elseif($mq_rs_settings->position == 'top-right'){
$cdata = mq_rs_ad('right'). $data;
}elseif($mq_rs_settings->position == 'bottom'){
$cdata = $data .mq_rs_ad('none');
}elseif($mq_rs_settings->position == 'bottom-left'){
$cdata = $data .mq_rs_ad('left');
}elseif($mq_rs_settings->position == 'bottom-right'){
$cdata = $data .mq_rs_ad('right');
}else{$cdata=$data;}

if (is_home()){
foreach ($mq_rs_posts as $pos) {
if ($post->ID == $pos->ID){
 $wpdb->query("UPDATE $mq_rs_table_one SET hits = hits+1 WHERE aid = '$mq_rs_pub'"); 
 return $cdata; 
		}else{ return $data; } 
	}
}elseif (is_archive()){ 
foreach ($mq_rs_posts as $pos) {
if ($post->ID == $pos->ID){
$wpdb->query("UPDATE $mq_rs_table_one SET hits = hits+1 WHERE aid = '$mq_rs_pub'"); 
return $cdata ;
		}else{ return $data; } 
	}
}elseif (is_search()){ return $data; 
}elseif (is_feed()){ return $data;
}else{
$wpdb->query("UPDATE $mq_rs_table_one SET hits = hits+1 WHERE aid = '$mq_rs_pub'"); 
return $cdata;
	}
}
 } 

 function mq_rs_adsense_quicktag() {
	if(strpos($_SERVER['REQUEST_URI'], 'post.php') ||  strpos($_SERVER['REQUEST_URI'], 'page.php') || strpos($_SERVER['REQUEST_URI'], 'post-new.php') || strpos($_SERVER['REQUEST_URI'], 'page-new.php') || strpos($_SERVER['REQUEST_URI'], 'bookmarklet.php')) {
?>
<script language="JavaScript" type="text/javascript"><!--
var toolbar = document.getElementById("ed_toolbar");
<?php	edit_insert_button("Adsense", "adsense_button", "Adsense"); ?>
function adsense_button() { edInsertContent(edCanvas, '<!-'+'-adsense-'+'->'); }
//--></script>
<?php	} }
if(!function_exists('edit_insert_button')) {
	function edit_insert_button($caption, $js_onclick, $title = '')	{
	?>
	if(toolbar)	{
		var theButton = document.createElement('input');
		theButton.type = 'button';
		theButton.value = '<?php echo $caption; ?>';
		theButton.onclick = <?php echo $js_onclick; ?>;
		theButton.className = 'ed_button';
		theButton.title = "<?php echo $title; ?>";
		theButton.id = "<?php echo "ed_{$caption}"; ?>";
		toolbar.appendChild(theButton);
	}
	<?php	
}
 }

$mq_rs_db_version = "1.0";
 function mq_rs_install () {
   global $wpdb, $mq_rs_db_version, $mq_rs_table_one, $mq_rs_table_two;
   if (($wpdb->get_var("show tables like '$mq_rs_table_one'") != $mq_rs_table_one) && ($wpdb->get_var("show tables like '$mq_rs_table_two'") != $mq_rs_table_two)){
      $sql = "CREATE TABLE " . $mq_rs_table_one . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  aid text NOT NULL,
	  name tinytext NOT NULL,
	  hits mediumint(9) NOT NULL,
	  UNIQUE KEY id (id) 
	);";
      $sqla = "CREATE TABLE " . $mq_rs_table_two . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  google_ad_format text NOT NULL,
	  google_ad_channel text NOT NULL,
	  google_color_border text NOT NULL,
	  google_color_bg text NOT NULL,
	  google_color_link text NOT NULL,
	  google_color_text text NOT NULL,
	  google_color_url text NOT NULL,
	  google_ui_features text NOT NULL,
	  all_pages text NOT NULL,
	  position text NOT NULL,
	  	  UNIQUE KEY id (id) 
	);";
	  require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
      dbDelta($sql);
      dbDelta($sqla);
	  
$wpdb->query("INSERT INTO ".$mq_rs_table_two."(google_ad_format, google_color_border, google_color_bg, google_color_link, google_color_text, google_color_url, google_ui_features, all_pages, position)"."VALUES('468x60_as','FFFFFF','FFFFFF','B33C01','4d4d4d','B33C01','rc:0', 'no', 'top')");
$wpdb->query("INSERT INTO ".$mq_rs_table_one."(aid, name)"."VALUES('pub-8227551131685831','maiq')");
add_option("$mq_rs_db_version", $mq_rs_db_version);
   }
 }
 function mq_rs_uninstall()  {   
    global $wpdb, $mq_rs_table_one, $mq_rs_table_two; 
	$tbl = array($mq_rs_table_one, $mq_rs_table_two);
    foreach($tbl as $table) 
    $wpdb->query("DROP TABLE `{$table}` ");
 }         

 function mq_rs_brag(){
	$mq_rs_bragg='
<div id="brag">
Thanks for using my plugin.
<br>
	If you find yourself using it a lot you might want to <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=yomaichi@gmail.com&currency_code=EUR&amount=5&return=&item_name=Buy+me+a+coffee+:)" target="_new">"Buy me a coffee :)"</a>
<br>
	Also you will find some good information about your blog <a href="http://www.intellirank.net/" target="_new">here</a>.
<br>
For updates visit the plugin page <a href="http://www.maiq.info/work/wordpress/adsense-revenue-sharing/" target="_new">here</a>
</div>
';
	return $mq_rs_bragg;
 }

 function mq_rs_maiq_add_pages() {
	add_options_page('Revenue Sharing', 'Revenue Sharing', 8, 'revenueoptions', 'maiq_options_page');
 }

 function mq_rs_user_update() {
	global $wpdb, $mq_rs_table_one;
	$id= $_REQUEST["id"]; $name= $_REQUEST["name"]; $hits= $_REQUEST["hits"]; $mq_rs_pub= $_REQUEST["pub"]; 
	$wpdb->query("UPDATE $mq_rs_table_one SET  name='$name', hits='$hits', aid='$mq_rs_pub' WHERE id = '$id'");
 }

 function mq_rs_hit_reset() {
	global $wpdb, $mq_rs_table_one;
	$id= $_REQUEST["id"]; 
	$wpdb->query("UPDATE $mq_rs_table_one SET  hits='0' WHERE id = '$id'");
 }
 function mq_rs_delete() {
	global $wpdb, $mq_rs_table_one;
	$wpdb->query("DELETE FROM $mq_rs_table_one WHERE id = '$_POST[id]'");
 }
 function mq_rs_add_new() {
	global $wpdb, $mq_rs_table_one;
	$mq_rs_pub= $_REQUEST["pub"]; $name= $_REQUEST["name"]; $hits= $_REQUEST["hits"]; 
	if((!empty($mq_rs_pub)) && (!empty($name))){
	$wpdb->query("INSERT INTO $mq_rs_table_one (aid, name, hits) VALUES ('$mq_rs_pub', '$name', '$hits')");
	}
 }

 function mq_rs_settings_update() {
	global $wpdb, $mq_rs_table_two;
	$google_ad_format= $_REQUEST["google_ad_format"]."_as";
	$google_ad_channel= $_REQUEST["google_ad_channel"];
	$google_color_border= $_REQUEST["google_color_border"];
	$google_color_bg= $_REQUEST["google_color_bg"]; 
	$google_color_link= $_REQUEST["google_color_link"]; 
	$google_color_text= $_REQUEST["google_color_text"]; 
	$google_color_url= $_REQUEST["google_color_url"]; 
	$google_ui_features= $_REQUEST["google_ui_features"];
	$all_pages= $_REQUEST["all_pages"];
	$position= $_REQUEST["ad_align"];
	$wpdb->query("UPDATE $mq_rs_table_two SET google_ad_format='$google_ad_format', google_ad_channel ='$google_ad_channel', google_color_border = '$google_color_border', google_color_bg = '$google_color_bg', google_color_link = '$google_color_link', google_color_text = '$google_color_text', google_color_url = '$google_color_url', google_ui_features = '$google_ui_features', all_pages = '$all_pages', position = '$position' WHERE id = '1'");
header('Location: '.$_SERVER["REQUEST_URI"]);
 }

if(isset($_POST['add_client'])){ mq_rs_add_new(); }

if(isset($_POST['client_update'])){ mq_rs_user_update(); }

if ((isset($_POST['delete']))  || (isset($_POST['delete_x']))) { mq_rs_delete(); }

if ((isset($_POST['hit_reset']))  || (isset($_POST['hit_reset_x']))) { mq_rs_hit_reset(); }

if(isset($_POST['settings_update'])){ mq_rs_settings_update(); }

 function maiq_options_page() {
	global $wpdb, $mq_rs_table_one, $mq_rs_table_two, $mq_rs_settings, $mq_rs_pub; 
echo '<div class="wrap">';
echo '<h2>AdSense Revenue Sharing Options</h2><table><tr><td width="400px;" valign="top">';
$mq_rs_publishers = $wpdb->get_results("SELECT * FROM $mq_rs_table_one order by id asc");
echo'<table id="maiq_tbl"><tr><td>ID</td><td>Name</td><td>Hits</td><td>pub</td><td>Misc</td></tr><tr><td colspan=5 id="maiqtd"</td></tr>';
foreach ($mq_rs_publishers as $val){
echo'<tr><td>'.$val->id.'</td><td>'.$val->name.'</td><td>'.$val->hits.'</td><td>'.$val->aid.'</td>
<td valign="top"><form name="form" method="post" action="">
<input type="hidden" name="id" value="'.$val->id.'">
<input type="image" src="../wp-content/plugins/adsense-revenue-sharing/img/reset.png" name="hit_reset" title="Reset hit count for this publisher" />
<img id="maiq" src="../wp-content/plugins/adsense-revenue-sharing/img/edit.png" title="Edit publisher`s data" onclick="edit(\''.$val->name.'\',\''.$val->aid.'\',\''.$val->hits.'\',\''.$val->id.'\')"></a>  
<input type="image" src="../wp-content/plugins/adsense-revenue-sharing/img/delete.png" name="delete" title="Delete publisher" />
</form></td>
</tr>';
}
echo'</table>
</td><td valign="top">
<form name="form1" method="post" action="" onSubmit="return checkform()">
<input type="hidden" name="id" id="id" size="2">
Name: <input type="text" name="name" id="name" size="15" onblur="return checkform()"> PUB: <input onblur="return checkform()" type="text" name="pub" id="pub" size="15"> Hits: <input type="text" name="hits" id="hits" size="5">
<input type="submit" id="maiq" name="add_client" value="add new" /></form>
</td><td id="error" valign="top"></td></tr></table>
<p></p>
<fieldset class="options">
<legend>AdSense ad settings</legend>
<form name="form2" method="post" action="">
<table id="ad_settings">
<tr>
	<td>Channel</td>
	<td><input maxlength="20" onblur="onk()" type="text"  name="google_ad_channel" size="28" value="'.$mq_rs_settings->google_ad_channel.'"></td>
	<td rowspan="9"><div id="ad_preview"></div></td>
</tr>
<tr>
	<td>Border</td>
	<td><input maxlength="6" onblur="onk()" type="text" name="google_color_border" size="28" value="'.$mq_rs_settings->google_color_border.'"></td>
</tr>
<tr>
	<td>Background</td>
	<td><input maxlength="6" onblur="onk()" type="text" name="google_color_bg" size="28" value="'.$mq_rs_settings->google_color_bg.'"></td>
</tr>
<tr>
	<td>Title</td>
	<td><input maxlength="6" onblur="onk()" type="text" name="google_color_link" size="28" value="'.$mq_rs_settings->google_color_link.'"></td>
</tr>
<tr>
	<td>Text</td>
	<td><input maxlength="6" onblur="onk()" type="text" name="google_color_text" size="28" value="'.$mq_rs_settings->google_color_text.'"></td>
</tr>
<tr>
	<td>URL</td>
	<td><input maxlength="6" onblur="onk()" type="text" name="google_color_url" size="28" value="'.$mq_rs_settings->google_color_url.'"></td>
</tr>
<tr>
	<td>Corners</td>
	<td>
<select name="google_ui_features" style="width:220px;" onchange="onk()">
</option>
<option value="rc:0"'; if ($mq_rs_settings->google_ui_features == 'rc:0') {echo' selected';} echo'>Square corners</option>
<option value="rc:6"'; if ($mq_rs_settings->google_ui_features == 'rc:6') {echo' selected';} echo'>Slightly rounded corners</option>
<option value="rc:10"'; if ($mq_rs_settings->google_ui_features == 'rc:10') {echo' selected';} echo'>Very rounded border</option>
</select>
	</td>
</tr>
<tr>
	<td>Ad format</td>
	<td>
<select name="google_ad_format" style="width:220px;"  onchange="onk()">
';
?>
</option>
<optgroup label="Horizontal">
<option value="468x60" <?php if ($mq_rs_settings->google_ad_format == '468x60_as') echo"selected"; ?>>468 x 60 Banner</option>
<option value="234x60" <?php if ($mq_rs_settings->google_ad_format == '234x60_as') echo"selected"; ?>>234 x 60 Half Banner</option>
</optgroup>
<optgroup label="Vertical">
<option value="120x240" <?php if ($mq_rs_settings->google_ad_format == '120x240_as') echo"selected"; ?>>120 x 240 Vertical Banner</option>
</optgroup>
<optgroup label="Square">
<option value="336x280" <?php if ($mq_rs_settings->google_ad_format == '336x280_as') echo"selected"; ?>>336 x 280 Large Rectangle</option>
<option value="300x250" <?php if ($mq_rs_settings->google_ad_format == '300x250_as') echo"selected"; ?>>300 x 250 Medium Rectangle</option>
<option value="250x250" <?php if ($mq_rs_settings->google_ad_format == '250x250_as') echo"selected"; ?>>250 x 250 Square</option>
<option value="200x200" <?php if ($mq_rs_settings->google_ad_format == '200x200_as') echo"selected"; ?>>200 x 200 Small Square</option>
<option value="180x150" <?php if ($mq_rs_settings->google_ad_format == '180x150_as') echo"selected"; ?>>180 x 150 Small Rectangle</option>
<option value="125x125" <?php if ($mq_rs_settings->google_ad_format == '125x125_as') echo"selected"; ?>>125 x 125 Button</option></optgroup>
</select>
	</td>
</tr>
<tr>
	<td>All pages</td>
	<td>
		Yes <input onselect="show('text')" type="radio" name="all_pages" value="yes" <?php if ($mq_rs_settings->all_pages == 'yes'){echo'checked';} ?>>
		No <input type="radio" name="all_pages" value="no" <?php if ($mq_rs_settings->all_pages == 'no'){echo'checked';} ?>>
		<p id="text">
		If you select yes all the &lt;!--adsense--&gt; tags will be ignored and one ad will be inserted at the end of every post and page.
		</p>
	</td>
</tr>
<tr>
<td>Position</td>
<td>
        <select name="ad_align">
			<option value="top" <?php if ($mq_rs_settings->position == "top") echo " selected"; ?> >Top</option>
			<option value="top-left" <?php if ($mq_rs_settings->position == "top-left") echo " selected"; ?> >Top Left</option>
			<option value="top-right" <?php if ($mq_rs_settings->position == "top-right") echo " selected"; ?> >Top Right</option>
			<option value="bottom" <?php if ($mq_rs_settings->position == "bottom") echo " selected"; ?> >Bottom</option>
			<option value="bottom-left" <?php if ($mq_rs_settings->position == "bottom-left") echo " selected"; ?> >Bottom Left</option>
			<option value="bottom-right" <?php if ($mq_rs_settings->position == "bottom-right") echo " selected"; ?> >Bottom Right</option>
		</select>
</td>
</tr>
</table>

<p class="submit"><input type="submit" name="settings_update" value="Update AdSense Settings &raquo;" /></p>
</form>
</fieldset>
<?php
echo mq_rs_brag().'</div>';
 }
?>