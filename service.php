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

$service_id = optional_param('serviceid', 0, PARAM_INT);

$url_parameters = array('serviceid' => $service_id);
$base_url = new moodle_url('/local/webhooks/service.php', $url_parameters);
$main_page = new moodle_url('/local/webhooks/index.php');

admin_externalpage_setup('local_webhooks', '', null, $base_url, array());
$context = context_system::instance();

$m_form = new local_webhooks_service_edit_form($PAGE->url);
$form_data = (array) $m_form->get_data();

/* Cancel */
if ($m_form->is_cancelled()) {
    redirect($main_page);
}

/* Updating the data */
if (!empty($form_data) && confirm_sesskey()) {
    if (isset($form_data['events'])) {
        $form_data['events'] = array_keys($form_data['events']);
    }

    if (!empty($service_id)) {
        $form_data['id'] = $service_id;
        local_webhooks_api::update_service($form_data);
    } else {
        local_webhooks_api::create_service($form_data);
    }

    redirect($main_page, new lang_string('changessaved', 'moodle'));
}

/* Loading service data */
if (!empty($service_id)) {
    $service = local_webhooks_api::get_service($service_id);
    $service->events = array_fill_keys($service->events, 1);
    $m_form->set_data($service);
}

/* The page title */
$title_page = new lang_string('externalservice', 'webservice');
$PAGE->navbar->add($title_page);
$PAGE->set_heading($title_page);
$PAGE->set_title($title_page);
echo $OUTPUT->header();

/* Displays the form */
$m_form->display();

/* Footer */
echo $OUTPUT->footer();