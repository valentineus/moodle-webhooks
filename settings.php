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
    $settings = new admin_settingpage("local_webhooks", new lang_string("pluginname", "local_webhooks"));
    $ADMIN->add("localplugins", $settings);

    /* The switch of the interceptor of events */
    $settings->add(new admin_setting_configcheckbox("local_webhooks/enable", new lang_string("enable", "moodle"), new lang_string("enablews", "webservice"), false));

    /* Link to the service manager */
    $linktext = new lang_string("managerservice", "local_webhooks");
    $linkurl = new moodle_url("/local/webhooks/managerservice.php");
    $settings->add(new admin_setting_heading("local_webhooks_managerservice", null, html_writer::link($linkurl, $linktext)));
}