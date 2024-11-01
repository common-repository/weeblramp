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

// read and apply user configuration for display
$userConfig      = $this->get( 'user_config' );
$customizeConfig = $this->get( 'customize_config' );
$displayConfig   = $customizeConfig->get( 'item_display_options' );

/**
 * Filter whether to use "times ago" style of displaying dates for items info block.
 *
 * @api
 * @package weeblrAMP\filter\output
 * @var weeblramp_item_header_author_use_moments
 * @since   1.0.0
 *
 * @param bool $useMoments If true, approximate moments are used for dates
 *
 * @return bool
 */
$useMoments = apply_filters( 'weeblramp_item_header_author_use_moments', wbArrayGet( $displayConfig, 'item_header_author_use_moments', true ) );

/**
 * Filter whether to show author avatars on item info blocks.
 *
 * @api
 * @package weeblrAMP\filter\output
 * @var weeblramp_item_header_author_show_avatar
 * @since   1.0.0
 *
 * @param bool $useAvatar If true, author avatar will be displayed
 *
 * @return bool
 */
$useAvatar = apply_filters( 'weeblramp_item_header_author_show_avatar', wbArrayGet( $displayConfig, 'item_header_author_show_avatar', true ) );

if ( $this->hasDisplayData( 'author' ) ):

	if ( $useAvatar ) {
		/**
		 * Filter the size of the author avatar displayed in an item info block.
		 *
		 * @api
		 * @package weeblrAMP\filter\output
		 * @var weeblramp_item_header_author_avatar_size
		 * @since   1.0.0
		 *
		 * @param int $avatarSize In pixels, size of default author avatar
		 *
		 * @return int
		 */
		$avatarSize = apply_filters(
			'weeblramp_item_header_author_avatar_size',
			$this->get( 'system_config' )->get( 'sizes.item_header_author_avatar_default_size' )
		);
		$avatar     = get_avatar( $this->get( 'author' )->user_email, $avatarSize );
	}

	// dates
	$date = $this->get( 'date_published' );
	if ( $useMoments ) {
		$formattedDate = WblSystem_Date::getAsMoments( $date );
	} else {
		$formattedDate = $date->format( get_option( 'date_format' ) );
	}

	?>
    <div class="wbamp-info-block">
        <p>
			<span class="wbamp-created-by" itemprop="author" itemscope itemtype="http://schema.org/Person">
				<?php if ( $useAvatar ): ?>
                    <span class="wbamp-author-image" itemprop="image"><?php echo $avatar; ?></span>
				<?php endif; ?>
				<?php echo esc_html( __( 'Written by ', 'weeblramp' ) ); ?>
                <span class="wbamp-author"
                      itemprop="name"><?php echo esc_html( $this->get( 'author' )->display_name ); ?></span>
			</span>
			<?php echo $useMoments ? ' ' : esc_html( __( ' on ', 'weeblramp' ) ); ?>
            <time itemprop="datePublished"
                  content="<?php echo esc_html( WblSystem_Date::toAtom( $date ) ); ?>"><?php echo esc_html( $formattedDate ); ?></time>
        </p>
    </div>
	<?php
endif;
