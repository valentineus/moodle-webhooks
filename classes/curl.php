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
 * Method of sending data.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_webhooks;

defined("MOODLE_INTERNAL") || die();

/**
 * Wrapper over cURL.
 *
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class curl {
    /**
     * The class constructor.
     */
    public function __construct() {
        if (!function_exists("curl_init")) {
            print_error("nocurl", "mnet");
        }
    }

    /**
     * Easy data sending.
     *
     * @param object $callback
     * @param string $data
     */
    public static function request($callback, $data) {
        $ch = curl_init($callback->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                "Content-Type: application/$callback->type",
                "Content-Length: " . mb_strlen($data, "UTF-8")
            )
        );

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}