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
 * Restore the settings page.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/classes/service_form.php");
require_once(__DIR__ . "/lib.php");

require_once($CFG->libdir . "/adminlib.php");

/* Link generation */
$baseurl        = new moodle_url("/local/webhooks/restorebackup.php");
$managerservice = new moodle_url("/local/webhooks/index.php");

/* Configure the context of the page */
admin_externalpage_setup("local_webhooks", "", null, $baseurl, array());
$context = context_system::instance();

/* Create an editing form */
$mform = new service_backup_form($PAGE->url);

/* Cancel processing */
if ($mform->is_cancelled()) {
    redirect($managerservice);
}

/* Processing the received file */
if ($data = $mform->get_data()) {
    $content = $mform->get_file_content("backupfile");
    local_webhooks_restore_backup($content, $data->deleterecords);
    redirect($managerservice, new lang_string("restorefinished", "moodle"));
}

/* The page title */
$titlepage = new lang_string("backup", "moodle");
$PAGE->navbar->add($titlepage);
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

/* Displays the form */
$mform->display();

/* Footer */
echo $OUTPUT->footer();