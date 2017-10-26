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

require_once($CFG->libdir . "/filelib.php");

use curl;

/**
 * Defines how to work with events.
 *
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class events {
    /**
     * External handler.
     *
     * @param object $event
     */
    public static function handler($event) {
        $enable = get_config("local_webhooks", "enable");

        if (boolval($enable)) {
            $data = $event->get_data();
            self::transmitter($data);
        }
    }

    /**
     * Transmitter, processing event and services.
     *
     * @param array $data
     */
    private static function transmitter($data) {
        global $DB;

        $callbacks = $DB->get_recordset("local_webhooks_service");

        if ($callbacks->valid()) {
            foreach ($callbacks as $callback) {
                self::handler_callback($data, $callback);
            }
        }

        $callbacks->close();
    }

    /**
     * Processes each callback.
     *
     * @param array $data
     * @param object $callback
     */
    private static function handler_callback($data, $callback) {
        if ($callback->enable) {
            if (!empty($callback->events)) {
                $events = unserialize(gzuncompress(base64_decode($callback->events)));
            }

            if (boolval($events[$data["eventname"]])) {
                /* Adding to the data token */
                if (boolval($callback->token)) {
                    $data["token"] = $callback->token;
                }

                self::send($data, $callback);
            }
        }
    }

    /**
     * Sending data to the node.
     *
     * @param array $data
     * @param object $callback
     */
    private static function send($data, $callback) {
        $package = json_encode($data);

        $curl = new curl();
        $curl->setHeader(array("Content-Type: application/$callback->type"));
        $curl->post($callback->url, $package);

        return $curl->getResponse();
    }
}