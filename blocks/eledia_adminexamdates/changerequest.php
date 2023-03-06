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


require_login();

// Get course
$courseid = $DB->get_field('course_modules', 'course', array('id' => get_config('block_eledia_adminexamdates', 'instanceofmodelediachecklist')));
$course = $DB->get_record('course', array('id' => $courseid));
if (!$course) {
    print_error('invalidcourseid');
}
require_login($course);

$context = context_course::instance($course->id);


$examdateid = optional_param('examdateid', 0, PARAM_INT);
$mailsend = optional_param('mailsend', 0, PARAM_INT);

$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('examdaterequest', 'block_eledia_adminexamdates'));
$PAGE->set_pagelayout('course');

$mform = new \block_eledia_adminexamdates\forms\changerequest_form();;


// Execute the form.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php'));
} else if (!empty($formdata = $mform->get_data()) && !empty($formdata->changerequesttext)) {
    block_eledia_adminexamdates\util::sendchangerequestemail($examdateid, $formdata->changerequesttext);
    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php'));
} else {
    $mform->set_data(['examdateid'=>$examdateid]);
    echo $OUTPUT->header();
    echo $OUTPUT->container_start();
    $blockid = $DB->get_record('eledia_adminexamdates_blocks', ['examdateid' => $examdateid], 'id', IGNORE_MULTIPLE)->id;
    echo \html_writer::start_tag('div', array('class' => 'container-fluid px-4'));
    echo \html_writer::start_tag('div', array('class' => 'row'));
    echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
    echo \html_writer::start_tag('p');
    echo \html_writer::tag('h1', get_string('changerequest_header', 'block_eledia_adminexamdates'));
    echo \html_writer::end_tag('p');
    echo \html_writer::start_tag('div', array('class' => 'row'));
    echo \html_writer::start_tag('div', array('class' => 'col-md-4'));

    echo block_eledia_adminexamdates\util::getexamdateoverview($blockid, $examdateid, false);

    echo \html_writer::end_tag('div');
    echo \html_writer::start_tag('div', array('class' => 'col-md-8'));
    echo \html_writer::start_tag('div', array('class' => 'card'));
    echo \html_writer::start_tag('div', array('class' => 'card-body'));
    echo \html_writer::tag('h5', '', array('class' => 'card-title'));
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
}
