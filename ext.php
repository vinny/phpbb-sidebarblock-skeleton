<?php
/**
 *
 * @package phpBB Extension - vinny/sidebarblock_skeleton
 * @copyright (c) Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace vinny\sidebarblock_skeleton;

class ext extends \phpbb\extension\base
{
	/**
	 * Checks whether Sidebar Manager is enabled before activation.
	 *
	 * This extension only provides additional block definitions and renderers.
	 * It depends on vinny/sidebar for the ACP interface, block table, sidebar
	 * placement rules, and frontend block rendering.
	 *
	 * @return bool|string True when enableable, otherwise a localized reason
	 */
	public function is_enableable()
	{
		if ($this->container->get('ext.manager')->is_enabled('vinny/sidebar'))
		{
			return true;
		}

		$language = $this->container->get('language');
		$language->add_lang('sidebarblock_skeleton', 'vinny/sidebarblock_skeleton');

		return $language->lang('SIDEBARBLOCK_SKELETON_REQUIRES_SIDEBAR');
	}

	/**
	 * Re-enable dynamic blocks when this extension is enabled again.
	 *
	 * Disabling an extension in phpBB does not remove its data. When the user
	 * enables this extension again, previously registered blocks should become
	 * available in Sidebar Manager without requiring a data purge/reinstall.
	 *
	 * @param mixed $old_state State returned by previous call of this method
	 * @return bool|string Returns false after last step, otherwise temporary state
	 */
	public function enable_step($old_state)
	{
		if ($old_state === false)
		{
			$this->set_sidebar_blocks_enabled(true);
		}

		return parent::enable_step($old_state);
	}

	/**
	 * Hide dynamic blocks while this extension is disabled but its data remains.
	 *
	 * This keeps existing Sidebar Manager rows intact for a regular disable
	 * operation, but prevents blocks owned by this extension from rendering while
	 * their PHP listener and template files are unavailable.
	 *
	 * @param mixed $old_state State returned by previous call of this method
	 * @return bool|string Returns false after last step, otherwise temporary state
	 */
	public function disable_step($old_state)
	{
		if ($old_state === false && $this->container->get('ext.manager')->is_enabled('vinny/sidebar'))
		{
			$this->set_sidebar_blocks_enabled(false);
		}

		return parent::disable_step($old_state);
	}

	/**
	 * Updates the enabled state of blocks registered by this extension.
	 *
	 * Add new block language keys to this list when the skeleton is extended
	 * with additional dynamic blocks.
	 *
	 * @param bool $enabled Block enabled state
	 */
	protected function set_sidebar_blocks_enabled($enabled)
	{
		$db = $this->container->get('dbal.conn');
		$table_prefix = $this->container->getParameter('core.table_prefix');
		$block_names = [
			'SIDEBAR_SKELETON_BIRTHDAYS',
		];

		$sql = 'UPDATE ' . $table_prefix . 'vinny_sidebar_blocks
			SET block_enabled = ' . (int) $enabled . '
			WHERE ' . $db->sql_in_set('block_name', $block_names);
		$db->sql_query($sql);
	}
}
