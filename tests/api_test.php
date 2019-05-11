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
 * Testing the API plugin class.
 *
 * @copyright 2019 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class local_webhooks_api_testcase extends advanced_testcase {
    /**
     * Generate random an event's list.
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    private static function get_random_events(): array {
        $result = array_rand(api::get_events(), random_int(2, 10));

        return is_array($result) ? $result : [];
    }

    /**
     * Generate a random record.
     *
     * @return \local_webhooks\local\record
     *
     * @throws \ReflectionException
     */
    private static function get_random_record(): record {
        $record = new record();

        $record->events = self::get_random_events();
        $record->header = 'application/json';
        $record->name = uniqid('', false);
        $record->point = 'http://example.org/' . urlencode($record->name);
        $record->status = true;
        $record->token = generate_uuid();

        return $record;
    }

    /**
     * Testing creation of the service.
     *
     * @group local_webhooks
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \ReflectionException
     */
    public function test_adding() {
        global $DB;

        $this->resetAfterTest();

        $record = self::get_random_record();
        $record->id = api::add_service($record);

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
     * @throws \ReflectionException
     */
    public function test_deleting() {
        global $DB;

        $this->resetAfterTest();

        // Testing correct delete record of the database.
        $record = self::get_random_record();
        $record->id = api::add_service($record);

        self::assertTrue(api::del_service($record->id));
        self::assertCount(0, $DB->get_records(LW_TABLE_EVENTS));
        self::assertCount(0, $DB->get_records(LW_TABLE_SERVICES));

        $ids = [];
        $total = random_int(5, 20);

        // Testing correct delete record of the record's list.
        for ($i = 0; $i < $total; $i++) {
            $record = self::get_random_record();
            $ids[] = api::add_service($record);
        }

        self::assertEquals(count($ids), api::get_total_count());
        self::assertTrue(api::del_service($ids[array_rand($ids, 1)]));
        self::assertEquals(count($ids) - 1, api::get_total_count());
    }

    /**
     * Testing get an event's list.
     *
     * @group local_webhooks
     *
     * @throws \ReflectionException
     */
    public function test_get_events() {
        $this->resetAfterTest();

        $events = api::get_events();

        self::assertNotCount(0, $events);

        foreach ($events as $event) {
            self::assertInternalType('array', $event);

            self::assertEquals([
                'eventname', 'component', 'target', 'action', 'crud', 'edulevel', 'objecttable',
            ], array_keys($event));
        }
    }

    /**
     * Testing get to a service.
     *
     * @group local_webhooks
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \ReflectionException
     */
    public function test_get_service() {
        $this->resetAfterTest();

        $record = self::get_random_record();
        $record->id = api::add_service($record);
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
     * @group local_webhooks
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \ReflectionException
     */
    public function test_get_services() {
        $this->resetAfterTest();

        $records = [];
        $total = random_int(5, 20);

        for ($i = 0; $i < $total; $i++) {
            $record = self::get_random_record();
            $record->id = api::add_service($record);
            $records[$record->id] = $record;
        }

        $services = api::get_services();
        self::assertCount(count($records), $services);

        foreach ($services as $service) {
            $record = $records[$service->id];

            self::assertEquals($record->header, $service->header);
            self::assertEquals($record->id, $service->id);
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
     * @group local_webhooks
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \ReflectionException
     */
    public function test_get_services_by_event() {
        $this->resetAfterTest();

        $eventname = generate_uuid();
        $limit = random_int(1, 5);
        $total = random_int(5, 20);

        $ids = [];

        for ($i = 0; $i < $total; $i++) {
            $record = self::get_random_record();
            $record->events[] = $i < $limit ? $eventname : '';
            $ids[] = api::add_service($record);
        }

        self::assertEquals(count($ids), api::get_total_count());
        $services = api::get_services_by_event($eventname);
        self::assertCount($limit, $services);

        foreach ($services as $service) {
            self::assertContains($service->id, $ids);
        }
    }

    /**
     * Testing get to the list services with conditions.
     *
     * @group local_webhooks
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \ReflectionException
     */
    public function test_get_services_with_conditions() {
        $this->resetAfterTest();

        $records = [];
        $total = random_int(5, 20);
        $limit = intdiv($total, 2);

        // Creating some records.
        for ($i = 0; $i < $total; $i++) {
            $record = self::get_random_record();
            $record->id = api::add_service($record);
            $records[] = $record;
        }

        // Testing condition fields.
        $record = $records[array_rand($records, 1)];
        self::assertCount(1, api::get_services([
            'point' => $record->point,
        ]));

        // Testing limit fields.
        self::assertCount($limit, api::get_services([], null, 1, $limit));

        // Testing sort fields.
        $service1 = api::get_services(null, 'id asc')[0];
        $service2 = api::get_services(null, 'id desc')[0];
        self::assertNotEquals($service1->id, $service2->id);
    }

    /**
     * Testing get a total count of existing records.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \ReflectionException
     */
    public function test_total() {
        $this->resetAfterTest();

        $total = random_int(5, 20);

        for ($i = 0; $i < $total; $i++) {
            $record = self::get_random_record();
            api::add_service($record);
        }

        self::assertEquals($total, api::get_total_count());
    }

    /**
     * Testing of the service update.
     *
     * @group local_webhooks
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \ReflectionException
     */
    public function test_updating() {
        global $DB;

        $this->resetAfterTest();

        $record1 = self::get_random_record();
        $record2 = self::get_random_record();

        $record2->id = api::add_service($record1);
        self::assertTrue(api::set_service($record2));

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