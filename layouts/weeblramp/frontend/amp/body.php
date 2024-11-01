<?php
/**
 * weeblrAMP - Accelerated Mobile Pages for Wordpress
 *
 * @author                  weeblrPress
 * @copyright               (c) WeeblrPress - Weeblr,llc - 2020
 * @package                 AMP on WordPress - weeblrAMP CE
 * @license                 http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version                 1.12.5.783
 *
 * 2020-05-19
 */

// no direct access
defined( 'WEEBLRAMP_EXEC' ) || die;

?>
<div class="wbamp-wrapper wbamp-wrapper-header">
	<?php

	// Header: link to home and/or logo
	echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.header', $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );

	?>
</div>

<div class="wbamp-wrapper wbamp-wrapper-content wbamp-h-padded">
	<?php

	// Main content
	echo WblMvcLayout_Helper::render( 'weeblramp.frontend.amp.contents.' . $this->get( 'request_type' ), $this->getDisplayData(), WEEBLRAMP_LAYOUTS_PATH );

	?>
</div>
