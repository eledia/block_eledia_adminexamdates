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
 * Script to manage rooms config.
 *
 * @package    block_eledia_adminexamdates
 * @author     René Hansen <support@eledia.de>
 * @copyright  2023 eLeDia GmbH
 */

global $DB, $CFG, $PAGE, $OUTPUT, $USER;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();

$deleteroomconfig = optional_param('deleteroomconfig', 0, PARAM_INT);

$sitecontext = context_system::instance();
if (!has_capability('moodle/site:config', $sitecontext)) {
    print_error('cannotaccessroomconfig');
}

$baseurl = new moodle_url('/blocks/eledia_adminexamdates/manageroomconfig.php');
$PAGE->set_url($baseurl);
$PAGE->set_context($sitecontext);
// Process any actions
if ($deleteroomconfig && confirm_sesskey()) {
    $DB->delete_records('eledia_adminexamdates_cfg_r', array('id' => $deleteroomconfig));

    redirect($PAGE->url, get_string('roomconfigdeleted', 'block_eledia_adminexamdates'));
}

$roomconfigs = $DB->get_records('eledia_adminexamdates_cfg_r', null, 'specialroom,roomid');
$roomwithcapacity = $DB->record_exists('eledia_adminexamdates_cfg_r', ['specialroom' => false]);

$strmanage = get_string('manage_roomconfig', 'block_eledia_adminexamdates');

$PAGE->set_title($strmanage);
$PAGE->set_pagelayout('standard');

$manageroomconfig = new moodle_url('/blocks/eledia_adminexamdates/manageroomconfig.php');
$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_eledia_adminexamdates'));
$PAGE->navbar->add(get_string('manage_roomconfig', 'block_eledia_adminexamdates'), $manageroomconfig);
echo $OUTPUT->header();
echo $OUTPUT->heading($strmanage);
$table = new flexible_table('display-roomconfig');

$table->define_columns(array('roomid', 'name', 'capacity', 'color', 'specialroom', 'actions'));
$table->define_headers(array(get_string('roomconfig_roomid', 'block_eledia_adminexamdates'),
        get_string('roomconfig_name', 'block_eledia_adminexamdates'),
        get_string('roomconfig_capacity', 'block_eledia_adminexamdates'),
        get_string('roomconfig_color', 'block_eledia_adminexamdates'),
        get_string('roomconfig_specialroom', 'block_eledia_adminexamdates'),
        get_string('actions', 'moodle')));
$table->define_baseurl($baseurl);

$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'roomconfig');
$table->set_attribute('class', 'generaltable generalbox mt-4');
$table->column_class('roomid', 'roomid');
$table->column_class('name', 'name');
$table->column_class('capacity', 'capacity');
$table->column_class('color', 'color');
$table->column_class('specialroom', 'specialroom');
$table->column_class('actions', 'actions');

$table->setup();

foreach ($roomconfigs as $roomconfig) {

    $editurl = new moodle_url('/blocks/eledia_adminexamdates/editroomconfig.php', ['id' => $roomconfig->id]);
    $editaction = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));

    $deleteurl = new moodle_url('/blocks/eledia_adminexamdates/manageroomconfig.php',
            ['deleteroomconfig' => $roomconfig->id, 'sesskey' => sesskey()]);
    $deleteicon = new pix_icon('t/delete', get_string('delete'));
    $deleteaction = $OUTPUT->action_icon($deleteurl, $deleteicon,
            new confirm_action(get_string('deleteroomconfigconfirm', 'block_eledia_adminexamdates')));

    $roomconfigicons = $editaction . ' ' . $deleteaction;
    $roomcolor = \html_writer::tag('div', $roomconfig->color,
            array('style' => 'background-color:' . $roomconfig->color));
    $specialroomiconclass = ($roomconfig->specialroom) ? 'fa-check-square-o' : 'fa-square-o';
    $specialroomicon = \html_writer::tag('i', '',
            ['class' => 'icon fa ' . $specialroomiconclass]);
    $table->add_data(array($roomconfig->roomid, $roomconfig->name, $roomconfig->capacity, $roomcolor,
            $specialroomicon, $roomconfigicons));
}

if (empty($roomwithcapacity)) {
    echo $OUTPUT->box(\html_writer::tag('span', get_string('no_roomconfig', 'block_eledia_adminexamdates'),
            ['class' => 'mt-3 mb-3 font-weight-bold']));
}
$table->finish_output();

$url = new moodle_url('/blocks/eledia_adminexamdates/editroomconfig.php');
echo $OUTPUT->box($OUTPUT->single_button($url, get_string('addnewroomconfig', 'block_eledia_adminexamdates')),
        'clearfix mt-3 mdl-right');

$urlblocksetting = new moodle_url('/admin/settings.php', ['section' => 'blocksettingeledia_adminexamdates']);
echo \html_writer::tag('a', get_string('backtoblocksetting', 'block_eledia_adminexamdates'),
        ['href' => $urlblocksetting, 'class' => 'mt-3']);

echo $OUTPUT->footer();
