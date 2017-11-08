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

	class q2apro_quickedit_admin {

		// option's value is requested but the option has not yet been set
		function option_default($option) {
			switch($option) {
				case 'q2apro_quickedit_enabled':
					return 1; // true
				case 'q2apro_quickedit_permission':
					return QA_PERMIT_ADMINS; // default level to access this page
				default:
					return null;				
			}
		}
			
		function allow_template($template) {
			return ($template!='admin');
		}       
			
		function admin_form(&$qa_content){                       

			// process the admin form if admin hit Save-Changes-button
			$ok = null;
			if (qa_clicked('q2apro_quickedit_save')) {
				qa_opt('q2apro_quickedit_enabled', (bool)qa_post_text('q2apro_quickedit_enabled')); // empty or 1
				qa_opt('q2apro_quickedit_permission', (int)qa_post_text('q2apro_quickedit_permission')); // level
				$ok = qa_lang('admin/options_saved');
			}
			
			// form fields to display frontend for admin
			$fields = array();
			
			$fields[] = array(
				'type' => 'checkbox',
				'label' => qa_lang('q2apro_quickedit_lang/enable_plugin'),
				'tags' => 'NAME="q2apro_quickedit_enabled"',
				'value' => qa_opt('q2apro_quickedit_enabled'),
			);
			
			$view_permission = (int)qa_opt('q2apro_quickedit_permission');
			$permitoptions = qa_admin_permit_options(QA_PERMIT_ALL, QA_PERMIT_SUPERS, false, false);
			$pluginpageURL = qa_opt('site_url').'quickedit';
			$fields[] = array(
				'type' => 'static',
				'note' => qa_lang('q2apro_quickedit_lang/plugin_page_url').' <a target="_blank" href="'.$pluginpageURL.'">'.$pluginpageURL.'</a>',
			);
			$fields[] = array(
				'type' => 'select',
				'label' => qa_lang('q2apro_quickedit_lang/minimum_level'),
				'tags' => 'name="q2apro_quickedit_permission"',
				'options' => $permitoptions,
				'value' => $permitoptions[$view_permission],
			);
			$fields[] = array(
				'type' => 'static',
				'note' => '<span style="font-size:75%;color:#789;">'.strtr( qa_lang('q2apro_quickedit_lang/contact'), array( 
							'^1' => '<a target="_blank" href="http://www.q2apro.com/plugins/quickedit">',
							'^2' => '</a>'
						  )).'</span>',
			);
			
			return array(           
				'ok' => ($ok && !isset($error)) ? $ok : null,
				'fields' => $fields,
				'buttons' => array(
					array(
						'label' => qa_lang_html('main/save_button'),
						'tags' => 'name="q2apro_quickedit_save"',
					),
				),
			);
		}
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/