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
    $examdatename=$DB->get_record('eledia_adminexamdates',['id'=>$confirmexamdate],'examname');
    $message = get_string('confirmexamdatemsg', 'block_eledia_adminexamdates', ['name' => $examdatename->examname]);
    $formcontinue = new single_button(new moodle_url($PAGE->url, ['confirmexamdateyes' => $confirmexamdate]), get_string('yes'), 'post');
    $formcancel = new single_button(new moodle_url($PAGE->url), get_string('no'));
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    echo $OUTPUT->confirm($message, $formcontinue, $formcancel);
    echo $OUTPUT->box_end();

} else if (!empty($cancelexamdate)) {
    $examdatename=$DB->get_record('eledia_adminexamdates',['id'=>$cancelexamdate],'examname');
    $message = get_string('cancelexamdatemsg', 'block_eledia_adminexamdates', ['name' => $examdatename->examname]);
    $formcontinue = new single_button(new moodle_url($PAGE->url, ['cancelexamdateyes' => $cancelexamdate]), get_string('yes'), 'post');
    $formcancel = new single_button(new moodle_url($PAGE->url), get_string('no'));
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    echo $OUTPUT->confirm($message, $formcontinue, $formcancel);
    echo $OUTPUT->box_end();

} else if (!empty($confirmexamdateyes)) {
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');


    block_eledia_adminexamdates\util::examconfirm($confirmexamdateyes);
    echo $OUTPUT->box_end();
    redirect(new moodle_url($PAGE->url));
} else if (!empty($cancelexamdateyes)) {
    $DB->delete_records('eledia_adminexamdates', ['id' => $cancelexamdateyes]);
    redirect(new moodle_url($PAGE->url));


} else {
    echo $OUTPUT->header();
    echo $OUTPUT->container_start();
    $url = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1]);
    $newexamdatebutton = new single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'), 'post');


    echo $OUTPUT->single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'), 'post');
    echo block_eledia_adminexamdates\util::getexamdateitems();
    echo $OUTPUT->container_end();
}


echo $OUTPUT->footer();



