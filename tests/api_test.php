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

use local_webhooks\local\api;
use local_webhooks\local\record;

/**
 * Class local_webhooks_api_testcase.
 *
 * @copyright 2019 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class local_webhooks_api_testcase extends advanced_testcase {
    /**
     * Testing creation of the service.
     *
     * @group local_webhooks
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_adding() {
        global $DB;

        $this->resetAfterTest();

        $record = new record();
        $record->header = 'application/json';
        $record->name = 'Example name';
        $record->point = 'http://example.org/';
        $record->status = true;
        $record->token = '967b2286-0874-4938-b088-efdbcf8a79bc';
        $record->events = [
            '\core\event\course_created',
            '\core\event\course_deleted',
            '\core\event\course_updated',
            '\core\event\course_viewed',
        ];

        $record->id = api::create_service($record);

        $events = $DB->get_records(LW_TABLE_EVENTS);
        $services = $DB->get_records(LW_TABLE_SERVICES);

        self::assertCount(1, $services);
        $service = array_shift($services);

        self::assertEquals($record->header, $service->header);
        self::assertEquals($record->id, $service->id);
        self::assertEquals($record->name, $service->name);
        self::assertEquals($record->point, $service->point);
        self::assertEquals($record->status, (bool) $service->status);
        self::assertEquals($record->token, $service->token);

        self::assertCount(count($record->events), $events);
        foreach ($events as $event) {
            self::assertContains($event->name, $record->events);
            self::assertEquals($record->id, $event->serviceid);
        }
    }

    /**
     * Test deletion of the service.
     *
     * @group local_webhooks
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_deleting() {
        global $DB;

        $this->resetAfterTest();

        $record = new record();
        $record->header = 'application/json';
        $record->name = 'Example name';
        $record->point = 'http://example.org/';
        $record->status = true;
        $record->token = '967b2286-0874-4938-b088-efdbcf8a79bc';
        $record->events = [
            '\core\event\course_created',
            '\core\event\course_deleted',
            '\core\event\course_updated',
            '\core\event\course_viewed',
        ];

        $record->id = api::create_service($record);
        self::assertTrue(api::delete_service($record->id));
        self::assertCount(0, $DB->get_records(LW_TABLE_EVENTS));
        self::assertCount(0, $DB->get_records(LW_TABLE_SERVICES));
    }

    /**
     * Testing get to a service.
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_get_service() {
        $this->resetAfterTest();

        $record = new record();
        $record->header = 'application/json';
        $record->name = 'Example name';
        $record->point = 'http://example.org/';
        $record->status = true;
        $record->token = '967b2286-0874-4938-b088-efdbcf8a79bc';
        $record->events = [
            '\core\event\course_created',
            '\core\event\course_deleted',
            '\core\event\course_updated',
            '\core\event\course_viewed',
        ];

        $record->id = api::create_service($record);
        $service = api::get_service($record->id);

        self::assertEquals($record->header, $service->header);
        self::assertEquals($record->id, $service->id);
        self::assertEquals($record->name, $service->name);
        self::assertEquals($record->point, $service->point);
        self::assertEquals($record->status, (bool) $service->status);
        self::assertEquals($record->token, $service->token);

        self::assertInternalType('array', $service->events);
        self::assertCount(count($record->events), $service->events);
        foreach ($service->events as $event) {
            self::assertContains($event, $record->events);
        }
    }

    /**
     * Testing get to the list services.
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_get_services() {
        $this->resetAfterTest();

        $record = new record();
        $record->header = 'application/json';
        $record->name = 'Example name';
        $record->point = 'http://example.org/';
        $record->status = true;
        $record->token = '967b2286-0874-4938-b088-efdbcf8a79bc';
        $record->events = [
            '\core\event\course_created',
            '\core\event\course_deleted',
            '\core\event\course_updated',
            '\core\event\course_viewed',
        ];

        $ids = [];
        $total = random_int(5, 20);
        for ($i = 0; $i < $total; $i++) {
            $ids[] = api::create_service($record);
        }

        $services = api::get_services();
        self::assertCount(count($ids), $services);

        foreach ($services as $service) {
            self::assertContains($service->id, $ids);
            self::assertEquals($record->header, $service->header);
            self::assertEquals($record->name, $service->name);
            self::assertEquals($record->point, $service->point);
            self::assertEquals($record->status, $service->status);
            self::assertEquals($record->token, $service->token);

            self::assertInternalType('array', $service->events);
            self::assertCount(count($record->events), $service->events);
            foreach ($service->events as $event) {
                self::assertContains($event, $record->events);
            }
        }
    }

    /**
     * Testing get to the list services by event name.
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_get_services_by_event() {
        $this->resetAfterTest();

        $record = new record();
        $record->header = 'application/json';
        $record->name = 'Example name';
        $record->point = 'http://example.org/';
        $record->status = true;
        $record->token = '967b2286-0874-4938-b088-efdbcf8a79bc';
        $record->events = [
            '\core\event\course_created',
            '\core\event\course_deleted',
            '\core\event\course_updated',
            '\core\event\course_viewed',
        ];

        $ids = [];
        $total = random_int(5, 20);
        for ($i = 0; $i < $total; $i++) {
            $ids[] = api::create_service($record);
        }

        $eventname = $record->events[random_int(1, count($record->events) - 1)];
        $services = api::get_services_by_event($eventname);
        self::assertCount(count($ids), $services);

        foreach ($services as $service) {
            self::assertContains($service->id, $ids);
        }
    }

    /**
     * Testing get to the list services with conditions.
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_get_services_with_conditions() {
        $this->resetAfterTest();

        $record = new record();
        $record->header = 'application/json';
        $record->status = true;
        $record->token = '967b2286-0874-4938-b088-efdbcf8a79bc';
        $record->events = [
            '\core\event\course_created',
            '\core\event\course_deleted',
            '\core\event\course_updated',
            '\core\event\course_viewed',
        ];

        $total = random_int(5, 20);
        for ($i = 0; $i < $total; $i++) {
            $record->name = 'Example name #' . $i;
            $record->point = 'http://example.org/test_' . $i;
            api::create_service($record);
        }

        self::assertCount(1, api::get_services([
            'name' => 'Example name #' . random_int(5, $total),
        ]));

        $limit = intdiv($total, 2);
        self::assertCount($limit, api::get_services([], 1, $limit));
    }

    /**
     * Testing of the service update.
     *
     * @group local_webhooks
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_updating() {
        global $DB;

        $this->resetAfterTest();

        $record1 = new record();
        $record1->header = 'application/json';
        $record1->name = 'Example name';
        $record1->point = 'http://example.org/';
        $record1->status = true;
        $record1->token = '967b2286-0874-4938-b088-efdbcf8a79bc';
        $record1->events = [
            '\core\event\course_created',
            '\core\event\course_deleted',
            '\core\event\course_updated',
            '\core\event\course_viewed',
        ];

        $record2 = new record();
        $record2->header = 'application/x-www-form-urlencoded';
        $record2->name = 'New name';
        $record2->point = 'http://domain.local/example';
        $record2->status = false;
        $record2->token = 'add62250-2f03-49a9-97c4-6cd73a79e83b';
        $record2->events = [
            '\core\event\calendar_event_created',
            '\core\event\calendar_event_deleted',
            '\core\event\calendar_event_updated',
        ];

        $record2->id = api::create_service($record1);
        self::assertTrue(api::update_service($record2));

        $events = $DB->get_records(LW_TABLE_EVENTS);
        $services = $DB->get_records(LW_TABLE_SERVICES);

        self::assertCount(1, $services);
        $service = array_shift($services);

        self::assertEquals($record2->header, $service->header);
        self::assertEquals($record2->id, $service->id);
        self::assertEquals($record2->name, $service->name);
        self::assertEquals($record2->point, $service->point);
        self::assertEquals($record2->status, (bool) $service->status);
        self::assertEquals($record2->token, $service->token);

        self::assertCount(count($record2->events), $events);
        foreach ($events as $event) {
            self::assertContains($event->name, $record2->events);
            self::assertEquals($record2->id, $event->serviceid);
        }
    }
}