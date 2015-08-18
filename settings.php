<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package    block_twitter
 * @copyright  Liam Mann <liam@liammann.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_heading('sampleheader',
                      get_string('headerconfig', 'block_twitter'), get_string('descconfig', 'block_twitter')));
$settings->add(new admin_setting_configtext('twitter/oauth_access_token', get_string('oauth_access_token', 'block_twitter'),
                      get_string('oauth_access_tokend', 'block_twitter')));
$settings->add(new admin_setting_configtext('twitter/oauth_access_token_secret', get_string('oauth_access_token_secret', 'block_twitter'),
                      get_string('oauth_access_token_secretd', 'block_twitter')));
$settings->add(new admin_setting_configtext('twitter/consumer_key', get_string('consumer_key', 'block_twitter'),
                      get_string('consumer_keyd', 'block_twitter')));
$settings->add(new admin_setting_configtext('twitter/consumer_secret', get_string('consumer_secret', 'block_twitter'),
                      get_string('consumer_secretd', 'block_twitter')));
$settings->add(new admin_setting_configtext('twitter/count', get_string('count', 'block_twitter'),
                      get_string('count', 'block_twitter')));
