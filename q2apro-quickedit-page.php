<?php

/*
   Plugin Name: Quick Edit
   Plugin URI: http://www.q2apro.com/plugins/quick-edit
   Plugin Description: Update all question titles and tags quickly on one page and save hours of time
   Plugin Version: 1.0
   Plugin Date: 2014-02-13
   Plugin Author: q2apro.com
   Plugin Author URI: http://www.q2apro.com
   Plugin Minimum Question2Answer Version: 1.5
   Plugin Update Check URI: http://www.q2apro.com/pluginupdate?id=20

   Licence: Copyright © q2apro.com - All rights reserved

 */

class q2apro_quickedit {

	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot)
	{
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	// for display in admin interface under admin/pages
	function suggest_requests() 
	{	
		return array(
				array(
					'title' => 'Quick Tagger Page', // title of page
					'request' => 'quickedit', // request name
					'nav' => 'F', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				     ),
			    );
	}

	// for url query
	function match_request($request)
	{
		if ($request=='quickedit') {
			return true;
		}

		return false;
	}

	function process_request($request) {

		if(qa_opt('q2apro_quickedit_enabled')!=1) {
			$qa_content=qa_content_prepare();
			$qa_content['error'] = '<div>'.qa_lang_html('q2apro_quickedit_lang/plugin_disabled').'</div>';
			return $qa_content;
		}
		// return if permission level is not sufficient
		if(qa_user_permit_error('q2apro_quickedit_permission')) {
			$qa_content=qa_content_prepare();
			$qa_content['error'] = qa_lang_html('q2apro_quickedit_lang/access_forbidden');
			return $qa_content;
		}

		// AJAX post: we received post data, so it should be the ajax call to update the tags of the post
		$transferString = qa_post_text('ajaxdata'); // holds postid, question title, tags
		if(isset($transferString)) {
			$newdata = json_decode($transferString,true);
			$newdata = str_replace('&quot;', '"', $newdata); // see stackoverflow.com/questions/3110487/
			// echo '# '.$newdata['postid'].' ||| '.$newdata['title'].' ||| '.$newdata['tags']; 	return;

			$postid = $newdata['postid'];
			$posttitle = $newdata['title'];
			$posttags = $newdata['tags'];

			if(!isset($postid) || !isset($posttitle) || !isset($posttags)) {
				echo 'tags='.qa_lang_html('q2apro_quickedit_lang/access_problem');
				return;
			}
			else {
				require_once QA_INCLUDE_DIR.'app/users.php';
				$userid = qa_get_logged_in_userid();
				$tagsIn = str_replace(' ', ',', $posttags); // convert spaces to comma
				// process new tags
				require_once QA_INCLUDE_DIR.'qa-app-posts.php';
				qa_post_set_content($postid, $posttitle, null, null, $tagsIn, null,null, $userid, null, null); 
				$tags = qa_post_tags_to_tagstring($tagsIn); // correctly parse tags string
				// update post with new tags
				//	qa_db_query_sub('UPDATE ^posts SET tags=# 
				//						WHERE `postid`=#
				//						LIMIT 1', $tags, $postid);

				// Update post with new title
				//	qa_db_query_sub('UPDATE ^posts SET title=# 
				//						WHERE `postid`=#
				//						LIMIT 1', $posttitle, $postid);
			} // end db update

			// header('Content-Type: text/plain; charset=utf-8');
			// echo 'updated postid: '.$postid.' with tags: '.$tags;

			// ajax return array data to write back into table
			$arrayBack = array(
					'postid' => $postid,
					'title' => $posttitle,
					'tags' => $tags
					);
			echo json_encode($arrayBack);
			return;
		} // end POST data

		/* start */
		$qa_content=qa_content_prepare();
		qa_set_template('qp-quickedit-page');
		$qa_content['title'] = qa_lang_html('q2apro_quickedit_lang/page_title'); // page title

		// counter for custom html output
		$c = 2;

		// do pagination
		$start = (int)qa_get('start'); // gets start value from URL
		$pagesize = 500; // items per page
		$count = qa_opt('cache_qcount'); // items total
		$qa_content['page_links'] = qa_html_page_links(qa_request(), $start, $pagesize, $count, true); // last parameter is prevnext
		$tagfilter = null;
		if(isset($_GET['tagfilter']))
			$tagfilter = $_GET['tagfilter'];
		$tagstring = '';
		if($tagfilter) $tagstring = " and tags like '%".$tagfilter."%'";


		// query to get all posts according to pagination, ignore closed questions
		$queryAllPosts = qa_db_query_sub('SELECT postid,tags,title,content,format FROM `^posts`
				WHERE `type` = "Q"
				AND `closedbyid` IS NULL'. $tagstring.' 
				ORDER BY postid DESC
				LIMIT #,#
				', $start, $pagesize);

		// initiate output string
		$tagtable = '<table class="tagtable"> <thead> <tr> <th>'.qa_lang_html('q2apro_quickedit_lang/th_postid').'</th> <th>'.qa_lang_html('q2apro_quickedit_lang/th_questiontitle').'</th> <th>'.qa_lang_html('q2apro_quickedit_lang/th_posttags').'</th> </tr></thead>';
		$maxlength = qa_opt('mouseover_content_max_len'); // 480

		require_once QA_INCLUDE_DIR.'qa-util-string.php'; // for qa_shorten_string_line()
		$blockwordspreg=qa_get_block_words_preg();

		while ( ($row = qa_db_read_one_assoc($queryAllPosts,true)) !== null ) {
			$text=qa_viewer_text($row['content'], $row['format'], array('blockwordspreg' => $blockwordspreg));
			$contentPreview = qa_html(qa_shorten_string_line($text, $maxlength));
			$tagtable .= '

				<tr data-original="'.$row['postid'].'">
				<td><a class="tooltipS" title="'.$contentPreview.'" target="_blank" href="./'.$row['postid'].'?state=edit-'.$row['postid'].'">'.$row['postid'].'</a>
				<td><div class="post_title_td"><input class="post_title" value="'.htmlspecialchars($row['title'], ENT_QUOTES, "UTF-8").'" /></div></td> 
				<td style="width:60%"><div class="post_tags_td"><input class="post_tags" value="'.$row['tags'].'"   name="q" id="tag_edit_'.$row['postid'].'" autocomplete="off" placeholder="Tags" onkeyup="qa_tag_edit_hints('.$row['postid'].')" onmouseup="qa_tag_edit_hints('.$row['postid'].')" /></div>

				<div class="qa-form-tall-note2">
				<span id="tag_edit_examples_title_'.$row['postid'].'" style="display:none;"> </span>
				<span id="tag_edit_complete_title_'.$row['postid'].'" style="display:none;"></span>
				<span id="tag_edit_hints_'.$row['postid'].'"></span></div>
				</td>
				</tr>';
		}
		$tagtable .= "</table>";

		// output into theme
		//$qa_content['custom'.++$c]='<p style="font-size:14px;">Click on the post tags to edit them!</p>';
		$qa_content['custom'.++$c]='<p>'.qa_lang_html('q2apro_quickedit_lang/edit_hint').'</p>';
		$qa_content['custom'.++$c]='<p>'.qa_lang_html('q2apro_quickedit_lang/edit_hint_q').'</p>';
		$qa_content['custom'.++$c]= $tagtable;

		// make newest users list bigger on page
		$qa_content['custom'.++$c] = '
			<style type="text/css">
			.qa-sidepanel {
display:none;
			}
		.qa-main {
width:100%;
		}
		table {
width:90%;
background:#EEE;
margin:30px 0 15px;
       text-align:left;
       border-collapse:collapse;
		}
		table th {
padding:4px;
background:#cfc;
border:1px solid #CCC;
       text-align:center;
		}
		table tr:nth-child(even){
background:#EEF;
		}
		table tr:nth-child(odd){
background:#F5F5F5;
		}
		table tr:hover {
background:#FFD;
		}
		table th:nth-child(1), table td:nth-child(1) {
width:60px;
      text-align:center;
		}
		td {
border:1px solid #CCC;
padding:1px 10px;
	line-height:25px;
		}
		table.tagtable td a { 
			font-size:12px;
		}
		input.post_title, input.post_tags, .inputdefault {
width:100%;
border:1px solid transparent;
padding:3px;
background:transparent;
		}
		input.post_title:focus, input.post_tags:focus, .inputactive {
background:#FFF !important;
	   box-shadow:0 0 2px #7AF
		}
		.post_title_td, .post_tags_td {
position:relative;
		}
		.sendr,.sendrOff {
padding:3px 10px;
background:#FC0;
border:1px solid #FEE;
       border-radius:2px;
position:absolute;
right:-77px;
top:-5px;
color:#123;
cursor:pointer;
		}
		.sendrOff {
			text-decoration:none !important;
		}
		</style>';

		$qa_content['custom'.++$c] = '
			<script type="text/javascript">
			$(document).ready(function(){
					var recentTR;
					$(".post_title, .post_tags").click( function() {
							// remove former css
							$(".post_title, .post_tags").removeClass("inputactive");
							recentTR = $(this).parent().parent().parent();
							recentTR.find("input.post_title, input.post_tags").addClass("inputactive");
							// alert(recentTR.find("input.post_tags").val());

							// add Update-Button if not yet added
							if(recentTR.find(".post_tags_td").has(".sendr").length == 0) {
							// remove all other update buttons
							$(".sendr").fadeOut(200, function(){$(this).remove() });
							recentTR.find(".post_tags_td").append("<a class=\'sendr\'>Update</a>");
							}
							});
					$(document).keyup(function(e) {
							// get focussed element
							var focused = $(":focus");
							// if enter key and input field selected
							if(e.which == 13 && (focused.hasClass("post_title") || focused.hasClass("post_tags"))) { 
							doAjaxPost();
							}
							// escape has been pressed
							else if(e.which == 27) {
							// remove all Update buttons and unfocus input fields
							$(".sendr").remove();
							// remove focus from input field
							$(":focus").blur();
							// remove active css class
							$(".post_title, .post_tags").removeClass("inputactive");									
							}
							});
					$(document).on("click", ".sendr", function() {
							doAjaxPost();
							});

					function doAjaxPost() {
						// get post data from <tr> element
						var postid = recentTR.attr("data-original"); 
						var posttitle = recentTR.find("input.post_title").val();
						var posttags = recentTR.find("input.post_tags").val();
						// alert(postid + " | " + posttitle + " | " + posttags);
						// var senddata = "postid="+postid+"&title="+posttitle+"&tags="+posttags;
						recentTR.find("#tag_edit_hints_"+postid).fadeOut(1500, function(){$(this).remove() });
						var dataArray = {
postid: postid,
	title: posttitle,
	tags: posttags
						};
						var senddata = JSON.stringify(dataArray);
						console.log("sending: "+senddata);
						// send ajax
						$.ajax({
type: "POST",
url: "'.qa_self_html().'",
data: { ajaxdata: senddata },
dataType:"json",
cache: false,
success: function(data) {
//dev
console.log("server returned:"+data+" #Tags: "+data["tags"]);

// prevent another click on button by assigning another class id
$(".sendr").attr("class","sendrOff");
// show success indicator checkmark
recentTR.find(".sendrOff").css("background", "#55CC55");
recentTR.find(".sendrOff").html("<span style=\'font-size:150%;\'>&check;</span>");

// write title back to posttitle input field
recentTR.find("input.post_title").val(data["title"]);
// write tags back to tags input field
recentTR.find("input.post_tags").val(data["tags"]);

// remove update button
recentTR.find(".sendrOff").fadeOut(1500, function(){$(this).remove() });
// remove focus from input field
$(":focus").blur();
// remove active css class
$(".post_title, .post_tags").removeClass("inputactive");									
}
});
}
});

</script>';

return $qa_content;
}

};


/*
   Omit PHP closing tag to help avoid accidental output
 */
