<?php
/*
Plugin Name: Siseko Facebook
Plugin URI: sisekoneti.com
Description: This will make my gallery a slideshow
Author: Siseko Neti
Author URI: sisekoneti.com
Version: 1.0
*/

//Exit if accessed directory, i.e No one should be allowed to access our file except WordPress
if (!defined('ABSPATH') ){
	exit;
}

/* Creating a custom post type */
function siseko_register_post_type() {
	$args = array( 
		'public' => true, 
		'label' => 'Facebook Post', 
		'has_archive' => true,
		'description' => 'This is how i put facebook posts on my blog');
	register_post_type('Facebook Post', $args);
}
add_action('init', 'siseko_register_post_type');

/* Global Variables */
$sisekofacebook_options = get_option('sisekofacebooksettings');

/* Creating the plugin page for the dashboard */
function siseko_facebook_plugin_page() {
	$sisekofacebook_options;
	ob_start();?>
<div class="wrap"> 
	<form action= "options.php" method="POST">
		<?php settings_fields('sisekofacebookgroup'); ?>
		<h1> WordPress Siseko Facebook Settings </h1>
		<p><h3> Edit your post before publishing it. </h3></p>
		<textarea name="sisekofacebooksettings"[original] rows="20" cols="100" ">
			<?php echo $sisekofacebook_options[original] ?>
		</textarea>
		<p><input type="submit" class="button-primary "value="Publish Post"></p>
	</form>
</div>

<?php
echo ob_get_clean();
}

/* Settings for my plugin to be registered */
function siseko_facebook_plugin() {
	register_setting('sisekofacebookgroup', 'sisekofacebooksettings');
}
add_action('admin_init', 'siseko_facebook_plugin');

/* Adding the plugin settings in the admin page */
function siseko_admin_sidebar_adder() {
	add_options_page('siseko facebook plugin', 'Siseko Facebook Plugin', 'manage_options', 'siseko_facebook_plugin', 'siseko_facebook_plugin_page');
}
add_action('admin_menu', 'siseko_admin_sidebar_adder');

/* Doing the posting in the site */
if(function_exists('fb_embedded_post') == false){
	function fb_embedded_post($atts){
		extract(shortcode_atts(array(
			'href' => NULL
		), $atts));

		if($href == NULL || empty($href)){
			return '<div style="color:red;">Post parameter "href" must contain a valid Facebook post URL, please check your post page.</div>';
		} elseif(strpos($href, 'facebook.com') == false){
			return '<div style="color:red;">Post parameter "href" must contain a valid Facebook post URL that, please check your post page.</div>';
		} else {
			if (!is_feed()) {
				return '
					<div id="fb-root"></div>
					<script>
						(function(d, s, id){
							var js, fjs = d.getElementsByTagName(s)[0];
							if (d.getElementById(id)) return;
							js = d.createElement(s); js.id = id;
							js.src = "//connect.facebook.net/'. str_replace('-', '_', get_bloginfo('language')) .'/sdk.js#xfbml=1&version=v2.2";
							fjs.parentNode.insertBefore(js, fjs);
						}(document, "script", "facebook-jssdk"));
					</script>
					<div class="fb-post" data-href="'. $href .'"></div>
				';
			}
		}
	}
}

if(function_exists('add_shortcode') == true && function_exists('shortcode_exists') == true){
	if(shortcode_exists('fb-post') == true){
		if(function_exists('remove_shortcode') == true){
			remove_shortcode('fb-post');
			add_shortcode('fb-post', 'fb_embedded_post');
		}
	} else {
		add_shortcode('fb-post', 'fb_embedded_post');
	}
}

/* Making the post to appear with all posts */
add_action('pre_get_posts', 'djg_includ_my_cpt_in_query', 99);
function djg_includ_my_cpt_in_query($query){

    if(is_home() && $query->is_main_query()) :              // Ensure you only alter your desired query

        $post_types = $query->get('post_type');             // Get the currnet post types in the query

        if(!is_array($post_types) && !empty($post_types))   // Check that the current posts types are stored as an array
            $post_types = explode(',', $post_types);

        if(empty($post_types))                              // If there are no post types defined, be sure to include posts so that they are not ignored
            $post_types[] = 'post';         
        $post_types[] = 'Facebook Post';                         // Add your custom post type

        $post_types = array_map('trim', $post_types);       // Trim every element, just in case
        $post_types = array_filter($post_types);            // Remove any empty elements, just in case

        $query->set('post_type', $post_types);              // Add the updated list of post types to your query

    endif; 

    return $query;

}

/*
function np_init() {
    $args = array(
        'public' => true,
        'label' => 'Siseko Gallery',
        'supports' => array(
            'title',
            'thumbnail'
        )
    );
    register_post_type('np_images', $args);
}
add_action('init', 'np_init');
*/
?>