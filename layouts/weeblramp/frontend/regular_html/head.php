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

// Security check to ensure this file is being included by a parent file.
defined( 'WEEBLRAMP_EXEC' ) || die;

if ( is_front_page() && $this->get( 'user_config' )->isFalsy( 'amplify_home' ) ) {
	return;
}

?>
<link rel="amphtml" href="<?php echo $this->getAsUrl( 'amp_url' ); ?>"/>
