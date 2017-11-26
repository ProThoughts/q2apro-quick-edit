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

	class q2apro_quickedit_ajax {
		
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
					'title' => 'Quick Tagger Ajax Page', // title of page
					'request' => 'quickedit_ajax_page', // request name
					'nav' => null, // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}
		
		// for url query
		function match_request($request)
		{
			if ($request=='quickedit_ajax_page') {
				return true;
			}

			return false;
		}

		function process_request($request) {
		
		$transferString = qa_post_text('ajax');
                        if($transferString !== null) {


                                require_once QA_INCLUDE_DIR.'db/selects.php';
                                $ctags=array_keys(qa_db_single_select(qa_db_popular_tags_selectspec(0, QA_DB_RETRIEVE_COMPLETE_TAGS)));
                                $output = qa_html(implode(',',$ctags));
                                header('Access-Control-Allow-Origin: '.qa_path(null));
                                echo $output;

                                exit();
                        } // END AJAX RETURN
                        else {
                        }
	
		}
		
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
