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

if ( $showReplyTo && comments_open() ) : ?>
    <div class="wbamp-leave-comment">
        <a href="<?php echo $this->getAsUrl( 'canonical' ) . '#respond'; ?>" class="wbamp-leave-comment">Leave a comment</a>
    </div>
<?php endif;
