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
 * Settings of the plugin.
 *
 * @package   local_webhooks
 * @copyright 2017 "Valentin Popov" <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

$settings = null;
if ($hassiteconfig) {
    $settings = new admin_settingpage(
        "local_webhooks",
        new lang_string("pluginname", "local_webhooks")
    );

    $ADMIN->add("localplugins", $settings);

    $settings->add(new admin_setting_configcheckbox(
        "local_webhooks/enabled",
        new lang_string("enableservice", "local_webhooks"),
        new lang_string("enableservice_help", "local_webhooks"),
        false
    ));

    /* Link to the service manager */
    $linktext = new lang_string("linkmanagerservice", "local_webhooks");
    $link = "<a href=\"" . $CFG->wwwroot . "/local/webhooks/managerservice.php\">" . $linktext . "</a>";
    $settings->add(new admin_setting_heading("local_webhooks_addheading", "", $link));
}