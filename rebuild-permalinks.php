<?php
/*
Plugin Name: Rebuild Permalinks
Plugin URI: http://gerrytucker.co.uk/wp-plugins/rebuild-permalinks.zip
Description: Rebuild Permalinks
Author: Gerry Tucker
Author URI: http://gerrytucker.co.uk/
Version: 0.9
License: GPLv2 or later
*/

function rebuild_permalinks_admin_actions() {

	add_submenu_page(
		'options-general.php',
		__('Rebuild Permalinks'),
		__('Rebuild Permalinks'),
		'manage_options',
		'rebuild-permalinks',
		'rebuild_permalinks_admin'
	);
	
}

add_action('admin_menu', 'rebuild_permalinks_admin_actions');

function rebuild_permalinks_admin() {

	$message = '';

	if ( isset( $_POST['submit'] ) ) {
		$count = rebuild_permalinks( $_POST['posttype'] );
		$message = $count . ' permalinks were rebuilt for all posts of type: <strong>' . $_POST['posttype'] . '</strong>';
	}
?>

	<div class="wrap" id="rebuild-permalinks-settings">
		<?php screen_icon(); ?>
		<h2><?php _e('Rebuild Permalinks'); ?></h2>

<?php if ( $message !== '' ) : ?>
		
		<div id="message" class="updated fade">
			<p>
				<?php _e($message); ?>
			</p>
		</div>

<?php endif; ?>
		
		<form action="" method="post" id="rebuild_permalinks_form">
			
			<table class="form-table">
				
				<tr>
					<th scope="row">
						<label for="posttype">Select Post Type</label>
					</th>
					<td>
						<select name="posttype">
<?php
	$posttypes = get_post_types( array( 'public' => true ), 'names' );
	foreach( $posttypes as $posttype ) :
?>
							<option value="<?php echo $posttype; ?>"><?php echo ucfirst( strtolower( $posttype ) ); ?></option>
<?php
	endforeach;
?>
						</select>
					</td>
				</tr>
				
			</table>

			<p>Make sure you have a backup of your WordPress database before rebuilding permalinks!</p>
			<p>
				<input type="submit" name="submit" class="button-primary" style="width: 300px;" value="<?php _e('Rebuild Selected Permalinks'); ?>"
							 onclick='if (!window.confirm("<?php _e('Are you sure you want to do this?'); ?>")) return false;'>
			</p>

		</form>
		
	</div>

<?php
}

function rebuild_permalinks( $posttype = 'post' ) {
	
	global $wpdb;
	
	$rows = $wpdb->get_results(
		"SELECT id, post_title
		FROM $wpdb->posts
		WHERE post_status = 'publish'
		AND posttype = '$posttype'"
	);
	
	$count = 0;
	
	foreach( $rows as $row ) {
		
		$post_title = _clear_diacritics( $row->post_title );
		$post_name = sanitize_title_with_dashes( $post_title );
		$guid = home_url() . '/' . sanitize_title_with_dashes( $post_title );
		$wpdb->query(
			"UPDATE $wpdb->posts
			SET post_name = '" . $post_name . "',
			guid = '" . $guid . "'
			WHERE ID = $row->id"
		);
		$count++;
	}
	
	return $count;
}

function _clear_diacritics( $post_title ) {
	
	$diacritics = array(
		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 
		'Æ' => 'A', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
		'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c', 'Ç' => 'C', 'ç' => 'c',
		'Ď' => 'D', 'ď' => 'd',
		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ě' => 'E', 'è' => 'e', 
		'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ě' => 'e',
		'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
		'Ñ' => 'N', 'ñ' => 'n',
		'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 
		'ð' => 'o', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
		'Ŕ' => 'R', 'Ř' => 'R', 'Ŕ' => 'R', 'ŕ' => 'r', 'ř' => 'r',
		'Š' => 'S', 'š' => 's', 'Ś' => 'S', 'ś' => 's',
		'Ť' => 'T', 'ť' => 't',
		'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'ù' => 'u', 'ú' => 'u', 
		'û' => 'u', 'ü' => 'u',
		'Ý' => 'Y', 'ÿ' => 'y', 'ý' => 'y', 'ý' => 'y',
		'Ž' => 'Z', 'ž' => 'z', 'Ź' => 'Z', 'ź' => 'z',
		'Đ' => 'Dj', 'đ' => 'dj', 'Þ' => 'B', 'ß' => 's', 'þ' => 'b',
	);

	return strtr($post_title, $diacritics);
}

