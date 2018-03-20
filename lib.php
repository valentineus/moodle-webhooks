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
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

define("LOCAL_WEBHOOKS_TABLE_SERVICES", "local_webhooks_service");
define("LOCAL_WEBHOOKS_TABLE_EVENTS", "local_webhooks_events");

require_once(__DIR__ . "/locallib.php");

/**
 * Change the status of the service.
 *
 * @param  number  $serviceid Service identifier
 * @return boolean            The result of the operation
 */
function local_webhooks_change_status($serviceid) {
    global $DB;

    /* Checks arguments */
    if (empty($serviceid)) {
        print_error("missingparam", "error", null, "serviceid");
    }

    /* Gets the current status */
    $status = $DB->get_field(LOCAL_WEBHOOKS_TABLE_SERVICES, "status", array("id" => $serviceid), IGNORE_MISSING);

    /* Changes the status to the opposite */
    $result = $DB->set_field(LOCAL_WEBHOOKS_TABLE_SERVICES, "status", !boolval($status), array("id" => $serviceid));

    /* Clears the cache */
    local_webhooks_cache_reset();

    return $result;
}

/**
 * Search for services that contain the specified event.
 *
 * @param  string $eventname Event name
 * @param  number $limitfrom Start position
 * @param  number $limitnum  End position
 * @return array             Search results
 */
function local_webhooks_search_record($eventname, $limitfrom = 0, $limitnum = 0) {
    global $DB;

    /* Checks arguments */
    if (empty($eventname)) {
        print_error("missingparam", "error", null, "eventname");
    }

    /* Checks for the presence of a cache */
    $namecache = "${eventname}_${limitfrom}_${limitnum}";
    if (is_array($records = local_webhooks_cache_get($namecache))) {
        return $records;
    }

    /* Loads a list of events */
    $rs = $DB->get_recordset(LOCAL_WEBHOOKS_TABLE_EVENTS, array("name" => $eventname, "status" => true), "id", "*", $limitfrom, $limitnum);
    $result = array();

    /* Loads services */
    foreach ($rs as $event) {
        if ($record = $DB->get_record(LOCAL_WEBHOOKS_TABLE_SERVICES, array("id" => $event->serviceid, "status" => true), "*", IGNORE_MISSING)) {
            $result[] = $record;
        }
    }

    $rs->close();

    /* Saves the result in the cache */
    local_webhooks_cache_set($namecache, $result);

    return $result;
}

/**
 * Get the record from the database.
 *
 * @param  number $serviceid Service identifier
 * @return object            Service data
 */
function local_webhooks_get_record($serviceid) {
    global $DB;

    /* Checks arguments */
    if (empty($serviceid)) {
        print_error("missingparam", "error", null, "serviceid");
    }

    /* Loads service data */
    $record = $DB->get_record(LOCAL_WEBHOOKS_TABLE_SERVICES, array("id" => $serviceid), "*", IGNORE_MISSING);

    /* Loads service events */
    if (!empty($record)) {
        $record->events = local_webhooks_get_list_events_for_service($serviceid);
    }

    return $record;
}

/**
 * Get all records from the database.
 *
 * @param  number $limitfrom
 * @param  number $limitnum
 * @param  array  $conditions
 * @return array
 */
function local_webhooks_get_list_records($limitfrom = 0, $limitnum = 0, $conditions = array()) {
    global $DB;

    $records = $DB->get_records(LOCAL_WEBHOOKS_TABLE_SERVICES, $conditions, "id", "*", $limitfrom, $limitnum);

    foreach ($records as $record) {
        $record->events = local_webhooks_get_list_events_for_service($record->id);
    }

    return $records;
}

/**
 * Get a list of all system events.
 *
 * @return array
 */
function local_webhooks_get_list_events() {
    return report_eventlist_list_generator::get_all_events_list(true);
}

/**
 * Create an entry in the database.
 *
 * @param  object $record
 * @return number
 */
function local_webhooks_create_record($record) {
    global $DB;

    if (empty($record->events)) {
        $record->events = array();
    }

    /* Adding entries */
    $transaction = $DB->start_delegated_transaction();
    $serviceid = $DB->insert_record(LOCAL_WEBHOOKS_TABLE_SERVICES, $record, true, false);
    local_webhooks_insert_events_for_service($serviceid, $record->events);
    $transaction->allow_commit();

    /* Clear the plugin cache */
    local_webhooks_cache_reset();

    /* Event notification */
    local_webhooks_events::service_added($serviceid);

    return $serviceid;
}

/**
 * Update the record in the database.
 *
 * @param  object  $record
 * @return boolean
 */
function local_webhooks_update_record($record) {
    global $DB;

    if (empty($record->id)) {
        print_error("missingparam", "error", null, "id");
    }

    if (empty($record->events)) {
        $record->events = array();
    }

    /* Update records */
    $transaction = $DB->start_delegated_transaction();
    $result = $DB->update_record(LOCAL_WEBHOOKS_TABLE_SERVICES, $record, false);
    local_webhooks_insert_events_for_service($record->id, $record->events);
    $transaction->allow_commit();

    /* Clear the plugin cache */
    local_webhooks_cache_reset();

    /* Event notification */
    local_webhooks_events::service_updated($record->id);

    return boolval($result);
}

/**
 * Delete the record from the database.
 *
 * @param  number  $serviceid
 * @return boolean
 */
function local_webhooks_delete_record($serviceid) {
    global $DB;

    $result = $DB->delete_records(LOCAL_WEBHOOKS_TABLE_SERVICES, array("id" => $serviceid));
    local_webhooks_delete_events_for_service($serviceid);

    /* Clear the plugin cache */
    local_webhooks_cache_reset();

    /* Event notification */
    local_webhooks_events::service_deleted($serviceid);

    return boolval($result);
}

/**
 * Delete all records from the database.
 *
 * @return boolean
 */
function local_webhooks_delete_all_records() {
    global $DB;

    $result = $DB->delete_records(LOCAL_WEBHOOKS_TABLE_SERVICES, null);
    $DB->delete_records(LOCAL_WEBHOOKS_TABLE_EVENTS, null);

    /* Clear the plugin cache */
    local_webhooks_cache_reset();

    /* Event notification */
    local_webhooks_events::service_deletedall();

    return boolval($result);
}

/**
 * Create a backup.
 *
 * @return string
 */
function local_webhooks_create_backup() {
    $records = local_webhooks_get_list_records();
    $result = false;

    if ($serialize = serialize($records)) {
        $result = gzcompress($serialize, 9);
    }

    /* Event notification */
    local_webhooks_events::backup_performed();

    return base64_encode($result);
}

/**
 * Restore from a backup.
 *
 * @param string $data
 */
function local_webhooks_restore_backup($backup) {
    global $DB;

    $serialize = gzuncompress(base64_decode($backup));
    $records = unserialize($serialize);

    $transaction = $DB->start_delegated_transaction();
    local_webhooks_delete_all_records();

    foreach ($records as $record) {
        local_webhooks_create_record($record);
    }

    $transaction->allow_commit();

    /* Event notification */
    local_webhooks_events::backup_restored();

    return true;
}

/**
 * Send the event remotely to the service.
 *
 * @param  object $event
 * @param  object $record
 * @return array
 */
function local_webhooks_send_request($event, $record) {
    global $CFG;

    $event["host"]  = parse_url($CFG->wwwroot)["host"]; /* @todo: Fucking shit */
    $event["token"] = $record->token;
    $event["extra"] = $record->other;

    $curl = new curl();
    $curl->setHeader(array("Content-Type: application/" . $record->type));
    $curl->post($record->url, json_encode($event));
    $response = $curl->getResponse();

    /* Event notification */
    local_webhooks_events::response_answer($record->id, $response);

    return $response;
}
