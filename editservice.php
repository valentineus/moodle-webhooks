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
 * Service editor.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/classes/form.php");

$idservice = optional_param("idservice", 0, PARAM_INT);

require_login();

$managerservice = new moodle_url("/local/webhooks/managerservice.php");
$baseurl = new moodle_url("/local/webhooks/editservice.php");
$PAGE->set_url($baseurl);

$context = context_system::instance();
$PAGE->set_context($context);

/* Preparing a template for data */
$titlepage = new lang_string("editserviceadds", "local_webhooks");
$servicerecord = new stdClass;

if (boolval($idservice)) {
    $titlepage = new lang_string("editserviceedits", "local_webhooks");
    $servicerecord = $DB->get_record(
        "local_webhooks_service",
        array("id" => $idservice),
        "*", MUST_EXIST);
}

/* Page template */
$PAGE->set_pagelayout("admin");
$PAGE->set_title($titlepage);
$PAGE->set_heading($titlepage);

/* Create an editing form */
$mform = new \local_webhooks\service_edit_form();

/* The page title */
$PAGE->navbar->add(new lang_string("local"));
$PAGE->navbar->add(new lang_string("pluginname", "local_webhooks"));
$PAGE->navbar->add(new lang_string("managementmanager", "local_webhooks"), $managerservice);
$PAGE->navbar->add($titlepage);
echo $OUTPUT->header();

/* Displays the form */
$mform->display();

echo $OUTPUT->footer();