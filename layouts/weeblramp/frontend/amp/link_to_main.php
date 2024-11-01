<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author       weeblrPress
 * @copyright    (c) WeeblrPress - Weeblr,llc - 2020
 * @package      AMP on WordPress - weeblrAMP CE
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version      1.12.5.783
 *
 * 2020-05-19
 */

// no direct access
defined( 'WEEBLRAMP_EXEC' ) || die;

// no text, no show
if ( ! $this->hasDisplayData( 'link_to_main_site', 'text' ) ) {
	return '';
}

?>
<div
        class="wbamp-link-to-main wbamp-link-to-main-<?php echo $this->getInArray( 'link_to_main_site', 'theme' ); ?>">
    <span class="wbamp-link-to-main-text"><?php echo $this->getInArray( 'link_to_main_site', 'text' ); ?></span>
	<?php
	if ( $this->hasDisplayData( 'link_to_main_site', 'link' ) ) {
		?>
        <span class="wbamp-link-to-main-link">
			<a href="<?php echo esc_url( $this->getInArray( 'link_to_main_site', 'link_url' ) ); ?>"><?php echo esc_html( $this->getInArray( 'link_to_main_site', 'link' ) ); ?></a>
			</span>
		<?php
	}
	?>
</div>
