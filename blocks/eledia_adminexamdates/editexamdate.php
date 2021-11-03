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

if (!has_capability('block/eledia_adminexamdates:view', $context)) {
    print_error(' only users with rights to view admin exam dates allowed');
}

$newexamdate = optional_param('newexamdate', 0, PARAM_INT);
$editexamdate = optional_param('editexamdate', 0, PARAM_INT);

$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('examdaterequest', 'block_eledia_adminexamdates'));
$PAGE->set_pagelayout('course');

$mform = new \block_eledia_adminexamdates\forms\examdate_form();


// Execute the form.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdatesschedule.php'));
} else if ($newexamdate || $editexamdate) {
    if (!empty($editexamdate)) {
        $data = block_eledia_adminexamdates\util::editexamdate($editexamdate);
        $mform->set_data($data);
    } else {
        $data = new stdClass();
        $data->contactpersonemail = $USER->email;
        $mform->set_data($data);
    }
    echo $OUTPUT->header();
    echo $OUTPUT->container_start();
    $mform->display();
    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
} else {
    if (!empty($formdata = $mform->get_data())) {
        $needfreetimeslots = empty($formdata->examdateid) ? true : false;
        $examdateid = block_eledia_adminexamdates\util::saveexamdate($formdata);
        if ($needfreetimeslots) {
            block_eledia_adminexamdates\util::getfreetimeslots($examdateid, $formdata);
        }
    }
    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdatesschedule.php'));

}


