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
 * Webhook dispatch tests using Moodle's HTTP test double.
 *
 * @package local_webhooks
 * @copyright 2026 Valentin Popov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class handler_test extends \advanced_testcase {
    /** Only enabled services subscribed to the event receive a webhook. */
    public function test_dispatch_filters_services(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        require_once($CFG->libdir . '/filelib.php');
        require_once(__DIR__ . '/../lib.php');

        $eventname = '\\core\\event\\user_created';
        $base = (object) array(
            'title' => 'Test service',
            'enable' => 1,
            'url' => 'https://example.invalid/webhook',
            'type' => 'json',
            'token' => 'test-secret',
            'events' => local_webhooks_archiving_data(array($eventname => 1)),
            'other' => 'Extra data',
        );
        $enabledid = $DB->insert_record('local_webhooks_service', $base);
        $disabled = clone $base;
        $disabled->enable = 0;
        $DB->insert_record('local_webhooks_service', $disabled);
        $unsubscribed = clone $base;
        $unsubscribed->events = local_webhooks_archiving_data(array('\\core\\event\\user_deleted' => 1));
        $DB->insert_record('local_webhooks_service', $unsubscribed);

        // A mocked response prevents any network request to the callback URL.
        \curl::mock_response('{"ok":true}');
        $sink = $this->redirectEvents();
        $event = \core\event\user_created::create(array(
            'context' => \context_system::instance(),
            'objectid' => 123,
        ));
        handler::events($event);
        $responses = $sink->get_events();
        $this->assertCount(1, $responses);
        $this->assertInstanceOf(event\response_answer::class, $responses[0]);
        $this->assertEquals($enabledid, $responses[0]->objectid);
        $sink->close();
    }
}
