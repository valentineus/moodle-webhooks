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
 * The event handler.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_webhooks;

defined("MOODLE_INTERNAL") || die();

require_once(__DIR__ . "/../lib.php");

/**
 * Defines event handlers.
 *
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class handler {
    /**
     * External handler.
     *
     * @param object $event
     */
    public static function events($event) {
        $data = $event->get_data();

        if ($callbacks = local_webhooks_get_list_records()) {
            foreach ($callbacks as $callback) {
                if (boolval($callback->enable) && !empty($callback->events[$data["eventname"]])) {
                    local_webhooks_send_request($data, $callback);
                }
            }
        }
    }
}