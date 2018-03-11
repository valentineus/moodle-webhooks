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
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

require_once(__DIR__ . "/../lib.php");

require_once($CFG->libdir . "/tablelib.php");

/**
 * Describes the main table of the plugin.
 *
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_webhooks_table extends table_sql {
    /**
     * Manager address.
     *
     * @var string $manager
     */
    protected static $manager = "/local/webhooks/index.php";

    /**
     * Editor's address.
     *
     * @var string $editor
     */
    protected static $editor = "/local/webhooks/editservice.php";

    /**
     * Constructor.
     *
     * @param string $uniqueid The unique identifier of the table.
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        $this->define_table_columns();
        $this->define_table_configs();
    }

    /**
     * Query the database for results to display in the table.
     *
     * @param number  $pagesize
     * @param boolean $useinitialsbar
     */
    public function query_db($pagesize, $useinitialsbar = false) {
        $listrecords = local_webhooks_get_list_records();
        $total       = count($listrecords);

        $this->pagesize($pagesize, $total);
        $this->rawdata = local_webhooks_get_list_records($this->get_page_start(), $this->get_page_size());
    }

    /**
     * Defines the basic settings of the table.
     */
    public function define_table_configs() {
        $this->collapsible(false);
        $this->is_downloadable(false);
        $this->no_sorting("actions");
        $this->pageable(true);
    }

    /**
     * Defines the main columns and table headers.
     */
    public function define_table_columns() {
        $columns = array(
            "title",
            "url",
            "events",
            "actions"
        );

        $headers = array(
            new lang_string("name", "moodle"),
            new lang_string("url", "moodle"),
            new lang_string("edulevel", "moodle"),
            new lang_string("actions", "moodle")
        );

        $this->define_columns($columns);
        $this->define_headers($headers);
    }

    /**
     * Specifies the display of a column with actions.
     *
     * @param  object $row Data from the database.
     * @return string      Displayed data.
     */
    public function col_actions($row) {
        global $OUTPUT;

        $hideshowicon   = "t/show";
        $hideshowstring = new lang_string("enable", "moodle");
        if (boolval($row->enable)) {
            $hideshowicon   = "t/hide";
            $hideshowstring = new lang_string("disable", "moodle");
        }

        $hideshowlink = new moodle_url(self::$manager, array("hideshowid" => $row->id, "sesskey" => sesskey()));
        $hideshowitem = $OUTPUT->action_icon($hideshowlink, new pix_icon($hideshowicon, $hideshowstring));

        $editlink = new moodle_url(self::$editor, array("serviceid" => $row->id, "sesskey" => sesskey()));
        $edititem = $OUTPUT->action_icon($editlink, new pix_icon("t/edit", new lang_string("edit", "moodle")));

        $deletelink = new moodle_url(self::$manager, array("deleteid" => $row->id, "sesskey" => sesskey()));
        $deleteitem = $OUTPUT->action_icon($deletelink, new pix_icon("t/delete", new lang_string("delete", "moodle")));

        $html = $hideshowitem . $edititem . $deleteitem;
        return $html;
    }

    /**
     * Specifies the display of a column with events.
     *
     * @param  object $row Data from the database.
     * @return number      Displayed data.
     */
    public function col_events($row) {
        $result = 0;

        if (!empty($row->events)) {
            $result = count($row->events);
        }

        return $result;
    }

    /**
     * Specifies the display of the column with the service name.
     *
     * @param  object $row Data from the database.
     * @return string      Displayed data.
     */
    public function col_title($row) {
        $link = new moodle_url(self::$editor, array("serviceid" => $row->id, "sesskey" => sesskey()));
        $html = html_writer::link($link, $row->title);
        return $html;
    }
}