<?php
/**
 * Search form.
 *
 * @package Kolofon
 */

?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" itemprop="potentialAction" itemscope="itemscope" itemtype="https://schema.org/SearchAction">
	<meta itemprop="target" content="<?php echo esc_url( home_url( '/?s={s}' ) ); ?>" />
	<label class="screen-reader-text" for="s"><?php esc_html_e( 'Search for:', 'kolofon' ); ?></label>
	<input type="search" id="s" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'kolofon' ); ?>" itemprop="query-input" />
	<button type="submit" class="search-submit"><?php esc_html_e( 'Search', 'kolofon' ); ?></button>
</form>
