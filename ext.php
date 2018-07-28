<?php
/**
 *
 * This file is part of the Moderator Messages extension for the phpBB forum software.
 *
 * @author Zoddo <phpbb-exts@zoddo.fr>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace zoddo\mm;

class ext extends \phpbb\extension\base
{
	public function is_enableable()
	{
		return version_compare(PHP_VERSION, '5.4.7', '>=');
	}
}
