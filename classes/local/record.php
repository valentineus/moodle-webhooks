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

namespace local_webhooks\local;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use function defined;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;

/**
 * It's a class description record.
 *
 * @copyright 2019 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks\local
 */
final class record extends stdClass {
    /**
     * List of some events.
     *
     * @var array|null
     */
    public $events;

    /**
     * Type of the package.
     *
     * @var string
     */
    public $header;

    /**
     * A unique identifier of the service.
     *
     * @var int|null
     */
    public $id;

    /**
     * Name of the service.
     *
     * @var string
     */
    public $name;

    /**
     * Url's an endpoint to send notifications.
     *
     * @var string
     */
    public $point;

    /**
     * Status activity of the service.
     *
     * @var bool
     */
    public $status;

    /**
     * Secret key of the service.
     *
     * @var string
     */
    public $token;

    /**
     * Classes constructor.
     *
     * @param array|null $conditions
     */
    public function __construct(array $conditions = null) {
        if (isset($conditions['events']) && is_array($conditions['events'])) {
            $this->events = [];

            foreach ($conditions['events'] as $event) {
                if (is_string($event)) {
                    $this->events[] = $event;
                }
            }
        }

        if (isset($conditions['header']) && is_string($conditions['header'])) {
            $this->header = $conditions['header'];
        }

        if (isset($conditions['id']) && is_int($conditions['id'])) {
            $this->id = $conditions['id'];
        }

        if (isset($conditions['name']) && is_string($conditions['name'])) {
            $this->name = $conditions['name'];
        }

        if (isset($conditions['point']) && is_string($conditions['point'])) {
            $this->point = $conditions['point'];
        }

        if (isset($conditions['status']) && is_bool($conditions['status'])) {
            $this->status = $conditions['status'];
        }

        if (isset($conditions['token']) && is_string($conditions['token'])) {
            $this->token = $conditions['token'];
        }
    }
}