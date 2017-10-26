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
        $config = get_config("local_webhooks");

        if (boolval($config->enable)) {
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
                self::handlerCallback($data, $callback);
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
    private static function handlerCallback($data, $callback) {
        if ($callback->enable) {
            $events = unserialize(gzuncompress(base64_decode($callback->events)));

            if (boolval($events[$data["eventname"]])) {
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
        $curl = new curl();
        $package = self::packup($data);
        $curl::request($callback->url, $package);
    }

    /**
     * Packs the data for transmission.
     *
     * @param array $data
     */
    private static function packup($data) {
        return json_encode($data);
    }
}