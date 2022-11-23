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

$save = optional_param('save', 0, PARAM_INT);
$examdateid = optional_param('examdateid', 0, PARAM_INT);
$blockid = optional_param('blockid', 0, PARAM_INT);
$newblock = optional_param('newblock', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$deleteyes = optional_param('deleteyes', 0, PARAM_INT);

$myurl = new \moodle_url($FULLME);
$titlestring = get_string('examdate_header', 'block_eledia_adminexamdates') . ': ' . get_string('singleexamdate_header', 'block_eledia_adminexamdates');
$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_title($titlestring);
$PAGE->set_pagelayout('standard');


if (!empty($blockid)) {
    $examdateid = $DB->get_record('eledia_adminexamdates_blocks', ['id' => $blockid])->examdateid;
} else if (!empty($examdateid)) {
    $blocks = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid], 'blocktimestart');
    $blockid = array_shift($blocks)->id;
}

$mform = new \block_eledia_adminexamdates\forms\singleexamdate_form(null, array('blockid' => $blockid, 'examdateid' => $examdateid));

// Execute the form.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php'));
} else if ($delete) {
    $examdatename = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid])->examname;
    $blocks = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid], 'blocktimestart');
    $pos = array_search($delete, array_keys($blocks)) + 1;
    $posstring = count($blocks) > 1 ? $pos . '. ' : '';
    $message = get_string('confirm_delete_singleexamdate_msg', 'block_eledia_adminexamdates', ['index' => $posstring, 'name' => $examdatename]);
    $formcontinue = new single_button(new moodle_url($PAGE->url, ['blockid' => $blockid, 'deleteyes' => $delete]), get_string('yes'));
    $formcancel = new single_button(new moodle_url($PAGE->url, ['blockid' => $blockid]), get_string('no'));
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    echo $OUTPUT->confirm($message, $formcontinue, $formcancel);
    echo $OUTPUT->box_end();
} else if (($blockid || $newblock || $deleteyes || empty($formdata = $mform->get_data())) && !$save) {
    if ($deleteyes) {
        $blockid = block_eledia_adminexamdates\util::deletesingleexamdate($blockid, $deleteyes, $examdateid);
    }
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
    $url = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1, 'url' => rawurlencode($myurl)]);
    $newexamdatebutton = new single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'));
    $urlcalendar = new moodle_url('/blocks/eledia_adminexamdates/calendar.php');
    $urllist = new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php');
    $unconfirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php');
    $statistics = new moodle_url('/blocks/eledia_adminexamdates/statistics.php');

    echo \html_writer::start_tag('div', array('class' => 'container-fluid px-4'));
    echo \html_writer::start_tag('div', array('class' => 'row'));
    echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
    echo $OUTPUT->single_button($urlcalendar, get_string('calendar_btn', 'block_eledia_adminexamdates'));
    echo $OUTPUT->single_button($urllist, get_string('examdateslist_btn', 'block_eledia_adminexamdates'));
    echo $OUTPUT->single_button($unconfirmed, get_string('unconfirmed_btn', 'block_eledia_adminexamdates'));
    echo $OUTPUT->single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'));
    echo $OUTPUT->single_button($statistics, get_string('statistics', 'block_eledia_adminexamdates'));
    $urlReport = new moodle_url('/mod/elediachecklist/terminreport.php');
    echo $OUTPUT->single_button($urlReport, get_string('report_button', 'elediachecklist'), 'get');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');

    echo \html_writer::start_tag('div', array('class' => 'row mt-3'));
    echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
    $editexamdateurl = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['editexamdate' => $examdateid, 'url' => rawurlencode($myurl)]);
    echo $OUTPUT->single_button($editexamdateurl, get_string('editexamdate_btn', 'block_eledia_adminexamdates'));
    $checklistlink =  new \moodle_url(get_string('checklistlink', 'block_eledia_adminexamdates') . $examdateid);
    echo \html_writer::start_tag('div', array('class' => 'singlebutton'));
    echo \html_writer::tag('button', get_string('singleexamdate_btn', 'block_eledia_adminexamdates'), array('disabled' => true, 'class' => 'btn '));
    echo \html_writer::end_tag('div');
    echo $OUTPUT->single_button($checklistlink, get_string('checklist_btn', 'block_eledia_adminexamdates'));

    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');

    echo \html_writer::start_tag('div', array('class' => 'row mt-4'));
    echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
    echo \html_writer::start_tag('div', array('class' => 'row'));
    echo \html_writer::start_tag('div', array('class' => 'col-md-4'));
    echo block_eledia_adminexamdates\util::getexamdateoverview($blockid, $examdateid, $newblock);
    echo \html_writer::end_tag('div');
    echo \html_writer::start_tag('div', array('class' => 'col-md-8'));
    echo \html_writer::start_tag('div', array('class' => 'card'));
    echo \html_writer::start_tag('div', array('class' => 'card-body'));
    echo \html_writer::tag('h5', '', array('class' => 'card-title'));
    echo \html_writer::start_tag('p', array('class' => 'card-text'));
    $examblocks = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid], 'blocktimestart');
    $index = 1;
    $buttons = '';
    foreach ($examblocks as $examblock) {
        $url = new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['blockid' => $examblock->id]);
        $viewindex = (count($examblocks) > 1 || $newblock) ? $index . '. ' : '';
        $date = $viewindex . get_string('partialdate', 'block_eledia_adminexamdates');
        if ($blockid != $examblock->id || $newblock) {
            $buttons .= $OUTPUT->single_button($url, $date);
        } else {
            $buttons .= \html_writer::start_tag('div', array('class' => 'singlebutton'));
            $buttons .= \html_writer::tag('button', $date, array('disabled' => true, 'class' => 'btn '));
            $buttons .= \html_writer::end_tag('div');
        }
        $index++;
    }
    if ($newblock) {
        $buttons .= \html_writer::start_tag('div', array('class' => 'singlebutton'));
        $buttons .= \html_writer::tag('button', $index . '. ' . get_string('partialdate', 'block_eledia_adminexamdates'), array('disabled' => true, 'class' => 'btn '));
        $buttons .= \html_writer::end_tag('div');
    } else {
        $newpartialdateurl = new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php',
            ['examdateid' => $examdateid, 'newblock' => 1]);
        $buttons .= $OUTPUT->single_button($newpartialdateurl, get_string('newpartialdate', 'block_eledia_adminexamdates'));
    }
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
} else if ($save) {
    if (!empty($formdata = $mform->get_data())) {
        $formcontinue = new single_button(new moodle_url($PAGE->url, ['blockid' => $blockid]), get_string('yes'));
        $blockid = block_eledia_adminexamdates\util::savesingleexamdate($formdata);

        $examdatename = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid])->examname;
        $blocks = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid], 'blocktimestart');
        $pos = array_search($blockid, array_keys($blocks)) + 1;
        $posstring = count($blocks) > 1 ? $pos . '. ' : '';

        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox', 'notice');
        echo \html_writer::start_tag('div', ['class' => 'text-center mb-3']);
        echo get_string('confirm_save_singleexamdate_msg', 'block_eledia_adminexamdates', ['name' => "$examdatename", 'index' => $posstring]);
        echo \html_writer::end_tag('div');
        echo $OUTPUT->continue_button(new moodle_url($PAGE->url, ['blockid' => $blockid]));
        echo $OUTPUT->box_end();
    }

} else {

    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php'));

}
