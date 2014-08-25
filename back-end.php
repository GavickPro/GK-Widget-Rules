<?php

// run the code only on the dashboard
if(is_admin() && !class_exists('GK_Widget_Rules_Back_End')) {
	// define an additional operation when save the widget
	add_filter('widget_update_callback', array('GK_Widget_Rules_Back_End', 'update'), 10, 4);
	add_action('admin_enqueue_scripts', array('GK_Widget_Rules_Back_End', 'load_scripts'));
	add_action('in_widget_form', array('GK_Widget_Rules_Back_End', 'control'), 10, 3);
	
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
			// get the POST data
			if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {	
				$widget_rules = array(
					'type' => '',
					'value' => '',
					'css' => '',
					'responsive' => '',
					'users' => ''
				);

				// save widget rules type
				if (isset($_POST['gk_widget_rules_type'])) {
					$widget_rules['type'] = preg_replace('@[^a-z\-]@', '', $_POST['gk_widget_rules_type']);
				}
				// save widget rules
				if (isset($_POST['gk_widget_rules_output'])) {
					$widget_rules['value'] = preg_replace('@[^A-Za-z0-9\-\(\)\!\_\+\:\,]@', '', $_POST['gk_widget_rules_output']);
				}
				// save widget style CSS
				if (isset($_POST['gk_widget_rules_css'])) {
					$widget_rules['css'] = preg_replace('@[^A-Za-z0-9\-\_\s]@', '', $_POST['gk_widget_rules_css']);
				}
				// save widget responsive
				if (isset($_POST['gk_widget_rules_responsive'])) {
					$widget_rules['responsive'] = preg_replace('@[^a-z\-]@', '', $_POST['gk_widget_rules_responsive']);
				}
				// save widget users
				if (isset($_POST['gk_widget_rules_users'])) {
					$widget_rules['users'] = preg_replace('@[^a-z\-]@', '', $_POST['gk_widget_rules_users']);
				}
				// store the data in the widget
				$instance['gk_widget_rules'] = serialize($widget_rules);
			}

			// return the widget instance
			return $instance;
		}
		
		// function to add the widget control 
		static function control($widget, $return, $instance) {	
			// get the access to the registered widget controls
			global $wp_registered_widget_controls;

			$widget_rules = array(
				'type' => '',
				'value' => '',
				'css' => '',
				'responsive' => '',
				'users' => ''
			);
		
			if(isset($instance['gk_widget_rules'])) {
				$widget_rules = unserialize($instance['gk_widget_rules']);
			}
		
			// value of the option
			$value_type = $widget_rules['type'];
			$value = $widget_rules['value'];
			$css = $widget_rules['css'];
			$responsive_mode = $widget_rules['responsive'];
			$users_mode = $widget_rules['users'];
			// random ID for the widget form
			$unique_id = rand(100000000, 999999999);

			?>
				<a class="gk_widget_rules_btn button"><?php _e('Widget rules', 'gk-widget-rules'); ?></a>
				<div class="gk_widget_rules_wrapper<?php if (isset($_POST['gk-widget-rules-visibility']) && $_POST['gk-widget-rules-visibility'] == '1') { ?> active<?php } ?>" id="gk_widget_rules_form_<?php echo $unique_id; ?>">
					<p>
						<label for="gk_widget_rules_type">
							<?php _e('Visible at: ', 'gk-widget-rules'); ?>
						</label>
						<select name="gk_widget_rules_type" id="gk_widget_rules_type" class="gk_widget_rules_select">
							<option value="all"<?php echo (($value_type != "include" && $value_type != 'exclude') ? " selected=\"selected\"":""); ?>><?php _e('All pages', 'gk-widget-rules'); ?></option>
							<option value="exclude"<?php selected($value_type, "exclude"); ?>><?php _e('All pages except:', 'gk-widget-rules'); ?></option>
							<option value="include"<?php selected($value_type, "include"); ?>><?php _e('No pages except:', 'gk-widget-rules'); ?></option>
						</select>
					</p>
					<fieldset class="gk_widget_rules_form" id="gk_widget_rules_form_<?php echo $unique_id; ?>" data-id="gk_widget_rules_form_<?php echo $unique_id; ?>">
						<legend><?php _e('Select page to add', 'gk-widget-rules'); ?></legend>
						
						 <select class="gk_widget_rules_form_select">
						 	<option value="homepage"><?php _e('Homepage', 'gk-widget-rules'); ?></option>
						 	<option value="page:"><?php _e('Page', 'gk-widget-rules'); ?></option>
						 	<option value="post:"><?php _e('Post', 'gk-widget-rules'); ?></option>
						 	<option value="category:"><?php _e('Category', 'gk-widget-rules'); ?></option>
						 	<option value="category_descendant:"><?php _e('Category with descendants', 'gk-widget-rules'); ?></option>
						 	<option value="tag:"><?php _e('Tag', 'gk-widget-rules'); ?></option>
						 	<option value="archive"><?php _e('Archive', 'gk-widget-rules'); ?></option>
						 	<option value="author:"><?php _e('Author', 'gk-widget-rules'); ?></option>
						 	<option value="template:"><?php _e('Page Template', 'gk-widget-rules'); ?></option>
						 	<option value="taxonomy:"><?php _e('Taxonomy', 'gk-widget-rules'); ?></option>
						 	<option value="posttype:"><?php _e('Post type', 'gk-widget-rules'); ?></option>
						 	<option value="search"><?php _e('Search page', 'gk-widget-rules'); ?></option>
						 	<option value="page404"><?php _e('404 page', 'gk-widget-rules'); ?></option>
						 </select>
						 <p>
						 	<label>
						 		<?php _e('Page ID/Title/slug:', 'gk-widget-rules'); ?>
						 		<input type="text" class="gk_widget_rules_form_input_page" />
						 	</label>
						 </p>
						 <p>
						 	<label>
						 		<?php _e('Post ID/Title/slug:', 'gk-widget-rules'); ?>
						 		<input type="text" class="gk_widget_rules_form_input_post" />
						 	</label>
						 </p>
						 <p>
						 	<label>
						 		<?php _e('Category ID/Name/slug:', 'gk-widget-rules'); ?>
						 		<input type="text" class="gk_widget_rules_form_input_category" />
						 	</label>
						 </p>
						 <p>
						 	<label>
						 		<?php _e('Category ID:', 'gk-widget-rules'); ?>
						 		<input type="text" class="gk_widget_rules_form_input_category_descendant" />
						 	</label>
						 </p>
						 <p>
						 	<label>
						 		<?php _e('Tag ID/Name:', 'gk-widget-rules'); ?>
						 		<input type="text" class="gk_widget_rules_form_input_tag" />
						 	</label>
						 </p>
						 <p>
						 	<label>
						 		<?php _e('Author:', 'gk-widget-rules'); ?>
						 		<input type="text" class="gk_widget_rules_form_input_author" />
						 	</label>
						 </p>
						 <p>
						 	<label>
						 		<?php _e('Template:', 'gk-widget-rules'); ?>
						 		<input type="text" class="gk_widget_rules_form_input_template" />
						 	</label>
						 </p>
						 <p>
						 	<label>
						 		<?php _e('Taxonomy:', 'gk-widget-rules'); ?>
						 		<input type="text" class="gk_widget_rules_form_input_taxonomy" />
						 	</label>
						 </p>
						 <p>
						 	<label>
						 		<?php _e('Taxonomy term:', 'gk-widget-rules'); ?>
						 		<input type="text" class="gk_widget_rules_form_input_taxonomy_term" />
						 	</label>
						 </p>
						 <p>
						 	<label>
						 		<?php _e('Post type:', 'gk-widget-rules'); ?>
						 		<input type="text" class="gk_widget_rules_form_input_posttype" />
						 	</label>
						 </p>
						 <p>
						 	<button class="gk_widget_rules_btn button-secondary"><?php _e('Add page', 'gk-widget-rules'); ?></button>
						 </p>
						 <input type="text" name="gk_widget_rules_output" id="gk_widget_rules_output" value="<?php echo $value; ?>" class="gk_widget_rules_output" />
						 
						 <fieldset class="gk_widget_rules_pages">
						 	<legend><?php _e('Selected pages', 'gk-widget-rules'); ?></legend>
						 	<span class="gk_widget_rules_nopages"><?php _e('No pages', 'gk-widget-rules'); ?></span>
						 	<div></div>
						 </fieldset>
					</fieldset>
				<div>
			
				<p>
					<label for="gk_widget_rules_css">
						<?php _e('Custom CSS class: ', 'gk-widget-rules'); ?>
						<input type="text" name="gk_widget_rules_css" value="<?php echo $css; ?>" />
					</label>
				</p>

					<p>
						<label for="gk_widget_rules_responsive"><?php _e('Visible on: ', 'gk-widget-rules'); ?>
							<select name="gk_widget_rules_responsive">
								<option value="all-devices"<?php echo ((!$responsive_mode || $responsive_mode == 'all-devices') ? ' selected="selected"' : ''); ?>><?php _e('All devices', 'gk-widget-rules'); ?></option>
								<option value="only-desktop"<?php selected($responsive_mode, 'only-desktop'); ?>><?php _e('Desktop', 'gk-widget-rules'); ?></option>
								<option value="only-tablets"<?php selected($responsive_mode, 'only-tablets'); ?>><?php _e('Tablets', 'gk-widget-rules'); ?></option>
								<option value="only-smartphones"<?php selected($responsive_mode, 'only-smartphones'); ?>><?php _e('Smartphones', 'gk-widget-rules'); ?></option>
								<option value="only-tablets-and-smartphones"<?php selected($responsive_mode, 'only-tablets-and-smartphones'); ?>><?php _e('Tablets/Smartphones', 'gk-widget-rules'); ?></option>
								<option value="only-desktop-and-tablets"<?php selected($responsive_mode, 'only-desktop-and-tablets'); ?>><?php _e('Desktop/Tablets', 'gk-widget-rules'); ?></option>
							</select>
						</label>
					</p>
					<p>
						<label for="gk_widget_rules_users">
							<?php _e('Visible for: ', 'gk-widget-rules'); ?>
							<select name="gk_widget_rules_users">
								<option value="all"<?php echo ($users_mode == null || !$users_mode || $users_mode == 'all') ? ' selected="selected"' : ''; ?>><?php _e('All users', 'gk-widget-rules'); ?></option>
								<option value="guests"<?php selected($users_mode, 'guests'); ?>><?php _e('Only guests', 'gk-widget-rules'); ?></option>
								<option value="registered"<?php selected($users_mode, 'registered'); ?>><?php _e('Only registered users', 'gk-widget-rules'); ?></option>
								<option value="administrator"<?php selected($users_mode, 'administrator'); ?>><?php _e('Only administrator', 'gk-widget-rules'); ?></option>
							</select>
						</label>
					</p>
				</div>

				<input type="hidden" name="gk-widget-rules-visibility" class="gk-widget-rules-visibility" value="<?php if ( isset( $_POST['gk-widget-rules-visibility'] ) ) { echo esc_attr( $_POST['gk-widget-rules-visibility'] ); } else { ?>0<?php } ?>" />

				<?php if(isset($_POST['gk-widget-rules-visibility']) && $_POST['gk-widget-rules-visibility'] == '1') : ?>
				<script type="text/javascript">gk_widget_control_init('#gk_widget_rules_form_<?php echo $unique_id; ?>');</script>
				<?php endif; ?>
			</div>
			<hr />
			<?php
		}
	}
}

// EOF
