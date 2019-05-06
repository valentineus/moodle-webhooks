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

namespace local_webhooks\task;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/webhooks/classes/local/api.php');

use core\task\adhoc_task;
use curl;
use local_webhooks\local\api;
use local_webhooks\local\record;
use function defined;
use function is_object;
use function is_string;

/**
 * Class for processing events.
 *
 * @package local_webhooks\task
 */
final class notify extends adhoc_task {
    /**
     * Debug information.
     *
     * @var array
     */
    public $debug;

    /**
     * Process an event.
     *
     * @throws \dml_exception
     */
    public function execute() {
        $event = $this->get_custom_data();

        if (!is_object($event) || !isset($event->eventname) || !is_string($event->eventname)) {
            return;
        }

        foreach (api::get_services_by_event($event->eventname) as $service) {
            if (!is_object($service)) {
                continue;
            }

            if (!$service->status) {
                continue;
            }

            $this->post($service, array_merge((array) $event, [
                'token' => $service->token,
            ]));
        }
    }

    /**
     * Send a request for the service.
     *
     * @param \local_webhooks\local\record $service
     * @param array                        $data
     */
    public function post(record $service, array $data) {
        $curl = new curl();
        $curl->setHeader(['Content-Type: ' . $service->header]);
        $curl->post($service->point, $data);

        if (defined('PHPUNIT_TEST') && PHPUNIT_TEST) {
            $this->debug = array_merge($this->debug ?? [], [
                compact('data', 'service'),
            ]);
        }
    }
}