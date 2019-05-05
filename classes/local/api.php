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
use function define;
use function defined;
use function is_array;
use function is_int;
use function is_object;

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
     * @param array|null $conditions
     * @param int|null   $limitfrom
     * @param int|null   $limitnum
     *
     * @return \local_webhooks\local\record[]
     * @throws \dml_exception
     */
    public static function get_services(array $conditions = null, int $limitfrom = null, int $limitnum = null): array {
        global $DB;

        $records = $DB->get_records(LW_TABLE_SERVICES, $conditions ?? [], '', '*', $limitfrom ?? 0, $limitnum ?? 0);

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
}