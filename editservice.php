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
require_once(__DIR__ . "/classes/editform.php");
require_once($CFG->libdir . "/adminlib.php");

admin_externalpage_setup("local_webhooks");

/* Optional parameters */
$serviceid = optional_param("serviceid", 0, PARAM_INT);

/* Link generation */
$urlparameters  = array("serviceid" => $serviceid);
$managerservice = new moodle_url("/local/webhooks/managerservice.php", $urlparameters);
$baseurl        = new moodle_url("/local/webhooks/editservice.php", $urlparameters);
$PAGE->set_url($baseurl, $urlparameters);

/* Configure the context of the page */
$context = context_system::instance();
$PAGE->set_context($context);

/* Preparing a template for data */
$titlepage     = new lang_string("externalservice", "webservice");
$servicerecord = new stdClass;

/* Create an editing form */
$mform = new \local_webhooks\service_edit_form($PAGE->url);

/* Cancel processing */
if ($mform->is_cancelled()) {
    redirect($managerservice);
}

/* Getting the data */
if ($editing = boolval($serviceid)) {
    $servicerecord = $DB->get_record("local_webhooks_service", array("id" => $serviceid), "*", MUST_EXIST);
    $mform->set_data($servicerecord);
}

/* Processing of received data */
if ($data = $mform->get_data()) {
    if (empty($data->events)) {
        $data->events = array();
    }

    /* Pack the list of events */
    $data->events = base64_encode(gzcompress(serialize($data->events), 9));

    if ($editing) {
        $data->id = $serviceid;
        $DB->update_record("local_webhooks_service", $data);

        /* Run the event */
        $event = \local_webhooks\event\service_updated::create(array("context" => $context, "objectid" => $data->id));
        $event->trigger();

        redirect($managerservice, new lang_string("eventwebserviceserviceupdated", "webservice"));
    } else {
        $servicenewid = $DB->insert_record("local_webhooks_service", $data);

        /* Run the event */
        $event = \local_webhooks\event\service_added::create(array("context" => $context, "objectid" => $servicenewid));
        $event->trigger();

        redirect($managerservice, new lang_string("eventwebserviceservicecreated", "webservice"));
    }
}

/* Page template */
$PAGE->set_pagelayout("admin");
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);

/* The page title */
$PAGE->navbar->add($titlepage);
echo $OUTPUT->header();

/* Displays the form */
$mform->display();

echo $OUTPUT->footer();