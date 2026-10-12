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

namespace local_webhooks\output;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/webhooks/classes/local/api.php');
require_once($CFG->libdir . '/tablelib.php');

use html_writer;
use lang_string;
use local_webhooks\local\api;
use moodle_url;
use pix_icon;
use stdClass;
use table_sql;
use function defined;
use function is_int;

/**
 * Class table for list services.
 *
 * @copyright 2019 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks\output
 */
final class services_table extends table_sql {
    /**
     * URL editor page.
     *
     * @var string
     */
    private static $editorpage = '/local/webhooks/service.php';

    /**
     * URL main page.
     *
     * @var string
     */
    private static $mainpage = '/local/webhooks/index.php';

    /**
     * Class constructor.
     *
     * @param string $uniqueid
     *
     * @throws \coding_exception
     */
    public function __construct(string $uniqueid) {
        parent::__construct($uniqueid);

        $this->define_table_columns();
        $this->define_table_configs();
    }

    /**
     * Define the action column.
     *
     * @param \stdClass $row
     *
     * @return string
     *
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_actions(stdClass $row): string {
        global $OUTPUT;

        $deletelink = new moodle_url(self::$mainpage, ['deleteid' => $row->id, 'sesskey' => sesskey()]);
        $deleteitem = $OUTPUT->action_icon($deletelink, new pix_icon('t/delete', new lang_string('delete', 'moodle')));

        $editlink = new moodle_url(self::$editorpage, ['serviceid' => $row->id, 'sesskey' => sesskey()]);
        $edititem = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', new lang_string('edit', 'moodle')));

        $hideshowlink = new moodle_url(self::$mainpage, ['hideshowid' => $row->id, 'sesskey' => sesskey()]);
        $hideshowitem = $OUTPUT->action_icon($hideshowlink, new pix_icon($row->status ? 't/hide' : 't/show', $row->status
            ? new lang_string('disable', 'moodle')
            : new lang_string('enable', 'moodle')
        ));

        return $hideshowitem . $edititem . $deleteitem;
    }

    /**
     * Define the events column.
     *
     * @param \stdClass $row
     *
     * @return int
     */
    public function col_events(stdClass $row): int {
        $total = count($row->events);

        return is_int($total) ? $total : 0;
    }

    /**
     * Define the name column.
     *
     * @param \stdClass $row
     *
     * @return string
     *
     * @throws \moodle_exception
     */
    public function col_name(stdClass $row): string {
        $link = new moodle_url(self::$editorpage, [
            'serviceid' => $row->id,
            'sesskey'   => sesskey(),
        ]);

        return html_writer::link($link, $row->name);
    }

    /**
     * Config table's columns.
     *
     * @throws \coding_exception
     */
    public function define_table_columns() {
        $this->define_columns([
            'name',
            'point',
            'events',
            'actions',
        ]);

        $this->define_headers([
            new lang_string('name', 'moodle'),
            new lang_string('url', 'moodle'),
            new lang_string('edulevel', 'moodle'),
            new lang_string('actions', 'moodle'),
        ]);
    }

    /**
     * Config table.
     */
    public function define_table_configs() {
        $this->collapsible(false);
        $this->is_downloadable(false);
        $this->no_sorting('actions');
        $this->no_sorting('events');
        $this->pageable(true);
    }

    /**
     * The query for the database.
     *
     * @param int  $pagesize
     * @param bool $useinitialsbar
     *
     * @throws \dml_exception
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        $this->pagesize($pagesize, $pagesize + 1);

        $sort = (string) $this->get_sql_sort();
        $this->rawdata = api::get_services(null, $sort, $this->get_page_start(), $this->get_page_size());

        $total = api::get_total_count();
        $this->pagesize($pagesize, $total);

        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }
}