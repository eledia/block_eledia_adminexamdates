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

//
//if (!$course) {
//    print_error('invalidcourseid');
//}
//require_login($course);
//
//$context = context_course::instance($course->id);
//// Check basic permission
//require_capability('block/eledia_courseadmin_fom:view', $context);

$newexamdate = optional_param('newexamdate', 0, PARAM_INT);
//$newexamdateyes = optional_param('newexamdate', 0, PARAM_INT);
$editexamdate = optional_param('editexamdate', 0, PARAM_INT);
$examtimestart = optional_param('examtimestart', 0, PARAM_INT);

$returnurl = optional_param('url', '', PARAM_RAW);

$calendarurl = new \moodle_url('/blocks/eledia_adminexamdates/calendar.php');
$returnurldecoded = (!empty($returnurl)) ? rawurldecode($returnurl) : $calendarurl;
$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$title = (empty($editexamdate)) ? get_string('newexamdate', 'block_eledia_adminexamdates') :
        get_string('editexamdate_btn', 'block_eledia_adminexamdates');
$PAGE->set_title($title);
$PAGE->set_pagelayout('course');

$hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());
$onlynumberstudents = false;
if ($editexamdate) {
    $examdate = $DB->get_record('eledia_adminexamdates', ['id' => $editexamdate]);
    $onlynumberstudents = (!$hasconfirmexamdatescap && ($examdate->confirmed == 1 || $examdate->confirmed == 2)) ? true : false;
}

$mform = new \block_eledia_adminexamdates\forms\examdate_form(null,
        array('onlynumberstudents' => $onlynumberstudents, 'editexamdate' => $editexamdate, 'url' => $returnurl));

// Execute the form.
if ($mform->is_cancelled()) {
    $calendarurl = new \moodle_url('/blocks/eledia_adminexamdates/calendar.php');
    if ($formdata = $mform->get_data()) {
        $returnurldecoded = (!empty($formdata->url)) ? rawurldecode($formdata->url) : $calendarurl;
    }
    redirect($returnurldecoded);
} else if ($hasconfirmexamdatescap && !empty($newexamdate)) {
    $urlspecialrooms = new moodle_url('/blocks/eledia_adminexamdates/specialrooms.php',
            ['booktimestart' => $examtimestart, 'url' => $returnurl]);
    $message = get_string('chooseroomcategory_msg', 'block_eledia_adminexamdates');
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
    echo "<p>" . $message . "</p>\n";
    echo $OUTPUT->single_button(new moodle_url($PAGE->url, ['examtimestart' => $examtimestart, 'url' => $returnurl]),
            get_string('newexamdate', 'block_eledia_adminexamdates'));
    echo $OUTPUT->single_button($urlspecialrooms, get_string('book_specialrooms', 'block_eledia_adminexamdates'));
    echo $OUTPUT->single_button($returnurldecoded, get_string('cancel'));
    //echo $OUTPUT->confirm($message, $continueexamdate, $bookspecialroom);
    echo $OUTPUT->box_end();
} else if (empty($formdata = $mform->get_data())) {

    $data = new stdClass();
    $data->selectdisplaydate = (!empty($displaydate)) ? $displaydate : time();
    $mform->set_data($data);

    if (!empty($editexamdate)) {
        $data = block_eledia_adminexamdates\util::editexamdate($editexamdate);
        $data->url = (!empty($returnurl)) ? $returnurl : '';
        $mform->set_data($data);
    } else {
        $data = new stdClass();
        $data->examtimestart = $examtimestart;
        $data->contactpersonemail = $USER->email;
        $data->url = (!empty($returnurl)) ? $returnurl : '';
        $mform->set_data($data);
    }
    echo $OUTPUT->header();
    echo $OUTPUT->container_start();
    $url = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1, 'url' => rawurlencode($myurl)]);
    $newexamdatebutton = new single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'));
    $urllist = new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php');
    $unconfirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php');
    $confirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesconfirmed.php');
    $urlcalendar = new moodle_url('/blocks/eledia_adminexamdates/calendar.php');
    $statistics = new moodle_url('/blocks/eledia_adminexamdates/statistics.php', ['url' => rawurlencode($myurl)]);

    echo \html_writer::start_tag('div', array('class' => 'container-fluid px-4'));
    echo \html_writer::start_tag('div', array('class' => 'row'));
    echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
    echo $OUTPUT->single_button($urlcalendar, get_string('calendar_btn', 'block_eledia_adminexamdates'));
    if ($hasconfirmexamdatescap) {
        echo $OUTPUT->single_button($urllist, get_string('examdateslist_btn', 'block_eledia_adminexamdates'));
    }
    echo $OUTPUT->single_button($unconfirmed, get_string('unconfirmed_btn', 'block_eledia_adminexamdates'));
    if (!$hasconfirmexamdatescap) {
        echo $OUTPUT->single_button($confirmed, get_string('confirmed_btn', 'block_eledia_adminexamdates'));
    }
    if ($hasconfirmexamdatescap) {
        if (empty($editexamdate)) {
            echo \html_writer::start_tag('div', array('class' => 'singlebutton mb-3'));
            echo \html_writer::tag('button', get_string('newexamdate', 'block_eledia_adminexamdates'),
                    array('disabled' => true, 'class' => 'btn '));
            echo \html_writer::end_tag('div');
        } else {
            echo $OUTPUT->single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'));
        }
        echo $OUTPUT->single_button($statistics, get_string('statistics', 'block_eledia_adminexamdates'));
        $urlReport = new moodle_url('/mod/elediachecklist/terminreport.php');
        echo $OUTPUT->single_button($urlReport, get_string('report_button', 'elediachecklist'), 'get');
    }
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    if ($hasconfirmexamdatescap && !$newexamdate && !empty($editexamdate)) {
        echo \html_writer::start_tag('div', array('class' => 'row mt-3'));
        echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
        echo \html_writer::start_tag('div', array('class' => 'singlebutton mb-3'));
        echo \html_writer::tag('button', get_string('editexamdate_btn', 'block_eledia_adminexamdates'),
                array('disabled' => true, 'class' => 'btn '));
        echo \html_writer::end_tag('div');
        $checklistlink = get_string('checklistlink', 'block_eledia_adminexamdates') . $editexamdate;
        $editsingleexamdateurl =
                new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['examdateid' => $editexamdate]);
        echo $OUTPUT->single_button($editsingleexamdateurl, get_string('singleexamdate_btn', 'block_eledia_adminexamdates'));
        echo $OUTPUT->single_button($checklistlink, get_string('checklist_btn', 'block_eledia_adminexamdates'));
        echo \html_writer::end_tag('div');
        echo \html_writer::end_tag('div');
    }
    echo \html_writer::start_tag('div', array('class' => 'row mt-3'));
    if ($onlynumberstudents){
        echo \html_writer::start_tag('div', array('class' => 'col-sm-6'));
    } else {
        echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
    }


    if ($hasconfirmexamdatescap && $newexamdate) {
        $urlspecialrooms =
                new moodle_url('/blocks/eledia_adminexamdates/specialrooms.php', ['bookingtimestart' => $examtimestart]);
        echo $OUTPUT->single_button($urlspecialrooms, get_string('book_specialrooms', 'block_eledia_adminexamdates'));
    }
    $param = (!empty($examtimestart) && is_integer($examtimestart)) ? ['displaydate' => $examtimestart] : null;
    $calendarurl = new \moodle_url('/blocks/eledia_adminexamdates/calendar.php', $param);
    //echo $OUTPUT->single_button($calendarurl, get_string('cancel'), 'post');

    echo \html_writer::start_tag('div', array('class' => 'card-deck'));
    echo \html_writer::start_tag('div', array('class' => 'card'));
    echo \html_writer::start_tag('div', array('class' => 'card-body'));
    echo \html_writer::start_tag('p', array('class' => 'card-text'));

    $mform->display();

    echo \html_writer::end_tag('p');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');

    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');

    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();

} else {

    $needfreetimeslots = empty($formdata->editexamdate) ? true : false;

    $examdateid = block_eledia_adminexamdates\util::saveexamdate($formdata);
    if ($needfreetimeslots) {
        block_eledia_adminexamdates\util::getfreetimeslots2($examdateid, $formdata);

        //if ($hasconfirmexamdatescap) {
        //    redirect(new moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['examdateid' => $examdateid]));
        //}

    } else {
        if (($examdate->examtimestart != $formdata->examtimestart) || ($examdate->examduration != $formdata->examduration)
                || ($examdate->numberstudents != $formdata->numberstudents)) {
            block_eledia_adminexamdates\util::updatefreetimeslots2($examdateid, $formdata);
        }
    }

    if ($hasconfirmexamdatescap) {
        redirect(new moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['examdateid' => $examdateid]));
    }
    $calendarurl = new \moodle_url('/blocks/eledia_adminexamdates/calendar.php');
    if ($formdata = $mform->get_data()) {
        $returnurldecoded = (!empty($formdata->url)) ? rawurldecode($formdata->url) : $calendarurl;
    }
    redirect($returnurldecoded);
}


