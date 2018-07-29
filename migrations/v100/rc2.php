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
class rc2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\zoddo\mm\migrations\v100\rc1');
	}

	public function effectively_installed()
	{
		return isset($this->config['mm_version']) && phpbb_version_compare($this->config['mm_version'], '1.0.0-rc2', '>=');
	}

	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'posts'	=> array(
					'post_moderation'				=> array('MTEXT', ''),
					'post_moderation_user_id'		=> array('UINT', 0),
					'post_moderation_username'		=> array('VCHAR:255', ''),
					'post_moderation_user_colour'	=> array('VCHAR:6', ''),
				),
				$this->table_prefix . 'topics'	=> array(
					'posts_moderation_total'		=> array('UINT', 0),
				),
			),

			// Accordingly to the original UMIL file, we need to remove columns, but we will do that in the custom function
			// update_remove_columns() because we need to convert data, first.
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('mm_version', '1.0.0-rc2')),

			array('config.add', array('post_moderation_username_replace', false)),
			array('config.add', array('post_moderation_parse_bbcode', false)),
			array('config.add', array('post_moderation_parse_urls', false)),
			array('config.add', array('post_moderation_parse_smilies', false)),

			array('permission.add', array('m_mm_read')),
			array('permission.add', array('m_mm_post')),
			array('permission.add', array('m_mm_edit')),
			array('permission.add', array('m_mm_delete')),

			array('permission.permission_set', array('ROLE_MOD_STANDARD', array('m_mm_read', 'm_mm_post'))),
			array('permission.permission_set', array('ROLE_MOD_SIMPLE', array('m_mm_read'))),
			array('permission.permission_set', array('ROLE_MOD_FULL', array('m_mm_read', 'm_mm_post', 'm_mm_edit', 'm_mm_delete'))),

			array('permission.remove', array('u_mm_view')),
			array('permission.remove', array('m_mm_view')),
			array('permission.remove', array('m_mm_use')),

			array('custom', array(array($this, 'update_to_rc2_step1'))),
			array('custom', array(array($this, 'update_to_rc2_step2'))),
			array('custom', array(array($this, 'update_remove_columns'))),
		);
	}

	public function revert_schema()
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
			'drop_columns'		=> array(
				$this->table_prefix . 'topics'	=> array(
					'posts_moderation_total',
				),
			),
		);

		// The columns "post_moderation_*" will be removed in the custom function downgrade_remove_columns() to have
		// the time to convert back data first.
	}

	public function revert_data()
	{
		return array(
			// From rc1 migration
			array('permission.permission_set', array('ROLE_USER_STANDARD', 'u_mm_view')),
			array('permission.permission_set', array('ROLE_USER_LIMITED', 'u_mm_view')),
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_mm_view')),
			array('permission.permission_set', array('ROLE_USER_NOAVATAR', 'u_mm_view')),
			array('permission.permission_set', array('ROLE_USER_NEW_MEMBER', 'u_mm_view')),
			array('permission.permission_set', array('GUESTS', 'u_mm_view', 'group')),
			array('permission.permission_set', array('ROLE_MOD_STANDARD', array('m_mm_view', 'm_mm_use'))),
			array('permission.permission_set', array('ROLE_MOD_SIMPLE', array('m_mm_view', 'm_mm_use'))),
			array('permission.permission_set', array('ROLE_MOD_FULL', array('m_mm_view', 'm_mm_use'))),

			array('custom', array(array($this, 'downgrade_from_rc2'))),
			array('custom', array(array($this, 'downgrade_remove_columns'))),
		);
	}

	/**
	 * This function came from the UMIL file of the original MOD.
	 */
	public function update_to_rc2_step1()
	{
		// first step
		$post_fields = array('post_id', 'mm_user_id', 'mm_user_name', 'mm_user_colour', 'mm_text', 'mm_text_bitfield', 'mm_text_uid', 'mm_text_options');

		$sql = 'SELECT ' . implode(', ', $post_fields) . '
		FROM ' . $this->table_prefix . "posts
		WHERE mm_text <> ''";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!empty($row['mm_text']))
			{
				$fields = array(
					'post_moderation_user_id' => $row['mm_user_id'],
					'post_moderation_username' => $row['mm_user_name'],
					'post_moderation_user_colour' => $row['mm_user_colour'],
					'post_moderation' => serialize(array($row['mm_text'], $row['mm_text_uid'], $row['mm_text_bitfield'], $row['mm_text_options'])),
				);

				$sql = 'UPDATE ' . $this->table_prefix . 'posts
				SET ' . $this->db->sql_build_array('UPDATE', $fields) . '
				WHERE post_id = ' . (int) $row['post_id'];
				$this->db->sql_query($sql);
			}
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * This function came from the UMIL file of the original MOD.
	 */
	public function update_to_rc2_step2()
	{
		// second step
		// incrementation query on phpbb_topics.posts_moderation_total
		$sql = 'SELECT topic_id, COUNT(post_id) AS total
		FROM ' . $this->table_prefix . "posts
		WHERE post_moderation <> ''
		GROUP BY topic_id";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql = 'UPDATE ' . $this->table_prefix . 'topics
			SET posts_moderation_total = ' . (int) $row['total'] . '
			WHERE topic_id = ' . (int) $row['topic_id'];
			$this->db->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
	}

	public function update_remove_columns()
	{
		// Remove old columns
		$this->db_tools->sql_column_remove($this->table_prefix . 'posts', 'mm_user_id');
		$this->db_tools->sql_column_remove($this->table_prefix . 'posts', 'mm_user_name');
		$this->db_tools->sql_column_remove($this->table_prefix . 'posts', 'mm_user_colour');
		$this->db_tools->sql_column_remove($this->table_prefix . 'posts', 'mm_text');
		$this->db_tools->sql_column_remove($this->table_prefix . 'posts', 'mm_text_bitfield');
		$this->db_tools->sql_column_remove($this->table_prefix . 'posts', 'mm_text_uid');
		$this->db_tools->sql_column_remove($this->table_prefix . 'posts', 'mm_text_options');
	}

	public function downgrade_from_rc2()
	{
		// first step
		$post_fields = array('post_id', 'post_moderation_user_id', 'post_moderation_username', 'post_moderation_user_colour', 'post_moderation');

		$sql = 'SELECT ' . implode(', ', $post_fields) . '
		FROM ' . $this->table_prefix . "posts
		WHERE post_moderation <> ''";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!empty($row['post_moderation']))
			{
				$text = unserialize($row['post_moderation']);
				if (!empty($text[0]))
				{
					$fields = array(
						'mm_user_id'		=> $row['post_moderation_user_id'],
						'mm_user_name'		=> $row['post_moderation_username'],
						'mm_user_colour'	=> $row['post_moderation_user_colour'],
						'mm_text'			=> $text[0],
						'mm_text_uid'		=> $text[1],
						'mm_text_bitfield'	=> $text[2],
						'mm_text_options'	=> $text[3],
					);

					$sql = 'UPDATE ' . $this->table_prefix . 'posts
					SET ' . $this->db->sql_build_array('UPDATE', $fields) . '
					WHERE post_id = ' . (int) $row['post_id'];
					$this->db->sql_query($sql);
				}
			}
		}
		$this->db->sql_freeresult($result);
	}

	public function downgrade_remove_columns()
	{
		// Remove columns
		$this->db_tools->sql_column_remove($this->table_prefix . 'posts', 'post_moderation');
		$this->db_tools->sql_column_remove($this->table_prefix . 'posts', 'post_moderation_user_id');
		$this->db_tools->sql_column_remove($this->table_prefix . 'posts', 'post_moderation_username');
		$this->db_tools->sql_column_remove($this->table_prefix . 'posts', 'post_moderation_user_colour');
	}
}
