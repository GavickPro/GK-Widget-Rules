<?php

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
		
		static function filter_widgets($instance) {	
			// get settings
			$config = unserialize($instance['gk_widget_rules']);
			// create function
			$type = '';
			if(isset($config['type'])) {
				$type = $config['type'];
			}
			
			$rules = '';
			if(isset($config['value'])) {
				$rules = $config['value'];
			}
			
			$users = '';
			if(isset($config['users'])) {
				$users = $config['users'];
			}
			// cache for conditions
			if(!isset(self::$conditions[md5($instance['gk_widget_rules'])])) {
				self::$conditions[md5($instance['gk_widget_rules'])] = self::condition($type, $rules, $users);
			}
			
			$conditional_function = create_function('', 'return '. self::$conditions[md5($instance['gk_widget_rules'])] .';');
			// generate the result of function
			$conditional_result = $conditional_function();
			// eval condition function
			if(!$conditional_result) {
				return false;
			}
			
			return $instance;
		}
		
		// function used to add new CSS classes to widgets
		static function add_classes($params) {
			global $wp_registered_widgets;
			// get the widget settings
			$widget_settings = get_option($wp_registered_widgets[$params[0]['widget_id']]['callback'][0]->option_name);
			// get the configuration
			$config = array_shift($widget_settings);
			if(isset($config['gk_widget_rules'])) {
				$config = unserialize($config['gk_widget_rules']);	
			} else {
				$config = array();
			}
			// additional CSS classes
			if(isset($config['css'])) {
				$widget_css_class = $config['css'];
				$params[0]['before_widget'] = str_replace('class="', 'class="' . $widget_css_class . ' ', $params[0]['before_widget']);
			}
			// responsive CSS classes
			if(isset($config['responsive'])) {
				$widget_rwd_css_class = $config['responsive'];
				$params[0]['before_widget'] = str_replace('class="', 'class="' . $widget_rwd_css_class . ' ', $params[0]['before_widget']);
			}
			
			return $params;
		}
	}
	
	add_filter('widget_display_callback', array('GK_Widget_Rules_Front_End', 'filter_widgets'), 10);
	add_filter('dynamic_sidebar_params', array('GK_Widget_Rules_Front_End', 'add_classes'), 10);
}

// EOF