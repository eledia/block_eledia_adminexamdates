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

defined('MOODLE_INTERNAL') || die();
global $DB;
if ($ADMIN->fulltree) {

    $configs = array();
    $configs[] = new admin_setting_heading('block_eledia_adminexamdates_header', '',
            get_string('configure_description', 'block_eledia_adminexamdates'));

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/apidomain',
            get_string('setting_apidomain', 'block_eledia_adminexamdates'),
            '', '', PARAM_URL, 60);

    $configs[] = new admin_setting_configpasswordunmask('block_eledia_adminexamdates/apitoken', get_string('setting_apitoken',
            'block_eledia_adminexamdates'), get_string('config_apitoken',
            'block_eledia_adminexamdates'), '');

    $configs[] = new admin_setting_configtextarea('examrooms',
            new lang_string('examrooms', 'block_eledia_adminexamdates'),
            new lang_string('config_examrooms', 'block_eledia_adminexamdates'),
            get_string('examrooms_default', 'block_eledia_adminexamdates'), PARAM_RAW, '15', '5');

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/envcategoryidnumber',
            get_string('setting_envcategoryidnumber', 'block_eledia_adminexamdates'),
            get_string('config_envcategoryidnumber', 'block_eledia_adminexamdates'), 'EXAMENV', PARAM_RAW);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/archivecategoryidnumber',
            get_string('setting_archivecategoryidnumber', 'block_eledia_adminexamdates'),
            get_string('config_archivecategoryidnumber', 'block_eledia_adminexamdates'), 'EXAMARCHIVE', PARAM_RAW);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/examcoursetemplateidnumber',
            get_string('setting_examcoursetemplateidnumber', 'block_eledia_adminexamdates'),
            get_string('config_examcoursetemplateidnumber', 'block_eledia_adminexamdates'), 'EXAMTEMPLATE', PARAM_RAW);

    $departmentchoices = unserialize(get_config('block_eledia_adminexamdates', 'departmentchoices'));
    $envcategoryidnumber = get_config('block_eledia_adminexamdates', 'envcategoryidnumber');
    if ((get_config('block_eledia_adminexamdates', 'reloaddepartments')
            && !empty($envcategoryidnumber))) {
        $subcategories = block_eledia_adminexamdates\util::get_sub_categories('idnumber', $envcategoryidnumber);
        if (!empty($subcategories)) {
            $departmentchoices = $subcategories;
            set_config('reloaddepartments', 0, 'block_eledia_adminexamdates');
            set_config('departmentchoices', serialize($departmentchoices), 'block_eledia_adminexamdates');
        }
    }

    $configs[] = new admin_setting_configmultiselect('block_eledia_adminexamdates/departments',
            new lang_string('departments', 'block_eledia_adminexamdates'),
            new lang_string('config_departments', 'block_eledia_adminexamdates'),
            array(), $departmentchoices);

    $configs[] = new admin_setting_configcheckbox('block_eledia_adminexamdates/reloaddepartments',
            new lang_string('reloaddepartments', 'block_eledia_adminexamdates'),
            new lang_string('configreloaddepartments', 'block_eledia_adminexamdates'), 0);

    $configs[] = new admin_setting_configtime('block_eledia_adminexamdates/startexam_hour',
            'startexam_minute', new lang_string('setting_startexam',
                    'block_eledia_adminexamdates'), '', array('h' => 9, 'm' => 15));

    $configs[] = new admin_setting_configtime('block_eledia_adminexamdates/endexam_hour',
            'endexam_minute', new lang_string('setting_endexam',
                    'block_eledia_adminexamdates'), '', array('h' => 19, 'm' => 0));

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/startcalendar',
            new lang_string('setting_startcalendar', 'block_eledia_adminexamdates'),
            '', 7, PARAM_INT);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/endcalendar',
            new lang_string('setting_endcalendar', 'block_eledia_adminexamdates'),
            '', 19, PARAM_INT);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/breakbetweenblockdates',
            new lang_string('setting_breakbetweenblockdates', 'block_eledia_adminexamdates'),
            '', 85, PARAM_INT);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/distancebetweenblockdates',
            new lang_string('setting_distancebetweenblockdates', 'block_eledia_adminexamdates'),
            '', 100, PARAM_INT);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/emailexamteam',
            new lang_string('setting_emailexamteam', 'block_eledia_adminexamdates'),
            '', '', PARAM_EMAIL);

    $configs[] = new admin_setting_configtext('responsiblepersons',
            new lang_string('responsiblepersons', 'block_eledia_adminexamdates'),
            new lang_string('config_responsiblepersons', 'block_eledia_adminexamdates'),
            '', PARAM_RAW, '40');

    $syscontext = context_system::instance();
    if ($cohorts = $DB->get_records('cohort', ['contextid' => $syscontext->id, 'visible' => 1], 'name')) {
        $cohorts = array_column($cohorts, 'name', 'id');
    }
    $configs[] = new admin_setting_configmultiselect('block_eledia_adminexamdates/examinercohorts',
            new lang_string('setting_examinercohorts', 'block_eledia_adminexamdates'),
            new lang_string('config_examinercohorts', 'block_eledia_adminexamdates'),
            array(), $cohorts);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/bordercolor1',
            get_string('setting_bordercolor_unconfirmed_dates',
                    'block_eledia_adminexamdates'), get_string('config_bordercolor_unconfirmed_dates',
                    'block_eledia_adminexamdates'), '#576874', PARAM_RAW, 8);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/bordercolor2',
            get_string('setting_bordercolor_unavailable_dates',
                    'block_eledia_adminexamdates'), get_string('config_bordercolor_unavailable_dates',
                    'block_eledia_adminexamdates'), '#576874', PARAM_RAW, 8);

    $configs[] = new admin_setting_configtextarea('holidays',
            new lang_string('holidays', 'block_eledia_adminexamdates'),
            new lang_string('config_holidays', 'block_eledia_adminexamdates'),
            '', PARAM_RAW, '15', '5');

    $options = [];
    $options[0] = get_string('choose');
    $sql = "SELECT cm.id, cl.name 
                FROM {elediachecklist} cl
                JOIN {course_modules} cm ON cm.instance = cl.id
                JOIN {modules} m ON m.id = cm.module AND m.name = :mname                                     
                WHERE m.visible = 1";
    $params = array('mname' => 'elediachecklist');

    if ($cminstances = $DB->get_records_sql($sql, $params)) {
        $options += array_column($cminstances, 'name', 'id');
    }
    $configs[] = new admin_setting_configselect('block_eledia_adminexamdates/instanceofmodelediachecklist',
            get_string('setting_instanceofmodelediachecklist', 'block_eledia_adminexamdates'),
            get_string('config_instanceofmodelediachecklist', 'block_eledia_adminexamdates'),
            '',
            $options);
    //
    //$options = [];
    //$options[0] = get_string('choose');
    //$sql = "SELECT cm.id, d.name
    //            FROM {data} d
    //            JOIN {course_modules} cm ON cm.instance = d.id
    //            JOIN {modules} m ON m.id = cm.module AND m.name = :mname
    //            WHERE m.visible = 1";
    //$params = array('mname' => 'data');
    //
    //if ($cminstances = $DB->get_records_sql($sql, $params)) {
    //    $options += array_column($cminstances, 'name', 'id');
    //}
    //
    //$configs[] = new admin_setting_configselect('block_eledia_adminexamdates/instanceofproblemdb',
    //        get_string('setting_instanceofmodproblemdb', 'block_eledia_adminexamdates'),
    //        get_string('config_instanceofmodproblemdb', 'block_eledia_adminexamdates'),
    //        'showboth',
    //        $options);

    foreach ($configs as $config) {
        $config->plugin = 'block_eledia_adminexamdates';
        $settings->add($config);
    }
}