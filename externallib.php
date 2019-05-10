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

global $CFG;

require_once($CFG->dirroot . '/local/webhooks/classes/local/api.php');
require_once($CFG->libdir . '/externallib.php');

use local_webhooks\local\api;

/**
 * WebHooks external functions.
 *
 * @copyright 2019 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class local_webhooks_external extends external_api {
    /**
     * Testing get to a service.
     *
     * @param int $serviceid Service's ID.
     *
     * @return \local_webhooks\local\record
     *
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public static function get_service(int $serviceid) {
        $parameters = self::validate_parameters(self::get_service_parameters(), ['serviceid' => $serviceid]);

        $context = context_system::instance();
        self::validate_context($context);

        return api::get_service($parameters['serviceid']);
    }

    /**
     * Returns description of method parameters.
     *
     * @return \external_function_parameters
     */
    public static function get_service_parameters() {
        return new external_function_parameters([
            'serviceid' => new external_value(PARAM_INT, 'The service\'s ID.'),
        ]);
    }

    /**
     * Returns description of method parameters.
     *
     * @return \external_single_structure
     */
    public static function get_service_returns() {
        return new external_single_structure([
            'header' => new external_value(PARAM_RAW, 'The request\'s header or type'),
            'id'     => new external_value(PARAM_INT, 'The service\'s ID.'),
            'name'   => new external_value(PARAM_RAW, 'The service\'s name.'),
            'point'  => new external_value(PARAM_URL, 'The service\'s endpoint.'),
            'status' => new external_value(PARAM_BOOL, 'The service\'s status.'),
            'token'  => new external_value(PARAM_RAW, 'The service\'s secret key.'),
            'events' => new external_multiple_structure(
                new external_value(PARAM_RAW, 'The event\'s name.'), 'The service\'s list events.'
            ),
        ]);
    }
}