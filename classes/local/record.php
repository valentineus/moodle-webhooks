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
}