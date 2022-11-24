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

$confirmexamdate = optional_param('confirmexamdate', 0, PARAM_INT);
$cancelexamdate = optional_param('cancelexamdate', 0, PARAM_INT);
$confirmexamdateyes = optional_param('confirmexamdateyes', 0, PARAM_INT);
$cancelexamdateyes = optional_param('cancelexamdateyes', 0, PARAM_INT);

$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('examdaterequest', 'block_eledia_adminexamdates'));
$PAGE->set_pagelayout('course');

if (!empty($confirmexamdate)) {
    $examdatename = $DB->get_record('eledia_adminexamdates', ['id' => $confirmexamdate], 'examname');
    $message = get_string('confirmexamdatemsg', 'block_eledia_adminexamdates', ['name' => $examdatename->examname]);
    $formcontinue =
            new single_button(new moodle_url($PAGE->url, ['confirmexamdateyes' => $confirmexamdate]), get_string('yes'));
    $formcancel = new single_button(new moodle_url($PAGE->url), get_string('no'));
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    echo $OUTPUT->confirm($message, $formcontinue, $formcancel);
    echo $OUTPUT->box_end();

} else if (!empty($cancelexamdate)) {
    $examdatename = $DB->get_record('eledia_adminexamdates', ['id' => $cancelexamdate], 'examname');
    $message = get_string('cancelexamdatemsg', 'block_eledia_adminexamdates', ['name' => $examdatename->examname]);
    $formcontinue =
            new single_button(new moodle_url($PAGE->url, ['cancelexamdateyes' => $cancelexamdate]), get_string('yes'));
    $formcancel = new single_button(new moodle_url($PAGE->url), get_string('no'));
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    echo $OUTPUT->confirm($message, $formcontinue, $formcancel);
    echo $OUTPUT->box_end();

} else {
    if (!empty($confirmexamdateyes)) {
        block_eledia_adminexamdates\util::examconfirm($confirmexamdateyes);
    }
    if (!empty($cancelexamdateyes)) {
        block_eledia_adminexamdates\util::examcancel($cancelexamdateyes);
    }
    echo $OUTPUT->header();
    echo $OUTPUT->container_start();

    $url = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1, 'url' => rawurlencode($myurl)]);
    $newexamdatebutton = new single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'));
    $urlcalendar = new moodle_url('/blocks/eledia_adminexamdates/calendar.php');
    $urllist = new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php');
    $confirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesconfirmed.php');
    $hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());
    $statistics = new moodle_url('/blocks/eledia_adminexamdates/statistics.php', ['url' => rawurlencode($myurl)]);

    echo \html_writer::start_tag('div', array('class' => 'container-fluid px-4'));
    echo \html_writer::start_tag('div', array('class' => 'row'));
    echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
    echo $OUTPUT->single_button($urlcalendar, get_string('calendar_btn', 'block_eledia_adminexamdates'));
    if ($hasconfirmexamdatescap) {
        echo $OUTPUT->single_button($urllist, get_string('examdateslist_btn', 'block_eledia_adminexamdates'));
    }
    echo \html_writer::start_tag('div', array('class' => 'singlebutton mb-3'));
    echo \html_writer::tag('button', get_string('unconfirmed_btn', 'block_eledia_adminexamdates'),
            array('disabled' => true, 'class' => 'btn '));
    echo \html_writer::end_tag('div');
    if (!$hasconfirmexamdatescap) {
        echo $OUTPUT->single_button($confirmed, get_string('confirmed_btn', 'block_eledia_adminexamdates'));
    }
    echo $OUTPUT->single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'));
    if ($hasconfirmexamdatescap) {
        echo $OUTPUT->single_button($statistics, get_string('statistics', 'block_eledia_adminexamdates'));
        $urlReport = new moodle_url('/mod/elediachecklist/terminreport.php');
        echo $OUTPUT->single_button($urlReport, get_string('report_button', 'elediachecklist'), 'get');
    }

    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::start_tag('div', array('id'=>'examdatesunconfirmed-container'));
    $PAGE->requires->js_call_amd('block_eledia_adminexamdates/examdatesunconfirmed','annotationText');
    echo \html_writer::start_tag('div', array('class' => 'row'));
    echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
    echo block_eledia_adminexamdates\util::getexamdateitems(false);
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');


    //echo '<div class="modal fade" id="examdatesunconfirmed-modal-annotationtext" tabindex="-1" role="dialog">';
    //echo '<div class="modal-dialog" role="document">';
    //echo '<div class="modal-content">';
    //echo '<div class="modal-header">';
    //echo '<h3 class="modal-title">' . get_string('annotationtext', 'block_eledia_adminexamdates') .
    //        ' <span class="modal-title-exam"></span></h3>';
    //echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button>';
    //echo '</div>';
    //echo '<div class="modal-body"></div>';
    //echo '</div>';
    //echo '</div>';
    //echo '</div>';

 //   echo " <script>
 //   $(document).ready(function(){
 //
 //$('#examdatesunconfirmed-container').on('click', '.annotation-text-link',function(event){
 //     console.log(event);
 //
 //      var modal = $('#modal-annotationtext');
 //      modal.find('.modal-title-exam').html(' - test');
 //       modal.modal('show');
 //       });
 //});
 //
 //
 //       $('a.item-delete').on('click', function(e) {
 //       var clickedLink = $(e.currentTarget);
 //       ModalFactory.create({
 //           type: ModalFactory.types.SAVE_CANCEL,
 //           title: 'Delete item',
 //           body: 'Do you really want to delete?',
 //       })
 //       .then(function(modal) {
 //           modal.setSaveButtonText('Delete');
 //           var root = modal.getRoot();
 //           root.on(ModalEvents.save, function() {
 //               var elementid = clickedLink.data('id');
 //               // Do something to delete item
 //           });
 //           modal.show();
 //   });
 //     </script>  ";
    echo $OUTPUT->container_end();
}

echo $OUTPUT->footer();



