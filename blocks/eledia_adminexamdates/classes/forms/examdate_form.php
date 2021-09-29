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
 * The exam date request form.
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

class examdate_form extends \moodleform
{

    public function definition()
    {
        $hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());

        $mform =& $this->_form;

        $mform->addElement('header', '', get_string('examdate_header', 'block_eledia_adminexamdates'));
        $options = [];
        $rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));

        foreach ($rooms as $room) {
            $roomitems = explode('|', $room);
            $roomcapacity = !empty($roomitems[2]) ? ' (max. ' . $roomitems[2] . ' TN)' : '';
            $options[$roomitems[0]] = $roomitems[1] . $roomcapacity;
        };
        $settings = array('multiple' => 'multiple');
        if ($hasconfirmexamdatescap) {
            $mform->addElement('select', 'examroom',
                get_string('select_examroom', 'block_eledia_adminexamdates'), $options, $settings);
            $mform->addRule('examroom', null, 'required');
        } else {
            $mform->addElement('hidden', 'examroom');
        }
        $mform->setType('examroom', PARAM_RAW);
        $mform->setDefault('examroom', 'PR1');

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
                $options[$year . '2'] = get_string('wintersemester', 'block_eledia_adminexamdates') . ' ' . $year . '/' . $years[$key + 1];
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
        if ($hasconfirmexamdatescap) {
            $mform->addElement('select', 'semester',
                get_string('select_semester', 'block_eledia_adminexamdates'), $options);
            $mform->addRule('semester', null, 'required');
        } else {
            $mform->addElement('hidden', 'semester');
        }
        $mform->setType('semester', PARAM_INT);
        $mform->setDefault('semester', $defaultsemester);

        $options=[];
        $departmentchoices=unserialize(get_config('block_eledia_adminexamdates', 'departmentchoices'));
        $departments=explode(',',get_config('block_eledia_adminexamdates', 'departments'));
        foreach($departments as $department){
            if(isset($departmentchoices[$department])){
                $options[$department]=$departmentchoices[$department];
            }
        }

        $mform->addElement('select', 'department',
            get_string('department', 'block_eledia_adminexamdates'), $options);
        $mform->addRule('department', null, 'required');

        $mform->addElement('text', 'examname', get_string('examname', 'block_eledia_adminexamdates'), array('size' => 50));
        $mform->setType('examname', PARAM_TEXT);
        $mform->addRule('examname', null, 'required');

        $mform->addElement('text', 'numberstudents', get_string('number_students', 'block_eledia_adminexamdates'), array('size' => 4));
        $mform->setType('numberstudents', PARAM_INT);
        $mform->addRule('numberstudents', null, 'required');

        $mform->addElement('date_time_selector', 'examtimestart', get_string('examtimestart', 'block_eledia_adminexamdates'));
        $mform->addRule('examtimestart', null, 'required');

        $mform->addElement('text', 'examduration', get_string('examduration', 'block_eledia_adminexamdates'));
        $mform->setType('examduration', PARAM_INT);
        $mform->addRule('examduration', null, 'required');

        $mform->addElement('text', 'examiner', get_string('examiner', 'block_eledia_adminexamdates'), array('size' => 50));
        $mform->setType('examiner', PARAM_TEXT);
        $mform->addRule('examiner', null, 'required');

        $mform->addElement('text', 'contactperson', get_string('contactperson', 'block_eledia_adminexamdates'), array('size' => 50));
        $mform->setType('contactperson', PARAM_TEXT);
        $mform->addRule('contactperson', null, 'required');

        $mform->addElement('textarea', 'annotationtext', get_string('annotationtext', 'block_eledia_adminexamdates'), array('rows' => 10, 'cols' => 80));
        $mform->setType('annotationtext', PARAM_RAW);


        $string['department'] = 'Department';
        $string['examiner'] = 'Examiner';
        $string['contactperson'] = 'Contact person';


        $mform->addElement('hidden', 'examdateid');
        $mform->setType('examdateid', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}

