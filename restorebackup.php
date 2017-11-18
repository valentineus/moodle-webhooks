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
 * Restore settings page.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/classes/editform.php");
require_once($CFG->libdir . "/adminlib.php");

admin_externalpage_setup("local_webhooks");

/* Link generation */
$managerservice = new moodle_url("/local/webhooks/managerservice.php");
$baseurl        = new moodle_url("/local/webhooks/restorebackup.php");
$PAGE->set_url($baseurl);

/* Configure the context of the page */
$context = context_system::instance();
$PAGE->set_context($context);

/* Create an editing form */
$mform = new \local_webhooks\service_backup_form($PAGE->url);

/* Cancel processing */
if ($mform->is_cancelled()) {
    redirect($managerservice);
}

/* Processing the received file */
$data = $mform->get_data();
if (boolval($data) && confirm_sesskey()) {
    $content   = $mform->get_file_content("backupfile");
    $callbacks = unserialize(gzuncompress(base64_decode($content)));

    $DB->delete_records("local_webhooks_service");
    foreach ($callbacks as $callback) {
        $DB->insert_record("local_webhooks_service", $callback);
    }

    redirect($managerservice, new lang_string("restorefinished", "moodle"));
}

/* Page template */
$titlepage = new lang_string("backup", "moodle");
$PAGE->set_pagelayout("admin");
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);

/* The page title */
$PAGE->navbar->add($titlepage);
echo $OUTPUT->header();

/* Displays the form */
$mform->display();

echo $OUTPUT->footer();