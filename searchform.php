<?php
/**
 * Search form.
 *
 * @package MENJ\Bio
 */

?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="s"><?php esc_html_e( 'Search for:', 'menj-bio' ); ?></label>
	<input type="search" id="s" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'menj-bio' ); ?>" />
	<button type="submit" class="search-submit"><?php esc_html_e( 'Search', 'menj-bio' ); ?></button>
</form>
