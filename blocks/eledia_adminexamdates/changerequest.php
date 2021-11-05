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

$examdateid = optional_param('examdateid', 0, PARAM_INT);
$mailsend = optional_param('mailsend', 0, PARAM_INT);

$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('examdaterequest', 'block_eledia_adminexamdates'));
$PAGE->set_pagelayout('course');

$mform = new \block_eledia_adminexamdates\forms\changerequest_form();


// Execute the form.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php'));
} else if (!empty($formdata = $mform->get_data()) && !empty($formdata->changerequesttext)) {
    block_eledia_adminexamdates\util::sendchengerequestemail($examdateid, $formdata->changerequesttext);
    redirect(new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php'));
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->container_start();
    $blockid = $DB->get_record('eledia_adminexamdates_blocks', ['examdateid' => $examdateid], 'id', IGNORE_MULTIPLE)->id;
    echo block_eledia_adminexamdates\util::getexamdateoverview($blockid, $examdateid, false);
    $mform->display();
    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
}
