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
     * @param string $base_url
     */
    public function __construct($base_url) {
        parent::__construct($base_url);
    }

    /**
     * Defines the standard structure of the form.
     *
     * @throws \coding_exception
     */
    protected function definition() {
        $m_form =& $this->_form;
        $size = array('size' => 60);

        /* Form heading */
        $m_form->addElement('header', 'edit-service-header-main', new lang_string('service', 'webservice'));

        /* Name of the service */
        $m_form->addElement('text', 'name', new lang_string('name', 'moodle'), $size);
        $m_form->addRule('name', null, 'required');
        $m_form->setType('name', PARAM_RAW);

        /* Callback address */
        $m_form->addElement('text', 'point', new lang_string('url', 'moodle'), $size);
        $m_form->addRule('point', null, 'required');
        $m_form->setType('point', PARAM_URL);

        /* Enabling the service */
        $m_form->addElement('advcheckbox', 'status', new lang_string('enable', 'moodle'));
        $m_form->setType('status', PARAM_BOOL);
        $m_form->setDefault('status', 1);
        $m_form->setAdvanced('status');

        /* Token */
        $m_form->addElement('text', 'token', new lang_string('token', 'webservice'), $size);
        $m_form->addRule('token', null, 'required');
        $m_form->setType('token', PARAM_RAW);

        /* Content type */
        $content_type = array(
            'application/json'                  => 'application/json',
            'application/x-www-form-urlencoded' => 'application/x-www-form-urlencoded',
        );

        $m_form->addElement('select', 'header', 'Content-Type', $content_type);
        $m_form->setAdvanced('header');

        /* Form heading */
        $m_form->addElement('header', 'edit-service-header-event', new lang_string('edulevel', 'moodle'));

        /* List of events */
        $event_list = report_eventlist_list_generator::get_all_events_list(true);

        $events = array();
        foreach ($event_list as $event) {
            $events[$event['component']][] =& $m_form->createElement('checkbox', $event['eventname'], $event['eventname']);
        }

        foreach ($events as $key => $event) {
            $m_form->addGroup($event, 'events', $key, '<br />', true);
        }

        /* Control Panel */
        $this->add_action_buttons(true);
    }
}