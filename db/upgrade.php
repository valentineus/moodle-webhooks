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
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade 'local_webhooks'.
 *
 * @param int $old_version
 *
 * @return boolean
 * @throws \dml_exception
 * @throws \downgrade_exception
 * @throws \upgrade_exception
 */
function xmldb_local_webhooks_upgrade($old_version = 0) {
    global $DB;

    /* Update from versions 3.* */
    if ($old_version < 2017112600 || $old_version === 2018061900) {
        $rs = $DB->get_recordset('local_webhooks_service', null, 'id', '*', 0, 0);

        foreach ($rs as $record) {
            if (!empty($record->events)) {
                $record->events = unserialize(gzuncompress(base64_decode($record->events)));
                // TODO: This method does not exist.
                /* local_webhooks_update_record( $record ); */
            }
        }

        $rs->close();
        upgrade_plugin_savepoint(true, 2017112600, 'local', 'webhooks');
    }

    /* Update from version 4.0.0-rc.1 */

    /* if ($old_version === 2017122900) {} */

    /* Update from version 4.0.0-rc.2 */

    /* if ($old_version === 2018022200) {} */

    /* Update from version 4.0.0-rc.3 */

    /* if ($old_version === 2018022500) {} */

    return true;
}