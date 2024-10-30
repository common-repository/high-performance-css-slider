<?php
/*
Plugin Name: High Performance CSS Slider
Plugin URI: https://moritz-scheitinger.de/HPCSSSLIDER/
Description: High Performance CSS Slider
Version: 1.0
Author: Moritz Scheitinger
*/
?>

<?php 
function hpcss_slider_activation() {} register_activation_hook(__FILE__, 'hpcss_slider_activation');
function hpcss_slider_deactivation() {}register_deactivation_hook(__FILE__, 'hpcss_slider_deactivation');

add_action('wp_enqueue_scripts', 'hpcss_slider_styles');
function hpcss_slider_styles() {
	wp_register_style('hpcss-slider', plugins_url('css/hpcss-slider.css', __FILE__));
}

add_shortcode("hpcss_image_slider", "hpcss_display_slider");
function hpcss_display_slider($atts,$content) {
	wp_enqueue_style('hpcss-slider');
	$atts = shortcode_atts(array('id' => '',), $atts);
	$current_post_id=$atts["id"];
	$test = get_post_field('post_content', $current_post_id);
	$test = str_replace("[gallery ids=\"", "",$test);
	$test = str_replace("\"]", "",$test);
	$pieces = explode(",", $test);

	$args = array(
		'post_type' => 'attachment',
		'post__in' => $pieces
	);
		$attachments = get_posts($args);

		if ($attachments){
			$html = '<div class="slider-container">';
				$slide_amount = count($attachments);

				for($x=1;$x<=$slide_amount; $x++) {
					$html .= "<s id='s" . $x . "'></s>";
				}

				$html .= "<div class='slider'>";
					foreach($attachments as $attachment){

						$gallery_images = wp_get_attachment_image( $attachment->ID, 'full' );
						$html .= "<div class='slide-item'>";
						$html .= $gallery_images;
						$html .= "<div>$attachment->post_title</div><p>$attachment->post_content</p></div>";
					}
				$html .= "</div class='slider'>";

				$html .= "<div class='prev-next-container'>";
					for($x=1;$x<=$slide_amount; $x++) {
						$next_value = $x+1;
						$prev_value = $x-1;
						if($next_value > $slide_amount){
							$next_value = 1;
						}

						if($prev_value < 1){
							$prev_value = $slide_amount;
						}
						$html .= "<div><a href='#s$prev_value' class='slider-prev'></a><a href='#s$next_value' class='slider-next'></a></div>";
					}
				$html .= "</div>";

				$html .= "<div class='bullets'>";
					for($x=1;$x<=$slide_amount; $x++) {
						$html .= "<a href='#s$x'></a>";
					}
				$html .= "</div>";
			$html .= '</div>';
		}
		return $html;
}

add_action('init', 'hpcss_register_slider');
function hpcss_register_slider() {
	$labels = array(
		'name' => 'All Slide',
		'menu_name' => 'HPCSS Slider',
		'add_new' => 'Add New Slide',
        'add_new_item' => 'Add New Slide',
        'edit_item' => 'Edit Slide'
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		'description' => 'Slideshows',
		'supports' => array('title', 'editor'),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'show_in_nav_menus' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'has_archive' => true,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => true,
		'capability_type' => 'post'
	);
	register_post_type('slider', $args);
}

add_filter('manage_edit-slider_columns', 'hpcss_set_custom_edit_slider_columns');
add_action('manage_slider_posts_custom_column', 'hpcss_custom_slider_column', 10, 2);

function hpcss_set_custom_edit_slider_columns($columns) {
	return $columns + array('slider_shortcode' => __('Shortcode'));
}

function hpcss_custom_slider_column($column, $post_id) {
	$slider_meta = get_post_meta($post_id, "_hpcss_slider_meta", true);
	$slider_meta = ($slider_meta != '') ? json_decode($slider_meta) : array();
	switch ($column){
		case 'slider_shortcode':
			echo "[hpcss_image_slider id=$post_id]";
		break;
    }
}
?>
