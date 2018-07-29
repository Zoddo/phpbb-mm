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
class rc3 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\zoddo\mm\migrations\v100\rc2');
	}

	public function effectively_installed()
	{
		return isset($this->config['mm_version']) && phpbb_version_compare($this->config['mm_version'], '1.0.0-rc3', '>=');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('mm_version', '1.0.0-rc3')),

			array('permission.permission_set', array('ROLE_MOD_STANDARD', 'm_mm_edit')),
			array('permission.permission_set', array('ROLE_MOD_FULL', 'm_mm_edit')),
		);
	}
}
