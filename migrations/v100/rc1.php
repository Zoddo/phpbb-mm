<?php
/**
 *
 * This file is part of the Moderator Messages extension for the phpBB forum software.
 *
 * @author Zoddo <phpbb-exts@zoddo.fr>
 * @author Adrien Bonnel (ABDev)
 * @author Ewan Martin (PastisD)
 * @author Geolim4
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace zoddo\mm\migrations\v100;

/**
 * Changes made by the 3.0.x MOD written by ABDev, PastisD and Geolim4
 */
class rc1 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\dev');
	}

	public function effectively_installed()
	{
		return isset($this->config['mm_version']) && phpbb_version_compare($this->config['mm_version'], '1.0.0-rc1', '>=');
	}

	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'posts'	=> array(
					'mm_user_id'		=> array('UINT', 0),
					'mm_user_name'		=> array('VCHAR:255', ''),
					'mm_user_colour'	=> array('VCHAR:6', ''),
					'mm_text'			=> array('MTEXT', ''),
					'mm_text_bitfield'	=> array('VCHAR', ''),
					'mm_text_uid'		=> array('VCHAR:8', ''),
					'mm_text_options'	=> array('UINT', 7),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('mm_version', '1.0.0-rc1')),

			array('permission.add', array('u_mm_view')),
			array('permission.add', array('m_mm_view')),
			array('permission.add', array('m_mm_use')),

			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_mm_view')),
			array('permission.permission_set', array('ROLE_USER_LIMITED', 'u_mm_view')),
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_mm_view')),
			array('permission.permission_set', array('ROLE_USER_NOAVATAR', 'u_mm_view')),
			array('permission.permission_set', array('ROLE_USER_NEW_MEMBER', 'u_mm_view')),
			array('permission.permission_set', array('GUESTS', 'u_mm_view', 'group')),
			array('permission.permission_set', array('ROLE_MOD_STANDARD', array('m_mm_view', 'm_mm_use'))),
			array('permission.permission_set', array('ROLE_MOD_SIMPLE', array('m_mm_view', 'm_mm_use'))),
			array('permission.permission_set', array('ROLE_MOD_FULL', array('m_mm_view', 'm_mm_use'))),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'posts'	=> array(
					'mm_user_id',
					'mm_user_name',
					'mm_user_colour',
					'mm_text',
					'mm_text_bitfield',
					'mm_text_uid',
					'mm_text_options',
				),
			),
		);
	}
}
