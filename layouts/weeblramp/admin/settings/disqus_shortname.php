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

$details = $this->getAsArray( 'details' );

// set some defaults if missing
wbArrayKeyInit( $details['content']['attr'], 'name', $__data['name'] );
wbArrayKeyInit( $details['content']['attr'], 'id', $this->getAsId( 'name' ) );

// set current value
$currentValue = $this->get( 'current_value' );
wbArrayKeyInit( $details['content']['attr'], 'value', $currentValue );

// which message to display?
$userConfig = WeeblrampFactory::getThe( 'weeblramp.config.user' );
$connected  = WeeblrampConfig_User::DISQUS_CONNECT_CONNECTED == $userConfig->get( 'disqus_connect_state' );
if ( $connected ) {
	$buttonTitle                            = __( 'Stop using Disqus AMP file from WeeblrPress', 'weeblramp' );
	$details['content']['attr']['disabled'] = 'disabled';
} else {
	$buttonTitle = __( 'Use Disqus AMP file from WeeblrPress', 'weeblramp' );
}

// optional description
if ( ! empty( $details['desc'] ) ) {
	$details['content']['attr']['aria-describedby'] = $this->getAsId( 'name' ) . '_description';
}

// turn all attributes into text
$attributes = WblHtml_Helper::attrToHtml( $details['content']['attr'] );

?>
<fieldset class="wblib-media-setting-wrapper">
    <legend class="screen-reader-text"><?php echo esc_html( $details['title'] ); ?></legend>
    <label for="<?php echo $this->getAsAttr( 'name' ); ?>">
        <input <?php echo WblHtml_Helper::attrToHtml( $this->get( 'show-if-attrs' ) ); ?> <?php echo $attributes; ?>
                type="text">
        <button type="button"
                class="js-wbamp-disqus-connect-button wbamp-disqus-connect-button button"
                id="<?php echo $this->getAsId( 'name' ) . '-button'; ?>"
                title="<?php echo esc_attr( $buttonTitle ); ?>"
        >
			<?php echo $buttonTitle; ?>
        </button>
        <button type="button"
                class="js-wbamp-disqus-download-button wbamp-disqus-download-button button"
                id="<?php echo $this->getAsId( 'name' ) . '-download-button'; ?>"
                title="<?php echo esc_attr( __( 'Download Disqus file', 'weeblramp' ) ); ?>"
        >
			<?php echo __( 'Download Disqus file', 'weeblramp' ); ?>
        </button>
        <div class="wbamp-disqus-connect-msg wbamp-ajax-response-msg">
            <div id="js-wbamp-disqus-connect-msg"></div>
            <div id="js-wbamp-disqus-connect-spinner"></div>
        </div>
    </label>
</fieldset>
<?php echo WblMvcLayout_Helper::render( 'wblib.settings.setting_description', $this->getDisplayData(), WBLIB_LAYOUTS_PATH ); ?>


