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

namespace local_webhooks\event;

defined('MOODLE_INTERNAL') || die();

use core\event\base as event;
use core\task\manager;
use local_webhooks\task\notify;
use function defined;

/**
 * Class observer.
 *
 * @copyright 2019 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks\event
 */
final class observer {
    /**
     * Main handler for events.
     *
     * @param \core\event\base $event
     */
    public static function handler(event $event) {
        $task = new notify();
        $task->set_custom_data($event->get_data());
        manager::queue_adhoc_task($task);
    }
}