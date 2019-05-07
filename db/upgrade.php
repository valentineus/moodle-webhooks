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

defined('MOODLE_INTERNAL') || die();

/**
 * It's a base64 encoded?
 *
 * @param string $string
 *
 * @return bool
 */
function is_base64(string $string) {
    return base64_encode(base64_decode($string)) === $string;
}

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
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
    $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
    $table->add_field('serviceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

    // Adding keys to table local_webhooks_events.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

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
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
    $table->add_field('header', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 'application/json');
    $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
    $table->add_field('point', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
    $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
    $table->add_field('token', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);

    // Adding keys to table local_webhooks_service.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

    // Conditionally launch create table for local_webhooks_service.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
}

/**
 * Save data to database.
 *
 * @param array $records
 *
 * @throws \dml_exception
 */
function save_records(array $records) {
    global $DB;

    foreach ($records as $record) {
        if (!is_array($record)) {
            continue;
        }

        $recordid = $DB->insert_record('local_webhooks_service', (object) $record);

        if ($recordid && is_array($record['events'])) {
            foreach ($record['events'] as $eventname) {
                $DB->insert_record('local_webhooks_events', (object) [
                    'name'      => $eventname,
                    'serviceid' => $recordid,
                ]);
            }
        }
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
function xmldb_local_webhooks_upgrade(int $oldversion) {
    global $DB;

    /* Update from versions 0.* */
    if (in_array($oldversion, [2017101900, 2017102500, 2017102600, 2017102610, 2017102620, 2017102630], true)) {
        upgrade_plugin_savepoint(true, 2019040100, 'local', 'webhooks');
    }

    /* Update from versions 1.* */
    if (in_array($oldversion, [2017102700, 2017102900, 2017102910], true)) {
        upgrade_plugin_savepoint(true, 2019040100, 'local', 'webhooks');
    }

    /* Update from versions 2.* */
    if (in_array($oldversion, [2017111800, 2017111810], true)) {
        upgrade_plugin_savepoint(true, 2019040100, 'local', 'webhooks');
    }

    /* Update from versions 3.* */
    if (in_array($oldversion, [2017112600, 2018061900, 2018061910, 2018061920], true)) {
        $records = $DB->get_records('local_webhooks_service', null, 'id');

        $services = [];

        foreach ($records as $record) {
            if (!is_object($record)) {
                continue;
            }

            $service = [
                'name'   => $record->title,
                'point'  => $record->url,
                'status' => (bool) $record->enable,
                'token'  => $record->token,
            ];

            if ($record->type === 'json') {
                $service['header'] = 'application/json';
            } else {
                $service['header'] = 'application/x-www-form-urlencoded';
            }

            if (!empty($record->events)) {
                $record->events = unserialize(gzuncompress(base64_decode($record->events)), [
                    'allowed_classes' => false,
                ]);
            }

            if (is_array($record->events) && !empty($record->events)) {
                foreach ($record->events as $eventname => $eventstatus) {
                    if ((bool) $eventstatus) {
                        $service['events'][] = is_base64($eventname)
                            ? base64_decode($eventname, true)
                            : $eventname;
                    }
                }
            }

            $services[] = $service;
        }

        /* Update structure */
        drop_table_service();
        create_table_events();
        create_table_service();

        /* Saving records */
        save_records($services);

        upgrade_plugin_savepoint(true, 2019040100, 'local', 'webhooks');
    }

    /* Update from versions 4.* */
    if (in_array($oldversion, [2017122900, 2018022500], true)) {
        upgrade_plugin_savepoint(true, 2019040100, 'local', 'webhooks');
    }

    return true;
}