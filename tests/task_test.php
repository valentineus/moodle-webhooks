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

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/webhooks/classes/local/api.php');

use core\task\manager;
use local_webhooks\local\api;
use local_webhooks\local\record;
use local_webhooks\task\notify;

/**
 * Class local_webhooks_task_testcase.
 *
 * @copyright 2019 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class local_webhooks_task_testcase extends advanced_testcase {
    /**
     * Testing add a task to the queue.
     */
    public function test_add_task() {
        global $DB;

        $DB->delete_records('task_adhoc');
        $this->resetAfterTest();

        manager::queue_adhoc_task(new notify());

        $tasks = manager::get_adhoc_tasks('\local_webhooks\task\notify');

        self::assertCount(1, $tasks);
        self::assertInstanceOf('\local_webhooks\task\notify', array_shift($tasks));
    }

    /**
     * Testing disabled processing service.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_execute_disabled() {
        global $DB;

        $DB->delete_records('task_adhoc');
        curl::mock_response('{}');
        $this->resetAfterTest();

        $record = new record();
        $record->events = ['\core\event\course_viewed'];
        $record->header = 'application/json';
        $record->name = 'Example name';
        $record->point = 'http://example.org/';
        $record->status = false;
        $record->token = '967b2286-0874-4938-b088-efdbcf8a79bc';

        api::create_service($record);

        $task = new notify();
        $task->set_custom_data(['eventname' => '\core\event\course_viewed']);
        $task->execute();

        self::assertNull($task->debug);
    }

    /**
     * Testing enabled processing service.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_execute_enabled() {
        global $DB;

        $DB->delete_records('task_adhoc');
        curl::mock_response('{}');
        $this->resetAfterTest();

        $record = new record();
        $record->events = ['\core\event\course_viewed'];
        $record->header = 'application/json';
        $record->name = 'Example name';
        $record->point = 'http://example.org/';
        $record->status = true;
        $record->token = '967b2286-0874-4938-b088-efdbcf8a79bc';

        $record->id = api::create_service($record);

        $task = new notify();
        $task->set_custom_data(['eventname' => '\core\event\course_viewed']);
        $task->execute();

        self::assertCount(1, $task->debug);
        self::assertInternalType('array', $task->debug);

        $element = array_shift($task->debug);
        self::assertInternalType('array', $element['data']);
        self::assertInternalType('object', $element['service']);

        self::assertEquals($record->events[0], $element['data']['eventname']);
        self::assertEquals($record->id, $element['service']->id);
        self::assertEquals($record->token, $element['data']['token']);
    }

    /**
     * Testing count creating tasks.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_observer_multiple() {
        global $DB;

        $DB->delete_records('task_adhoc');
        curl::mock_response('{}');
        $this->resetAfterTest();

        $generator = self::getDataGenerator();

        $record = new record();
        $record->events = ['\core\event\course_created'];
        $record->header = 'application/json';
        $record->name = 'Example name';
        $record->point = 'http://example.org/';
        $record->status = true;
        $record->token = '967b2286-0874-4938-b088-efdbcf8a79bc';

        api::create_service($record);

        $total = random_int(5, 20);
        for ($i = 0; $i < $total; $i++) {
            $generator->create_course();
        }

        $debug = [];
        foreach (manager::get_adhoc_tasks(notify::class) as $event) {
            if (!is_object($event)) {
                continue;
            }

            /** @var \local_webhooks\task\notify $event */
            $event->execute();

            if (isset($event->debug) && is_array($event->debug)) {
                $debug[] = array_merge(...$event->debug);
            }
        }

        self::assertCount($total, $debug);
    }

    /**
     * Testing structure a creating task.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_observer_single() {
        global $DB;

        $DB->delete_records('task_adhoc');
        curl::mock_response('{}');
        $this->resetAfterTest();

        $generator = self::getDataGenerator();

        $record = new record();
        $record->events = ['\core\event\course_created'];
        $record->header = 'application/json';
        $record->name = 'Example name';
        $record->point = 'http://example.org/';
        $record->status = true;
        $record->token = '967b2286-0874-4938-b088-efdbcf8a79bc';

        api::create_service($record);

        $course = $generator->create_course();

        $debug = [];
        foreach (manager::get_adhoc_tasks(notify::class) as $event) {
            if (!is_object($event)) {
                continue;
            }

            /** @var \local_webhooks\task\notify $event */
            $event->execute();

            if (isset($event->debug) && is_array($event->debug)) {
                $debug[] = array_merge(...$event->debug);
            }
        }

        self::assertCount(1, $debug);
        $element = array_shift($debug);

        self::assertInternalType('array', $element['data']);
        self::assertEquals($course->id, $element['data']['courseid']);
    }
}