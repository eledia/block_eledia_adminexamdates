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

$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$title = get_string('statistics', 'block_eledia_adminexamdates');
$PAGE->set_title($title);
$PAGE->set_pagelayout('course');

$onlynumberstudents = false;

$mform = new \block_eledia_adminexamdates\forms\statistics_form();

// Execute the form.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php'));
} else {
    $formdata = $mform->get_data();
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
    echo \html_writer::tag('button', get_string('statistics', 'block_eledia_adminexamdates'),
            array('disabled' => true, 'class' => 'btn '));
    echo \html_writer::end_tag('div');

    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');

    echo \html_writer::start_tag('div', array('class' => 'row mt-3'));
    echo \html_writer::start_tag('div', array('class' => 'col-xs-6'));
    $mform->display();

    echo \html_writer::end_tag('div');
    if (!empty($formdata)) {
        echo \html_writer::start_tag('div', array('class' => 'col-xs-6'));
        echo block_eledia_adminexamdates\util::get_html_statisticstable($formdata);
        echo \html_writer::end_tag('div');
    }
    echo \html_writer::end_tag('div');
    echo \html_writer::end_tag('div');
    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
}

