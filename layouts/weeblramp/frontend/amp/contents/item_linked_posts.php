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

if ( $this->hasDisplayData( 'previous_post_link' ) || $this->hasDisplayData( 'next_post_link' ) ):
	?>
    <nav id="secondary" class="wbamp-navigation wbamp-block wbamp-content" role="navigation">
        <ul class="pager pagenav">
			<?php if ( $this->hasDisplayData( 'previous_post_link' ) ): ?>
                <li class="previous">
                    <a href="<?php echo $this->getAsUrl( 'previous_post_link' ); ?>" rel="prev"
                       title="<?php echo esc_attr( __( 'Previous: ', 'weeblramp' ) . $this->get( 'previous_post' )->post_title ); ?>">
						<span class="wbamp-navigation wbamp-link-title">
							<?php
							echo esc_html(
								wbAbridge(
									$this->get( 'previous_post' )->post_title,
									$this->get( 'system_config' )->get( 'sizes.pagination_links_abridged_length' ),
									$this->get( 'system_config' )->get( 'sizes.pagination_links_abridged_intro' )
								)
							);
							?>
						</span>
                    </a>
                </li>
			<?php endif; ?>
			<?php if ( $this->hasDisplayData( 'next_post_link' ) ) : ?>
                <li class="next">
                    <a href="<?php echo $this->getAsUrl( 'next_post_link' ); ?>" rel="next"
                       title="<?php echo esc_attr( __( 'Next: ', 'weeblramp' ) . $this->get( 'next_post' )->post_title ); ?>">
						<span class="wbamp-navigation wbamp-link-title">
							<?php
							echo esc_html(
								wbAbridge(
									$this->get( 'next_post' )->post_title,
									$this->get( 'system_config' )->get( 'sizes.pagination_links_abridged_length' ),
									$this->get( 'system_config' )->get( 'sizes.pagination_links_abridged_intro' )
								)
							);
							?>
						</span>
                    </a>
                </li>
			<?php endif; ?>
        </ul>
    </nav>
	<?php
endif;
