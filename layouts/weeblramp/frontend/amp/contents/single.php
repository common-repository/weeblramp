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
defined('WEEBLRAMP_EXEC') || die;

if (!$this->hasDisplaydata('main_content'))
{
	return;
}

global $wp_query;

$requestType = $this->get('request_type');

?>
<div class="wbamp-content wbamp-block  wbl-no-border">
	<?php

	$contentData = $this->getAsArray('main_content');
	foreach ($contentData as $contentItem)
	{
		// export to globals current post, for pagination
		$wp_query->setup_postdata($contentItem['post']);
		WblWordpress_Html::exportToGlobals($contentItem);

		// let WP process comments, so that they are ready to display
		WblWordpress_Html::processComments();

		// find out an existing template
		$layoutName = WblMvcLayout_Helper::getExistingLayout(
			array(
				// allow custom post templates, per post name
				'weeblramp.frontend.amp.contents.single-' . $contentItem['post']->post_name,
				// allow custom post templates, per post format
				'weeblramp.frontend.amp.contents.single-' . $contentItem['post']->post_type,
				// fallback
				'weeblramp.frontend.amp.contents.' . $requestType . '_item'
			),
			WEEBLRAMP_LAYOUTS_PATH
		);

		// display the post
		echo WblMvcLayout_Helper::render(
			$layoutName,
			array_merge(
				$this->getDisplayData(),
				// those are shortcuts, to make data usage easier in sub layout
				array(
					'post' => $contentItem['post'],
					'request_type' => $requestType,
					'content' => $contentItem['content'],
					'featured_image' => $contentItem['featured_image'],
					'excerpt' => $contentItem['excerpt'],
					'comment_type' => $contentItem['comment_type'],
					'comment_status' => $contentItem['comment_status'],
					'comment_count' => $contentItem['comment_count'],
					'comment_location_id' => $contentItem['comment_location_id'],
					'comment_location_url' => $contentItem['comment_location_url'],
					'comments' => $contentItem['comments'],
				)
			),
			WEEBLRAMP_LAYOUTS_PATH
		);

		WblWordpress_Html::resetGlobals();
	}

	?>

</div>

