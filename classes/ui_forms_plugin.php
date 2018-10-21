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

require_once $CFG->dirroot . '/local/webhooks/lib.php';
require_once $CFG->libdir . '/formslib.php';

/**
 * Description editing form definition.
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */
class local_webhooks_service_edit_form extends moodleform {
    /**
     * @param string $baseUrl
     */
    public function __construct($baseUrl) {
        parent::__construct($baseUrl);
    }

    /**
     * Defines the standard structure of the form.
     *
     * @throws \coding_exception
     */
    protected function definition() {
        $mForm =& $this->_form;
        $size = array('size' => 60);

        /* Form heading */
        $mForm->addElement('header', 'editserviceheadermain', new lang_string('service', 'webservice'));

        /* Name of the service */
        $mForm->addElement('text', 'name', new lang_string('name', 'moodle'), $size);
        $mForm->addRule('name', null, 'required');
        $mForm->setType('name', PARAM_RAW);

        /* Callback address */
        $mForm->addElement('text', 'point', new lang_string('url', 'moodle'), $size);
        $mForm->addRule('point', null, 'required');
        $mForm->setType('point', PARAM_URL);

        /* Enabling the service */
        $mForm->addElement('advcheckbox', 'status', new lang_string('enable', 'moodle'));
        $mForm->setType('status', PARAM_BOOL);
        $mForm->setDefault('status', 1);
        $mForm->setAdvanced('status');

        /* Token */
        $mForm->addElement('text', 'token', new lang_string('token', 'webservice'), $size);
        $mForm->addRule('token', null, 'required');
        $mForm->setType('token', PARAM_RAW);

        /* Content type */
        $contentType = array(
            'application/json'                  => 'application/json',
            'application/x-www-form-urlencoded' => 'application/x-www-form-urlencoded',
        );

        $mForm->addElement('select', 'header', 'Content-Type', $contentType);
        $mForm->setAdvanced('header');

        /* Form heading */
        $mForm->addElement('header', 'editserviceheaderevent', new lang_string('edulevel', 'moodle'));

        /* List of events */
        $eventList = report_eventlist_list_generator::get_all_events_list(true);

        $events = array();
        foreach ($eventList as $event) {
            $events[$event['component']][] =& $mForm->createElement('checkbox', $event['eventname'], $event['eventname']);
        }

        foreach ($events as $key => $event) {
            $mForm->addGroup($event, 'events', $key, '<br />', true);
        }

        /* Control Panel */
        $this->add_action_buttons(true);
    }
}