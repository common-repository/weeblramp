<?php
/**
 * @ant_title_ant@
 *
 * @author       @ant_author_ant@
 * @copyright    @ant_copyright_ant@
 * @package      @ant_package_ant@
 * @license      @ant_license_ant@
 * @version      @ant_version_ant@
 * @date        @ant_current_date_ant@
 */

// Security check to ensure this file is being included by a parent file.
defined('WEEBLRAMP_EXEC') || die;

$ogpData = $this->getInArray('metadata', 'ogp');
if (empty($ogpData) || !is_array($ogpData))
{
	return;
}

echo "\t<!-- weeblrAMP: Open Graph meta data -->";
?>

    <meta property="og:locale" content="<?php echo str_replace('-', '_', get_bloginfo('language')); ?>"/>
<?php if (!empty($ogpData['title'])) : ?>
    <meta property="og:title"
          content="<?php echo esc_attr($ogpData['title']); ?>"/>
<?php endif; ?>
<?php if (!empty($ogpData['description'])) : ?>
    <meta property="og:description"
          content="<?php echo esc_attr($ogpData['description']) ?>"/>
<?php endif; ?>
<?php if (!empty($ogpData['type'])) : ?>
    <meta property="og:type" content="<?php echo esc_attr($ogpData['type']); ?>"/>
<?php endif; ?>
    <meta property="og:url" content="<?php echo esc_attr($ogpData['url']); ?>"/>
<?php if (!empty($ogpData['image'])) : ?>
    <meta property="og:image" content="<?php echo esc_attr($ogpData['image']); ?>"/>
<?php endif; ?>
<?php if (!empty($ogpData['image_width'])) : ?>
    <meta property="og:image:width" content="<?php echo esc_attr($ogpData['image_width']); ?>"/>
<?php endif; ?>
<?php if (!empty($ogpData['image_height'])) : ?>
    <meta property="og:image:height" content="<?php echo esc_attr($ogpData['image_height']); ?>"/>
<?php endif; ?>
<?php if (!empty($ogpData['image_secure_url'])) : ?>
    <meta property="og:image:secure_url" content="<?php echo esc_attr($ogpData['image_secure_url']); ?>"/>
<?php endif; ?>
<?php if (!empty($ogpData['site_name'])) : ?>
    <meta property="og:site_name" content="<?php echo esc_attr($ogpData['site_name']); ?>"/>
<?php endif; ?>
<?php if (!empty($ogpData['facebook_app_id'])) : ?>
    <meta property="fb:app_id" content="<?php echo esc_attr($ogpData['facebook_app_id']); ?>"/>
<?php endif;
echo "\t<!-- weeblrAMP: Open Graph meta data -->";
