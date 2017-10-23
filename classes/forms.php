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
     * Defines the standard structure of the form.
     */
    protected function definition() {
        $mform =& $this->_form;

        /* Form heading */
        $mform->addElement("header", "editserviceheader",
            new lang_string("editserviceheader", "local_webhooks"));

        /* Name of the service */
        $mform->addElement("text", "title",
            new lang_string("editservicetitle", "local_webhooks"),
            array("size" => 60));
        $mform->setType("title", PARAM_NOTAGS);
        $mform->addRule("title", null, "required");

        /* Callback address */
        $mform->addElement("text", "url",
            new lang_string("editserviceurl", "local_webhooks"),
            array("size" => 60));
        $mform->setType("url", PARAM_URL);
        $mform->addRule("url", null, "required");

        /* Enabling the service */
        $mform->addElement("checkbox", "enable",
            new lang_string("editserviceenable", "local_webhooks"));
        $mform->setType("enable", PARAM_BOOL);
        $mform->setDefault("enable", 1);
        $mform->setAdvanced("enable");

        /* Control Panel */
        $this->add_action_buttons(true);
    }
}