<?php if (!empty($tb)) { ?>
<!-- you can START editing here -->

	<?php // don't touch these 2 lines
	$resultc = $wpdb->get_results("SELECT * FROM $tablecomments WHERE comment_post_ID = $id AND comment_content LIKE '%<trackback />%' ORDER BY comment_date"); if ($resultc) {
	?>

<h2>Trackbacks</h2>

<p>The URL to TrackBack this entry is:</p>
<p><em><?php trackback_url() ?></em></p>

<ol id="trackbacks">
	<?php /* this line is b2's motor, do not delete it */ foreach ($resultc as $rowc) { $commentdata = get_commentdata($rowc->comment_ID); ?>
	<li id="trackback-<?php comment_ID() ?>">
	<?php comment_text() ?>
	
	<p><cite>Tracked on <a href="<?php comment_author_url(); ?>" title="<?php comment_author() ?>"><?php comment_author() ?></a> on <?php comment_date() ?> @ <?php comment_time() ?></cite></p>
	</li>

	<?php /* end of the loop, don't delete */ } if (!$wxcvbn_c) { ?>

<!-- this is displayed if there are no trackbacks so far -->
	<li>No trackbacks yet.</li>

	<?php /* if you delete this the sky will fall on your head */ } ?>
</ol>
<div><a href="javascript:history.go(-1)">Go back</a></div>
	<?php /* if you delete this the sky will fall on your head */ } ?>
<!-- STOP editing there -->

<?php
	
} else {

if (!empty($HTTP_GET_VARS['tb_id'])) {
	// trackback is done by a GET
	$tb_id = $HTTP_GET_VARS['tb_id'];
	$tb_url = $HTTP_GET_VARS['url'];
	$title = $HTTP_GET_VARS['title'];
	$excerpt = $HTTP_GET_VARS['excerpt'];
	$blog_name = $HTTP_GET_VARS['blog_name'];
} elseif (!empty($HTTP_POST_VARS['url'])) {
	// trackback is done by a POST
	$request_array = 'HTTP_POST_VARS';
	$tb_id = explode('/', $HTTP_SERVER_VARS['REQUEST_URI']);
	$tb_id = $tb_id[count($tb_id)-1];
	$tb_url = $HTTP_POST_VARS['url'];
	$title = $HTTP_POST_VARS['title'];
	$excerpt = $HTTP_POST_VARS['excerpt'];
	$blog_name = $HTTP_POST_VARS['blog_name'];
}

if ((strlen(''.$tb_id)) && (empty($HTTP_GET_VARS['__mode'])) && (strlen(''.$tb_url))) {

	@header('Content-Type: text/xml');


	require_once('wp-config.php');
	require_once($abspath.$b2inc.'/b2template.functions.php');
	require_once($abspath.$b2inc.'/b2vars.php');
	require_once($abspath.$b2inc.'/b2functions.php');

	if (!$use_trackback) {
		trackback_response(1, 'Sorry, this weblog does not allow you to trackback its posts.');
	}
	$pingstatus = $wpdb->get_var("SELECT ping_status FROM $tableposts WHERE ID = $tb_id");

	if ('closed' == $pingstatus)
		die('Sorry, trackbacks are closed for this item.');

	$tb_url = addslashes($tb_url);
	$title = strip_tags($title);
	$title = (strlen($title) > 255) ? substr($title, 0, 252).'...' : $title;
	$excerpt = strip_tags($excerpt);
	$excerpt = (strlen($excerpt) > 255) ? substr($excerpt, 0, 252).'...' : $excerpt;
	$blog_name = htmlspecialchars($blog_name);
	$blog_name = (strlen($blog_name) > 255) ? substr($blog_name, 0, 252).'...' : $blog_name;

	$comment = '<trackback />';
	$comment .= "<strong>$title</strong><br />$excerpt";

	$author = addslashes($blog_name);
	$email = '';
	$original_comment = $comment;
	$comment_post_ID = $tb_id;
	$autobr = 1;

	$user_ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
	$user_domain = gethostbyaddr($user_ip);
	$time_difference = get_settings('time_difference');
	$now = date('Y-m-d H:i:s',(time() + ($time_difference * 3600)));

	$comment = convert_chars($comment);
	$comment = format_to_post($comment);

	$comment_author = $author;
	$comment_author_email = $email;
	$comment_author_url = $tb_url;

	$author = addslashes($author);

	$query = "INSERT INTO $tablecomments VALUES ('0','$comment_post_ID','$author','$email','$tb_url','$user_ip','$now','$comment','0')";
	$result = $wpdb->query($query);
	if (!$result) {
		die ("There is an error with the database, it can't store your comment...<br />Contact the <a href=\"mailto:$admin_email\">webmaster</a>");
	} else {

		if ($comments_notify) {

			$notify_message  = "New trackback on your post #$comment_post_ID.\r\n\r\n";
			$notify_message .= "website: $comment_author (IP: $user_ip , $user_domain)\r\n";
			$notify_message .= "url    : $comment_author_url\r\n";
			$notify_message .= "excerpt: \n".stripslashes($original_comment)."\r\n\r\n";
			$notify_message .= "You can see all trackbacks on this post there: \r\n";
			$notify_message .= "$siteurl/$blogfilename?p=$comment_post_ID&tb=1\r\n\r\n";

			$postdata = get_postdata($comment_post_ID);
			$authordata = get_userdata($postdata["Author_ID"]);
			$recipient = $authordata["user_email"];
			$subject = "trackback on post #$comment_post_ID \"".$postdata["Title"]."\"";

			@mail($recipient, $subject, $notify_message, "From: wordpress@".$HTTP_SERVER_VARS['SERVER_NAME']."\r\n"."X-Mailer: WordPress $b2_version - PHP/" . phpversion());
			
		}

		trackback_response(0);
	}

}/* elseif (empty($HTTP_GET_VARS['__mode'])) {

	header('Content-type: application/xml');
	echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?".">\n<response>\n<error>1</error>\n";
	echo "<message>Tell me a lie. \nOr just a __mode or url parameter ?</message>\n";
	echo "</response>";

}*/


}

?>