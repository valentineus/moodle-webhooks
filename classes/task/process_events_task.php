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
 * Defining task handlers.
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */

namespace local_webhooks\task;

global $CFG;

require_once $CFG->dirroot . '/local/webhooks/lib.php';

defined('MOODLE_INTERNAL') || die();

/**
 * Class process_events_task
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks\task
 */
class process_events_task extends \core\task\adhoc_task {
    /**
     * Task handler.
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function execute() {
        $services = \local_webhooks_api::get_services_by_event($this->get_custom_data()->eventname);

        foreach ($services as $service) {
            if ((bool) $service->status !== true) {
                return;
            }

            $curl = new \curl();

            $event = (array) $this->get_custom_data();
            $event['token'] = $service->token;

            $curl->setHeader(array('Content-Type: ' . $service->header));
            $curl->post($service->point, json_encode($event));

            // TODO: Mark the log
            $curl->getResponse();
        }
    }
}