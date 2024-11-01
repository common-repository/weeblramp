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
defined('WEEBLRAMP_EXEC') || die('');

$tCards = $this->getInArray('metadata', 'tcards');
if (empty($tCards) || !is_array($tCards))
{
	return;
}

echo "\t<!-- weeblrAMP: Twitter cards meta data -->";
?>

	<meta name="twitter:card" content="<?php echo esc_attr($tCards['card_type']); ?>"/>
<?php if (!empty($tCards['site_account'])) : ?>
	<meta name="twitter:site" content="<?php echo esc_attr($tCards['site_account']); ?>"/>
<?php endif; ?>
<?php if (!empty($tCards['creator'])) : ?>
	<meta name="twitter:creator" content="<?php echo esc_attr($tCards['creator']); ?>"/>
<?php endif; ?>
<?php if (!empty($tCards['title'])) : ?>
	<meta name="twitter:title" content="<?php echo esc_attr($tCards['title']); ?>"/>
<?php endif; ?>
<?php if (!empty($tCards['description'])) : ?>
	<meta name="twitter:description" content="<?php echo esc_attr($tCards['description']); ?>"/>
<?php endif; ?>
<?php if (!empty($tCards['url'])) : ?>
	<meta name="twitter:url" content="<?php echo esc_attr($tCards['url']); ?>"/>
<?php endif; ?>
<?php if (!empty($tCards['image'])) : ?>
	<meta name="twitter:image" content="<?php echo esc_attr($tCards['image']); ?>"/>
<?php endif; ?>
	<!-- weeblrAMP: Twitter Cards meta data -->
