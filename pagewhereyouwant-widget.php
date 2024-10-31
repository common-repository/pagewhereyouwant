<?php
/*
Plugin Name: PageWhereYouWant
Plugin URI: http://wordpress.org/extend/plugins/pagewhereyouwant/
Description: Put a custom title, page title, page thumbnail, page content or page excerpt where you want in a widget ready area.
Version: 1.2
Author: Thomas Lamarche
Author URI: https://github.com/OloZ17
License: GPLv2
*/

class page_where_you_want extends WP_Widget {
	function __construct() {
		parent::__construct('page_where_you_want', 'Page Where You Want', array('description' => 'Put a custom title, page title, page thumbnail, page content or page excerpt, where you want in a widget ready area.'));		
	}

	function widget($args, $instance) {
		// Widget Output
		extract($args, EXTR_SKIP);
		$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
		$before_title = '<h1>';
		$after_title = '</h1>';
		$pageid = $instance['pageid'];
		$show_title = !empty($instance['show_title']) ? '1' : '0';
		$show_page_title = !empty($instance['show_page_title']) ? '1' : '0';
		$thumbnail_disabled = !empty($instance['thumbnail_disabled']) ? '1' : '0';
		$show_page_thumbnail = !empty($instance['show_page_thumbnail']) ? '1' : '0';
		$show_page_content = empty($instance['show_page_content']) ? '&nbsp;' : $instance['show_page_content'];
		
		/* Before widget (defined by themes). */
		echo $before_widget;
		
		if ($show_title == 1) {		
			if (!empty($title)) {
				echo $before_title .$title .$after_title;
			};
		};
		
		if ($show_page_title == 1) {
			echo $before_title;
			echo get_the_title($pageid);
			echo $after_title;
		};
		
		if ( function_exists( 'get_the_post_thumbnail' ) ) {
			if ($show_page_thumbnail == 1 && $thumbnail_disabled == 0) {
					echo get_the_post_thumbnail($pageid);
			}
		}
		
		if ($show_page_content == 'content') {
			$page_data = get_page( $pageid );
			// Get Content and retain Wordpress filters such as paragraph tags
			$content = apply_filters('the_content', $page_data->post_content);
			echo $content;
		};
			
		function wp_trim_all_excerpt($text) {
			global $post;
				$raw_excerpt = $text;
					if ($text == '') {
					   $text = get_the_content('');
					   $text = strip_shortcodes( $text );
					   $text = apply_filters('the_content', $text);
					   $text = str_replace(']]>', ']]&gt;', $text);
				  	}		 
				$text = strip_tags($text, '<p>');
				$excerpt_length = apply_filters('excerpt_length', 50);
				$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
				$text = wp_trim_words( $text, $excerpt_length, $excerpt_more ); 
				return apply_filters('wp_trim_excerpt', $text, $raw_excerpt);
		}
		 
		remove_filter('get_the_excerpt', 'wp_trim_excerpt');
		add_filter('get_the_excerpt', 'wp_trim_all_excerpt');
		
		if ($show_page_content == 'excerpt') {
			$page_data = get_page( $pageid );
			// Get Content and retain Wordpress filters such as paragraph tags
			$content = apply_filters('the_content', $page_data->post_content);
			$page_excerpt = wp_trim_all_excerpt($content);
			echo "<p>";
			echo $page_excerpt;
			echo "<br />";
			$excerpt_link = '<a href="'. get_permalink( $pageid ) . '">'  .__('Read more').' </a>';
			echo $excerpt_link;
			echo "</p>";
		};			
		
		/* After widget (defined by themes). */
		echo $after_widget;

	}
	
	function update( $new_instance, $old_instance ) {
		// Save widget options
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['pageid'] = strip_tags($new_instance['pageid']);
		$instance['show_title'] = strip_tags($new_instance['show_title']) ? 1 : 0;
		$instance['show_page_title'] = strip_tags($new_instance['show_page_title']) ? 1 : 0;
		$instance['thumbnail_disabled'] = strip_tags($new_instance['thumbnail_disabled']) ? 1 : 0;
		$instance['show_page_thumbnail'] = strip_tags($new_instance['show_page_thumbnail']) ? 1 : 0;
		$instance['show_page_content'] = strip_tags($new_instance['show_page_content']);
		return $instance;
	}
	
	function form( $instance ) {
		// Output admin widget options form
		$default = array(
					'title' => __('Put a title'),
					'pageid' => '',
					'show_title' => 0,
					'show_page_title' => 0,
					'thumbnail_disabled' => 0,
					'show_page_thumbnail' => 0,
					'show_page_content' => '',
					);
					
		$title = esc_attr(isset($instance['title']));
		$pageid = esc_attr(isset($instance['pageid']));
		$show_title = isset($instance['show_title']) ? (bool) $instance['show_title'] : false;
		$show_page_title = isset($instance['show_page_title']) ? (bool) $instance['show_page_title'] : false;
		$thumbnail_disabled = isset($instance['thumbnail_disabled']) ? (bool) $instance['thumbnail_disabled'] : false;
		$show_page_thumbnail = isset($instance['show_page_thumbnail']) ? (bool) $instance['show_page_thumbnail'] : false;
		$show_page_content = isset($instance['show_page_content']);		
		
		$instance = wp_parse_args((array) $instance, $default);
		$field_id = $this->get_field_id('title');
		$field_name = $this->get_field_name('title');
		echo "\r\n".'<p><label for="'.$field_id.'">'.__('Title').': <input type="text" class="widefat" id="'.$field_id.'" name="'.$field_name.'" value="'.esc_attr( $instance['title'] ).'" /><label></p>';	
		?>
		
		<p>
		<input id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" type="checkbox" value="1" <?php checked( '1', $show_title ); ?>/>
		 <label for="<?php echo $this->get_field_id('show_title'); ?>"><?php _e('Show title'); ?></label>
		</p>
		
		<p>
		<select name="<?php echo $this->get_field_name('pageid'); ?>" id="<?php echo $this->get_field_id('pageid'); ?> " class="widefat">
		 <option value="">
		<?php echo esc_attr( __( 'Select page' ) ); ?></option> 
		<?php 
		  $pages_args = array(
		  		'sort_column'=>'menu_order',
		  );
		  $pages = get_pages($pages_args);
		  foreach ( $pages as $page ) {
		  	$option = '<option value="' . ( $page->ID) . '"';
				if (( $page->ID) == $instance['pageid']) {
					$option .= 'selected = "selected" ';		
				}		
		    $option .='">';

		    	if (( !$page->post_parent) =='') {
		    		$option .= '&nbsp; &nbsp;';
		    	}	    
			$option .= $page->post_title;
			$option .= '</option>';
			echo $option;
		   }
		 ?>
		</select>
		</p>
		
		<p>
		<input id="<?php echo $this->get_field_id('show_page_title'); ?>" name="<?php echo $this->get_field_name('show_page_title'); ?>" type="checkbox" value="1" <?php checked( '1', $show_page_title ); ?>/>
		 <label for="<?php echo $this->get_field_id('show_page_title'); ?>"><?php _e('Show page title'); ?></label>
		</p>	
		
		<?php 
		$thumnbailsupport = get_theme_support( 'post-thumbnails' );
		    if( $thumnbailsupport === false ) {
		    	$show_page_thumbnail = 0;
		    	$thumbnail_disabled = 1;
		    	$before = "<p><b>";
		    	$after ="</b></p>";
		    	echo $before;
		    	_e('post-thumbnails option is not supported by your current wordpress theme');
		    	echo $after;
		    }	    
		?>	
		<p>
		   <input id="<?php echo $this->get_field_id('show_page_thumbnail'); ?>" name="<?php echo $this->get_field_name('show_page_thumbnail'); ?>" type="checkbox" value="1" <?php checked( '1', $show_page_thumbnail ); disabled( '1', $thumbnail_disabled ); ?>/>
		  <label for="<?php echo $this->get_field_id('show_page_thumbnail'); ?>"><?php _e('Show page thumbnail'); ?></label>
		</p>		
									
		<p>
		<input id="<?php echo $this->get_field_id('show_page_content'); ?>" name="<?php echo $this->get_field_name('show_page_content'); ?>" type="radio" value="content" <?php if ( 'content' == $instance['show_page_content'] ) echo 'checked'; ?> />
		 <label for="<?php echo $this->get_field_id('show_page_content'); ?>"><?php _e('Show page content'); ?></label>
		</p>
		
		<p>
		<input id="<?php echo $this->get_field_id('show_page_content'); ?>" name="<?php echo $this->get_field_name('show_page_content'); ?>" type="radio" value="excerpt" <?php if ( 'excerpt' == $instance['show_page_content'] ) echo 'checked'; ?> />
		 <label for="<?php echo $this->get_field_id('show_page_content'); ?>"><?php _e('Show page excerpt'); ?></label>
		</p>
		
		<?php	
	}
}
         
// Widget registration
add_action( 'widgets_init', create_function( '', 'register_widget( "page_where_you_want" );' ) );
       
?>