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
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_webhooks_get_service' => array(
        'classname'   => 'local_webhooks_external',
        'classpath'   => 'local/webhooks/externallib.php',
        'description' => 'Get information about the service.',
        'methodname'  => 'get_service',
        'type'        => 'read',
    ),

    'local_webhooks_get_services' => array(
        'classname'   => 'local_webhooks_external',
        'classpath'   => 'local/webhooks/externallib.php',
        'description' => 'Get a list of services.',
        'methodname'  => 'get_services',
        'type'        => 'read',
    ),

    'local_webhooks_get_services_by_event' => array(
        'classname'   => 'local_webhooks_external',
        'classpath'   => 'local/webhooks/externallib.php',
        'description' => 'Get the list of services subscribed to the event.',
        'methodname'  => 'get_services_by_event',
        'type'        => 'read',
    ),
);