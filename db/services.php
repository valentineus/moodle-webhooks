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
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

$functions = array(
    "local_webhooks_change_status" => array(
        "classname"   => "local_webhooks_external",
        "methodname"  => "change_status",
        "classpath"   => "local/webhooks/externallib.php",
        "description" => "Change the status of the service.",
        "type"        => "write"
    ),

    "local_webhooks_get_record" => array(
        "classname"   => "local_webhooks_external",
        "methodname"  => "get_record",
        "classpath"   => "local/webhooks/externallib.php",
        "description" => "Get the record from the database.",
        "type"        => "read"
    ),

    "local_webhooks_get_list_records" => array(
        "classname"   => "local_webhooks_external",
        "methodname"  => "get_list_records",
        "classpath"   => "local/webhooks/externallib.php",
        "description" => "Get all records from the database.",
        "type"        => "read"
    ),

    "local_webhooks_get_list_events" => array(
        "classname"   => "local_webhooks_external",
        "methodname"  => "get_list_events",
        "classpath"   => "local/webhooks/externallib.php",
        "description" => "Get a list of all system events.",
        "type"        => "read"
    ),

    "local_webhooks_create_record" => array(
        "classname"   => "local_webhooks_external",
        "methodname"  => "create_record",
        "classpath"   => "local/webhooks/externallib.php",
        "description" => "Create an entry in the database.",
        "type"        => "write"
    ),

    "local_webhooks_update_record" => array(
        "classname"   => "local_webhooks_external",
        "methodname"  => "update_record",
        "classpath"   => "local/webhooks/externallib.php",
        "description" => "Update the record in the database.",
        "type"        => "write"
    ),

    "local_webhooks_delete_record" => array(
        "classname"   => "local_webhooks_external",
        "methodname"  => "delete_record",
        "classpath"   => "local/webhooks/externallib.php",
        "description" => "Delete the record from the database.",
        "type"        => "write"
    ),

    "local_webhooks_delete_all_records" => array(
        "classname"   => "local_webhooks_external",
        "methodname"  => "delete_all_records",
        "classpath"   => "local/webhooks/externallib.php",
        "description" => "Delete all records from the database.",
        "type"        => "write"
    ),

    "local_webhooks_create_backup" => array(
        "classname"   => "local_webhooks_external",
        "methodname"  => "create_backup",
        "classpath"   => "local/webhooks/externallib.php",
        "description" => "Create a backup.",
        "type"        => "read"
    ),

    "local_webhooks_restore_backup" => array(
        "classname"   => "local_webhooks_external",
        "methodname"  => "restore_backup",
        "classpath"   => "local/webhooks/externallib.php",
        "description" => "Restore from a backup.",
        "type"        => "write"
    )
);