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
     * @param int $serviceid
     *
     * @return object
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_service($serviceid = 0) {
        global $DB;

        if (!is_numeric($serviceid) || $serviceid === 0) {
            print_error('unknowparamtype', 'error', null, 'serviceid');
        }

        $service = $DB->get_record(LW_TABLE_SERVICES, array('id' => $serviceid), '*', MUST_EXIST);
        $events = $DB->get_records(LW_TABLE_EVENTS, array('serviceid' => $serviceid), '', '*', 0, 0);

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
     * @param int   $limitfrom
     * @param int   $limitnum
     *
     * @return array
     * @throws \dml_exception
     */
    public static function get_services(array $conditions = array(), $limitfrom = 0, $limitnum = 0) {
        global $DB;

        $services = $DB->get_records(LW_TABLE_SERVICES, $conditions, '', '*', $limitfrom, $limitnum);

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
     * @param string $eventname
     *
     * @return array
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_services_by_event($eventname = '') {
        global $DB;

        if (!is_string($eventname) || $eventname === '') {
            print_error('unknowparamtype', 'error', null, 'eventname');
        }

        $events = $DB->get_records(LW_TABLE_EVENTS, array('name' => $eventname), '', '*', 0, 0);

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

        $serviceid = $DB->insert_record(LW_TABLE_SERVICES, (object) $service, true, false);
        if ($serviceid && !empty($service['events']) && is_array($service['events'])) {
            self::insert_events($service['events'], $serviceid);
        }

        // TODO: Mark the log.

        return (int) $serviceid;
    }

    /**
     * Delete the service data from the database.
     *
     * @param  int $serviceid
     *
     * @return bool
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function delete_service($serviceid = 0) {
        global $DB;

        if (!is_numeric($serviceid) || $serviceid === 0) {
            print_error('unknowparamtype', 'error', null, 'serviceid');
        }

        // TODO: Mark the log.

        $DB->delete_records(LW_TABLE_EVENTS, array('serviceid' => $serviceid));

        return $DB->delete_records(LW_TABLE_SERVICES, array('id' => $serviceid));
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
     * @param int   $serviceid
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected static function insert_events(array $events = array(), $serviceid = 0) {
        global $DB;

        $conditions = array();
        foreach ($events as $eventname) {
            $conditions[] = array('name' => $eventname, 'serviceid' => $serviceid);
        }

        $DB->insert_records(LW_TABLE_EVENTS, $conditions);
    }
}