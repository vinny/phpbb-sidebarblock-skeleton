<?php
/**
 *
 * @package phpBB Extension - vinny/sidebarblock_skeleton
 * @copyright (c) Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace vinny\sidebarblock_skeleton\migrations;

class v100_initial extends \phpbb\db\migration\migration
{
	/**
	 * Checks whether this migration has effectively been installed.
	 *
	 * The parent extension owns the block table. This check first confirms that
	 * the table exists, then verifies that this extension's demonstration block
	 * has already been registered. This prevents phpBB from treating the
	 * skeleton as installed when Sidebar Manager is missing.
	 *
	 * @return bool
	 */
	public function effectively_installed()
	{
		$blocks_table = $this->table_prefix . 'vinny_sidebar_blocks';

		if (!$this->db_tools->sql_table_exists($blocks_table))
		{
			return false;
		}

		$sql = 'SELECT block_id
			FROM ' . $blocks_table . "
			WHERE block_name = 'SIDEBAR_SKELETON_BIRTHDAYS'";
		$result = $this->db->sql_query_limit($sql, 1);
		$block_id = (int) $this->db->sql_fetchfield('block_id');
		$this->db->sql_freeresult($result);

		return $block_id > 0;
	}

	/**
	 * Defines dependencies for this migration.
	 *
	 * @return array
	 */
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v330\v330',
			'\vinny\sidebar\migrations\v100_initial',
		];
	}

	/**
	 * Updates the database data.
	 *
	 * @return array
	 */
	public function update_data()
	{
		return [
			['custom', [[$this, 'insert_sidebar_blocks']]],
		];
	}

	/**
	 * Reverts the database data.
	 *
	 * @return array
	 */
	public function revert_data()
	{
		return [
			['custom', [[$this, 'remove_sidebar_blocks']]],
		];
	}

	/**
	 * Insert system blocks handled by this extension into Sidebar Manager.
	 *
	 * The migration avoids duplicate inserts so it can run cleanly on boards
	 * where rows already exist because of a previous partial installation or
	 * manual recovery. Keep this behavior when adapting the skeleton.
	 */
	public function insert_sidebar_blocks()
	{
		$blocks_table = $this->table_prefix . 'vinny_sidebar_blocks';
		$blocks = $this->get_sidebar_blocks();
		$block_names = array_keys($blocks);
		$existing_blocks = [];

		$sql = 'SELECT block_name
			FROM ' . $blocks_table . '
			WHERE ' . $this->db->sql_in_set('block_name', $block_names);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$existing_blocks[] = $row['block_name'];
		}
		$this->db->sql_freeresult($result);

		$this->db->sql_transaction('begin');
		foreach ($blocks as $block_name => $block_data)
		{
			if (in_array($block_name, $existing_blocks, true))
			{
				continue;
			}

			$sql = 'INSERT INTO ' . $blocks_table . ' ' . $this->db->sql_build_array('INSERT', $block_data);
			$this->db->sql_query($sql);
		}
		$this->db->sql_transaction('commit');
	}

	/**
	 * Remove system blocks handled by this extension from Sidebar Manager.
	 *
	 * This runs when the administrator disables the extension and deletes data.
	 * A normal disable keeps the rows and only marks them disabled in ext.php.
	 */
	public function remove_sidebar_blocks()
	{
		$blocks_table = $this->table_prefix . 'vinny_sidebar_blocks';
		$block_names = array_keys($this->get_sidebar_blocks());

		$sql = 'DELETE FROM ' . $blocks_table . '
			WHERE block_is_system = 1
				AND ' . $this->db->sql_in_set('block_name', $block_names);
		$this->db->sql_query($sql);
	}

	/**
	 * Returns block rows registered by this skeleton extension.
	 *
	 * To add a new dynamic block, add another array entry here, create its
	 * language key, and handle the same key in event/listener.php.
	 *
	 * @return array
	 */
	protected function get_sidebar_blocks()
	{
		return [
			'SIDEBAR_SKELETON_BIRTHDAYS' => [
				'block_name'		=> 'SIDEBAR_SKELETON_BIRTHDAYS',
				'block_content'		=> '',
				'sidebar_side'		=> 'left',
				'block_order'		=> 150,
				'block_enabled'		=> 1,
				'block_is_system'	=> 1,
			],
		];
	}
}
