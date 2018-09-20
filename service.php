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
 * Page of the service editor.
 *
 * @copyright 2018 'Valentin Popov' <info@valentineus.link>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   local_webhooks
 */

require_once( __DIR__ . "/../../config.php" );

require_once( $CFG->dirroot . "/local/webhooks/classes/ui_forms_plugin.php" );
require_once( $CFG->dirroot . "/local/webhooks/lib.php" );
require_once( $CFG->libdir . "/adminlib.php" );

$serviceId = optional_param( "serviceid", 0, PARAM_INT );

$urlParameters = array( "serviceid" => $serviceId );
$baseUrl = new moodle_url( "/local/webhooks/service.php", $urlParameters );
$mainPage = new moodle_url( "/local/webhooks/index.php" );

admin_externalpage_setup( "local_webhooks", "", null, $baseUrl, array() );
$context = context_system::instance();

$mForm = new local_webhooks_service_edit_form( $PAGE->url );
$formData = (array) $mForm->get_data();

if ( $mForm->is_cancelled() ) {
    redirect( $mainPage );
}

if ( !empty( $formData ) && confirm_sesskey() ) {
    if ( isset( $formData[ "events" ] ) ) {
        $formData[ "events" ] = array_keys( $formData[ "events" ] );
    }

    if ( !empty( $serviceId ) ) {
        $formData[ "id" ] = $serviceId;
        local_webhooks_api::update_service( $formData );
    } else {
        local_webhooks_api::create_service( $formData );
    }

    redirect( $mainPage, new lang_string( "changessaved", "moodle" ) );
}

if ( !empty( $serviceId ) ) {
    $service = local_webhooks_api::get_service( $serviceId );
    $service->events = array_fill_keys( $service->events, 1 );
    $mForm->set_data( $service );
}

/* The page title */
$titlePage = new lang_string( "externalservice", "webservice" );
$PAGE->navbar->add( $titlePage );
$PAGE->set_heading( $titlePage );
$PAGE->set_title( $titlePage );
echo $OUTPUT->header();

/* Displays the form */
$mForm->display();

/* Footer */
echo $OUTPUT->footer();