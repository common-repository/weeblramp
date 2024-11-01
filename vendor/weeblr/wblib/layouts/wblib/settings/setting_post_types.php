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

$details      = $this->getAsArray( 'details' );
$currentValue = $this->getAsArray( 'current_value' );

// force type
$details['content']['attr']['type'] = 'checkbox';

// sanitize incoming data
$options       = empty( $details['content']['options'] ) ? array() : $details['content']['options'];

// optional description
if ( ! empty( $details['desc'] ) ) {
	$details['content']['attr']['aria-describedby'] = $this->getAsId( 'name' ) . '_description';
}

foreach ( $options['post_types'] as $postId => $postDef ) :
	$name = $this->get( 'name' ) . '[' . $postId . '][enabled]';
	$isEnabled = isset( $currentValue[ $postId ] ) ? ! empty( $currentValue[ $postId ]['enabled'] ) : 1;

	// turn into text
	$details['content']['attr']['name'] = $name;
	$details['content']['attr']['id']   = WblSystem_Strings::asHtmlId( $name );
	$attributes                         = WblHtml_Helper::attrToHtml( $details['content']['attr'] );

	?>
    <fieldset class="wblib-settings-post-types<?php echo $this->hasDisplayData('disabled') ? ' wblib-na-this-edition' : ''; ?>">
        <legend class="screen-reader-text"><?php echo esc_html( $postDef->label ); ?></legend>
        <label for="<?php echo esc_attr( $name ); ?>" class="wblib-settings-post-types">
            <input <?php echo $attributes; ?> value="1"<?php checked( $isEnabled, true ); ?> />
			<?php echo esc_html( $postDef->label ); ?>
        </label>

		<?php

		$userSelectableTaxonomies = $this->get( 'user_selectable_taxonomies' );

		// display all taxonomies, after applying the weeblramp_taxonomies_selectable_terms filter
		if ( ! empty( $details['select_taxonomies'] ) && ! empty( $details['content']['options']['taxonomies'][ $postId ] ) ) {
			foreach ( $details['content']['options']['taxonomies'][ $postId ] as $taxoName => $terms ) {
				if ( ! empty( $userSelectableTaxonomies[ $postId ] ) && in_array( $taxoName, $userSelectableTaxonomies[ $postId ] ) ) {
					$taxonomyDefinition = get_taxonomy( $taxoName );
					$listData           = array(
						'name'           => $this->get( 'name' ) . '[' . $postId . ']',
						'post_type'      => $postId,
						'terms'          => $terms,
						'terms_name'     => $taxoName,
						'taxonomy_title' => $taxonomyDefinition->labels->name,
						'attr'           => array(),
						'current_value'  => $currentValue
					);

					echo WblMvcLayout_Helper::render( 'wblib.settings.setting_terms_select', $listData, WBLIB_LAYOUTS_PATH );
				}
			}
		}
		?>
    </fieldset>

<?php endforeach;

echo '<span id="' . WblSystem_Strings::asHtmlId( $this->getAsAttr( 'name' ) ) . '" ' . WblHtml_Helper::attrToHtml( $this->get( 'show-if-attrs' ) ) . '></span>';
echo WblMvcLayout_Helper::render( 'wblib.settings.setting_description', $this->getDisplayData(), WBLIB_LAYOUTS_PATH );
