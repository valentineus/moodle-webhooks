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
 * Defines forms.
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/webhooks/lib.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * Description editing form definition.
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */
class local_webhooks_service_edit_form extends moodleform {
    /**
     * @param string $baseurl
     */
    public function __construct($baseurl) {
        parent::__construct($baseurl);
    }

    /**
     * Defines the standard structure of the form.
     *
     * @throws \coding_exception
     */
    protected function definition() {
        $mform =& $this->_form;
        $size = array('size' => 60);

        /* Form heading */
        $mform->addElement('header', 'edit-service-header-main', new lang_string('service', 'webservice'));

        /* Name of the service */
        $mform->addElement('text', 'name', new lang_string('name', 'moodle'), $size);
        $mform->addRule('name', null, 'required');
        $mform->setType('name', PARAM_RAW);

        /* Callback address */
        $mform->addElement('text', 'point', new lang_string('url', 'moodle'), $size);
        $mform->addRule('point', null, 'required');
        $mform->setType('point', PARAM_URL);

        /* Enabling the service */
        $mform->addElement('advcheckbox', 'status', new lang_string('enable', 'moodle'));
        $mform->setType('status', PARAM_BOOL);
        $mform->setDefault('status', 1);
        $mform->setAdvanced('status');

        /* Token */
        $mform->addElement('text', 'token', new lang_string('token', 'webservice'), $size);
        $mform->addRule('token', null, 'required');
        $mform->setType('token', PARAM_RAW);

        /* Content type */
        $contenttype = array(
            'application/json'                  => 'application/json',
            'application/x-www-form-urlencoded' => 'application/x-www-form-urlencoded',
        );

        $mform->addElement('select', 'header', 'Content-Type', $contenttype);
        $mform->setAdvanced('header');

        /* Form heading */
        $mform->addElement('header', 'edit-service-header-event', new lang_string('edulevel', 'moodle'));

        /* List of events */
        $eventlist = report_eventlist_list_generator::get_all_events_list(true);

        $events = array();
        foreach ($eventlist as $event) {
            $events[$event['component']][] =& $mform->createElement('checkbox', $event['eventname'], $event['eventname']);
        }

        foreach ($events as $key => $event) {
            $mform->addGroup($event, 'events', $key, '<br />', true);
        }

        /* Control Panel */
        $this->add_action_buttons(true);
    }
}