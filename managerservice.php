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
 * Service Management Manager.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
$deleteservice = optional_param("deleteservice", 0, PARAM_INT);

require_login();
$baseurl = new moodle_url("/local/webhooks/managerservice.php");
$PAGE->set_url($baseurl);

$context = context_system::instance();
$PAGE->set_context($context);

/* Delete the service */
if ($deleteservice && confirm_sesskey()) {
    $DB->delete_records("local_webhooks_service", array("id" => $deleterssid));
    redirect($PAGE->url, new lang_string("servicedeleted", "local_webhooks"));
}

/* Retrieving a list of services */
$select = null;
$callbacks = $DB->get_records_select("local_webhooks_service", $select, null, $DB->sql_order_by_text("title"));

/* Page template */
$titlepage = new lang_string("managementmanager", "local_webhooks");
$PAGE->set_pagelayout("standard");
$PAGE->set_title($titlepage);
$PAGE->set_heading($titlepage);

/* The page title */
$PAGE->navbar->add(new lang_string("local"));
$PAGE->navbar->add(new lang_string("pluginname", "local_webhooks"));
$PAGE->navbar->add($titlepage, $baseurl);
echo $OUTPUT->header();

/* @todo: Place the formation table */

/* Add service button */
$addurl = new moodle_url("/local/webhooks/editservice.php");
$addurltext = new lang_string("managementmanageradd", "local_webhooks");
echo "<div class=\"actionbuttons\">" . $OUTPUT->single_button($addurl, $addurltext, "get") . "</div>";

echo $OUTPUT->footer();