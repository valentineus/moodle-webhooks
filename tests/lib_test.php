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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

namespace local_webhooks;

/**
 * Integration tests for service storage, events and backups.
 *
 * @package local_webhooks
 * @copyright 2026 Valentin Popov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        require_once(__DIR__ . '/../lib.php');
    }

    /** Create a service which cannot make outgoing requests. */
    private function service(string $title = 'Test service'): \stdClass {
        return (object) array(
            'title' => $title,
            'enable' => 0,
            'url' => 'https://example.invalid/webhook',
            'type' => 'json',
            'token' => 'test-secret',
            'events' => array('\\core\\event\\user_created' => 1),
            'other' => 'Extra data',
        );
    }

    /** Services round-trip through the database and emit lifecycle events. */
    public function test_service_lifecycle(): void {
        $sink = $this->redirectEvents();
        $service = $this->service('Сервис');
        $expected = clone $service;
        $this->assertTrue(local_webhooks_update_record($service));
        $records = local_webhooks_get_list_records();
        $this->assertCount(1, $records);
        $record = reset($records);
        $this->assertSame($expected->title, $record->title);
        $this->assertSame($expected->events, $record->events);
        $this->assertSame($expected->token, $record->token);
        $this->assertEquals($record, local_webhooks_get_record($record->id));

        $record->title = 'Updated service';
        $record->events = array('\\core\\event\\user_deleted' => 1);
        $expectedevents = $record->events;
        $this->assertTrue(local_webhooks_update_record($record, false));
        $updated = local_webhooks_get_record($record->id);
        $this->assertSame('Updated service', $updated->title);
        $this->assertSame($expectedevents, $updated->events);

        local_webhooks_remove_record($record->id);
        $this->assertSame(array(), local_webhooks_get_list_records());
        $events = $sink->get_events();
        $this->assertCount(3, $events);
        $this->assertInstanceOf(event\service_added::class, $events[0]);
        $this->assertInstanceOf(event\service_updated::class, $events[1]);
        $this->assertInstanceOf(event\service_deleted::class, $events[2]);
        foreach ($events as $event) {
            $this->assertEquals($record->id, $event->objectid);
        }
        $sink->close();
    }

    /** Paging is stable and empty subscriptions remain an array. */
    public function test_pagination_and_empty_events(): void {
        $this->redirectEvents();
        foreach (array('First', 'Second', 'Third') as $title) {
            $service = $this->service($title);
            unset($service->events);
            local_webhooks_update_record($service);
        }
        $records = local_webhooks_get_list_records(1, 1);
        $this->assertCount(1, $records);
        $record = reset($records);
        $this->assertSame('Second', $record->title);
        $this->assertSame(array(), $record->events);
        local_webhooks_remove_list_records();
        $this->assertSame(array(), local_webhooks_get_list_records());
    }

    /** Restore replaces current services and preserves settings and subscriptions. */
    public function test_backup_restore(): void {
        $sink = $this->redirectEvents();
        local_webhooks_update_record($this->service('First'));
        local_webhooks_update_record($this->service('Second'));
        $expected = array_values(local_webhooks_get_list_records());
        $backup = local_webhooks_create_backup();
        local_webhooks_remove_list_records();
        local_webhooks_update_record($this->service('Must disappear'));
        local_webhooks_restore_backup($backup);
        $restored = array_values(local_webhooks_get_list_records());
        $this->assertCount(2, $restored);
        foreach ($expected as $index => $service) {
            unset($service->id, $restored[$index]->id);
            $this->assertEquals($service, $restored[$index]);
        }
        $events = $sink->get_events();
        $this->assertInstanceOf(event\backup_performed::class, $events[2]);
        $this->assertInstanceOf(event\backup_restored::class, end($events));
        $sink->close();
    }

    /** An empty backup clears existing records. */
    public function test_empty_backup_restore(): void {
        $this->redirectEvents();
        $backup = local_webhooks_create_backup();
        local_webhooks_update_record($this->service());
        local_webhooks_restore_backup($backup);
        $this->assertSame(array(), local_webhooks_get_list_records());
    }

    /** A missing service must fail explicitly. */
    public function test_missing_service(): void {
        $this->expectException(\dml_missing_record_exception::class);
        local_webhooks_get_record(0);
    }
}
