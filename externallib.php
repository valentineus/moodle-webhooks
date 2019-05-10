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
use local_webhooks\local\record;

/**
 * WebHooks external functions.
 *
 * @copyright 2019 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class local_webhooks_external extends external_api {
    /**
     * Add a new service.
     *
     * @param array $conditions
     *
     * @return int
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public static function add_service(array $conditions): int {
        $parameters = self::validate_parameters(self::add_service_parameters(), [
            'record' => array_filter($conditions),
        ]);

        $context = context_system::instance();
        self::validate_context($context);

        $parameters = array_filter($parameters['record']);
        $record = new record($parameters);

        return api::add_service($record);
    }

    /**
     * Returns description of the method parameters.
     *
     * @return \external_function_parameters
     */
    public static function add_service_parameters(): external_function_parameters {
        return new external_function_parameters([
            'record' => new external_single_structure([
                'events' => new external_multiple_structure(
                    new external_value(PARAM_RAW, 'The event\'s name.'), 'The service\'s list events.', false
                ),
                'header' => new external_value(PARAM_RAW, 'The request\'s header or type', false, 'application/json'),
                'name'   => new external_value(PARAM_RAW, 'The service\'s name.'),
                'point'  => new external_value(PARAM_URL, 'The service\'s endpoint.'),
                'status' => new external_value(PARAM_BOOL, 'The service\'s status.', false, true),
                'token'  => new external_value(PARAM_RAW, 'The service\'s secret key.'),
            ], ''),
        ], '');
    }

    /**
     * Returns description of the method result value.
     *
     * @return \external_value
     */
    public static function add_service_returns(): external_value {
        return new external_value(PARAM_INT, 'The service\'s ID.');
    }

    /**
     * Delete the existing service.
     *
     * @param int $serviceid
     *
     * @return bool
     *
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public static function del_service(int $serviceid): bool {
        $parameters = self::validate_parameters(self::del_service_parameters(), [
            'serviceid' => $serviceid,
        ]);

        $context = context_system::instance();
        self::validate_context($context);

        return api::del_service($parameters['serviceid']);
    }

    /**
     * Returns description of the method parameters.
     *
     * @return \external_function_parameters
     */
    public static function del_service_parameters(): external_function_parameters {
        return new external_function_parameters([
            'serviceid' => new external_value(PARAM_INT, 'The service\'s ID.'),
        ], '');
    }

    /**
     * Returns description of the method result value.
     *
     * @return \external_value
     */
    public static function del_service_returns(): external_value {
        return new external_value(PARAM_BOOL, 'The result operation.');
    }

    /**
     * Get the event's list.
     *
     * @return array
     *
     * @throws \ReflectionException
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public static function get_events(): array {
        $context = context_system::instance();
        self::validate_context($context);

        return api::get_events();
    }

    /**
     * Returns description of the method parameters.
     *
     * @return \external_function_parameters
     */
    public static function get_events_parameters(): external_function_parameters {
        return new external_function_parameters([], '');
    }

    /**
     * Returns description of the method result value.
     *
     * @return \external_multiple_structure
     */
    public static function get_events_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'action'      => new external_value(PARAM_ALPHANUMEXT, ''),
                'component'   => new external_value(PARAM_COMPONENT, ''),
                'crud'        => new external_value(PARAM_ALPHA, ''),
                'edulevel'    => new external_value(PARAM_INT, ''),
                'eventname'   => new external_value(PARAM_RAW, ''),
                'objecttable' => new external_value(PARAM_RAW, ''),
                'target'      => new external_value(PARAM_RAW, ''),
            ], ''), ''
        );
    }

    /**
     * Get data by service.
     *
     * @param int $serviceid
     *
     * @return \local_webhooks\local\record
     *
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public static function get_service(int $serviceid): record {
        $parameters = self::validate_parameters(self::get_service_parameters(), [
            'serviceid' => $serviceid,
        ]);

        $context = context_system::instance();
        self::validate_context($context);

        return api::get_service($parameters['serviceid']);
    }

    /**
     * Returns description of the method parameters.
     *
     * @return \external_function_parameters
     */
    public static function get_service_parameters(): external_function_parameters {
        return new external_function_parameters([
            'serviceid' => new external_value(PARAM_INT, 'The service\'s ID.'),
        ], '');
    }

    /**
     * Returns description of the method result value.
     *
     * @return \external_single_structure
     */
    public static function get_service_returns(): external_single_structure {
        return new external_single_structure([
            'events' => new external_multiple_structure(
                new external_value(PARAM_RAW, 'The event\'s name.'), 'The service\'s list events.'
            ),
            'header' => new external_value(PARAM_RAW, 'The request\'s header or type'),
            'id'     => new external_value(PARAM_INT, 'The service\'s ID.'),
            'name'   => new external_value(PARAM_RAW, 'The service\'s name.'),
            'point'  => new external_value(PARAM_URL, 'The service\'s endpoint.'),
            'status' => new external_value(PARAM_BOOL, 'The service\'s status.'),
            'token'  => new external_value(PARAM_RAW, 'The service\'s secret key.'),
        ], '');
    }

    /**
     * Get the service's list.
     *
     * @param array|null  $conditions
     * @param string|null $sort
     * @param int|null    $from
     * @param int|null    $limit
     *
     * @return array
     *
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public static function get_services(array $conditions = null, string $sort = null, int $from = null, int $limit = null): array {
        $parameters = self::validate_parameters(self::get_services_parameters(), [
            'conditions' => $conditions ?? [],
            'from'       => $from,
            'limit'      => $limit,
            'sort'       => $sort,
        ]);

        $context = context_system::instance();
        self::validate_context($context);

        return api::get_services(
            array_filter($parameters['conditions']),
            $parameters['sort'],
            $parameters['from'],
            $parameters['limit']
        );
    }

    /**
     * Returns description of the method parameters.
     *
     * @return \external_function_parameters
     */
    public static function get_services_parameters(): external_function_parameters {
        return new external_function_parameters([
            'conditions' => new external_single_structure([
                'header' => new external_value(PARAM_RAW, 'The request\'s header or type', false),
                'name'   => new external_value(PARAM_RAW, 'The service\'s name.', false),
                'point'  => new external_value(PARAM_URL, 'The service\'s endpoint.', false),
                'status' => new external_value(PARAM_BOOL, 'The service\'s status.', false),
                'token'  => new external_value(PARAM_RAW, 'The service\'s secret key.', false),
            ], '', false),
            'sort'       => new external_value(PARAM_RAW, '', false),
            'from'       => new external_value(PARAM_INT, '', false),
            'limit'      => new external_value(PARAM_INT, '', false),
        ], '');
    }

    /**
     * Returns description of the method result value.
     *
     * @return \external_multiple_structure
     */
    public static function get_services_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'events' => new external_multiple_structure(
                    new external_value(PARAM_RAW, 'The event\'s name.'), 'The service\'s list events.'
                ),
                'header' => new external_value(PARAM_RAW, 'The request\'s header or type'),
                'id'     => new external_value(PARAM_INT, 'The service\'s ID.'),
                'name'   => new external_value(PARAM_RAW, 'The service\'s name.'),
                'point'  => new external_value(PARAM_URL, 'The service\'s endpoint.'),
                'status' => new external_value(PARAM_BOOL, 'The service\'s status.'),
                'token'  => new external_value(PARAM_RAW, 'The service\'s secret key.'),
            ], ''), ''
        );
    }

    /**
     * Update the existing service.
     *
     * @param array $conditions
     *
     * @return bool
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public static function set_service(array $conditions): bool {
        $conditions = array_filter($conditions);
        $conditions['events'] = $conditions['events'] ?? [];

        $parameters = self::validate_parameters(self::set_service_parameters(), [
            'record' => $conditions,
        ]);

        $context = context_system::instance();
        self::validate_context($context);

        $parameters = array_filter($parameters['record']);
        $record = api::get_service($parameters['id']);

        foreach ($parameters as $index => $value) {
            if (property_exists($record, $index)) {
                $record->$index = $value;
            }
        }

        return api::set_service($record);
    }

    /**
     * Returns description of the method parameters.
     *
     * @return \external_function_parameters
     */
    public static function set_service_parameters(): external_function_parameters {
        return new external_function_parameters([
            'record' => new external_single_structure([
                'events' => new external_multiple_structure(
                    new external_value(PARAM_RAW, 'The event\'s name.'), 'The service\'s list events.', false
                ),
                'header' => new external_value(PARAM_RAW, 'The request\'s header or type', false),
                'id'     => new external_value(PARAM_INT, 'The service\'s ID.'),
                'name'   => new external_value(PARAM_RAW, 'The service\'s name.', false),
                'point'  => new external_value(PARAM_URL, 'The service\'s endpoint.', false),
                'status' => new external_value(PARAM_BOOL, 'The service\'s status.', false),
                'token'  => new external_value(PARAM_RAW, 'The service\'s secret key.', false),
            ], ''),
        ], '');
    }

    /**
     * Returns description of the method result value.
     *
     * @return \external_value
     */
    public static function set_service_returns(): external_value {
        return new external_value(PARAM_BOOL, '');
    }
}