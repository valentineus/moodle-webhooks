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
 * This file contains the functions used by the plugin.
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */

defined("MOODLE_INTERNAL") || die();

define("LW_TABLE_SERVICES", "local_webhooks_service");
define("LW_TABLE_EVENTS", "local_webhooks_events");

/**
 * Class local_webhooks_api
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */
class local_webhooks_api {
    /**
     * Create service data in the database.
     *
     * @param  array $service Data to the service
     * @return int Service ID
     */
    public static function create_service($service = array()) {
        global $DB;

        if (!is_array($service) || empty($service)) {
            print_error("unknowparamtype", "error", null, "service");
        }

        $serviceId = $DB->insert_record(LW_TABLE_SERVICES, $service, true, false);
        if ($serviceId && is_array($service["events"]) && !empty($service["events"])) {
            self::insert_events($service["events"], $serviceId);
        }

        return (int) $serviceId;
    }

    /**
     * Delete the service data from the database.
     *
     * @param  int $serviceId Service ID
     * @return bool Execution result
     */
    public static function delete_service($serviceId = 0) {
        global $DB;

        if (!is_numeric($serviceId) || empty($serviceId)) {
            print_error("unknowparamtype", "error", null, "serviceId");
        }

        $DB->delete_records(LW_TABLE_EVENTS, array("serviceid" => $serviceId));
        return $DB->delete_records(LW_TABLE_SERVICES, array("id" => $serviceId));
    }

    /**
     * Update the service data in the database.
     *
     * @param  array $service Data to the service
     * @return bool Execution result
     */
    public static function update_service($service = array()) {
        global $DB;

        if (!is_array($service) || empty($service) || empty($service["id"])) {
            print_error("unknowparamtype", "error", null, "service");
        }

        $result = $DB->update_record(LW_TABLE_SERVICES, $service, false);
        $DB->delete_records(LW_TABLE_EVENTS, array("serviceid" => $service["id"]));
        if ($result && is_array($service["events"]) && !empty($service["events"])) {
            self::insert_events($service["events"], $service["id"]);
        }

        return $result;
    }

    /**
     * Save the list of events to the database.
     *
     * @param array $events    List of events
     * @param int   $serviceId Service ID
     */
    private static function insert_events($events = array(), $serviceId = 0) {
        global $DB;

        $conditions = array();
        foreach ($events as $eventName) {
            $conditions[] = array("name" => $eventName, "serviceid" => $serviceId);
        }

        $DB->insert_records(LW_TABLE_EVENTS, $conditions);
    }
}
