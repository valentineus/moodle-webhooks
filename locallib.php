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
 * Classes of modules.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

define("LOCAL_WEBHOOKS_NAME_CACHE_REPOSITORY", "webhooks_services");
define("LOCAL_WEBHOOKS_NAME_PLUGIN", "local_webhooks");

/**
 * Get data from the cache by key.
 *
 * @param  string $eventname
 * @return array
 */
function local_webhooks_cache_get($eventname) {
    $cache = cache::make(LOCAL_WEBHOOKS_NAME_PLUGIN, LOCAL_WEBHOOKS_NAME_CACHE_REPOSITORY);
    return $cache->get($eventname);
}

/**
 * Update the data in the cache by key.
 *
 * @param  string  $eventname
 * @param  array   $recordlist
 * @return boolean
 */
function local_webhooks_cache_set($eventname, $recordlist = array()) {
    $cache = cache::make(LOCAL_WEBHOOKS_NAME_PLUGIN, LOCAL_WEBHOOKS_NAME_CACHE_REPOSITORY);
    return $cache->set($eventname, $recordlist);
}

/**
 * Delete the data in the cache by key.
 *
 * @param  string  $eventname
 * @return boolean
 */
function local_webhooks_cache_delete($eventname) {
    $cache = cache::make(LOCAL_WEBHOOKS_NAME_PLUGIN, LOCAL_WEBHOOKS_NAME_CACHE_REPOSITORY);
    return $cache->delete($eventname);
}

/**
 * Clear the cache of the plugin.
 *
 * @return boolean
 */
function local_webhooks_cache_reset() {
    $cache = cache::make(LOCAL_WEBHOOKS_NAME_PLUGIN, LOCAL_WEBHOOKS_NAME_CACHE_REPOSITORY);
    return $cache->purge();
}

/**
 * Deleting all the events linked to the given service.
 *
 * @param number $serviceid
 */
function local_webhooks_delete_events($serviceid) {
    global $DB;
    $DB->delete_records(LOCAL_WEBHOOKS_TABLE_EVENTS, array("serviceid" => $serviceid));
}

/**
 * Data serialization.
 *
 * @param  array|object $data
 * @return string
 */
function local_webhooks_serialization_data($data) {
    return serialize($data);
}

/**
 * Data deserialization.
 *
 * @param  string       $data
 * @return array|object
 */
function local_webhooks_deserialization_data($data) {
    return unserialize($data);
}

/**
 * Description of functions of the call of events
 *
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_webhooks_events {
    /**
     * Call the event when creating a backup.
     */
    public static function backup_performed() {
        $context = context_system::instance();
        $event   = local_webhooks\event\backup_performed::create(array("context" => $context, "objectid" => 0));
        $event->trigger();
    }

    /**
     * Call the event when restoring from a backup.
     */
    public static function backup_restored() {
        $context = context_system::instance();
        $event   = local_webhooks\event\backup_restored::create(array("context" => $context, "objectid" => 0));
        $event->trigger();
    }

    /**
     * Call event when the response is received from the service
     *
     * @param number $objectid Service ID
     * @param array  $response Server response
     */
    public static function response_answer($objectid = 0, $response = array()) {
        $context = context_system::instance();
        $status  = "Error sending request";

        if (!empty($response["HTTP/1.1"])) {
            $status = $response["HTTP/1.1"];
        }

        $event = local_webhooks\event\response_answer::create(array("context" => $context, "objectid" => $objectid, "other" => array("status" => $status)));
        $event->trigger();
    }

    /**
     * Call the event when the service is added.
     *
     * @param number $objectid Service ID
     */
    public static function service_added($objectid = 0) {
        $context = context_system::instance();
        $event   = local_webhooks\event\service_added::create(array("context" => $context, "objectid" => $objectid));
        $event->trigger();
    }

    /**
     * Call the event when the service is deleted.
     *
     * @param number $objectid Service ID
     */
    public static function service_deleted($objectid = 0) {
        $context = context_system::instance();
        $event   = local_webhooks\event\service_deleted::create(array("context" => $context, "objectid" => $objectid));
        $event->trigger();
    }

    /**
     * Call the event when all services are deleted.
     */
    public static function service_deletedall() {
        $context = context_system::instance();
        $event   = local_webhooks\event\service_deletedall::create(array("context" => $context, "objectid" => 0));
        $event->trigger();
    }

    /**
     * Call event when the service is updated.
     *
     * @param number $objectid Service ID
     */
    public static function service_updated($objectid = 0) {
        $context = context_system::instance();
        $event   = local_webhooks\event\service_updated::create(array("context" => $context, "objectid" => $objectid));
        $event->trigger();
    }
}