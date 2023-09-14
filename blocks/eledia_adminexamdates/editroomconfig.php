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
 * Script to let a user edit a room config.
 *
 * @package    block_eledia_adminexamdates
 * @author     René Hansen <support@eledia.de>
 * @copyright  2023 eLeDia GmbH
 */

require_once(__DIR__ . '/../../config.php');
global $DB, $PAGE, $OUTPUT;


$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$roomconfigid = optional_param('id', 0, PARAM_INT); // 0 mean create new.


require_login();

$sitecontext = context_system::instance();
if (!has_capability('moodle/site:config', $sitecontext)) {
    print_error('cannotaccessroomconfig');
}

$urlparams = array('id' => $roomconfigid);

if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}
$manageroomconfig = new moodle_url('/blocks/eledia_adminexamdates/manageroomconfig.php', $urlparams);

$PAGE->set_url('/blocks/eledia_adminexamdates/editroomconfig.php', $urlparams);
$PAGE->set_context($sitecontext);
$PAGE->set_pagelayout('standard');

if ($roomconfigid) {
    $isadding = false;
    $roomconfigrecord = $DB->get_record('eledia_adminexamdates_cfg_r', array('id' => $roomconfigid), '*', MUST_EXIST);
} else {
    $isadding = true;
    $roomconfigrecord = new stdClass;
}

$mform = new \block_eledia_adminexamdates\forms\roomconfig_form($PAGE->url, ['isadding' => $isadding]);

$mform->set_data($roomconfigrecord);

if ($mform->is_cancelled()) {
    redirect($manageroomconfig);

} else if ($data = $mform->get_data()) {

    if ($isadding) {
        $DB->insert_record('eledia_adminexamdates_cfg_r', $data);
    } else {
        $data->id = $roomconfigid;

        $DB->update_record('eledia_adminexamdates_cfg_r', $data);
    }

    redirect($manageroomconfig);

} else {
    if ($isadding) {
        $strtitle = get_string('addnewroomconfig', 'block_eledia_adminexamdates');
    } else {
        $strtitle = get_string('editaroomconfig', 'block_eledia_adminexamdates');
    }

    $PAGE->set_title($strtitle);
    $PAGE->set_heading($strtitle);

    $PAGE->navbar->add(get_string('block'));
    $PAGE->navbar->add(get_string('pluginname', 'block_eledia_adminexamdates'));
    $PAGE->navbar->add(get_string('manage_roomconfig', 'block_eledia_adminexamdates'), $manageroomconfig);
    $PAGE->navbar->add($strtitle);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle, 2);
    echo '<br>&nbsp</br>';
    $mform->display();

    echo $OUTPUT->footer();
}

