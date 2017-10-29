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
 * Defines the form of editing the service.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_webhooks;

defined("MOODLE_INTERNAL") || die();

require_once($CFG->libdir . "/formslib.php");

use report_eventlist_list_generator;
use lang_string;
use moodleform;

/**
 * Description editing form definition.
 *
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class service_edit_form extends moodleform {
    /**
     * @param string $baseurl
     */
    public function __construct($baseurl) {
        parent::__construct($baseurl);
    }

    /**
     * Preparing data before displaying.
     *
     * @param object $record
     */
    public function set_data($record) {
        if (!empty($record->events)) {
            $record->events = unserialize(gzuncompress(base64_decode($record->events)));
        }

        return parent::set_data($record);
    }

    /**
     * Defines the standard structure of the form.
     */
    protected function definition() {
        $mform =& $this->_form;
        $size = array("size" => 60);

        /* Form heading */
        $mform->addElement("header", "editserviceheader", new lang_string("service", "webservice"));

        /* Name of the service */
        $mform->addElement("text", "title", new lang_string("name", "moodle"), $size);
        $mform->addRule("title", null, "required");
        $mform->setType("title", PARAM_NOTAGS);

        /* Callback address */
        $mform->addElement("text", "url", new lang_string("url", "moodle"), $size);
        $mform->addRule("url", null, "required");
        $mform->setType("url", PARAM_URL);

        /* Enabling the service */
        $mform->addElement("advcheckbox", "enable", new lang_string("enable", "moodle"));
        $mform->setType("enable", PARAM_BOOL);
        $mform->setDefault("enable", 1);
        $mform->setAdvanced("enable");

        /* Token */
        $mform->addElement("text", "token", new lang_string("token", "webservice"), $size);
        $mform->setType("token", PARAM_NOTAGS);

        /* Additional information */
        $mform->addElement("text", "other", new lang_string("sourceext", "plugin"), $size);
        $mform->setType("other", PARAM_NOTAGS);
        $mform->setAdvanced("other");

        /* Content type */
        $contenttype = array("json" => "application/json", "x-www-form-urlencoded" => "application/x-www-form-urlencoded");
        $mform->addElement("select", "type", "Content type", $contenttype);
        $mform->setAdvanced("type");

        /* Form heading */
        $mform->addElement("header", "editserviceheaderevent", new lang_string("edulevel", "moodle"));

        /* List of events */
        $eventlist = report_eventlist_list_generator::get_all_events_list(true);
        $events = array();

        /* Formation of the list of elements */
        foreach ($eventlist as $event) {
            $events[$event["component"]][] =& $mform->createElement("checkbox", $event["eventname"], $event["eventname"]);
        }

        /* Displays groups of items */
        foreach ($events as $key => $event) {
            $mform->addGroup($event, "events", $key, "<br />", true);
        }

        /* Control Panel */
        $this->add_action_buttons(true);
    }
}