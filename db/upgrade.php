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
 * Create a table local_webhooks_events.
 *
 * @throws \ddl_exception
 */
function create_table_events() {
    global $DB;

    // Loads ddl manager and xmldb classes.
    $dbman = $DB->get_manager();

    // Define table local_webhooks_events to be created.
    $table = new xmldb_table('local_webhooks_events');

    // Adding fields to table local_webhooks_events.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('serviceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

    // Adding keys to table local_webhooks_events.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    // Conditionally launch create table for local_webhooks_events.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
}

/**
 * Delete table local_webhooks_service.
 *
 * @throws \ddl_exception
 * @throws \ddl_table_missing_exception
 */
function drop_table_service() {
    global $DB;

    // Loads ddl manager and xmldb classes.
    $dbman = $DB->get_manager();

    // Define table local_webhooks_service to be dropped.
    $table = new xmldb_table('local_webhooks_service');

    // Conditionally launch drop table for local_webhooks_service.
    if ($dbman->table_exists($table)) {
        $dbman->drop_table($table);
    }
}

/**
 * Create a table local_webhooks_service.
 *
 * @throws \ddl_exception
 */
function create_table_service() {
    global $DB;

    // Loads ddl manager and xmldb classes.
    $dbman = $DB->get_manager();

    // Define table local_webhooks_service to be created.
    $table = new xmldb_table('local_webhooks_service');

    // Adding fields to table local_webhooks_service.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('header', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 'application/json');
    $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('point', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
    $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('token', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

    // Adding keys to table local_webhooks_service.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

    // Conditionally launch create table for local_webhooks_service.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
}

/**
 * Function to upgrade 'local_webhooks'.
 *
 * @param int $oldversion
 *
 * @return bool
 * @throws \ddl_exception
 * @throws \ddl_table_missing_exception
 * @throws \dml_exception
 * @throws \downgrade_exception
 * @throws \upgrade_exception
 */
function xmldb_local_webhooks_upgrade($oldversion = 0) {
    global $DB;

    /* Update from versions 3.* */
    if ($oldversion < 2017112600 || $oldversion === 2018061900) {
        $records = $DB->get_records('local_webhooks_service', null, 'id', '*', 0, 0);

        $services = array();

        foreach ($records as $record) {
            if (!empty($record->events)) {
                $record->events = unserialize(gzuncompress(base64_decode($record->events)));
            }

            $service = array(
                'name'   => $record->title,
                'point'  => $record->url,
                'status' => (bool) $record->enable,
                'token'  => $record->token,
            );

            if ($record->type === 'json') {
                $service['header'] = 'application/json';
            } else {
                $service['header'] = 'application/x-www-form-urlencoded';
            }

            foreach ($record->events as $eventname => $eventStatus) {
                if ((bool) $eventStatus) {
                    $service['events'][] = $eventname;
                }
            }

            $services[] = $service;
        }

        /* Update structure */
        drop_table_service();
        create_table_events();
        create_table_service();

        /* Saving records */
        foreach ($services as $service) {
            $serviceid = $DB->insert_record('local_webhooks_service', (object) $service, true, false);
            if ($serviceid && is_array($service['events'])) {
                foreach ($service['events'] as $eventname) {
                    $DB->insert_record('local_webhooks_events', (object) array('name' => $eventname, 'serviceid' => $serviceid), true, false);
                }
            }
        }

        upgrade_plugin_savepoint(true, 2018061900, 'local', 'webhooks');
    }

    /* Update from version 4.0.0-rc.1 */

    /* if ($oldversion === 2017122900) {} */

    /* Update from version 4.0.0-rc.2 */

    /* if ($oldversion === 2018022200) {} */

    /* Update from version 4.0.0-rc.3 */

    /* if ($oldversion === 2018022500) {} */

    return true;
}