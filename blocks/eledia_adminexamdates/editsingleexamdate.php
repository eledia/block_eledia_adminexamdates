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

if (!has_capability('block/eledia_adminexamdates:confirmexamdates', $context)) {
    print_error(' only users with rights to confirm admin exam dates allowed');
}

$save = optional_param('save', 0, PARAM_INT);
$examdateid = optional_param('examdateid', 0, PARAM_INT);
$blockid = optional_param('blockid', 0, PARAM_INT);
$newblock = optional_param('newblock', 0, PARAM_INT);

$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('editsingleexamdate', 'block_eledia_adminexamdates'));
$PAGE->set_pagelayout('course');

if (!empty($blockid)) {
    $examdateid = $DB->get_record('eledia_adminexamdates_blocks', ['id' => $blockid])->examdateid;
} else if (!empty($examdateid)) {
    $blockid = $DB->get_record('eledia_adminexamdates_blocks', ['examdateid' => $examdateid], 'id', IGNORE_MULTIPLE)->id;
}

$mform = new \block_eledia_adminexamdates\forms\singleexamdate_form(null, array('blockid' => $blockid, 'examdateid' => $examdateid));

// Execute the form.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php'));
} else if (empty($save)) {
    $data = block_eledia_adminexamdates\util::editsingleexamdate($blockid, $examdateid, $newblock);
    $mform->set_data($data);
//    echo " <script>
//    $(document).ready(function(){
//        $('.form-check-input').change(function() {
//        if($(this).is(\":checked\")) {
//            confirm($(this));
//        };
//        });
//    });
//    </script>
//    ";
    echo $OUTPUT->header();
    echo $OUTPUT->container_start();
    $url = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1]);
    $newexamdatebutton = new single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'), 'post');
    $urlcalendar = new moodle_url('/blocks/eledia_adminexamdates/calendar.php');
    $urllist = new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php');
    $unconfirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php');

    echo \html_writer::start_tag('div', array('class' => 'container-fluid px-4'));
    echo \html_writer::start_tag('div', array('class' => 'row'));
    echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
    echo $OUTPUT->single_button($urlcalendar, get_string('calendar_btn', 'block_eledia_adminexamdates'), 'post');
    echo $OUTPUT->single_button($urllist, get_string('examdateslist_btn', 'block_eledia_adminexamdates'), 'post');
    echo $OUTPUT->single_button($unconfirmed, get_string('unconfirmed_btn', 'block_eledia_adminexamdates'), 'post');
    echo $OUTPUT->single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'), 'post');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::start_tag('div', array('class' => 'row'));
    echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
    echo \html_writer::start_tag('p');
    echo \html_writer::tag('h1', get_string('singleexamdate_header', 'block_eledia_adminexamdates'));
    echo \html_writer::end_tag('p');
    echo \html_writer::start_tag('div', array('class' => 'row'));
    echo \html_writer::start_tag('div', array('class' => 'col-md-4'));
    echo block_eledia_adminexamdates\util::getexamdateoverview($blockid, $examdateid, $newblock);
    echo \html_writer::end_tag('div');
    echo \html_writer::start_tag('div', array('class' => 'col-md-8'));
    echo \html_writer::start_tag('div', array('class' => 'card'));
    echo \html_writer::start_tag('div', array('class' => 'card-body'));
    echo \html_writer::tag('h5', '', array('class' => 'card-title'));
    echo \html_writer::start_tag('p', array('class' => 'card-text'));
    $examblocks = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid]);
    $index = 1;
    $buttons = '';
    foreach ($examblocks as $examblock) {
        $url = new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['blockid' => $examblock->id]);
        $date = $index . '. ' . get_string('partialdate', 'block_eledia_adminexamdates');
        if ($blockid != $examblock->id) {
            $buttons .= $OUTPUT->single_button($url, $date, 'post');
        } else {
            $buttons .= \html_writer::start_tag('div', array('class' => 'singlebutton'));
            $buttons .= \html_writer::tag('button', $date, array('disabled' => true, 'class' => 'btn '));
            $buttons .= \html_writer::end_tag('div');
        }
        $index++;
    }
    $newpartialdateurl = new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php',
        ['examdateid' => $examdateid,'newblock'=>1]);
    $buttons .= $OUTPUT->single_button($newpartialdateurl, get_string('newpartialdate', 'block_eledia_adminexamdates'), 'post');
    echo $buttons;
    $mform->display();
    echo \html_writer::end_tag('p');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    $PAGE->requires->js_call_amd('block_eledia_adminexamdates/editsingleexamdate', 'init');
    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
} else {
    if (!empty($formdata = $mform->get_data())) {

        $examdateid = block_eledia_adminexamdates\util::savesingleexamdate($formdata);
    }
    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php'));

}
