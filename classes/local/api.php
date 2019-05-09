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

namespace local_webhooks\local;

defined('MOODLE_INTERNAL') || die();

define('LW_TABLE_EVENTS', 'local_webhooks_events');
define('LW_TABLE_SERVICES', 'local_webhooks_service');

global $CFG;

require_once($CFG->dirroot . '/local/webhooks/classes/local/record.php');

use coding_exception;
use core_component;
use ReflectionClass;
use function define;
use function defined;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function strlen;

/**
 * The main class for the plugin.
 *
 * @copyright 2019 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks\local
 */
final class api {
    /**
     * Create a new record in the database.
     *
     * @param \local_webhooks\local\record $record
     *
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function create_service(record $record): int {
        global $DB;

        $id = $DB->insert_record(LW_TABLE_SERVICES, $record);

        if (is_int($id) && is_array($record->events)) {
            $DB->insert_records(LW_TABLE_EVENTS, array_map(static function (string $name) use ($id) {
                return ['name' => $name, 'serviceid' => $id];
            }, $record->events));
        }

        if (!is_int($id)) {
            throw new coding_exception('Variable \'id\' must be type \'Integer\'');
        }

        return $id;
    }

    /**
     * Delete an existing record in the database.
     *
     * @param int $id
     *
     * @return bool
     * @throws \dml_exception
     */
    public static function delete_service(int $id): bool {
        global $DB;

        $DB->delete_records(LW_TABLE_EVENTS, ['serviceid' => $id]);

        return $DB->delete_records(LW_TABLE_SERVICES, ['id' => $id]);
    }

    /**
     * Get an event's list.
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function get_events(): array {
        return array_merge(self::get_core_events_list(), self::get_non_core_event_list());
    }

    /**
     * Get an existing record from the database.
     *
     * @param int $id
     *
     * @return \local_webhooks\local\record
     * @throws \dml_exception
     */
    public static function get_service(int $id): record {
        global $DB;

        $record = $DB->get_record(LW_TABLE_SERVICES, ['id' => $id], '*', MUST_EXIST);
        $events = $DB->get_records(LW_TABLE_EVENTS, ['serviceid' => $id]);

        $service = new record();
        $service->events = array_column($events, 'name');
        $service->header = $record->header;
        $service->id = $record->id;
        $service->name = $record->name;
        $service->point = $record->point;
        $service->status = $record->status;
        $service->token = $record->token;

        return $service;
    }

    /**
     * Get list records from the database.
     *
     * @param array|null  $conditions
     * @param string|null $sort
     * @param int|null    $from
     * @param int|null    $limit
     *
     * @return array
     * @throws \dml_exception
     */
    public static function get_services(array $conditions = null, string $sort = null, int $from = null, int $limit = null): array {
        global $DB;

        $records = $DB->get_records(LW_TABLE_SERVICES, $conditions ?? [], $sort ?? '', '*', $from ?? 0, $limit ?? 0);

        $services = [];
        foreach ($records as $record) {
            if (!is_object($record)) {
                continue;
            }

            $events = $DB->get_records(LW_TABLE_EVENTS, ['serviceid' => $record->id]);

            $service = new record();
            $service->events = array_column($events, 'name');
            $service->header = $record->header;
            $service->id = $record->id;
            $service->name = $record->name;
            $service->point = $record->point;
            $service->status = $record->status;
            $service->token = $record->token;

            $services[] = $service;
        }

        return $services;
    }

    /**
     * Get list records from the database by the event's name.
     *
     * @param string $name
     *
     * @return \local_webhooks\local\record[]
     * @throws \dml_exception
     */
    public static function get_services_by_event(string $name): array {
        global $DB;

        $events = $DB->get_records(LW_TABLE_EVENTS, ['name' => $name]);

        $services = [];
        foreach ($events as $event) {
            if (!is_object($event)) {
                continue;
            }

            $services[] = self::get_service($event->serviceid);
        }

        return $services;
    }

    /**
     * Update an existing record in the database.
     *
     * @param \local_webhooks\local\record $service
     *
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function update_service(record $service): bool {
        global $DB;

        $result = $DB->update_record(LW_TABLE_SERVICES, $service);
        $DB->delete_records(LW_TABLE_EVENTS, ['serviceid' => $service->id]);

        if ($result && is_array($service->events)) {
            $DB->insert_records(LW_TABLE_EVENTS, array_map(static function (string $name) use ($service) {
                return ['name' => $name, 'serviceid' => $service->id];
            }, $service->events));
        }

        return $result;
    }

    /**
     * Get a system's events list.
     *
     * @return array
     * @throws \ReflectionException
     */
    private static function get_core_events_list(): array {
        global $CFG;

        $debugdeveloper = $CFG->debugdeveloper;
        $debugdisplay = $CFG->debugdisplay;
        $debuglevel = $CFG->debug;

        $CFG->debug = 0;
        $CFG->debugdeveloper = false;
        $CFG->debugdisplay = false;

        $directory = $CFG->libdir . '/classes/event';
        $files = self::get_file_list($directory);

        if (isset($files['unknown_logged'])) {
            unset($files['unknown_logged']);
        }

        $events = [];
        foreach ($files as $file => $location) {
            $name = '\\core\\event\\' . $file;

            if (method_exists($name, 'get_static_info')) {
                $class = new ReflectionClass($name);

                if ($file !== 'manager' && !$class->isAbstract()) {
                    $events[$name] = $name::get_static_info();
                }
            }
        }

        $CFG->debug = $debuglevel;
        $CFG->debugdeveloper = $debugdeveloper;
        $CFG->debugdisplay = $debugdisplay;

        return $events;
    }

    /**
     * Get a file's list in the directory.
     *
     * @param string $directory
     *
     * @return array
     */
    private static function get_file_list(string $directory): array {
        global $CFG;

        $root = $CFG->dirroot;

        $files = [];
        if (is_dir($directory) && is_readable($directory)) {
            $handle = opendir($directory);

            if ($handle) {
                foreach (scandir($directory, SCANDIR_SORT_NONE) as $file) {
                    if (!is_string($file)) {
                        continue;
                    }

                    if ($file !== '.' && $file !== '..' && strrpos($directory, $root) !== false) {
                        $location = substr($directory, strlen($root));
                        $eventname = substr($file, 0, -4);

                        if (is_string($eventname)) {
                            $files[$eventname] = $location . '/' . $file;
                        }
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Get a plugins' events list.
     *
     * @return array
     * @throws \ReflectionException
     */
    private static function get_non_core_event_list(): array {
        global $CFG;

        $debugdeveloper = $CFG->debugdeveloper;
        $debugdisplay = $CFG->debugdisplay;
        $debuglevel = $CFG->debug;

        $CFG->debug = 0;
        $CFG->debugdeveloper = false;
        $CFG->debugdisplay = false;

        $events = [];
        foreach (core_component::get_plugin_types() as $type => $unused) {
            foreach (core_component::get_plugin_list($type) as $plugin => $directory) {
                $directory .= '/classes/event';
                $files = self::get_file_list($directory);

                if (isset($files['unknown_logged'])) {
                    unset($files['unknown_logged']);
                }

                foreach ($files as $file => $location) {
                    $name = '\\' . $type . '_' . $plugin . '\\event\\' . $file;

                    if (method_exists($name, 'get_static_info')) {
                        $class = new ReflectionClass($name);

                        if ($type . '_' . $plugin !== 'logstore_legacy' && !$class->isAbstract()) {
                            $events[$name] = $name::get_static_info();
                        }
                    }
                }
            }
        }

        $CFG->debug = $debuglevel;
        $CFG->debugdeveloper = $debugdeveloper;
        $CFG->debugdisplay = $debugdisplay;

        return $events;
    }
}