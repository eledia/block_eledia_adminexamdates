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

$newexamdate = optional_param('newexamdate', 0, PARAM_INT);
$editexamdate = optional_param('editexamdate', 0, PARAM_INT);
$examtimestart = optional_param('examtimestart', 0, PARAM_INT);

$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('examdaterequest', 'block_eledia_adminexamdates'));
$PAGE->set_pagelayout('course');

$mform = new \block_eledia_adminexamdates\forms\examdate_form();
$hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());

// Execute the form.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php'));
} else if (empty($formdata = $mform->get_data())) {
    if (!empty($editexamdate)) {
        $data = block_eledia_adminexamdates\util::editexamdate($editexamdate);
        $mform->set_data($data);
    } else {
        $data = new stdClass();
        $data->examtimestart = $examtimestart;
        $data->contactpersonemail = $USER->email;
        $mform->set_data($data);
    }
    echo $OUTPUT->header();
    echo $OUTPUT->container_start();
    $url = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1]);
    $newexamdatebutton = new single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'), 'post');
    $urllist = new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php');
    $unconfirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php');
    $urlcalendar = new moodle_url('/blocks/eledia_adminexamdates/calendar.php');

    echo \html_writer::start_tag('div', array('class' => 'container-fluid px-4'));
    echo \html_writer::start_tag('div', array('class' => 'row'));
    echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
    echo $OUTPUT->single_button($urlcalendar, get_string('calendar_btn', 'block_eledia_adminexamdates'), 'post');
    if ($hasconfirmexamdatescap) {
        echo $OUTPUT->single_button($urllist, get_string('examdateslist_btn', 'block_eledia_adminexamdates'), 'post');
    }
    echo $OUTPUT->single_button($unconfirmed, get_string('unconfirmed_btn', 'block_eledia_adminexamdates'), 'post');
    if ($newexamdate) {
        echo \html_writer::start_tag('div', array('class' => 'singlebutton'));
        echo \html_writer::tag('button', get_string('newexamdate', 'block_eledia_adminexamdates'), array('disabled' => true, 'class' => 'btn '));
        echo \html_writer::end_tag('div');
    } else {
        echo $OUTPUT->single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'), 'post');
    }
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    if ($hasconfirmexamdatescap && !$newexamdate) {
        echo \html_writer::start_tag('div', array('class' => 'row mt-3'));
        echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
        echo \html_writer::start_tag('div', array('class' => 'singlebutton'));
        echo \html_writer::tag('button', get_string('editexamdate_btn', 'block_eledia_adminexamdates'), array('disabled' => true, 'class' => 'btn '));
        echo \html_writer::end_tag('div');
        $checklistlink = get_string('checklistlink', 'block_eledia_adminexamdates').$editexamdate;
        $editsingleexamdateurl =
            new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['examdateid' => $editexamdate]);
        echo $OUTPUT->single_button($editsingleexamdateurl, get_string('singleexamdate_btn', 'block_eledia_adminexamdates'), 'post');
        echo $OUTPUT->single_button($checklistlink, get_string('checklist_btn', 'block_eledia_adminexamdates'), 'post');

        echo \html_writer::end_tag('div');
        echo \html_writer::end_tag('div');
    }
    echo \html_writer::start_tag('div', array('class' => 'row mt-3'));
    echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));

    echo \html_writer::start_tag('p');
/*    if ($newexamdate) {
        echo \html_writer::tag('h1', get_string('newexamdate', 'block_eledia_adminexamdates'));
    } else {
        echo \html_writer::tag('h1', get_string('editexamdate_header', 'block_eledia_adminexamdates'));
    }*/
    echo \html_writer::end_tag('p');

    $mform->display();
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');

    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
} else {
    if (!empty($formdata = $mform->get_data())) {
        $needfreetimeslots = empty($formdata->editexamdate) ? true : false;
        $examdateid = block_eledia_adminexamdates\util::saveexamdate($formdata);
        if ($needfreetimeslots) {
            block_eledia_adminexamdates\util::getfreetimeslots($examdateid, $formdata);
            if ($hasconfirmexamdatescap) {
                redirect(new moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['examdateid' => $examdateid]));
            }
        }
    }
    if ($hasconfirmexamdatescap) {
        redirect(new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php'));
    }
    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php'));
}


