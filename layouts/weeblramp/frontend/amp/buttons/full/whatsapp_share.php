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

$title = esc_attr( sprintf( __( 'Share %1$s on %2$s', 'weeblramp' ), $this->get( 'share_title' ), 'WhatsApp' ) );
$href  = esc_attr( 'whatsapp://send?text=' . urlencode( sprintf( __( 'Read %1$s at ', 'weeblramp' ), $this->get( 'share_title' ) ) . $this->get( 'share_url' ) ) );
?>

<li class="wbamp-social-button-whatsapp-share">
    <a class="wbamp-social-buttons"
       id="wbamp-button_whatsapp_share_1"
       href="<?php echo $href; ?>"
       title="<?php echo $title; ?>">
<span class="wbamp-social-icon wbamp-whatsapp">
<svg width="22" height="22" viewBox="0 0 28 28">
<path
        d="M13.693,1C6.991,1,1.557,6.433,1.557,13.138c0,2.292,0.637,4.436,1.742,6.267l-2.189,6.512l6.72-2.152  c1.737,0.961,3.735,1.508,5.864,1.508c6.701,0,12.132-5.429,12.132-12.134C25.825,6.436,20.395,1,13.693,1z M13.693,23.238  c-2.053,0-3.964-0.616-5.557-1.671l-3.883,1.245l1.263-3.751c-1.212-1.668-1.925-3.714-1.925-5.924  c0-5.57,4.531-10.103,10.103-10.103c5.572,0,10.104,4.53,10.104,10.103C23.799,18.711,19.263,23.238,13.693,23.238z  M19.384,15.894c-0.308-0.166-1.801-0.972-2.08-1.087c-0.282-0.111-0.486-0.172-0.703,0.129c-0.218,0.301-0.839,0.979-1.028,1.181  c-0.187,0.198-0.369,0.219-0.673,0.05c-0.303-0.169-1.29-0.531-2.435-1.632c-0.89-0.854-1.473-1.892-1.642-2.21  c-0.167-0.317,0-0.478,0.162-0.625c0.144-0.134,0.321-0.352,0.484-0.526c0.161-0.178,0.22-0.302,0.329-0.505  c0.111-0.204,0.069-0.385-0.003-0.545c-0.076-0.155-0.638-1.704-0.872-2.333c-0.235-0.63-0.498-0.537-0.678-0.543  S9.859,7.205,9.652,7.2C9.447,7.191,9.109,7.253,8.814,7.554C8.52,7.855,7.688,8.571,7.633,10.096  c-0.057,1.524,0.996,3.042,1.145,3.252c0.148,0.213,2.012,3.514,5.115,4.876c3.101,1.359,3.118,0.941,3.688,0.914  c0.568-0.031,1.862-0.681,2.145-1.394c0.287-0.714,0.311-1.333,0.238-1.466C19.891,16.146,19.688,16.063,19.384,15.894z"/>
</svg>
</span>
    </a>
</li>
