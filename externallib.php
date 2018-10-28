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
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/webhooks/lib.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * External functions.
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */
class local_webhooks_external extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return \external_function_parameters
     *
     * @since Moodle 2.2
     * @since Moodle 2.9 Options available
     */
    public static function get_service_parameters() {
        return new external_function_parameters(
            array(
                'serviceid' => new external_value(PARAM_INT, 'Service ID.'),
            )
        );
    }

    /**
     * Get information about the service.
     *
     * @param $serviceid
     *
     * @return array
     *
     * @since Moodle 2.2
     * @since Moodle 2.9 Options available
     *
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \restricted_context_exception
     */
    public static function get_service($serviceid) {
        $parameters = self::validate_parameters(self::get_service_parameters(), array('serviceid' => $serviceid));

        $context = context_system::instance();
        self::validate_context($context);

        return (array) local_webhooks_api::get_service($parameters['serviceid']);
    }

    /**
     * Returns description of method result value.
     *
     * @return \external_single_structure
     *
     * @since Moodle 2.2
     * @since Moodle 2.9 Options available
     */
    public static function get_service_returns() {
        return new external_single_structure(
            array(
                'id'     => new external_value(PARAM_INT, 'Service ID.'),
                'header' => new external_value(PARAM_RAW, 'Type of outgoing header.'),
                'name'   => new external_value(PARAM_RAW, 'Name of the service.'),
                'point'  => new external_value(PARAM_URL, 'Point of delivery of notifications.'),
                'status' => new external_value(PARAM_BOOL, 'Current status of the service.'),
                'token'  => new external_value(PARAM_RAW, 'Token for verification of requests.'),
                'events' => new external_multiple_structure(
                    new external_value(PARAM_RAW, 'Event name.'), 'List of events.'
                ),
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return \external_function_parameters
     *
     * @since Moodle 2.2
     * @since Moodle 2.9 Options available
     */
    public static function get_services_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get a list of services.
     *
     * @return array
     *
     * @since Moodle 2.2
     * @since Moodle 2.9 Options available
     *
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public static function get_services() {
        $context = context_system::instance();
        self::validate_context($context);

        return local_webhooks_api::get_services();
    }

    /**
     * Returns description of method result value.
     *
     * @return \external_multiple_structure
     *
     * @since Moodle 2.2
     * @since Moodle 2.9 Options available
     */
    public static function get_services_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'     => new external_value(PARAM_INT, 'Service ID.'),
                    'header' => new external_value(PARAM_RAW, 'Type of outgoing header.'),
                    'name'   => new external_value(PARAM_RAW, 'Name of the service.'),
                    'point'  => new external_value(PARAM_URL, 'Point of delivery of notifications.'),
                    'status' => new external_value(PARAM_BOOL, 'Current status of the service.'),
                    'token'  => new external_value(PARAM_RAW, 'Token for verification of requests.'),
                    'events' => new external_multiple_structure(
                        new external_value(PARAM_RAW, 'Event name.'), 'List of events.'
                    ),
                )
            )
        );
    }
}