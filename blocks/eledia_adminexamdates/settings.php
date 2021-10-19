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
if ($ADMIN->fulltree) {

    $configs = array();
    $configs[] = new admin_setting_heading('block_eledia_adminexamdates_header', '',
        get_string('configure_description', 'block_eledia_adminexamdates'));

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/apidomain', get_string('setting_apidomain', 'block_eledia_adminexamdates'),
        '', '', PARAM_URL, 60);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/apitoken', get_string('setting_apitoken',
        'block_eledia_adminexamdates'), get_string('config_apitoken',
        'block_eledia_adminexamdates'), '', PARAM_RAW);

    $configs[] = new admin_setting_configtextarea('examrooms',
        new lang_string('examrooms', 'block_eledia_adminexamdates'),
        new lang_string('config_examrooms', 'block_eledia_adminexamdates'),
        get_string('examrooms_default', 'block_eledia_adminexamdates'), PARAM_RAW, '15', '5');

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/envcategoryidnumber',
        get_string('setting_envcategoryidnumber', 'block_eledia_adminexamdates'),
        get_string('config_envcategoryidnumber', 'block_eledia_adminexamdates'), 'EXAMENV', PARAM_RAW);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/examcoursetemplateidnumber',
        get_string('setting_examcoursetemplateidnumber', 'block_eledia_adminexamdates'),
        get_string('config_examcoursetemplateidnumber', 'block_eledia_adminexamdates'), 'EXAMTEMPLATE', PARAM_RAW);


    $departmentchoices = unserialize(get_config('block_eledia_adminexamdates', 'departmentchoices'));
    $envcategoryidnumber = get_config('block_eledia_adminexamdates', 'envcategoryidnumber');
    if ((get_config('block_eledia_adminexamdates', 'reloaddepartments')
         && !empty($envcategoryidnumber))) {
        $subcategories = block_eledia_adminexamdates\util::get_sub_categories('idnumber',$envcategoryidnumber);
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

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/startexam',
        new lang_string('setting_startexam', 'block_eledia_adminexamdates'),
        '', 7, PARAM_INT);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/endexam',
        new lang_string('setting_endexam', 'block_eledia_adminexamdates'),
        '', 19, PARAM_INT);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/breakbetweenblockdates',
        new lang_string('setting_breakbetweenblockdates', 'block_eledia_adminexamdates'),
        '', 85, PARAM_INT);

    $configs[] = new admin_setting_configtext('block_eledia_adminexamdates/distancebetweenblockdates',
        new lang_string('setting_distancebetweenblockdates', 'block_eledia_adminexamdates'),
        '', 100, PARAM_INT);

    foreach ($configs as $config) {
        $config->plugin = 'block_eledia_adminexamdates';
        $settings->add($config);
    }
}