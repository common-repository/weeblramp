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

class WeeblrampHelper_Admin {

	/**
	 * Add a link to the settings page from the plugin page
	 *
	 * @param $links
	 *
	 * @return array
	 */
	public static function filter_plugins_action_links( $links ) {

		$addedLinks = array(
			'<a href="' . admin_url( 'admin.php?page=' . WeeblrampViewAdmin_Options::SETTINGS_PAGE ) . '">' . __( 'Settings' ) . '</a>',
			'<a href="' . admin_url( 'admin.php?page=' . WeeblrampViewAdmin_Customize::SETTINGS_PAGE ) . '">' . __( 'Customize' ) . '</a>',
		);

		return array_merge( $addedLinks, $links );
	}

	/**
	 * Add minimal javascript to the settings page
	 */
	public static function admin_action_scripts() {

		// built in required javascript
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-accordion' );

		// media manager
		wp_enqueue_media();

		// custom weeblrAMP scripts
		$htmlManager = WeeblrampFactory::getThe( 'weeblramp.html_manager' );
		wp_enqueue_script(
			'wblib-strings',
			$htmlManager->getMediaLink(
				'strings',
				'js',
				array(
					'files_path'      => array( WBLIB_ASSETS_PATH => '' ),
					'assets_bundling' => false
				)
			)
		);
		wp_enqueue_script(
			'wblib-spinner',
			$htmlManager->getMediaLink(
				'spinner',
				'js',
				array(
					'files_path'      => array( WBLIB_ASSETS_PATH => '' ),
					'assets_bundling' => false
				)
			)
		);
		wp_enqueue_script(
			'wblib-admin',
			$htmlManager->getMediaLink(
				'wblib_admin',
				'js',
				array(
					'files_path'      => array( WBLIB_ASSETS_PATH => '' ),
					'assets_bundling' => false
				)
			)
		);
		wp_enqueue_script(
			'weeblramp-admin',
			$htmlManager->getMediaLink(
				'weeblramp_admin',
				'js',
				array(
					'files_path'      => array( 'assets/admin' => '' ),
					'assets_bundling' => false
				)
			)
		);

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

		// and styles
		wp_enqueue_style(
			'wblib-admin',
			$htmlManager->getMediaLink(
				'wblib_admin',
				'css',
				array(
					'files_path'      => array( WBLIB_ASSETS_PATH => '' ),
					'assets_bundling' => false
				)
			)
		);
		wp_enqueue_style(
			'wblib-spinner',
			$htmlManager->getMediaLink(
				'spinner',
				'css',
				array(
					'files_path'      => array( WBLIB_ASSETS_PATH => '' ),
					'assets_bundling' => false
				)
			)
		);
		wp_enqueue_style(
			'weeblramp-admin',
			$htmlManager->getMediaLink(
				'weeblramp_admin',
				'css',
				array(
					'files_path'      => array( 'assets/admin' => '' ),
					'assets_bundling' => false
				)
			)
		);
	}

	/**
	 * Adds the root menu (admin side) to which
	 * our settings page will be attached
	 */
	public static function addAdminMenu() {

		static $added = false;

		if ( ! $added ) {
			$svg     = WeeblrampFactory::getThe( 'weeblramp.config.system' )->get( 'assets.amp_logo' );
			$svg     = str_replace( '<path ', '<path fill="#FFFFFF" ', $svg );
			$iconUrl = 'data:image/svg+xml;base64,' . base64_encode( $svg );
			add_menu_page(
				__( 'weeblrAMP', 'weeblramp' ),
				__( 'weeblrAMP', 'weeblramp' ),
				'manage_options',
				WeeblrampViewAdmin_Options::ROOT_MENU_PAGE,
				array( WeeblrampFactory::getThe( 'WeeblrampViewAdmin_Options' ), 'render' ),
				$iconUrl
			);
			$added = true;
		}
	}

	/**
	 * Add a meta box to all supported post types.
	 * Allows forcing on/off AMPlification.
	 *
	 * @param WP_Post $post
	 */
	public static function addMetaBox( $post ) {

		$postType = get_post_type(
			$post
		);

		add_meta_box(
			'weeblramp_meta_box',
			__( 'AMP options', 'weeblramp' ),
			function ( $post ) use ( $postType ) {

				wp_nonce_field(
					WEEBLRAMP_PLUGIN,
					'weeblramp_meta_box_nonce'
				);

				// must get this from user config, based on post ID and user AMP rules
				$enabled = get_post_meta(
					$post->ID,
					'_wbamp_enable_amp',
					true
				);

				?>
                <div class='inside'>
                    <h3><?php _e( 'Enable AMP', 'weeblramp' ); ?></h3>
                    <p>
                        <select name="_wbamp_enable_amp" id="_wbamp_enable_amp">
                            <option value="default"><?php echo __( 'As configured in weeblrAMP', 'weeblramp' ); ?></option>
                            <option value="1" <?php selected( $enabled, '1' ); ?>><?php echo __( 'Enable AMP', 'weeblramp' ); ?></option>
                            <option value="0" <?php selected( $enabled, '0' ); ?>><?php echo __( 'Disable AMP', 'weeblramp' ); ?></option>
                        </select>
                    </p>
                </div>
				<?php

			},
			$postType,
			'side',
			'low'
		);
	}

	/**
	 * Saves user data coming from meta box on supported post types.
	 *
	 * @param int $postId
	 */
	public static function saveMetaBox( $postId ) {

		if (
			! isset( $_POST['weeblramp_meta_box_nonce'] )
			||
			! wp_verify_nonce( $_POST['weeblramp_meta_box_nonce'], WEEBLRAMP_PLUGIN )
		) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return;
		}

		if ( isset( $_REQUEST['_wbamp_enable_amp'] ) ) {
			$value = in_array(
				$_REQUEST['_wbamp_enable_amp'],
				array(
					'0',
					'1'
				)
			)
				?
				$_REQUEST['_wbamp_enable_amp']
				:
				'';

			update_post_meta(
				$postId,
				'_wbamp_enable_amp',
				$value
			);
		}
	}
}
