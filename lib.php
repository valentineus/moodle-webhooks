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

defined( "MOODLE_INTERNAL" ) || die();

define( "LW_TABLE_SERVICES", "local_webhooks_service" );
define( "LW_TABLE_EVENTS", "local_webhooks_events" );

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
     * @param int $serviceId
     * @return object
     */
    public static function get_service( $serviceId = 0 ) {
        global $DB;

        if ( empty( $serviceId ) || !is_numeric( $serviceId ) ) {
            print_error( "unknowparamtype", "error", null, "serviceId" );
        }

        $service = $DB->get_record( LW_TABLE_SERVICES, array( "id" => $serviceId ), "*", MUST_EXIST );
        $events = $DB->get_records( LW_TABLE_EVENTS, array( "serviceid" => $serviceId ), "", "*", 0, 0 );

        $service->events = array();
        foreach ( $events as $event ) {
            $service->events[] = $event->name;
        }

        return $service;
    }

    /**
     * Get a list of services.
     * By default, the entire list of services is given.
     *
     * @param array $conditions
     * @param int   $limitFrom
     * @param int   $limitNum
     * @return array
     */
    public static function get_services( $conditions = array(), $limitFrom = 0, $limitNum = 0 ) {
        global $DB;

        $services = $DB->get_records( LW_TABLE_SERVICES, $conditions, "", "*", $limitFrom, $limitNum );

        foreach ( $services as $service ) {
            $events = $DB->get_records( LW_TABLE_EVENTS, array( "serviceid" => $service->id ), "", "*", 0, 0 );

            $service->events = array();
            foreach ( $events as $event ) {
                $service->events[] = $event->name;
            }
        }

        return $services;
    }

    /**
     * Get the list of services subscribed to the event.
     *
     * @param string $eventName
     * @return array
     */
    public static function get_services_by_event( $eventName = "" ) {
        global $DB;

        if ( empty( $eventName ) || !is_string( $eventName ) ) {
            print_error( "unknowparamtype", "error", null, "eventName" );
        }

        $events = $DB->get_records( LW_TABLE_EVENTS, array( "name" => $eventName ), "", "*", 0, 0 );

        $services = array();
        foreach ( $events as $event ) {
            $services[] = local_webhooks_api::get_service( $event->serviceid );
        }

        return $services;
    }

    /**
     * Create service data in the database.
     *
     * @param  array $service
     * @return int
     */
    public static function create_service( $service = array() ) {
        global $DB;

        if ( empty( $service ) || !is_array( $service ) ) {
            print_error( "unknowparamtype", "error", null, "service" );
        }

        $serviceId = $DB->insert_record( LW_TABLE_SERVICES, $service, true, false );
        if ( $serviceId && !empty( $service[ "events" ] ) && is_array( $service[ "events" ] ) ) {
            self::insert_events( $service[ "events" ], $serviceId );
        }

        // TODO: Mark the log

        return (int) $serviceId;
    }

    /**
     * Delete the service data from the database.
     *
     * @param  int $serviceId
     * @return bool
     */
    public static function delete_service( $serviceId = 0 ) {
        global $DB;

        if ( empty( $serviceId ) || !is_numeric( $serviceId ) ) {
            print_error( "unknowparamtype", "error", null, "serviceId" );
        }

        // TODO: Mark the log

        $DB->delete_records( LW_TABLE_EVENTS, array( "serviceid" => $serviceId ) );
        return $DB->delete_records( LW_TABLE_SERVICES, array( "id" => $serviceId ) );
    }

    /**
     * Update the service data in the database.
     *
     * @param  array $service
     * @return bool
     */
    public static function update_service( $service = array() ) {
        global $DB;

        if ( empty( $service ) || !is_array( $service ) || empty( $service[ "id" ] ) ) {
            print_error( "unknowparamtype", "error", null, "service" );
        }

        // TODO: Add transactions for operations
        $result = $DB->update_record( LW_TABLE_SERVICES, $service, false );
        $DB->delete_records( LW_TABLE_EVENTS, array( "serviceid" => $service[ "id" ] ) );
        if ( $result && !empty( $service[ "events" ] ) && is_array( $service[ "events" ] ) ) {
            self::insert_events( $service[ "events" ], $service[ "id" ] );
        }

        // TODO: Mark the log

        return $result;
    }

    /**
     * Save the list of events to the database.
     *
     * @param array $events
     * @param int   $serviceId
     */
    protected static function insert_events( $events = array(), $serviceId = 0 ) {
        global $DB;

        $conditions = array();
        foreach ( $events as $eventName ) {
            $conditions[] = array( "name" => $eventName, "serviceid" => $serviceId );
        }

        $DB->insert_records( LW_TABLE_EVENTS, $conditions );
    }
}
