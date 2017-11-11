function qa_tag_search_hints(postid)
{
	var elem=document.getElementById('tag_search_'+postid);
	var html='';
	var completed=false;

	// first try to auto-complete
	if (qa_tags_complete) {
		var parts=qa_tag_search_typed_parts(elem);

		if (parts.typed) {
			html=qa_search_tags_to_html((qa_html_unescape(qa_tags_examples+','+qa_tags_complete)).split(','), parts.typed.toLowerCase());
			completed=html ? true : false;
		}
	}

	// otherwise show examples
	if (qa_tags_examples && !completed)
		html=qa_search_tags_to_html((qa_html_unescape(qa_tags_examples)).split(','), null);

	// set title visiblity and hint list
	document.getElementById('tag_search_examples_title_'+postid).style.display=(html && !completed) ? '' : 'none';
	document.getElementById('tag_search_complete_title_'+postid).style.display=(html && completed) ? '' : 'none';
	document.getElementById('tag_search_hints_'+postid).innerHTML=html;
}






function qa_tag_search_click(link)

{

	var id = link.parentNode.id;
	var post = id.split("_");
	var postid = post[post.length-1];
	var elem=document.getElementById("tag_search_"+postid);

	var parts=qa_tag_search_typed_parts(elem);



	// removes any HTML tags and ampersand

	var tag=qa_html_unescape(link.innerHTML.replace(/<[^>]*>/g, ''));



	var separator=' ';


	// replace if matches typed, otherwise append

	var newvalue=(parts.typed && (tag.toLowerCase().indexOf(parts.typed.toLowerCase())>=0))

		? (parts.before+separator+tag+separator+parts.after+separator) : (elem.value+separator+tag+separator);



	// sanitize and set value

	if (false)

		elem.value=newvalue.replace(/[\s,]*,[\s,]*/g, ', ').replace(/^[\s,]+/g, '');

	else

		elem.value=newvalue.replace(/[\s,]+/g, ' ').replace(/^[\s,]+/g, '');



//	elem.focus();

	qa_tag_search_hints(postid);



	return false;

}




function qa_search_tags_to_html(tags, matchlc)

{

	var html='';

	var added=0;

	var tagseen={};



	for (var i=0; i<tags.length; i++) {

		var tag=tags[i];

		var taglc=tag.toLowerCase();



		if (!tagseen[taglc]) {

			tagseen[taglc]=true;



			if ( (!matchlc) || (taglc.indexOf(matchlc)>=0) ) { // match if necessary

				if (matchlc) { // if matching, show appropriate part in bold

					var matchstart=taglc.indexOf(matchlc);

					var matchend=matchstart+matchlc.length;

					inner='<span style="font-weight:normal;">'+qa_html_escape(tag.substring(0, matchstart))+'<b>'+

						qa_html_escape(tag.substring(matchstart, matchend))+'</b>'+qa_html_escape(tag.substring(matchend))+'</span>';

				} else // otherwise show as-is

					inner=qa_html_escape(tag);



				html+=qa_tag_search_template.replace(/\^/g, inner.replace('$', '$$$$'))+' '; // replace ^ in template, escape $s



				if (++added>=5)

					break;

			}

		}

	}



	return html;

}


function qa_tag_search_typed_parts(elem)

{

	var caret=elem.value.length-qa_tag_search_caret_from_end(elem);

	var active=elem.value.substring(0, caret);

	console.log("active = "+active);
	var passive=elem.value.substring(active.length);


	var qa_tag_search_onlycomma = false;
	// if the caret is in the middle of a word, move the end of word from passive to active

	if (

			active.match(qa_tag_search_onlycomma ? /[^\s,][^,]*$/ : /[^\s,]$/) &&

			(adjoinmatch=passive.match(qa_tag_search_onlycomma ? /^[^,]*[^\s,][^,]*/ : /^[^\s,]+/))

	   ) {

		active+=adjoinmatch[0];

		passive=elem.value.substring(active.length);

	}



	// find what has been typed so far

	var typedmatch=active.match(qa_tag_search_onlycomma ? /[^\s,]+[^,]*$/ : /[^\s,]+$/) || [''];



	return {before:active.substring(0, active.length-typedmatch[0].length), after:passive, typed:typedmatch[0]};

}

function qa_tag_search_caret_from_end(elem)

{

	if (document.selection) { // for IE

		elem.focus();

		var sel=document.selection.createRange();

		sel.moveStart('character', -elem.value.length);



		return elem.value.length-sel.text.length;



	} else if (typeof(elem.selectionEnd)!='undefined') // other browsers

		return elem.value.length-elem.selectionEnd;



	else // by default return safest value

		return 0;

}
function qa_html_unescape(html)
{
	return html.replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
}
function qa_html_escape(text)
{
	return text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

