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

require_once(__DIR__ . '/../../config.php');

require_once($CFG->dirroot . '/local/webhooks/classes/ui_tables_plugin.php');
require_once($CFG->dirroot . '/local/webhooks/lib.php');
require_once($CFG->libdir . '/adminlib.php');

$deleteid = optional_param('deleteid', 0, PARAM_INT);
$hideshowid = optional_param('hideshowid', 0, PARAM_INT);

$editpage = '/local/webhooks/service.php';
$mainpage = '/local/webhooks/index.php';
$baseurl = new moodle_url($mainpage);

admin_externalpage_setup('local_webhooks', '', null, $baseurl, array());
$context = context_system::instance();

/* Remove the service */
if (!empty($deleteid) && confirm_sesskey()) {
    local_webhooks_api::delete_service($deleteid);
    redirect($PAGE->url, new lang_string('deleted', 'moodle'));
}

/* Disable / Enable the service */
if (!empty($hideshowid) && confirm_sesskey()) {
    $service = local_webhooks_api::get_service($hideshowid);
    $service->status = !(bool) $service->status;
    local_webhooks_api::update_service((array) $service);
    redirect($PAGE->url, new lang_string('changessaved', 'moodle'));
}

/* The page title */
$titlepage = new lang_string('pluginname', 'local_webhooks');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

/* Displays the table */
$table = new local_webhooks_services_table('local-webhooks-table');
$table->define_baseurl($baseurl);
$table->out(25, true);

/* Separation */
echo html_writer::empty_tag('br');

/* Adds the add button */
$addserviceurl = new moodle_url($editpage, array('sesskey' => sesskey()));
echo $OUTPUT->single_button($addserviceurl, new lang_string('add', 'moodle'));

/* Footer */
echo $OUTPUT->footer();