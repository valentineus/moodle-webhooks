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

defined('MOODLE_INTERNAL') || die();

define('LW_TABLE_SERVICES', 'local_webhooks_service');
define('LW_TABLE_EVENTS', 'local_webhooks_events');

/**
 * Class local_webhooks_api
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */
class local_webhooks_api {
    /**
     * Get information about the service.
     *
     * @param int $service_id
     *
     * @return object
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_service($service_id = 0) {
        global $DB;

        if (!is_numeric($service_id) || $service_id === 0) {
            print_error('unknowparamtype', 'error', null, 'service_id');
        }

        $service = $DB->get_record(LW_TABLE_SERVICES, array('id' => $service_id), '*', MUST_EXIST);
        $events = $DB->get_records(LW_TABLE_EVENTS, array('serviceid' => $service_id), '', '*', 0, 0);

        $service->events = array();
        foreach ($events as $event) {
            $service->events[] = $event->name;
        }

        return $service;
    }

    /**
     * Get a list of services.
     * By default, the entire list of services is given.
     *
     * @param array $conditions
     * @param int   $limit_from
     * @param int   $limit_num
     *
     * @return array
     * @throws \dml_exception
     */
    public static function get_services(array $conditions = array(), $limit_from = 0, $limit_num = 0) {
        global $DB;

        $services = $DB->get_records(LW_TABLE_SERVICES, $conditions, '', '*', $limit_from, $limit_num);

        foreach ($services as $service) {
            $events = $DB->get_records(LW_TABLE_EVENTS, array('serviceid' => $service->id), '', '*', 0, 0);

            $service->events = array();
            foreach ($events as $event) {
                $service->events[] = $event->name;
            }
        }

        return $services;
    }

    /**
     * Get the list of services subscribed to the event.
     *
     * @param string $event_name
     *
     * @return array
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_services_by_event($event_name = '') {
        global $DB;

        if (!is_string($event_name) || $event_name === '') {
            print_error('unknowparamtype', 'error', null, 'event_name');
        }

        $events = $DB->get_records(LW_TABLE_EVENTS, array('name' => $event_name), '', '*', 0, 0);

        $services = array();
        foreach ($events as $event) {
            $services[] = self::get_service($event->serviceid);
        }

        return $services;
    }

    /**
     * Create service data in the database.
     *
     * @param  array $service
     *
     * @return int
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function create_service(array $service = array()) {
        global $DB;

        if (!is_array($service) || count($service) === 0) {
            print_error('unknowparamtype', 'error', null, 'service');
        }

        $service_id = $DB->insert_record(LW_TABLE_SERVICES, (object) $service, true, false);
        if ($service_id && !empty($service['events']) && is_array($service['events'])) {
            self::insert_events($service['events'], $service_id);
        }

        // TODO: Mark the log.

        return (int) $service_id;
    }

    /**
     * Delete the service data from the database.
     *
     * @param  int $service_id
     *
     * @return bool
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function delete_service($service_id = 0) {
        global $DB;

        if (!is_numeric($service_id) || $service_id === 0) {
            print_error('unknowparamtype', 'error', null, 'service_id');
        }

        // TODO: Mark the log.

        $DB->delete_records(LW_TABLE_EVENTS, array('serviceid' => $service_id));

        return $DB->delete_records(LW_TABLE_SERVICES, array('id' => $service_id));
    }

    /**
     * Update the service data in the database.
     *
     * @param  array $service
     *
     * @return bool
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function update_service(array $service = array()) {
        global $DB;

        if (!is_array($service) || count($service) === 0 || !isset($service['id'])) {
            print_error('unknowparamtype', 'error', null, 'service');
        }

        // TODO: Add transactions for operations.
        $result = $DB->update_record(LW_TABLE_SERVICES, (object) $service, false);
        $DB->delete_records(LW_TABLE_EVENTS, array('serviceid' => $service['id']));

        if ($result && is_array($service['events']) && count($service) !== 0) {
            self::insert_events($service['events'], $service['id']);
        }

        // TODO: Mark the log.

        return $result;
    }

    /**
     * Save the list of events to the database.
     *
     * @param array $events
     * @param int   $service_id
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected static function insert_events(array $events = array(), $service_id = 0) {
        global $DB;

        $conditions = array();
        foreach ($events as $event_name) {
            $conditions[] = array('name' => $event_name, 'serviceid' => $service_id);
        }

        $DB->insert_records(LW_TABLE_EVENTS, $conditions);
    }
}