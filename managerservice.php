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
require_once($CFG->libdir . "/tablelib.php");

$deleteservice = optional_param("deleteservice", 0, PARAM_INT);

require_login();

/* Link generation */
$managerservice = "/local/webhooks/managerservice.php";
$editservice = "/local/webhooks/editservice.php";
$baseurl = new moodle_url($managerservice);
$PAGE->set_url($baseurl);

/* Configure the context of the page */
$context = context_system::instance();
$PAGE->set_context($context);

/* Delete the service */
if ($deleteservice && confirm_sesskey()) {
    $DB->delete_records("local_webhooks_service", array("id" => $deleteservice));
    redirect($PAGE->url, new lang_string("deleted", "moodle"));
}

/* Retrieving a list of services */
$select = null;
$callbacks = $DB->get_records_select("local_webhooks_service", $select, null, $DB->sql_order_by_text("id"));

/* Page template */
$titlepage = new lang_string("managerservice", "local_webhooks");
$PAGE->set_pagelayout("standard");
$PAGE->set_title($titlepage);
$PAGE->set_heading($titlepage);

/* The page title */
$PAGE->navbar->add(new lang_string("localplugins", "moodle"));
$PAGE->navbar->add(new lang_string("pluginname", "local_webhooks"));
$PAGE->navbar->add($titlepage, $baseurl);
echo $OUTPUT->header();

/* Table declaration */
$table = new flexible_table("callbacks-table");

/* Customize the table */
$table->define_columns(array("title", "url", "actions"));
$table->define_headers(array(
    new lang_string("name", "moodle"),
    new lang_string("url", "moodle"),
    new lang_string("actions", "moodle")));
$table->define_baseurl($baseurl);
$table->setup();

foreach ($callbacks as $callback) {
    /* Filling of information columns */
    $titlecallback = html_writer::div($callback->title, "title");
    $urlcallback = html_writer::div($callback->url, "url");

    /* Link for editing */
    $editlink = new moodle_url($editservice,
        array("idservice" => $callback->id));
    $edititem = $OUTPUT->action_icon($editlink,
        new pix_icon("t/edit", get_string("edit")));

    /* Link to remove */
    $deletelink = new moodle_url($managerservice,
        array("deleteservice" => $callback->id, "sesskey" => sesskey()));
    $deleteitem = $OUTPUT->action_icon($deletelink,
        new pix_icon("t/delete", get_string("delete")));

    /* Adding data to the table */
    $table->add_data(array($titlecallback, $urlcallback, $edititem . $deleteitem));
}

/* Display the table */
$table->print_html();

/* Add service button */
$addurl = new moodle_url("/local/webhooks/editservice.php");
echo $OUTPUT->single_button($addurl,
    new lang_string("addaservice", "webservice"), "get");

echo $OUTPUT->footer();