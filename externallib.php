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
 * This file defines the plugin's external functions.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

require_once(__DIR__ . "/lib.php");

require_once($CFG->libdir . "/externallib.php");

/**
 * External functions.
 *
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_webhooks_external extends external_api {
    /**
     * Single template output parameters.
     *
     * @return external_description
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    private static function structure_record_parameters() {
        return new external_single_structure(
            array(
                "id"     => new external_value(PARAM_INT, "Service ID."),
                "enable" => new external_value(PARAM_BOOL, "Enable or disable the service."),
                "title"  => new external_value(PARAM_TEXT, "Name of the service."),
                "url"    => new external_value(PARAM_URL, "Web address of the service."),
                "type"   => new external_value(PARAM_TEXT, "Used header type."),
                "token"  => new external_value(PARAM_RAW, "Authorization key.", VALUE_OPTIONAL),
                "other"  => new external_value(PARAM_RAW, "Additional field.", VALUE_OPTIONAL),
                "events" => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            "name"  => new external_value(PARAM_TEXT, "The name of the event."),
                            "value" => new external_value(PARAM_BOOL, "Enabled or disabled handler.")
                        )
                    ), "List of observed events.", VALUE_OPTIONAL
                )
            ), "Information about the service."
        );
    }

    /**
     * Formation of the final list.
     *
     * @param  array $listrecords
     * @return array
     */
    private static function formation_list($listrecords) {
        $result = array();

        foreach ($listrecords as $index => $record) {
            $result[$index] = (array) $record;
            $result[$index]["events"] = self::formation_events($record->events);
        }

        return $result;
    }

    /**
     * Formation of the final list of events.
     *
     * @param  array $listevents
     * @return array
     */
    private static function formation_events($listevents) {
        $result = array();

        foreach ($listevents as $key => $value) {
            $result[] = array("name" => $key, "value" => $value);
        }

        return $result;
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function change_status_parameters() {
        return new external_function_parameters(
            array(
                "serviceid" => new external_value(PARAM_INT, "Service ID.")
            )
        );
    }

    /**
     * Change the status of the service.
     *
     * @param  number  $serviceid
     * @return boolean
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function change_status($serviceid) {
        $parameters = self::validate_parameters(self::change_status_parameters(), array("serviceid" => $serviceid));

        $context = context_system::instance();
        self::validate_context($context);

        return local_webhooks_change_status($parameters["serviceid"]);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since  Moodle 2.2
     */
    public static function change_status_returns() {
        return new external_value(PARAM_BOOL, "Result of execution.");
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function search_services_by_event_parameters() {
        return new external_function_parameters(
            array(
                "eventname" => new external_value(PARAM_TEXT, "The name of the event."),
                "active"    => new external_value(PARAM_BOOL, "Filter show active or all services.")
            )
        );
    }

    /**
     * Search for services that contain the specified event.
     *
     * @param  string  $eventname
     * @param  boolean $active
     * @return array
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function search_services_by_event($eventname, $active) {
        $parameters = self::validate_parameters(self::search_services_by_event_parameters(), array("eventname" => $eventname, "active" => $active));

        $context = context_system::instance();
        self::validate_context($context);

        $result = array();

        if ($listrecords = local_webhooks_search_services_by_event($parameters["eventname"], $parameters["active"])) {
            $result = self::formation_list($listrecords);
        }

        return $result;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since  Moodle 2.2
     */
    public static function search_services_by_event_returns() {
        return new external_multiple_structure(
            self::structure_record_parameters(), "List of services."
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function get_record_parameters() {
        return new external_function_parameters(
            array(
                "serviceid" => new external_value(PARAM_INT, "Service ID.")
            )
        );
    }

    /**
     * Get the record from the database.
     *
     * @param  number $serviceid
     * @return array
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function get_record($serviceid) {
        $parameters = self::validate_parameters(self::get_record_parameters(), array("serviceid" => $serviceid));

        $context = context_system::instance();
        self::validate_context($context);

        $result = array();

        if ($record = local_webhooks_get_record($parameters["serviceid"])) {
            $result = (array) $record;
            $result["events"] = self::formation_events($record->events);
        }

        return $result;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since  Moodle 2.2
     */
    public static function get_record_returns() {
        return self::structure_record_parameters();
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function get_list_records_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get all records from the database.
     *
     * @return array
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function get_list_records() {
        $context = context_system::instance();
        self::validate_context($context);

        $result = array();

        if ($listrecords = local_webhooks_get_list_records()) {
            $result = self::formation_list($listrecords);
        }

        return $result;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since  Moodle 2.2
     */
    public static function get_list_records_returns() {
        return new external_multiple_structure(
            self::structure_record_parameters(), "List of services."
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function get_list_events_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get a list of all system events.
     *
     * @return array
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function get_list_events() {
        $context = context_system::instance();
        self::validate_context($context);

        $result = array();

        if ($eventlist = local_webhooks_get_list_events()) {
            foreach ($eventlist as $item) {
                $result[] = $item["eventname"];
            }
        }

        return $result;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since  Moodle 2.2
     */
    public static function get_list_events_returns() {
        return new external_multiple_structure(
            new external_value(PARAM_TEXT, "The name of the event.")
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function create_record_parameters() {
        return new external_function_parameters(
            array(
                "service" => new external_single_structure(
                    array(
                        "enable" => new external_value(PARAM_BOOL, "Enable or disable the service.", VALUE_OPTIONAL),
                        "title"  => new external_value(PARAM_TEXT, "Name of the service."),
                        "url"    => new external_value(PARAM_URL, "Web address of the service."),
                        "type"   => new external_value(PARAM_TEXT, "Used header type.", VALUE_OPTIONAL),
                        "token"  => new external_value(PARAM_RAW, "Authorization key.", VALUE_OPTIONAL),
                        "other"  => new external_value(PARAM_RAW, "Additional field.", VALUE_OPTIONAL),
                        "events" => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    "name"  => new external_value(PARAM_TEXT, "The name of the event."),
                                    "value" => new external_value(PARAM_BOOL, "Enabled or disabled handler.")
                                )
                            ), "List of observed events.", VALUE_OPTIONAL
                        )
                    ), "Information about the service."
                )
            )
        );
    }

    /**
     * Create an entry in the database.
     *
     * @param  array   $service
     * @return boolean
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function create_record($service) {
        $parameters = self::validate_parameters(self::create_record_parameters(), array("service" => $service));

        $context = context_system::instance();
        self::validate_context($context);

        $record = new stdClass();
        $record->other  = $parameters["service"]["other"];
        $record->title  = $parameters["service"]["title"];
        $record->token  = $parameters["service"]["token"];
        $record->url    = $parameters["service"]["url"];
        $record->events = array();

        $record->enable = !empty($parameters["service"]["enable"]) ? $parameters["service"]["enable"] : false;
        $record->type   = !empty($parameters["service"]["type"]) ? $parameters["service"]["type"] : "json";

        foreach ($parameters["service"]["events"] as $value) {
            $record->events[$value["name"]] = $value["value"];
        }

        return local_webhooks_create_record($record);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since  Moodle 2.2
     */
    public static function create_record_returns() {
        return new external_value(PARAM_BOOL, "Result of execution.");
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function update_record_parameters() {
        return new external_function_parameters(
            array(
                "service" => new external_single_structure(
                    array(
                        "id"     => new external_value(PARAM_INT, "Service ID."),
                        "enable" => new external_value(PARAM_BOOL, "Enable or disable the service.", VALUE_OPTIONAL),
                        "title"  => new external_value(PARAM_TEXT, "Name of the service.", VALUE_OPTIONAL),
                        "url"    => new external_value(PARAM_URL, "Web address of the service.", VALUE_OPTIONAL),
                        "type"   => new external_value(PARAM_TEXT, "Used header type.", VALUE_OPTIONAL),
                        "token"  => new external_value(PARAM_RAW, "Authorization key.", VALUE_OPTIONAL),
                        "other"  => new external_value(PARAM_RAW, "Additional field.", VALUE_OPTIONAL),
                        "events" => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    "name"  => new external_value(PARAM_TEXT, "The name of the event."),
                                    "value" => new external_value(PARAM_BOOL, "Enabled or disabled handler.")
                                )
                            ), "List of observed events.", VALUE_OPTIONAL
                        )
                    ), "Information about the service."
                )
            )
        );
    }

    /**
     * Update the record in the database.
     *
     * @param  array   $service
     * @return boolean
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function update_record($service) {
        $parameters = self::validate_parameters(self::update_record_parameters(), array("service" => $service));

        $context = context_system::instance();
        self::validate_context($context);

        $result = false;

        if ($record = local_webhooks_get_record($parameters["service"]["id"])) {
            $record->enable = !empty($parameters["service"]["enable"]) ? $parameters["service"]["enable"] : $record->enable;
            $record->other  = !empty($parameters["service"]["other"]) ? $parameters["service"]["other"] : $record->other;
            $record->title  = !empty($parameters["service"]["title"]) ? $parameters["service"]["title"] : $record->title;
            $record->token  = !empty($parameters["service"]["token"]) ? $parameters["service"]["token"] : $record->token;
            $record->type   = !empty($parameters["service"]["type"]) ? $parameters["service"]["type"] : $record->type;
            $record->url    = !empty($parameters["service"]["url"]) ? $parameters["service"]["url"] : $record->url;

            if (!empty($parameters["service"]["events"])) {
                $record->events = array();

                foreach ($parameters["service"]["events"] as $value) {
                    $record->events[$value["name"]] = $value["value"];
                }
            }

            $result = local_webhooks_update_record($record);
        }

        return $result;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function update_record_returns() {
        return new external_value(PARAM_BOOL, "Result of execution.");
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function delete_record_parameters() {
        return new external_function_parameters(
            array(
                "serviceid" => new external_value(PARAM_INT, "Service ID.")
            )
        );
    }

    /**
     * Delete the record from the database.
     *
     * @param  number  $serviceid
     * @return boolean
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function delete_record($serviceid) {
        $parameters = self::validate_parameters(self::delete_record_parameters(), array("serviceid" => $serviceid));

        $context = context_system::instance();
        self::validate_context($context);

        return local_webhooks_delete_record($parameters["serviceid"]);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since  Moodle 2.2
     */
     public static function delete_record_returns() {
        return new external_value(PARAM_BOOL, "Result of execution.");
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function delete_all_records_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Delete all records from the database.
     *
     * @return boolean
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function delete_all_records() {
        $context = context_system::instance();
        self::validate_context($context);

        return local_webhooks_delete_all_records();
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since  Moodle 2.2
     */
    public static function delete_all_records_returns() {
        return new external_value(PARAM_BOOL, "Result of execution.");
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function create_backup_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Create a backup.
     *
     * @return string
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function create_backup() {
        $context = context_system::instance();
        self::validate_context($context);

        return local_webhooks_create_backup();
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since  Moodle 2.2
     */
    public static function create_backup_returns() {
        return new external_value(PARAM_RAW, "Backup copy.");
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     * @since  Moodle 2.9 Options available
     * @since  Moodle 2.2
     */
    public static function restore_backup_parameters() {
        return new external_function_parameters(
            array(
                "options" => new external_single_structure(
                    array(
                        "backup"        => new external_value(PARAM_RAW, "Backup copy."),
                        "deleterecords" => new external_value(PARAM_BOOL, "Delete or leave all records.", VALUE_OPTIONAL)
                    )
                )
            )
        );
    }

    /**
     * Restore from a backup.
     *
     * @param array $options
     * @since Moodle 2.9 Options available
     * @since Moodle 2.2
     */
    public static function restore_backup($options) {
        $parameters = self::validate_parameters(self::restore_backup_parameters(), array("options" => $options));

        $context = context_system::instance();
        self::validate_context($context);

        $deleterecords = !empty($parameters["options"]["deleterecords"]) ? $parameters["options"]["deleterecords"] : false;
        local_webhooks_restore_backup($parameters["options"]["backup"], $deleterecords);
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since  Moodle 2.2
     */
    public static function restore_backup_returns() {
        return null;
    }
}
