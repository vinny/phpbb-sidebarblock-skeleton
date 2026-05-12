<?php
/**
 *
 * @package phpBB Extension - vinny/sidebarblock_skeleton
 * @copyright (c) Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	// Extension dependency message displayed by ext.php before activation.
	'SIDEBARBLOCK_SKELETON_REQUIRES_SIDEBAR'	=> 'Sidebar Block Skeleton requires Sidebar Manager to be installed and enabled before activation.',

	// Demonstration block labels. Additional dynamic blocks should follow this key pattern.
	'SIDEBAR_SKELETON_BIRTHDAYS'			=> 'Birthdays',
	'SIDEBAR_SKELETON_BIRTHDAYS_EMPTY'		=> 'No birthdays today.',
]);
