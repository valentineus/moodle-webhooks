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
require_once(__DIR__ . "/classes/webhooks_table.php");
require_once(__DIR__ . "/lib.php");

require_once($CFG->libdir . "/adminlib.php");

/* Optional parameters */
$backupservices = optional_param("getbackup", 0, PARAM_BOOL);
$deleteall      = optional_param("deleteall", 0, PARAM_INT);
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
if (!empty($deleteid) && confirm_sesskey()) {
    local_webhooks_delete_record($deleteid);
    redirect($PAGE->url, new lang_string("deleted", "moodle"));
}

/* Switching the status of the service */
if (!empty($hideshowid) && confirm_sesskey()) {
    local_webhooks_change_status($hideshowid);
    redirect($PAGE->url, new lang_string("changessaved", "moodle"));
}

/* Deletes all data */
if (boolval($deleteall) && confirm_sesskey()) {
    local_webhooks_delete_all_records();
    redirect($PAGE->url, new lang_string("deleted", "moodle"));
}

/* Upload settings as a file */
if (boolval($backupservices)) {
    $filecontent = local_webhooks_create_backup();
    $filename    = "webhooks_" . date("U") . ".backup";
    send_file($filecontent, $filename, 0, 0, true, true);
}

/* The page title */
$titlepage = new lang_string("pluginname", "local_webhooks");
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

/* Adds the add button */
$addserviceurl = new moodle_url($editservice);
echo $OUTPUT->single_button($addserviceurl, new lang_string("addaservice", "webservice"));

/* Adds a delete button */
$deleteallurl = new moodle_url($managerservice, array("deleteall" => true, "sesskey" => sesskey()));
echo $OUTPUT->single_button($deleteallurl, new lang_string("deleteall", "moodle"), "get");

/* Adds a backup button */
$backupurl = new moodle_url($managerservice, array("getbackup" => true));
echo $OUTPUT->single_button($backupurl, new lang_string("backup", "moodle"), "get");

/* Adds a restore button */
$restorebackupurl = new moodle_url($restorebackup);
echo $OUTPUT->single_button($restorebackupurl, new lang_string("restore", "moodle"));

/* Displays the table */
$table = new local_webhooks_table("local-webhooks-table");
$table->define_baseurl($baseurl);
$table->out(25, true);

/* Footer */
echo $OUTPUT->footer();