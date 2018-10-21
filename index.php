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
 * Service list manager.
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */

require_once __DIR__ . '/../../config.php';

require_once $CFG->dirroot . '/local/webhooks/classes/ui_tables_plugin.php';
require_once $CFG->dirroot . '/local/webhooks/lib.php';
require_once $CFG->libdir . '/adminlib.php';

$deleteId = optional_param('deleteid', 0, PARAM_INT);
$hideShowId = optional_param('hideshowid', 0, PARAM_INT);

$editPage = '/local/webhooks/service.php';
$mainPage = '/local/webhooks/index.php';
$baseUrl = new moodle_url($mainPage);

admin_externalpage_setup('local_webhooks', '', null, $baseUrl, array());
$context = context_system::instance();

/* Remove the service */
if (!empty($deleteId) && confirm_sesskey()) {
    local_webhooks_api::delete_service($deleteId);
    redirect($PAGE->url, new lang_string('deleted', 'moodle'));
}

/* Disable / Enable the service */
if (!empty($hideShowId) && confirm_sesskey()) {
    $service = local_webhooks_api::get_service($hideShowId);
    $service->status = !(bool) $service->status;
    local_webhooks_api::update_service((array) $service);
    redirect($PAGE->url, new lang_string('changessaved', 'moodle'));
}

/* The page title */
$titlePage = new lang_string('pluginname', 'local_webhooks');
$PAGE->set_heading($titlePage);
$PAGE->set_title($titlePage);
echo $OUTPUT->header();

/* Displays the table */
$table = new local_webhooks_services_table('local-webhooks-table');
$table->define_baseurl($baseUrl);
$table->out(25, true);

/* Separation */
echo html_writer::empty_tag('br');

/* Adds the add button */
$addServiceUrl = new moodle_url($editPage, array('sesskey' => sesskey()));
echo $OUTPUT->single_button($addServiceUrl, new lang_string('add', 'moodle'));

/* Footer */
echo $OUTPUT->footer();