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

require_once($CFG->dirroot . '/local/webhooks/lib.php');

/**
 * Class local_webhooks_lib_testcase
 *
 * @copyright 2019 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */
final class local_webhooks_lib_testcase extends advanced_testcase {
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

        $data = [
            'events' => [
                '\core\event\course_created',
                '\core\event\course_deleted',
                '\core\event\course_updated',
                '\core\event\course_viewed',
            ],
            'header' => 'application/json',
            'name'   => 'Example name',
            'point'  => 'http://example.org/',
            'status' => 1,
            'token'  => '967b2286-0874-4938-b088-efdbcf8a79bc',
        ];

        $serviceid = local_webhooks_api::create_service($data);

        $events = $DB->get_records(LW_TABLE_EVENTS);
        $services = $DB->get_records(LW_TABLE_SERVICES);

        self::assertCount(1, $services);
        $service = array_shift($services);

        self::assertEquals($data['header'], $service->header);
        self::assertEquals($data['name'], $service->name);
        self::assertEquals($data['point'], $service->point);
        self::assertEquals($data['status'], (int) $service->status);
        self::assertEquals($data['token'], $service->token);
        self::assertEquals($serviceid, $service->id);

        self::assertCount(count($data['events']), $events);
        foreach ($events as $event) {
            self::assertEquals($serviceid, $event->serviceid);
            self::assertContains($event->name, $data['events']);
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

        $data = [
            'events' => [
                '\core\event\course_created',
                '\core\event\course_deleted',
                '\core\event\course_updated',
                '\core\event\course_viewed',
            ],
            'header' => 'application/json',
            'name'   => 'Example name',
            'point'  => 'http://example.org/',
            'status' => 1,
            'token'  => '967b2286-0874-4938-b088-efdbcf8a79bc',
        ];

        $serviceid = local_webhooks_api::create_service($data);
        self::assertTrue(local_webhooks_api::delete_service($serviceid));
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

        $data = [
            'events' => [
                '\core\event\course_created',
                '\core\event\course_deleted',
                '\core\event\course_updated',
                '\core\event\course_viewed',
            ],
            'header' => 'application/json',
            'name'   => 'Example name',
            'point'  => 'http://example.org/',
            'status' => 1,
            'token'  => '967b2286-0874-4938-b088-efdbcf8a79bc',
        ];

        $serviceid = local_webhooks_api::create_service($data);
        $service = local_webhooks_api::get_service($serviceid);

        self::assertEquals($data['header'], $service->header);
        self::assertEquals($data['name'], $service->name);
        self::assertEquals($data['point'], $service->point);
        self::assertEquals($data['status'], (int) $service->status);
        self::assertEquals($data['token'], $service->token);
        self::assertEquals($serviceid, $service->id);

        self::assertInternalType('array', $service->events);
        self::assertCount(count($data['events']), $service->events);
        foreach ($service->events as $event) {
            self::assertContains($event, $data['events']);
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

        $data = [
            'events' => [
                '\core\event\course_created',
                '\core\event\course_deleted',
                '\core\event\course_updated',
                '\core\event\course_viewed',
            ],
            'header' => 'application/json',
            'name'   => 'Example name',
            'point'  => 'http://example.org/',
            'status' => 1,
            'token'  => '967b2286-0874-4938-b088-efdbcf8a79bc',
        ];

        $ids = [];
        $total = random_int(5, 20);
        for ($i = 0; $i < $total; $i++) {
            $ids[] = local_webhooks_api::create_service($data);
        }

        $services = local_webhooks_api::get_services();
        self::assertCount(count($ids), $services);

        foreach ($services as $service) {
            self::assertContains($service->id, $ids);
            self::assertEquals($data['header'], $service->header);
            self::assertEquals($data['name'], $service->name);
            self::assertEquals($data['point'], $service->point);
            self::assertEquals($data['status'], $service->status);
            self::assertEquals($data['token'], $service->token);

            self::assertInternalType('array', $service->events);
            self::assertCount(count($data['events']), $service->events);
            foreach ($service->events as $event) {
                self::assertContains($event, $data['events']);
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

        $data = [
            'events' => [
                '\core\event\course_created',
                '\core\event\course_deleted',
                '\core\event\course_updated',
                '\core\event\course_viewed',
            ],
            'header' => 'application/json',
            'name'   => 'Example name',
            'point'  => 'http://example.org/',
            'status' => 1,
            'token'  => '967b2286-0874-4938-b088-efdbcf8a79bc',
        ];

        $ids = [];
        $total = random_int(5, 20);
        for ($i = 0; $i < $total; $i++) {
            $ids[] = local_webhooks_api::create_service($data);
        }

        $eventname = $data['events'][random_int(1, count($data['events']) - 1)];
        $services = local_webhooks_api::get_services_by_event($eventname);
        self::assertCount(count($ids), $services);

        foreach ($services as $service) {
            self::assertContains($service->id, $ids);
            self::assertEquals($data['header'], $service->header);
            self::assertEquals($data['name'], $service->name);
            self::assertEquals($data['point'], $service->point);
            self::assertEquals($data['status'], $service->status);
            self::assertEquals($data['token'], $service->token);

            self::assertInternalType('array', $service->events);
            self::assertCount(count($data['events']), $service->events);
            foreach ($service->events as $event) {
                self::assertContains($event, $data['events']);
            }
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

        $data = [
            'events' => [
                '\core\event\course_created',
                '\core\event\course_deleted',
                '\core\event\course_updated',
                '\core\event\course_viewed',
            ],
            'header' => 'application/json',
            'status' => 1,
            'token'  => '967b2286-0874-4938-b088-efdbcf8a79bc',
        ];

        $total = random_int(5, 20);
        for ($i = 0; $i < $total; $i++) {
            local_webhooks_api::create_service(array_merge($data, [
                'name'  => 'Example name #' . $i,
                'point' => 'http://example.org/test_' . $i,
            ]));
        }

        self::assertCount(1, local_webhooks_api::get_services([
            'name' => 'Example name #' . random_int(1, $total),
        ]));

        $limit = random_int(1, $total);
        self::assertCount($limit, local_webhooks_api::get_services([], 1, $limit));
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

        $data1 = [
            'events' => [
                '\core\event\course_created',
                '\core\event\course_deleted',
                '\core\event\course_updated',
                '\core\event\course_viewed',
            ],
            'header' => 'application/json',
            'name'   => 'Example name',
            'point'  => 'http://example.org/',
            'status' => 1,
            'token'  => '967b2286-0874-4938-b088-efdbcf8a79bc',
        ];

        $data2 = [
            'events' => [
                '\core\event\calendar_event_created' => '1',
                '\core\event\calendar_event_deleted' => '1',
                '\core\event\calendar_event_updated' => '1',
            ],
            'header' => 'application/x-www-form-urlencoded',
            'name'   => 'New name',
            'point'  => 'http://domain.local/example',
            'status' => 0,
            'token'  => 'add62250-2f03-49a9-97c4-6cd73a79e83b',
        ];

        $serviceid = local_webhooks_api::create_service($data1);
        self::assertTrue(local_webhooks_api::update_service(array_merge($data2, ['id' => $serviceid])));

        $events = $DB->get_records(LW_TABLE_EVENTS);
        $services = $DB->get_records(LW_TABLE_SERVICES);

        self::assertCount(1, $services);
        $service = array_shift($services);

        self::assertEquals($data2['header'], $service->header);
        self::assertEquals($data2['name'], $service->name);
        self::assertEquals($data2['point'], $service->point);
        self::assertEquals($data2['status'], (int) $service->status);
        self::assertEquals($data2['token'], $service->token);
        self::assertEquals($serviceid, $service->id);

        self::assertCount(count($data2['events']), $events);
        foreach ($events as $event) {
            self::assertEquals($serviceid, $event->serviceid);
            self::assertContains($event->name, $data2['events']);
        }
    }
}