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

/**
 * Describes the plugin tables.
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/webhooks/lib.php');
require_once($CFG->libdir . '/tablelib.php');

/**
 * Display the list of services table.
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */
class local_webhooks_services_table extends table_sql {
    /**
     * @var string $main_page
     */
    protected static $main_page = '/local/webhooks/index.php';

    /**
     * @var string $editor_page
     */
    protected static $editor_page = '/local/webhooks/service.php';

    /**
     * Constructor.
     *
     * @param string $unique_id
     *
     * @throws \coding_exception
     */
    public function __construct($unique_id = '') {
        parent::__construct($unique_id);
        $this->define_table_columns();
        $this->define_table_configs();
    }

    /**
     * Query the database for results to display in the table.
     *
     * @param int     $page_size
     * @param boolean $use_initials_bar
     *
     * @throws \dml_exception
     */
    public function query_db($page_size = 0, $use_initials_bar = false) {
        $this->rawdata = local_webhooks_api::get_services(array(), $this->get_page_start(), $this->get_page_size());
    }

    /**
     * Defines the basic settings of the table.
     */
    public function define_table_configs() {
        $this->collapsible(false);
        $this->is_downloadable(false);
        $this->no_sorting('actions');
        $this->pageable(true);
    }

    /**
     * Defines the main columns and table headers.
     *
     * @throws \coding_exception
     */
    public function define_table_columns() {
        $columns = array(
            'name',
            'point',
            'events',
            'actions',
        );

        $headers = array(
            new lang_string('name', 'moodle'),
            new lang_string('url', 'moodle'),
            new lang_string('edulevel', 'moodle'),
            new lang_string('actions', 'moodle'),
        );

        $this->define_columns($columns);
        $this->define_headers($headers);
    }

    /**
     * Specifies the display of a column with actions.
     *
     * @param object $row
     *
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_actions($row) {
        global $OUTPUT;

        $hide_show_icon = 't/show';
        $hide_show_string = new lang_string('enable', 'moodle');

        if (!empty($row->status)) {
            $hide_show_icon = 't/hide';
            $hide_show_string = new lang_string('disable', 'moodle');
        }

        /* Link for activation / deactivation */
        $hide_show_link = new moodle_url(self::$main_page, array('hideshowid' => $row->id, 'sesskey' => sesskey()));
        $hide_show_item = $OUTPUT->action_icon($hide_show_link, new pix_icon($hide_show_icon, $hide_show_string));

        /* Link for editing */
        $edit_link = new moodle_url(self::$editor_page, array('serviceid' => $row->id, 'sesskey' => sesskey()));
        $edit_item = $OUTPUT->action_icon($edit_link, new pix_icon('t/edit', new lang_string('edit', 'moodle')));

        /* Link to remove */
        $delete_link = new moodle_url(self::$main_page, array('deleteid' => $row->id, 'sesskey' => sesskey()));
        $delete_item = $OUTPUT->action_icon($delete_link, new pix_icon('t/delete', new lang_string('delete', 'moodle')));

        return $hide_show_item . $edit_item . $delete_item;
    }

    /**
     * Specifies the display of a column with events.
     *
     * @param object $row
     *
     * @return number
     */
    public function col_events($row) {
        return count($row->events);
    }

    /**
     * Specifies the display of the column with the service name.
     *
     * @param object $row
     *
     * @return string
     * @throws \moodle_exception
     */
    public function col_name($row) {
        $link = new moodle_url(self::$editor_page, array('serviceid' => $row->id, 'sesskey' => sesskey()));

        return html_writer::link($link, $row->name);
    }
}