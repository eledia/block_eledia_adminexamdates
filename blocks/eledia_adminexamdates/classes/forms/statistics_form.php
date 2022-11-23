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
 * The statistics.
 *
 * @package     block_eledia_adminexamdates
 * @copyright   2021 René Hansen <support@eledia.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_eledia_adminexamdates\forms;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->libdir . '/formslib.php');

class statistics_form extends \moodleform {

    public function definition() {
        global $DB, $USER;

        $mform =& $this->_form;

        $radioarray = array();
        $radioarray[] = $mform->createElement('radio', 'period', '',
                get_string('period_semester', 'block_eledia_adminexamdates'), 0);
        $radioarray[] = $mform->createElement('radio', 'period', '',
                get_string('period_date', 'block_eledia_adminexamdates'), 1);
        $mform->addGroup($radioarray, 'periods', get_string('select_period', 'block_eledia_adminexamdates'),
                array(' '), false);
        $mform->setDefault('period', 0);

        $years = [];
        $years[] = date('Y', strtotime('-1 year'));
        $years[] = date('Y');
        for ($i = 1; $i < 5; $i++) {
            $years[] = date('Y', strtotime('+' . $i . ' year'));
        }
        $options = [];
        foreach ($years as $key => $year) {
            $options[$year . '1'] = get_string('summersemester', 'block_eledia_adminexamdates') . ' ' . $year;
            if (array_key_exists($key + 1, $years)) {
                $options[$year . '2'] =
                        get_string('wintersemester', 'block_eledia_adminexamdates') . ' ' . $year . '/' . $years[$key + 1];
            }
        }

        $time = time();
        if ($time < strtotime("1 April")) {
            $defaultsemester = $years[0] . '2';
        } else if ($time < strtotime("1 October")) {
            $defaultsemester = $years[1] . '1';
        } else {
            $defaultsemester = $years[1] . '2';
        }

        $mform->addElement('select', 'semester',
                get_string('select_semester', 'block_eledia_adminexamdates'), $options);

        $mform->setType('semester', PARAM_INT);
        $mform->setDefault('semester', $defaultsemester);
        $mform->hideIf('semester','period', 'checked');

        $mform->addElement('date_selector', 'datestart', get_string('datestart', 'block_eledia_adminexamdates'));
        $mform->addElement('date_selector', 'dateend', get_string('dateend', 'block_eledia_adminexamdates'));

        $mform->hideIf('datestart','period', 'notchecked');
        $mform->hideIf('dateend','period', 'notchecked');

        $checkboxes= [];
        $checkboxes[] =
                $mform->createElement('advcheckbox', "category_regularexam", '',  get_string('category_regularexam', 'block_eledia_adminexamdates'));
        $checkboxes[] =
                $mform->createElement('advcheckbox', "category_semestertest", '',  get_string('category_semestertest', 'block_eledia_adminexamdates'));

        $mform->addGroup($checkboxes, 'categories', get_string('selection_exam_category', 'block_eledia_adminexamdates'), ['<br>'], false);
        $mform->setDefault('category_regularexam', 1);
        $mform->setDefault('category_semestertest', 1);

        $options = [];
        $departmentchoices = unserialize(get_config('block_eledia_adminexamdates', 'departmentchoices'));
        $departments = explode(',', get_config('block_eledia_adminexamdates', 'departments'));
        foreach ($departments as $department) {
            if (isset($departmentchoices[$department])) {
                $options[$department] = $departmentchoices[$department];
            }
        }
        $settings = array('multiple' => 'multiple');
        $mform->addElement('select', 'department',
                get_string('department', 'block_eledia_adminexamdates'), $options,$settings);
        $mform->addRule('department', null, 'required', null, 'client');
        $mform->setDefault('department',  array_keys($options));

        $mform->addElement('hidden', 'url');
        $mform->setType('url', PARAM_RAW);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('choose'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        return $errors;
    }
}

