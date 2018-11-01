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
 * Registration of the system of events.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_webhooks\event;

defined('MOODLE_INTERNAL') || die();

use core\event\base;
use lang_string;
use moodle_url;

/**
 * Defines how to work with events.
 *
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class service_added extends base {
    /**
     * Return localised event name.
     *
     * @throws \coding_exception
     */
    public static function get_name() {
        return new lang_string('create', 'moodle');
    }

    /**
     * Returns description of what happened.
     *
     * @throws \coding_exception
     */
    public function get_description() {
        return new lang_string('eventwebserviceservicecreated', 'webservice');
    }

    /**
     * Get URL related to the action.
     *
     * @throws \moodle_exception
     */
    public function get_url() {
        return new moodle_url('/local/webhooks/editservice.php', array('serviceid' => $this->objectid));
    }

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_webhooks_service';
    }
}