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
 * This file registers the plugin's external functions.
 *
 * @copyright 2019 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_webhooks_add_service' => [
        'classname'   => 'local_webhooks_external',
        'classpath'   => 'local/webhooks/externallib.php',
        'description' => 'Add a new service.',
        'methodname'  => 'add_service',
        'type'        => 'write',
    ],

    'local_webhooks_del_service' => [
        'classname'   => 'local_webhooks_external',
        'classpath'   => 'local/webhooks/externallib.php',
        'description' => 'Delete the existing service.',
        'methodname'  => 'del_service',
        'type'        => 'write',
    ],

    'local_webhooks_get_events' => [
        'classname'   => 'local_webhooks_external',
        'classpath'   => 'local/webhooks/externallib.php',
        'description' => 'Get the event\'s list.',
        'methodname'  => 'get_events',
        'type'        => 'read',
    ],

    'local_webhooks_get_service' => [
        'classname'   => 'local_webhooks_external',
        'classpath'   => 'local/webhooks/externallib.php',
        'description' => 'Get data by service.',
        'methodname'  => 'get_service',
        'type'        => 'read',
    ],

    'local_webhooks_get_services' => [
        'classname'   => 'local_webhooks_external',
        'classpath'   => 'local/webhooks/externallib.php',
        'description' => 'Get the service\'s list.',
        'methodname'  => 'get_services',
        'type'        => 'read',
    ],

    'local_webhooks_set_service' => [
        'classname'   => 'local_webhooks_external',
        'classpath'   => 'local/webhooks/externallib.php',
        'description' => 'Update the existing service.',
        'methodname'  => 'set_service',
        'type'        => 'write',
    ],
];