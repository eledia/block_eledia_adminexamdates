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
 * @package    block_eledia_adminexamdates
 * @copyright  2021 René Hansen <support@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

global $USER, $CFG, $PAGE, $OUTPUT, $DB;

$context = context_system::instance();

require_login();

if (!has_capability('block/eledia_adminexamdates:confirmexamdates', $context)) {
    print_error(' only users with rights to confirm admin exam dates allowed');
}

$booktimestart = optional_param('booktimestart', 0, PARAM_INT);
$blockid = optional_param('blockid', 0, PARAM_INT);
$cancelspecialrooms = optional_param('cancelspecialrooms', 0, PARAM_INT);
$cancelspecialroomsyes = optional_param('cancelspecialroomsyes', 0, PARAM_INT);
$returnurl = optional_param('url', '', PARAM_RAW);

$calendarurl = new \moodle_url('/blocks/eledia_adminexamdates/calendar.php');
$returnurldecoded = (!empty($returnurl)) ? rawurldecode($returnurl) : $calendarurl;

$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('examdaterequest', 'block_eledia_adminexamdates'));
$PAGE->set_pagelayout('course');

$mform = new \block_eledia_adminexamdates\forms\specialrooms_form();
$hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());

// Execute the form.
if ($mform->is_cancelled()) {
    redirect($returnurldecoded);

} else if (!empty($cancelspecialroomsyes)) {
    block_eledia_adminexamdates\util::cancelspecialrooms($cancelspecialroomsyes);
    redirect($returnurldecoded);

} else if (!empty($cancelspecialrooms)) {
    $block = $DB->get_record('eledia_adminexamdates_blocks', ['id' => $cancelspecialrooms]);
    $blockrooms = $DB->get_records('eledia_adminexamdates_rooms', ['blockid' => $cancelspecialrooms]);
    $specialrooms = array_column($blockrooms, 'examroom');
    $rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));
    $roomnames = [];
    foreach ($rooms as $room) {
        $roomitems = explode('|', $room);
        if (in_array($roomitems[0], $specialrooms)) {
            array_push($roomnames, $roomitems[1]);
        }
    }
    $date = date('d.m.Y, H.i', $block->blocktimestart) . ' - ' . date('H.i', $block->blocktimestart + ($block->blockduration * 60)). get_string('hour', 'block_eledia_adminexamdates');
    $message = get_string('cancelspecialrooms_msg', 'block_eledia_adminexamdates',
            ['date' => $date, 'rooms' => implode(', ', $roomnames)]);
    $formcontinue =
            new single_button(new moodle_url($PAGE->url, ['cancelspecialroomsyes' => $cancelspecialrooms]), get_string('yes'));
    $formcancel = new single_button(new moodle_url('/blocks/eledia_adminexamdates/calendar.php'), get_string('no'));
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    echo $OUTPUT->confirm($message, $formcontinue, $formcancel);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
} else if (empty($formdata = $mform->get_data())) {
    if (!empty($blockid)) {
        $data = block_eledia_adminexamdates\util::editspecialroom($blockid);
        $mform->set_data($data);
    } else {
        $data = new stdClass();
        $data->booktimestart = $booktimestart;
        $mform->set_data($data);
    }
    echo $OUTPUT->header();
    echo $OUTPUT->container_start();
    echo \html_writer::tag('h1', get_string('book_specialrooms', 'block_eledia_adminexamdates'));
    $mform->display();
    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
} else if (!empty($formdata = $mform->get_data())) {
    $examdateid = block_eledia_adminexamdates\util::savespecialrooms($formdata);
    redirect($returnurldecoded);
} else {
    redirect($returnurldecoded);
}





