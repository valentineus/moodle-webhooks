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
require_once(__DIR__ . "/lib.php");

require_once($CFG->libdir . "/adminlib.php");
require_once($CFG->libdir . "/tablelib.php");

/* Optional parameters */
$backupservices = optional_param("getbackup", 0, PARAM_BOOL);
$deleteid       = optional_param("deleteid", 0, PARAM_INT);
$hideshowid     = optional_param("hideshowid", 0, PARAM_INT);

/* Link generation */
$editservice    = "/local/webhooks/editservice.php";
$managerservice = "/local/webhooks/index.php";
$restorebackup  = "/local/webhooks/restorebackup.php";
$baseurl        = new moodle_url($managerservice);

/* Configure the context of the page */
admin_externalpage_setup("local_webhooks", "", null, $baseurl, array());
$context = context_system::instance();

/* Delete the service */
if (boolval($deleteid)) {
    local_webhooks_remove_record($deleteid);
    redirect($PAGE->url, new lang_string("eventwebserviceservicedeleted", "webservice"));
}

/* Retrieving a list of services */
$callbacks = local_webhooks_get_list_records();

/* Upload settings as a file */
if (boolval($backupservices)) {
    $filecontent = local_webhooks_create_backup();
    $filename    = "webhooks_" . date("U") . ".backup";
    send_file($filecontent, $filename, 0, 0, true, true);
}

/* Switching the status of the service */
if (boolval($hideshowid)) {
    $callback = $callbacks[$hideshowid];

    if (!empty($callback)) {
        $callback->enable = !boolval($callback->enable);
        local_webhooks_update_record($callback, false);
        redirect($PAGE->url, new lang_string("eventwebserviceserviceupdated", "webservice"));
    }
}

/* The page title */
$titlepage = new lang_string("pluginname", "local_webhooks");
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

/* Table declaration */
$table = new flexible_table("webhooks-service-table");

/* Customize the table */
$table->define_columns(array("title", "url", "actions"));
$table->define_headers(array(new lang_string("name", "moodle"), new lang_string("url", "moodle"), new lang_string("actions", "moodle")));
$table->define_baseurl($baseurl);
$table->setup();

foreach ($callbacks as $callback) {
    /* Filling of information columns */
    $titlecallback = html_writer::div($callback->title, "title");
    $urlcallback   = html_writer::div($callback->url, "url");

    /* Defining service status */
    $hideshowicon   = "t/show";
    $hideshowstring = new lang_string("enable", "moodle");
    if (boolval($callback->enable)) {
        $hideshowicon   = "t/hide";
        $hideshowstring = new lang_string("disable", "moodle");
    }

    /* Link to enable / disable the service */
    $hideshowlink = new moodle_url($managerservice, array("hideshowid" => $callback->id));
    $hideshowitem = $OUTPUT->action_icon($hideshowlink, new pix_icon($hideshowicon, $hideshowstring));

    /* Link for editing */
    $editlink = new moodle_url($editservice, array("serviceid" => $callback->id));
    $edititem = $OUTPUT->action_icon($editlink, new pix_icon("t/edit", new lang_string("edit", "moodle")));

    /* Link to remove */
    $deletelink = new moodle_url($managerservice, array("deleteid" => $callback->id));
    $deleteitem = $OUTPUT->action_icon($deletelink, new pix_icon("t/delete", new lang_string("delete", "moodle")));

    /* Adding data to the table */
    $table->add_data(array($titlecallback, $urlcallback, $hideshowitem . $edititem . $deleteitem));
}

/* Display the table */
$table->print_html();

/* Add service button */
$addserviceurl = new moodle_url($editservice);
echo $OUTPUT->single_button($addserviceurl, new lang_string("addaservice", "webservice"), "get");

/* Button to get a backup */
$backupurl = new moodle_url($managerservice, array("getbackup" => true));
echo $OUTPUT->single_button($backupurl, new lang_string("backup", "moodle"), "get");

/* Button for restoring settings */
$restorebackupurl = new moodle_url($restorebackup);
echo $OUTPUT->single_button($restorebackupurl, new lang_string("restore", "moodle"), "get");

echo $OUTPUT->footer();