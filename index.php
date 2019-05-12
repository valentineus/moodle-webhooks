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

require_once(dirname(__DIR__, 2) . '/config.php');

global $CFG;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/webhooks/classes/local/api.php');
require_once($CFG->dirroot . '/local/webhooks/classes/output/services_table.php');
require_once($CFG->libdir . '/adminlib.php');

use local_webhooks\local\api;
use local_webhooks\output\services_table;

$deleteid = optional_param('deleteid', 0, PARAM_INT);
$hideshowid = optional_param('hideshowid', 0, PARAM_INT);

$editpage = '/local/webhooks/service.php';
$mainpage = '/local/webhooks/index.php';

$baseurl = new moodle_url($mainpage);
$context = context_system::instance();

admin_externalpage_setup('local_webhooks', '', ['contextid' => $context->contextlevel], $baseurl, []);

// Deleting the existing service.
if ($deleteid !== 0 && confirm_sesskey()) {
    api::del_service($deleteid);

    redirect($PAGE->url, new lang_string('deleted', 'moodle'));
}

// Setting status to existing service.
if ($hideshowid !== 0 && confirm_sesskey()) {
    $service = api::get_service($hideshowid);
    $service->status = !filter_var($service->status, FILTER_VALIDATE_BOOLEAN);
    api::set_service($service);

    redirect($PAGE->url, new lang_string('changessaved', 'moodle'));
}

// Page header.
$titlepage = new lang_string('pluginname', 'local_webhooks');
$PAGE->set_heading($titlepage);
$PAGE->set_title($titlepage);
echo $OUTPUT->header();

// Adds the add button.
$addserviceurl = new moodle_url($editpage, ['sesskey' => sesskey()]);
echo $OUTPUT->single_button($addserviceurl, new lang_string('add', 'moodle'));

// Separation.
echo html_writer::empty_tag('br');

// Displays the table.
$table = new services_table('local-webhooks-services');
$table->define_baseurl($baseurl);
$table->out(25, true);

// Footer.
echo $OUTPUT->footer();