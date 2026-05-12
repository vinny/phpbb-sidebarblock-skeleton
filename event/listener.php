<?php
/**
 *
 * @package phpBB Extension - vinny/sidebarblock_skeleton
 * @copyright (c) Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace vinny\sidebarblock_skeleton\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/**
	 * Constructor
	 *
	 * @param \phpbb\template\template $template Template object
	 * @param \phpbb\user $user User object
	 * @param \phpbb\config\config $config Config object
	 * @param \phpbb\db\driver\driver_interface $db Database object
	 * @param \phpbb\auth\auth $auth Auth object
	 * @param \phpbb\event\dispatcher_interface $dispatcher Event dispatcher
	 */
	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, \phpbb\event\dispatcher_interface $dispatcher)
	{
		$this->template = $template;
		$this->user = $user;
		$this->config = $config;
		$this->db = $db;
		$this->auth = $auth;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core.
	 *
	 * @return array
	 */
	static public function getSubscribedEvents()
	{
		return [
			'core.user_setup'					=> 'load_language_on_setup',
			'vinny.sidebar.render_system_block'	=> 'on_sidebar_render_system_block',
		];
	}

	/**
	 * Load language file.
	 *
	 * @param \phpbb\event\data $event The event object
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'vinny/sidebarblock_skeleton',
			'lang_set' => 'sidebarblock_skeleton',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Populate dynamic system blocks registered by this extension.
	 *
	 * Sidebar Manager creates the visible sidebar block shell. This listener
	 * decides whether the current block belongs to this extension, prepares its
	 * template data, and points the parent renderer to this extension's template.
	 * Setting S_DISPLAY to false removes the whole block from the sidebar for
	 * the current request, which is useful when board settings or permissions
	 * make the block unavailable.
	 *
	 * @param \phpbb\event\data $event The event object
	 */
	public function on_sidebar_render_system_block($event)
	{
		$row = $event['row'];
		$block_data = $event['block_data'];

		if ($row['block_name'] === 'SIDEBAR_SKELETON_BIRTHDAYS')
		{
			if (!$this->render_birthdays())
			{
				$block_data['S_DISPLAY'] = false;
			}
			else
			{
				$block_data['TEMPLATE_FILE'] = '@vinny_sidebarblock_skeleton/birthdays.html';
			}
		}

		$event['block_data'] = $block_data;
	}

	/**
	 * Render users whose birthday matches the current day and month.
	 *
	 * This method is example code for the skeleton. When creating a new block
	 * extension, replace this method with the new block's data and permission
	 * logic instead of keeping unused birthday code.
	 *
	 * The query mirrors phpBB's index birthday logic so this block follows the
	 * same board settings, permission checks, leap-year behavior, banned-user
	 * filtering, and extension events as the native birthday list.
	 *
	 * @return bool False when phpBB birthday display is disabled for the user
	 */
	protected function render_birthdays()
	{
		if (!$this->config['load_birthdays'] || !$this->config['allow_birthdays'] || !$this->auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'))
		{
			return false;
		}

		// phpBB stores birthdays as day-month-year text using padded day/month values.
		$time = $this->user->create_datetime();
		$now = \phpbb_gmgetdate($time->getTimestamp() + $time->getOffset());
		$leap_year_birthdays = '';

		if ($now['mday'] == 28 && $now['mon'] == 2 && !$time->format('L'))
		{
			$leap_year_birthdays = " OR u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', 29, 2)) . "%'";
		}

		$sql_ary = [
			'SELECT'	=> 'u.user_id, u.username, u.user_colour, u.user_birthday',
			'FROM'		=> [
				USERS_TABLE	=> 'u',
			],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [
						BANLIST_TABLE	=> 'b',
					],
					'ON'	=> 'u.user_id = b.ban_userid',
				],
			],
			'WHERE'		=> "(b.ban_id IS NULL OR b.ban_exclude = 1)
				AND (u.user_birthday LIKE '" . $this->db->sql_escape(sprintf('%2d-%2d-', $now['mday'], $now['mon'])) . "%' $leap_year_birthdays)
				AND u.user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')',
			'ORDER_BY'	=> 'u.username_clean ASC',
		];

		$vars = ['now', 'sql_ary', 'time'];
		extract($this->dispatcher->trigger_event('core.index_modify_birthdays_sql', compact($vars)));

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$birthdays = [];

		foreach ($rows as $row)
		{
			$birthday_year = (int) substr($row['user_birthday'], -4);
			$birthday_age = ($birthday_year) ? max(0, $now['year'] - $birthday_year) : '';

			$birthdays[] = [
				'USERNAME'	=> \get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'AGE'		=> $birthday_age,
			];
		}

		// Keep compatibility with extensions that modify the native birthday list.
		$vars = ['birthdays', 'rows'];
		extract($this->dispatcher->trigger_event('core.index_modify_birthdays_list', compact($vars)));

		foreach ($birthdays as $birthday)
		{
			$this->template->assign_block_vars('sidebarblock_skeleton_birthdays', [
				'USERNAME_FULL'	=> $birthday['USERNAME'],
				'AGE'			=> $birthday['AGE'],
			]);
		}

		$this->template->assign_var('S_SIDEBARBLOCK_SKELETON_BIRTHDAYS', !empty($birthdays));

		return true;
	}
}
