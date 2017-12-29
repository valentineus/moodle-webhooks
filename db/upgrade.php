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
 * Keeps track of upgrades to the 'local_webhooks' plugin.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

require_once(__DIR__ . "/../lib.php");

/**
 * Function to upgrade 'local_webhooks'.
 *
 * @param  number  $oldversion
 * @return boolean
 */
function xmldb_local_webhooks_upgrade($oldversion) {
    global $CFG, $DB;

    /* Update from version 3.0.0 */
    if ($oldversion < 2017112600) {
        $rs = $DB->get_recordset("local_webhooks_service", null, "id", "*", 0, 0);
        foreach ($rs as $record) {
            if (!empty($record->events)) {
                $record->events = unserialize(gzuncompress(base64_decode($record->events)));
                local_webhooks_update_record($record);
            }
        }
        $rs->close();
        upgrade_plugin_savepoint(true, 2017112600, "local", "webhooks");
    }

    return true;
}