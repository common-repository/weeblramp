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
defined( 'WBLIB_ROOT_PATH' ) || die;

$docLink        = '';
$docLinkUrl     = '';
$docEmbed       = false;
$embedUrl       = '';
$analyticsQuery = 'utm_source=link&utm_medium=plugin&utm_campaign=weeblramp_doc';
if ( $this->hasDisplayData( 'details', 'doc_link' ) ) {
	$docLink  = $this->getInArray( 'details', 'doc_link' );
	$docEmbed = $this->getInArray( 'details', 'doc_embed' );
	if ( WblSystem_Route::isFullyQUalified( $docLink ) ) {
		$docLinkUrl = $docLink;
		$docLinkUrl = WblSystem_Route::appendQuery( $docLinkUrl, $analyticsQuery );
	} else {
		$docLinkUrl = WeeblrampFactory::getThe( 'weeblramp.config.system' )->get( 'urls.documentation_server' ) . '/documentation/' . $docLink;
		$embedUrl   = WeeblrampFactory::getThe( 'weeblramp.config.system' )->get( 'urls.documentation_embed_url' ) . $docLink;
		$embedUrl   = WblSystem_Route::appendQuery( $embedUrl, $analyticsQuery );
	}
	if ( empty( $docLinkUrl ) || ! wbStartsWith( $docLinkUrl, WeeblrampFactory::getThe( 'weeblramp.config.system' )->get( 'urls.documentation_server' ) ) ) {
		$docEmbed = false;
	}
}

if ( ! empty( $docLinkUrl ) && ! $docEmbed ) : ?>
    <p class="wblib-settings-doc-link" id="<?php echo $this->getAsId( 'name' ); ?>_doc-link">
        <a href="<?php echo esc_url( $docLinkUrl ); ?>"
           class="wblib-settings-doc-link"
           title="<?php __( 'View corresponding documentation page.', 'weeblramp' ); ?>"
           target="_blank"><?php echo __( 'Documentation', 'weeblramp' ); ?></a>
    </p>
<?php endif;

if ( ! empty( $docLinkUrl ) && $docEmbed ) : ?>
	<?php
	$instanceId = md5( $this->get( 'name' ) );
	// do we have a hash?
	$docLinkHash = '';
	if ( wbContains( $docLink, '#' ) ) {
		$bits        = explode( '#', $docLink, 2 );
		$docLinkHash = empty( $bits[1] ) ? '' : $bits[1];
		$embedUrl    = wbRTrim( $embedUrl, '#' . $docLinkHash );
	}

	$buttonTitle = $this->hasDisplayData( 'details', 'doc_link_button' ) ? $this->getInArray( 'details', 'doc_link_button' ) : __( 'help', 'weeblramp' );
	?>
    <div class="wblib-settings-doc-link wblib-settings-doc-embed" id="<?php echo $this->getAsId( 'name' ); ?>_doc-link">
        <button class="wblib-settings-doc-link js-wblib-settings-doc-embed" id="wblib-doc-btn-<?php echo $instanceId; ?>"
                href="<?php echo esc_url( $docLinkUrl ); ?>"
                data-id="<?php echo esc_attr( $docLink ); ?>"
                data-embed_url="<?php echo esc_attr( $embedUrl ); ?>"
                data-embed_url_hash="<?php echo esc_attr( $docLinkHash ); ?>"
                data-hash="<?php echo esc_attr( $instanceId ); ?>"
                data-state="closed">
			<?php echo esc_html( $buttonTitle ); ?>
        </button>
        <span class="wblib-settings-doc-spinner" id="wblib-doc-spinner-<?php echo $instanceId; ?>"></span>
        <div id="js-wbamp-settings-doc-msg-<?php echo $instanceId; ?>" class="wbamp-ajax-response-msg wbamp-ajax-response-msg-failure"></div>
        <div class="wblib-settings-doc-frame-container" id="wblib-doc-frame-container-<?php echo $instanceId; ?>"></div>
    </div>
<?php endif;

