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

        // Check the table of services.
        self::assertCount(1, $services);
        $service = array_shift($services);

        self::assertEquals($data['header'], $service->header);
        self::assertEquals($data['name'], $service->name);
        self::assertEquals($data['point'], $service->point);
        self::assertEquals($data['status'], (int) $service->status);
        self::assertEquals($data['token'], $service->token);
        self::assertEquals($serviceid, $service->id);

        // Check the table of events.
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

        // Creating a service.
        $serviceid = local_webhooks_api::create_service($data);

        $events = $DB->get_records(LW_TABLE_EVENTS);
        $services = $DB->get_records(LW_TABLE_SERVICES);

        // Check the table of services.
        self::assertCount(1, $services);
        $service = array_shift($services);

        self::assertEquals($data['header'], $service->header);
        self::assertEquals($data['name'], $service->name);
        self::assertEquals($data['point'], $service->point);
        self::assertEquals($data['status'], (int) $service->status);
        self::assertEquals($data['token'], $service->token);
        self::assertEquals($serviceid, $service->id);

        // Check the table of events.
        self::assertCount(count($data['events']), $events);

        foreach ($events as $event) {
            self::assertEquals($serviceid, $event->serviceid);
            self::assertContains($event->name, $data['events']);
        }

        // Deleting a service.
        self::assertTrue(local_webhooks_api::delete_service($serviceid));
        self::assertCount(0, $DB->get_records(LW_TABLE_EVENTS));
        self::assertCount(0, $DB->get_records(LW_TABLE_SERVICES));
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

        // Creating a service.
        $serviceid = local_webhooks_api::create_service($data1);
        $events = $DB->get_records(LW_TABLE_EVENTS);
        $services = $DB->get_records(LW_TABLE_SERVICES);

        // Check the table of services.
        self::assertCount(1, $services);
        $service = array_shift($services);

        self::assertEquals($data1['header'], $service->header);
        self::assertEquals($data1['name'], $service->name);
        self::assertEquals($data1['point'], $service->point);
        self::assertEquals($data1['status'], (int) $service->status);
        self::assertEquals($data1['token'], $service->token);
        self::assertEquals($serviceid, $service->id);

        // Check the table of events.
        self::assertCount(count($data1['events']), $events);

        foreach ($events as $event) {
            self::assertEquals($serviceid, $event->serviceid);
            self::assertContains($event->name, $data1['events']);
        }

        // Updating a service.
        self::assertTrue(local_webhooks_api::update_service(array_merge($data2, ['id' => $serviceid])));

        $events = $DB->get_records(LW_TABLE_EVENTS);
        $services = $DB->get_records(LW_TABLE_SERVICES);

        // Check the table of services.
        self::assertCount(1, $services);
        $service = array_shift($services);

        self::assertEquals($data2['header'], $service->header);
        self::assertEquals($data2['name'], $service->name);
        self::assertEquals($data2['point'], $service->point);
        self::assertEquals($data2['status'], (int) $service->status);
        self::assertEquals($data2['token'], $service->token);
        self::assertEquals($serviceid, $service->id);

        // Check the table of events.
        self::assertCount(count($data2['events']), $events);

        foreach ($events as $event) {
            self::assertEquals($serviceid, $event->serviceid);
            self::assertContains($event->name, $data2['events']);
        }
    }
}