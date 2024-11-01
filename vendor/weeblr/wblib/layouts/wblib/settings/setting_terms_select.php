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

use Weeblr\Wblib\Joomla\StringHelper\StringHelper;

// Security check to ensure this file is being included by a parent file.
defined( 'WBLIB_ROOT_PATH' ) || die;

// turn into text
$attributes     = $this->getAsArray( 'attr' );
$attributesHtml = WblHtml_Helper::attrToHtml(
	array_merge(
		$this->get( 'attr' ),
		// force input type
		array(
			'type' => 'checkbox'
		)
	)
);

// handy variables
$class = empty( $attributes['class'] ) ? 'wblib-settings-categories' : 'wblib-settings-categories ' . $attributes['class'];
// level-dependent class
$class      .= ' wblib-settings-level-';
$postType   = $this->get( 'post_type' );
$termHelper = WblFactory::getThe( 'WeeblrampHelper_Terms' );
?>
<div class="weeblramp-settings wblib-settings-accordion">
    <h3>
        <button
                class="button"><?php echo esc_html( sprintf( __( 'Select %s...', 'weeblramp' ), StringHelper::strtolower( $this->get( 'taxonomy_title' ) ) ) ); ?></button>
    </h3>
    <div>
		<?php

		foreach ( $this->getAsArray( 'terms' ) as $termDef ):
			$perTermName = $this->get( 'terms_name' );
			$name = $this->get( 'name' ) . '[per_taxonomy][' . $perTermName . '][' . $termDef->term_id . ']';
			$isEnabled = isset( $__data['current_value'][ $postType ] )
			             && isset( $__data['current_value'][ $postType ]['per_taxonomy'] )
			             && isset( $__data['current_value'][ $postType ]['per_taxonomy'][ $perTermName ] )
			             && isset( $__data['current_value'][ $postType ]['per_taxonomy'][ $perTermName ][ $termDef->term_id ] ) ?
				! empty( $__data['current_value'][ $postType ]['per_taxonomy'][ $perTermName ][ $termDef->term_id ] )
				: 1;
			?>
            <fieldset
                    class="<?php echo esc_attr( $class . $termHelper->getTermLevel( $termDef->term_id ) ); ?>">
                <legend class="screen-reader-text"><?php echo esc_html( $termDef->name ); ?></legend>
                <label for="<?php echo esc_attr( $name ); ?>">
                    <input <?php echo $attributesHtml; ?> name="<?php echo esc_attr( $name ); ?>"
                                                          value="1"<?php checked( $isEnabled, 1 ); ?> />
					<?php echo esc_html( $termDef->name ); ?>
                </label>
            </fieldset>

			<?php
		endforeach;

		?>
    </div>
</div>
