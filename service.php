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
 * Page of the service editor.
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */

require_once(__DIR__ . '/../../config.php');

require_once($CFG->dirroot . '/local/webhooks/classes/ui_forms_plugin.php');
require_once($CFG->dirroot . '/local/webhooks/lib.php');
require_once($CFG->libdir . '/adminlib.php');

$serviceid = optional_param('serviceid', 0, PARAM_INT);

$urlparameters = array('serviceid' => $serviceid);
$baseurl = new moodle_url('/local/webhooks/service.php', $urlparameters);
$mainpage = new moodle_url('/local/webhooks/index.php');

admin_externalpage_setup('local_webhooks', '', null, $baseurl, array());
$context = context_system::instance();

$mform = new local_webhooks_service_edit_form($PAGE->url);
$formdata = (array) $mform->get_data();

/* Cancel */
if ($mform->is_cancelled()) {
    redirect($mainpage);
}

/* Updating the data */
if (!empty($formdata) && confirm_sesskey()) {
    if (isset($formdata['events'])) {
        $formdata['events'] = array_keys($formdata['events']);
    }

    if (!empty($serviceid)) {
        $formdata['id'] = $serviceid;
        local_webhooks_api::update_service($formdata);
    } else {
        local_webhooks_api::create_service($formdata);
    }

    redirect($mainpage, new lang_string('changessaved', 'moodle'));
}

/* Loading service data */
if (!empty($serviceid)) {
    $service = local_webhooks_api::get_service($serviceid);
    $service->events = array_fill_keys($service->events, 1);
    $mform->set_data($service);
}

/* The page title */
$titlepage = new lang_string('externalservice', 'webservice');
$PAGE->navbar->add($titlepage);
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

/* Displays the form */
$mform->display();

/* Footer */
echo $OUTPUT->footer();