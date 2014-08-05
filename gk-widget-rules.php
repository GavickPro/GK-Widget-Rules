<?php 

/*
Plugin Name:    GK Widget Rules
Plugin URI:     http://wordpress.org/extend/plugins/gk-widget-rules/
Description:    Control widgets with WP's conditional tags is_home etc
Version:        1.0.0
Author:         GavickPro
Author URI:     http://www.gavick.com
 
Text Domain:   gk-widget-rules
Domain Path:   /languages/
*/ 

global $pagenow;

load_plugin_textdomain( 'gk-widget-rules', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

//
// Common functions for back-end and front-end
//

// function used to get the plugin configuration
function gk_widget_rules_get_config() {
	$config = json_decode(get_option('gk_widget_rules'), true);		
	// if this option is set at first time
	if(!is_array($config['type']) ) {
		$config['type'] = array();
	}
	// if this option is set at first time
	if(!is_array($config['rules']) ) {
		$config['rules'] = array();
	}
	// if this styles is set at first time
	if( !is_array($config['css']) ) {
		$config['css'] = array();
	}
	// if this responsive is set at first time
	if( !is_array($config['responsive']) ) {
		$config['responsive'] = array();
	}
	// if this users is set at first time
	if( !is_array($config['users']) ) {
		$config['users'] = array();
	}
	
	return $config;
}

// run the code only on the dashboard
if(is_admin() && !class_exists('GK_Widget_Rules_Back_End')) {
	// define an additional operation when save the widget
	add_filter('widget_update_callback', array('GK_Widget_Rules_Back_End', 'update'), 10, 4);
	add_action('sidebar_admin_setup', array('GK_Widget_Rules_Back_End', 'add_control')); 
	add_action('admin_enqueue_scripts', array('GK_Widget_Rules_Back_End', 'load_scripts'));
	
	class GK_Widget_Rules_Back_End {
		static function load_scripts($hook) {
			if($hook != 'widgets.php') {
				return;
			}
		 
			wp_enqueue_script( 'gk-widget-rules-js', plugins_url('gk-widget-rules.js', __FILE__));
			wp_enqueue_style( 'gk-widget-rules-css', plugins_url('gk-widget-rules.css', __FILE__));
		}
	
		// definition of the additional operation
		static function update($instance, $new_instance, $old_instance, $widget) {	
			// check if param was set
			if ( isset( $_POST['gk_widget_rules_' . $widget->id] ) ) {	
				$config = gk_widget_rules_get_config();
				// set the new key in the array
				$config['type'][$widget->id] = $_POST['gk_widget_rules_type_' . $widget->id];
				$config['rules'][$widget->id] = $_POST['gk_widget_rules_' . $widget->id];
				$config['css'][$widget->id] = $_POST['gk_widget_style_css_' . $widget->id];
				$config['responsive'][$widget->id] = $_POST['gk_widget_responsive_' . $widget->id];
				$config['users'][$widget->id] = $_POST['gk_widget_users_' . $widget->id];	
				// get all widgets names
				$all_widgets = array();
				$all_widgets_assoc = get_option('sidebars_widgets'); 
				// iterate throug the sidebar widgets settings to get all active widgets names
				foreach($all_widgets_assoc as $sidebar_name => $sidebar) {
					// remember about wp_inactive_widgets and array_version fields!
					if($sidebar_name != 'wp_inactive_widgets' && is_array($sidebar) && count($sidebar) > 0) {
						foreach($sidebar as $widget_name) {
							array_push($all_widgets, $widget_name);
						}
					}
				}
				// get the widget names from the exisitng settings
				$widget_names = array_keys($config['type']);
				// check for the unexisting widgets
		        foreach($widget_names as $widget_name) {
		            // if widget doesn't exist - remove it from the options
		            if(in_array($widget_name, $all_widgets) !== TRUE) {
		                if(isset($config['type']) && is_array($config['type']) && isset($config['type'][$widget_name])) {
		                    unset($config['type'][$widget_name]);
		                }
		
		                if(isset($config['rules']) && is_array($config['rules']) && isset($config['rules'][$widget_name])) {
		                    unset($config['rules'][$widget_name]);
		                }
		
		                if(isset($config['css']) && is_array($config['css']) && isset($config['css'][$widget_name])) {
		                    unset($config['css'][$widget_name]);
		                }
		
		                if(isset($config['responsive']) && is_array($config['responsive']) && isset($config['responsive'][$widget_name])) {
		                    unset($config['responsive'][$widget_name]);
		                }
		
		                if(isset($config['users']) && is_array($config['users']) && isset($config['users'][$widget_name])) {
		                    unset($config['users'][$widget_name]);
		                }
		            }
		        }
				// update the settings			
				update_option('gk_widget_rules', json_encode($config));
			}	
			// return the widget instance
			return $instance;
		}
		
		// function to add the widget control 
		static function control() {	
			// get the access to the registered widget controls
			global $wp_registered_widget_controls;
		
			// get the widget parameters
			$params = func_get_args();
			// find the widget ID
			$id = $params[0]['widget_id'];
			$unique_id = $id . '-' . rand(10000000, 99999999);
			$config = gk_widget_rules_get_config();
			// get the widget form callback
			$callback = $wp_registered_widget_controls[$id]['callback_redir'];
			// if the callbac exist - run it with the widget parameters
			if (is_callable($callback)) {
				call_user_func_array($callback, $params);
			}
			// value of the option
			$value_type = !empty($config['type'][$id]) ? htmlspecialchars(stripslashes($config['type'][$id]),ENT_QUOTES) : '';
			$value = !empty($config['rules'][$id]) ? htmlspecialchars(stripslashes($config['rules'][$id]),ENT_QUOTES) : '';	
			$style_css = !empty($config['css'][$id]) ? htmlspecialchars(stripslashes($config['css'][$id]),ENT_QUOTES) : '';	
			$responsive_mode = !empty($config['responsive'][$id]) ? htmlspecialchars(stripslashes($config['responsive'][$id]),ENT_QUOTES) : '';	
			$users_mode = !empty($config['users'][$id]) ? htmlspecialchars(stripslashes($config['users'][$id]),ENT_QUOTES) : '';	
			// 
			echo '
			<a class="gk_widget_rules_btn button">Widget rules</a>
			<div class="gk_widget_rules_wrapper'.((isset($_COOKIE['gk_last_opened_widget_rules_wrap']) && $_COOKIE['gk_last_opened_widget_rules_wrap'] == 'gk_widget_rules_form_'.$id) ? ' active' : '').'" data-id="gk_widget_rules_form_'.$id.'">
				<p>
					<label for="gk_widget_rules_'.$id.'">'.__('Visible at: ', 'gk-widget-rules').'</label>
					<select name="gk_widget_rules_type_'.$id.'" id="gk_widget_rules_type_'.$id.'" class="gk_widget_rules_select">
						<option value="all"'.(($value_type != "include" && $value_type != 'exclude') ? " selected=\"selected\"":"").'>'.__('All pages', 'gk-widget-rules').'</option>
						<option value="exclude"'.selected($value_type, "exclude", false).'>'.__('All pages except:', 'gk-widget-rules').'</option>
						<option value="include"'.selected($value_type, "include", false).'>'.__('No pages except:', 'gk-widget-rules').'</option>
					</select>
				</p>
				<fieldset class="gk_widget_rules_form" id="gk_widget_rules_form_'.$unique_id.'" data-id="gk_widget_rules_form_'.$id.'">
					<legend>'.__('Select page to add', 'gk-widget-rules').'</legend>
					 <select class="gk_widget_rules_form_select">
					 	<option value="homepage">'.__('Homepage', 'gk-widget-rules').'</option>
					 	<option value="page:">'.__('Page', 'gk-widget-rules').'</option>
					 	<option value="post:">'.__('Post', 'gk-widget-rules').'</option>
					 	<option value="category:">'.__('Category', 'gk-widget-rules').'</option>
					 	<option value="category_descendant:">'.__('Category with descendants', 'gk-widget-rules').'</option>
					 	<option value="tag:">'.__('Tag', 'gk-widget-rules').'</option>
					 	<option value="archive">'.__('Archive', 'gk-widget-rules').'</option>
					 	<option value="author:">'.__('Author', 'gk-widget-rules').'</option>
					 	<option value="template:">'.__('Page Template', 'gk-widget-rules').'</option>
					 	<option value="taxonomy:">'.__('Taxonomy', 'gk-widget-rules').'</option>
					 	<option value="posttype:">'.__('Post type', 'gk-widget-rules').'</option>
					 	<option value="search">'.__('Search page', 'gk-widget-rules').'</option>
					 	<option value="page404">'.__('404 page', 'gk-widget-rules').'</option>
					 </select>
					 <p><label>'.__('Page ID/Title/slug:', 'gk-widget-rules').'<input type="text" class="gk_widget_rules_form_input_page" /></label></p>
					 <p><label>'.__('Post ID/Title/slug:', 'gk-widget-rules').'<input type="text" class="gk_widget_rules_form_input_post" /></label></p>
					 <p><label>'.__('Category ID/Name/slug:', 'gk-widget-rules').'<input type="text" class="gk_widget_rules_form_input_category" /></label></p>
					 <p><label>'.__('Category ID:', 'gk-widget-rules').'<input type="text" class="gk_widget_rules_form_input_category_descendant" /></label></p>
					 <p><label>'.__('Tag ID/Name:', 'gk-widget-rules').'<input type="text" class="gk_widget_rules_form_input_tag" /></label></p>
					 <p><label>'.__('Author:', 'gk-widget-rules').'<input type="text" class="gk_widget_rules_form_input_author" /></label></p>
					 <p><label>'.__('Template:', 'gk-widget-rules').'<input type="text" class="gk_widget_rules_form_input_template" /></label></p>
					 <p><label>'.__('Taxonomy:', 'gk-widget-rules').'<input type="text" class="gk_widget_rules_form_input_taxonomy" /></label></p>
					 <p><label>'.__('Taxonomy term:', 'gk-widget-rules').'<input type="text" class="gk_widget_rules_form_input_taxonomy_term" /></label></p>
					 <p><label>'.__('Post type:', 'gk-widget-rules').'<input type="text" class="gk_widget_rules_form_input_posttype" /></label></p>
					 <p><button class="gk_widget_rules_btn button-secondary">'.__('Add page', 'gk-widget-rules').'</button></p>
					 <input type="text" name="gk_widget_rules_'.$id.'"  id="gk_widget_rules_'.$id.'" value="'.$value.'" class="gk_widget_rules_output" />
					 <fieldset class="gk_widget_rules_pages">
					 	<legend>'.__('Selected pages', 'gk-widget-rules').'</legend>
					 	<span class="gk_widget_rules_nopages">'.__('No pages', 'gk-widget-rules').'</span>
					 	<div></div>
					 </fieldset>
				</fieldset>
				<script type="text/javascript">gk_widget_control_init(\'#gk_widget_rules_form_'.$unique_id.'\');</script>';
			// create the list of suffixes
			self::control_styles_list($params[0]['widget_id'], $id, $responsive_mode, $users_mode, $style_css);
		}
		
		static function add_control() {	
			global $tpl;
			global $wp_registered_widgets; 
			global $wp_registered_widget_controls;
			// get option value
			$config = gk_widget_rules_get_config();
			// AJAX updates
			if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {	
				foreach ( (array) $_POST['widget-id'] as $widget_number => $widget_id ) {
					// save widget rules type
					if (isset($_POST['gk_widget_rules_type_' . $widget_id])) {
						$config['type'][$widget_id] = $_POST['gk_widget_rules_type_' . $widget_id];
					}
					// save widget rules
					if (isset($_POST['gk_widget_rules_' . $widget_id])) {
						$config['rules'][$widget_id] = $_POST['gk_widget_rules_' . $widget_id];
					}
					// save widget style CSS
					if (isset($_POST['gk_widget_style_css_' . $widget_id])) {
						$config['css'][$widget_id] = $_POST['gk_widget_style_css_' . $widget_id];
					}
					// save widget responsive
					if (isset($_POST['gk_widget_responsive_' . $widget_id])) {
						$config['responsive'][$widget_id] = $_POST['gk_widget_responsive_' . $widget_id];
					}
					// save widget users
					if (isset($_POST['gk_widget_users_' . $widget_id])) {
						$config['users'][$widget_id] = $_POST['gk_widget_users_' . $widget_id];
					}
				}
			}
			// save the widget id
			foreach ( $wp_registered_widgets as $id => $widget ) {	
				// save the widget id		
				$wp_registered_widget_controls[$id]['params'][0]['widget_id'] = $id;
				// do the redirection
				$wp_registered_widget_controls[$id]['callback_redir'] = $wp_registered_widget_controls[$id]['callback'];
				$wp_registered_widget_controls[$id]['callback'] = array('GK_Widget_Rules_Back_End', 'control');		
			}
		}
		
		static function control_styles_list($widget_name, $id, $responsive, $users, $css = '') {
			echo '<div>';
			//
			echo '<p><label for="gk_widget_style_css_'.$id.'">'.__('Custom CSS class: ', 'gk-widget-rules').'<input type="text" name="gk_widget_style_css_'.$id.'"  id="gk_widget_style_class_'.$id.'" value="'.$css.'" /></label></p>';
			// output the responsive select
			$items = array(
				'<option value="all-devices"'.((!$responsive || $responsive == 'all-devices') ? ' selected="selected"' : '').'>'.__('All devices', 'gk-widget-rules').'</option>',
				'<option value="only-desktop"'.selected($responsive, 'only-desktop', false).'>'.__('Desktop', 'gk-widget-rules').'</option>',
				'<option value="only-tablets"'.selected($responsive, 'only-tablets', false).'>'.__('Tablets', 'gk-widget-rules').'</option>',
				'<option value="only-smartphones"'.selected($responsive, 'only-smartphones', false).'>'.__('Smartphones', 'gk-widget-rules').'</option>',
				'<option value="only-tablets-and-smartphones"'.selected($responsive, 'only-tablets-and-smartphones', false).'>'.__('Tablets/Smartphones', 'gk-widget-rules').'</option>',
				'<option value="only-desktop-and-tablets"'.selected($responsive, 'only-desktop-and-tablets', false).'>'.__('Desktop/Tablets', 'gk-widget-rules').'</option>'
			);
			//
			echo '<p><label for="gk_widget_responsive_'.$id.'">'.__('Visible on: ', 'gk-widget-rules').'<select name="gk_widget_responsive_'.$id.'"  id="gk_widget_responsive_'.$id.'">';
			//
			foreach($items as $item) {
				echo $item;
			}
			//
			echo '</select></label></p>';
			// output the user groups select
			$items = array(
				'<option value="all"'.(($users == null || !$users || $users == 'all') ? ' selected="selected"' : '').'>'.__('All users', 'gk-widget-rules').'</option>',
				'<option value="guests"'.selected($users, 'guests', false).'>'.__('Only guests', 'gk-widget-rules').'</option>',
				'<option value="registered"'.selected($users, 'registered', false).'>'.__('Only registered users', 'gk-widget-rules').'</option>',
				'<option value="administrator"'.selected($users, 'administrator', false).'>'.__('Only administrator', 'gk-widget-rules').'</option>'
			);
			//
			echo '<p><label for="gk_widget_users_'.$id.'">'.__('Visible for: ', 'gk-widget-rules').'<select name="gk_widget_users_'.$id.'"  id="gk_widget_users_'.$id.'">';
			//
			foreach($items as $item) {
				echo $item;
			}
			//
			echo '</select></label></p></div></div>';
			//
			echo '<hr />';
		}
	}
} // end of the code for the dashboard


if(!is_admin() && !class_exists('GK_Widget_Rules_Front_End')) {
	class GK_Widget_Rules_Front_End {
		
		static $conditions = array();
		/**
		 *
		 * Function used to create conditional string
		 *
		 * @param mode - mode of the condition - exclude, all, include
		 * @param input - input data separated by commas, look into example inside the function
		 * @param users - the value of the user access
		 *
		 * @return HTML output
		 *
		 **/
		static function condition($mode, $input, $users) {
			// Example input:
			// homepage,page:12,post:10,category:test,tag:test
		
			$output = ' (';
			if($mode == 'all') {
				$output = '';
			} else if($mode == 'exclude') {
				$output = ' !(';
			}
		
			if($mode != 'all') {
				$input = preg_replace('@[^a-zA-Z0-9\-_,;\:\.\s]@mis', '', $input); 
				$input = substr($input, 1);
				$input = explode(',', $input);
		
				for($i = 0; $i < count($input); $i++) {
					if($i > 0) {
						$output .= '||'; 
					}
		
					if(stripos($input[$i], 'homepage') !== FALSE) {
					    $output .= ' is_home() ';
					} else if(stripos($input[$i], 'page:') !== FALSE) {
					    $output .= ' is_page(\'' . substr($input[$i], 5) . '\') ';
					} else if(stripos($input[$i], 'post:') !== FALSE) {
					    $output .= ' is_single(\'' . substr($input[$i], 5) . '\') ';
					} else if(stripos($input[$i], 'category:') !== FALSE) {
					    $output .= ' (is_category(\'' . substr($input[$i], 9) . '\') || (in_category(\'' . substr($input[$i], 9) . '\') && is_single())) ';
					} else if(stripos($input[$i], 'category_descendant:') !== FALSE) {
						$output .= ' (is_category(\'' . substr($input[$i], 20) . '\') || (in_category(\'' . substr($input[$i], 20) . '\') || post_is_in_descendant_category( \'' . substr($input[$i], 20) . '\' ) && !is_home())) ';
					} else if(stripos($input[$i], 'tag:') !== FALSE) {
					    $output .= ' (is_tag(\'' . substr($input[$i], 4) . '\') || (has_tag(\'' . substr($input[$i], 4) . '\') && is_single())) ';
					} else if(stripos($input[$i], 'archive') !== FALSE) {
					    $output .= ' is_archive() ';
					} else if(stripos($input[$i], 'author:') !== FALSE) {
					    $output .= ' (is_author(\'' . substr($input[$i], 7) . '\')) ';
				    } else if(stripos($input[$i], 'template:') !== FALSE) {
				        if(substr($input[$i], 9) != '') {
				       		$output .= ' (is_page_template(\'' . substr($input[$i], 9) . '.php\') && is_singular()) ';
				       	} else {
				       		$output .= ' (is_page_template() && is_singular()) ';
				       	}
			        } else if(stripos($input[$i], 'format:') !== FALSE) {
			        	if(substr($input[$i], 7 != '')) {
			            	$output .= ' (has_term( \'post_format\', \'post-format-' . substr($input[$i], 7) . '\') && is_single()) ';
			            } else {
			            	$output .= ' (has_term( \'post_format\') && is_single()) ';
			            }
					} else if(stripos($input[$i], 'taxonomy:') !== FALSE) {
					    if(substr($input[$i], 9) != '') {
					    	$taxonomy = substr($input[$i], 9);
					    	$taxonomy = explode(';', $taxonomy);
					    	// check amount of taxonomies
					    	if(count($taxonomy) == 1) {
					    	     $output .= ' (is_tax(\'' . $taxonomy[0] . '\'))';
					    	} else if(count($taxonomy) == 2) {
					    	     $output .= ' (has_term(\'' . $taxonomy[1] . '\', \'' . $taxonomy[0] . '\')) ';
					    	}
					   	}
					} else if(stripos($input[$i], 'posttype:') !== FALSE) {
					    if(substr($input[$i], 9) != '') {
					    	$type = substr($input[$i], 9);
					    	// check for post types
					    	if($type != '') {
					   			$output .= ' (get_post_type() == \'' . $type . '\' && is_single()) ';
					   		}
					   	}
					} else if(stripos($input[$i], 'search') !== FALSE) {
					    $output .= ' is_search() ';
					} else if(stripos($input[$i], 'page404') !== FALSE) {
					    $output .= ' is_404() ';
					}
				}
		
				$output .= ')';
			}
		
			if($users != 'all') {
				if($users == 'guests') {
					$output .= (($output == '') ? '' : ' && ') . ' !is_user_logged_in()';
				} else if($users == 'registered') {
					$output .= (($output == '') ? '' : ' && ') . ' is_user_logged_in()';
				} else if($users == 'administrator') {
					$output .= (($output == '') ? '' : ' && ') . ' current_user_can(\'manage_options\')';
				}
			}
		
			if($output == '' || trim($output) == '()' || trim($output) == '!()') {
				$output = ' TRUE';
			}
			
			return $output;
		}
		
		static function filter_widgets($sidebars_widgets) {	
			$config = gk_widget_rules_get_config();
			
			// iterate all sidebars
			foreach($sidebars_widgets as $sidebar => $widgets) {	
				// skip inactive and empty sidebars
				if ($sidebar == 'wp_inactive_widgets' || empty($widgets)) {
					continue;
				}
				// get all widgets
				foreach($widgets as $index => $id) {	
					// create function
					$type = '';
					if(isset($config['type'][$id])) {
						$type = $config['type'][$id];
					}
					
					$rules = '';
					if(isset($config['rules'][$id])) {
						$rules = $config['rules'][$id];
					}
					
					$users = '';
					if(isset($config['users'][$id])) {
						$users = $config['users'][$id];
					}
					// cache for conditions
					if(!isset(self::$conditions[$id])) {
						self::$conditions[$id] = self::condition($type, $rules, $users);
					}
					
					$conditional_function = create_function('', 'return '. self::$conditions[$id] .';');
					
					// generate the result of function
					$conditional_result = $conditional_function();
					// eval condition function
					if(!$conditional_result) {
						unset($sidebars_widgets[$sidebar][$index]);
						continue;
					}
				}
			}
			
			return $sidebars_widgets;
		}
		
		// function used to add new CSS classes to widgets
		static function add_classes($params) {
			$config = gk_widget_rules_get_config();
			// additional CSS classes
			if(
				isset($config['css']) && 
				isset($config['css'][$params[0]['widget_id']])
			) {
				$widget_css_class = $config['css'][$params[0]['widget_id']];
				$params[0]['before_widget'] = str_replace('class="', 'class="' . $widget_css_class . ' ', $params[0]['before_widget']);
			}
			// responsive CSS classes
			if(
				isset($config['responsive']) && 
				isset($config['responsive'][$params[0]['widget_id']])
			) {
				$widget_rwd_css_class = $config['responsive'][$params[0]['widget_id']];
				$params[0]['before_widget'] = str_replace('class="', 'class="' . $widget_rwd_css_class . ' ', $params[0]['before_widget']);
			}
			
			return $params;
		}
	}
	
	add_filter('sidebars_widgets', array('GK_Widget_Rules_Front_End', 'filter_widgets'), 10);
	add_filter('dynamic_sidebar_params', array('GK_Widget_Rules_Front_End', 'add_classes'), 10);
}

// EOF