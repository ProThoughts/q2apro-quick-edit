<?php

class qa_html_theme_layer extends qa_html_theme_base
{


	function head_script()//add custom js
	{
		qa_html_theme_base::head_script();
		$version=0.00698;
		$url=qa_request();
		$url_parts=explode('/',$url);
		foreach($url_parts as $val)
		{
			/*Add new pages here, for inclusion*/
			if($val==='quickedit')// 
			{

				 $this->output('<script> var qa_tags_examples="";');
		                $this->output('if (typeof qa_tags_complete === "undefined") {var qa_tags_complete =\'\';}');
                		$template='<a href="#" class="qa-tag-link" onclick="return qa_tag_edit_click(this);">^</a>';
                		$this->output('var qa_tag_edit_template =\''.$template.'\';');
                		$this->output('</script>');

				$this->output('<script type="text/javascript" src="'.QA_HTML_THEME_LAYER_URLTOROOT.'js/qedit.js?v='.$version.'"></script>');

				$this->output(' <script type="text/javascript">
                                $(document).ready(function(){

                                        $(".post_tags").click( function() {

                                                if(qa_tags_complete == ""){
                                                $.ajax({
type: "POST",
url: "'.qa_path("quickedit_ajax_page").'",
data: {ajax:"hello" },
error: function() {
console.log("server: ajax error");
},
success: function(htmldata) {
qa_tags_complete = htmldata;
}
});
                                                }
                                                else {
                                                }
                                                });

});
</script> 
');

			}

		}
	}
}
