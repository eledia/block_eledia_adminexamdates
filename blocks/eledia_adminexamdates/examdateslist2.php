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
    $formcontinue = new single_button(new moodle_url($PAGE->url, ['confirmexamdateyes' => $confirmexamdate]), get_string('yes'));
    $formcancel = new single_button(new moodle_url($PAGE->url), get_string('no'));
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    echo $OUTPUT->confirm($message, $formcontinue, $formcancel);
    echo $OUTPUT->box_end();

} else if (!empty($cancelexamdate)) {
    $examdatename = $DB->get_record('eledia_adminexamdates', ['id' => $cancelexamdate], 'examname');
    $message = get_string('cancelexamdatemsg', 'block_eledia_adminexamdates', ['name' => $examdatename->examname]);
    $formcontinue = new single_button(new moodle_url($PAGE->url, ['cancelexamdateyes' => $cancelexamdate]), get_string('yes'));
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
        block_eledia_adminexamdates\util::examcancel($confirmexamdateyes);
    }
    echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/datatables/datatables.min.js"></script>';
    $PAGE->requires->css('/blocks/eledia_adminexamdates/styles/datatables.min.css');
   //$PAGE->requires->js_call_amd('block_eledia_adminexamdates/examdateslist_datatables', 'init');
    echo $OUTPUT->header();
    echo $OUTPUT->container_start();

    $url = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1, 'url' => rawurlencode($myurl)]);
    $newexamdatebutton = new single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'));
    $urlcalendar = new moodle_url('/blocks/eledia_adminexamdates/calendar.php');
    $unconfirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php');

    echo \html_writer::start_tag('div',array('class' => 'container-fluid px-4'));
    echo \html_writer::start_tag('div',array('class' => 'row'));
    echo \html_writer::start_tag('div',array('class' => 'col-md-12'));
    echo $OUTPUT->single_button($urlcalendar, get_string('calendar_btn', 'block_eledia_adminexamdates'));
    echo \html_writer::start_tag('div', array('class' => 'singlebutton'));
    echo \html_writer::tag('button', get_string('examdateslist_btn', 'block_eledia_adminexamdates'), array('disabled' => true, 'class' => 'btn '));
    echo \html_writer::end_tag('div');
    echo $OUTPUT->single_button($unconfirmed, get_string('unconfirmed_btn', 'block_eledia_adminexamdates'));
    echo $OUTPUT->single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'));
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::start_tag('div',array('class' => 'row'));
    echo \html_writer::start_tag('div',array('class' => 'col-md-12'));
    echo \html_writer::tag('h1', get_string('examdateslist_btn', 'block_eledia_adminexamdates'));


    $urleditsingleexamdate = new moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['blockid' => '']);
    echo $OUTPUT->box($OUTPUT->single_button($urleditsingleexamdate, ''), 'd-none', 'editsingleexamdate');

    echo '
 <div id="btn-place" style="width: 25px; height: 25px;"></div>
<table id="table1">
<thead> <tr>
            <th>Subscriber ID</th>
            <th>Install Location</th>

        </tr> </thead> <tbody> </tbody>
</table>
';

echo '<script> var data = [
        [
            "Tiger Nixon",
            "System Architect",
        ],
        [
            "Garrett Winters",
            "Director",
        ]
    ];
    $(document).ready(function() {
        var table =$("#table1").DataTable({
            buttons: [ "copy", "excel", "pdf" ],
            data: data
        });
$("#btn-place").html(table.buttons().container());
$("#table1").removeClass("dataTable");
});
    </script>';

    ///////////////////////


///

    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo $OUTPUT->container_end();


}


echo $OUTPUT->footer();