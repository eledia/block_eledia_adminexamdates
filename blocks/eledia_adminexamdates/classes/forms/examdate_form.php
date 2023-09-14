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
 * The special room booking form.
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

class examdate_form extends \moodleform {

    public function definition() {
        global $DB, $USER;
        $hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());

        $mform =& $this->_form;
        $onlynumberstudents =& $this->_customdata['onlynumberstudents'];
        $editexamdate =& $this->_customdata['editexamdate'];
        if ($editexamdate) {
            $examdate = $DB->get_record('eledia_adminexamdates', ['id' => $editexamdate]);
        }
        if ($hasconfirmexamdatescap) {
            $radioarray = array();
            $radioarray[] = $mform->createElement('radio', 'category', '',
                    get_string('category_regularexam', 'block_eledia_adminexamdates'), 0);
            $radioarray[] = $mform->createElement('radio', 'category', '',
                    get_string('category_semestertest', 'block_eledia_adminexamdates'), 1);
            $mform->addGroup($radioarray, 'categories', get_string('selection_exam_category', 'block_eledia_adminexamdates'),
                    array(' '), false);
            $mform->setDefault('category', 0);
        } else {
            $mform->addElement('hidden', 'category');
            $mform->setType('category', PARAM_INT);
            $mform->setDefault('category', 0);
        }

        list($in_sql, $in_params) =
                $DB->get_in_or_equal(explode(',', get_config('block_eledia_adminexamdates', 'examinercohorts')));
        $sql = "SELECT DISTINCT u.*
                  FROM {user} u
                  LEFT JOIN {cohort_members} cm ON cm.userid = u.id
                  LEFT JOIN {cohort} c ON c.id = cm.cohortid
                  WHERE u.deleted = 0 AND c.visible = 1 
                  AND c.id $in_sql
                  ORDER BY lastname, firstname ";

        $users = $DB->get_records_sql($sql, $in_params);
        $examinerlist = [0 => ''];
        foreach ($users as $id => $user) {
            $examinerlist[$id] = fullname($user) . ' | ' . $user->email;
        }

        $sql = "SELECT *
                  FROM {user} 
                 WHERE deleted = 0
                  ORDER BY lastname, firstname";

        $users = $DB->get_records_sql($sql);
        $contactpersonlist = [0 => ''];
        foreach ($users as $id => $user) {
            if ($user->id > 2) {
                $contactpersonlist[$id] = fullname($user) . ' | ' . $user->email;
            }
        }

        $autocompleteoptions = [
                'multiple' => true
        ];
        //$mform->addElement('header', '', get_string('examdate_header', 'block_eledia_adminexamdates'));
        $options = [];

        $defaultrooms = [];
        $examrooms = $DB->get_records('eledia_adminexamdates_cfg_r',null,'specialroom,roomid');

        foreach ($examrooms as $examroom) {
            $roomcapacity = !empty($examroom->capacity) ? ' (max. ' . $examroom->capacity . ' TN)' : '';
            if (!$examroom->specialroom) {
                array_push($defaultrooms, $examroom->roomid);
            }
            $options[$examroom->roomid] = $examroom->name . $roomcapacity;
        }

        $settings = array('multiple' => 'multiple');
        if ($hasconfirmexamdatescap) {
            $mform->addElement('select', 'examrooms',
                    get_string('select_examroom', 'block_eledia_adminexamdates'), $options, $settings);
            $mform->addRule('examrooms', null, 'required');
        } else {
            $mform->addElement('hidden', 'examrooms');
        }
        $mform->setType('examrooms', PARAM_RAW);
        $mform->setDefault('examrooms', implode(',', $defaultrooms));

      /*  $years = [];
        $years[] = date('Y', strtotime('-1 year'));
        $years[] = date('Y');
        for ($i = 1; $i < 5; $i++) {
            $years[] = date('Y', strtotime('+' . $i . ' year'));
        }
        $options[0] = get_string('pleasechoose', 'block_eledia_adminexamdates');
        foreach ($years as $key => $year) {
            $options[$year . '1'] = get_string('summersemester', 'block_eledia_adminexamdates') . ' ' . $year;
            if (array_key_exists($key + 1, $years)) {
                $options[$year . '2'] =
                        get_string('wintersemester', 'block_eledia_adminexamdates') . ' ' . $year . '/' . $years[$key + 1];
            }
        }

        $time = ($editexamdate) ? $examdate->examtimestart : time();
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
        $mform->setDefault('semester', $defaultsemester);*/

        $options = [];
        $options[0] = get_string('pleasechoose', 'block_eledia_adminexamdates');
        $departmentchoices = unserialize(get_config('block_eledia_adminexamdates', 'departmentchoices'));
        $departments = explode(',', get_config('block_eledia_adminexamdates', 'departments'));
        foreach ($departments as $department) {
            if (isset($departmentchoices[$department])) {
                $options[$department] = $departmentchoices[$department];
            }
        }

        $mform->addElement('select', 'department',
                get_string('department', 'block_eledia_adminexamdates'), $options);
        $mform->addRule('department', null, 'required', null, 'client');
        $mform->addRule('department', get_string('error_choose', 'block_eledia_adminexamdates'), 'nonzero', null, 'client');

        if (!$onlynumberstudents) {
            $mform->addElement('text', 'examname', get_string('examname', 'block_eledia_adminexamdates'), array('size' => 50));
            $mform->addHelpButton('examname', 'examname', 'block_eledia_adminexamdates');
            $mform->setType('examname', PARAM_TEXT);
            $mform->addRule('examname', null, 'required', null, 'client');
        } else {
            $mform->addElement('static', 'examnametext', get_string('examname', 'block_eledia_adminexamdates'),
                    \html_writer::tag('div', $examdate->examname, ['class' => 'col-form-label']));
            $mform->addElement('hidden', 'examname');
            $mform->setType('examname', PARAM_TEXT);
        }

        $mform->addElement('text', 'numberstudents', get_string('number_students', 'block_eledia_adminexamdates'),
                array('size' => 4));
        $mform->setType('numberstudents', PARAM_INT);
        $mform->addRule('numberstudents', null, 'required', null, 'client');
        $mform->addRule('numberstudents', null, 'numeric', null, 'client');

        $time = date('H.i', strtotime(get_config('block_eledia_adminexamdates', 'startexam_hour') . ':' .
                        get_config('block_eledia_adminexamdates', 'startexam_minute'))) .
                '&nbsp;-&nbsp;' . date('H.i', strtotime(get_config('block_eledia_adminexamdates', 'endexam_hour') . ':' .
                        get_config('block_eledia_adminexamdates', 'endexam_minute')));

        if (!$onlynumberstudents) {
            $mform->addElement('date_time_selector', 'examtimestart',
                    get_string('examtimestart', 'block_eledia_adminexamdates', $time));
            $mform->addRule('examtimestart', null, 'required', null, 'client');

        } else {
            if ($editexamdate) {

                $date = userdate($examdate->examtimestart, '%d. %B %Y, %H.%M') . ' ' .
                        get_string('hour', 'block_eledia_adminexamdates');

                $mform->addElement('static', 'description', get_string('examtimestart', 'block_eledia_adminexamdates', $time),
                        \html_writer::tag('div', $date, ['class' => 'col-form-label']));
            }
            $mform->addElement('hidden', 'examtimestart');
            $mform->setType('examtimestart', PARAM_INT);
        }

        if (!$onlynumberstudents) {
            $mform->addElement('text', 'examduration', get_string('examduration', 'block_eledia_adminexamdates'));
            $mform->setType('examduration', PARAM_INT);
            $mform->addRule('examduration', null, 'required', null, 'client');
            $mform->addRule('examduration', null, 'numeric', null, 'client');
        } else {
            $mform->addElement('static', 'examdurationtext', get_string('examduration', 'block_eledia_adminexamdates'),
                    \html_writer::tag('div', $examdate->examduration, ['class' => 'col-form-label']));
            $mform->addElement('hidden', 'examduration');
            $mform->setType('examduration', PARAM_INT);
        }

        $mform->addElement('autocomplete', 'examiner', get_string('examiner', 'block_eledia_adminexamdates'),
                $examinerlist, $autocompleteoptions);
        $mform->addHelpButton('examiner', 'examiner', 'block_eledia_adminexamdates');
        $mform->setType('examiner', PARAM_RAW_TRIMMED);
        $mform->addRule('examiner', get_string('required'), 'required', null, 'client');
        //$mform->addRule('examiner', get_string('required'), 'nonzero', null, 'client');

        $autocompleteoptions = [
                'multiple' => false
        ];
        $default = array_key_exists($USER->id, $contactpersonlist) ? $USER->id : '';
        $mform->addElement('autocomplete', 'contactperson', get_string('contactperson', 'block_eledia_adminexamdates'),
                $contactpersonlist, $autocompleteoptions);
        $mform->setType('contactperson', PARAM_RAW_TRIMMED);
        $mform->addRule('contactperson', get_string('required'), 'required', null, 'client');
        $mform->addRule('contactperson', get_string('required'), 'nonzero', null, 'client');
        // $mform->addRule('contactperson', get_string('error_choose_or_enter', 'block_eledia_adminexamdates'), 'nonzero', null, 'client');
        $mform->setDefault('contactperson', $default);

        //        $mform->addElement('autocomplete', 'contactpersonemail', get_string('contactpersonemail', 'block_eledia_adminexamdates'),
        //            $useremaillist, $autocompleteoptions);
        //        $mform->setType('contactpersonemail', PARAM_RAW_TRIMMED);
        //        $mform->addRule('contactpersonemail', get_string('error_choose_or_enter', 'block_eledia_adminexamdates'), 'required', null, 'client');
        //        $mform->setDefault('contactpersonemail', $default);
        if ($hasconfirmexamdatescap) {
            $responsiblepersons = explode(',',
                    preg_replace('/^\h*\v+/m', '',
                            get_config('block_eledia_adminexamdates', 'responsiblepersons')));
            $options = [0 => ''];

            foreach ($responsiblepersons as $responsibleperson) {
                if ($user = \core_user::get_user($responsibleperson)) {
                    $options[$user->id] = fullname($user) . ' | ' . $user->email;;
                }
            }

            $mform->addElement('autocomplete', 'responsibleperson',
                    get_string('responsibleperson', 'block_eledia_adminexamdates'),
                    $options);
            $mform->setType('responsibleperson', PARAM_RAW_TRIMMED);
            //$mform->addRule('responsibleperson', get_string('required'), 'required', null, 'client');
            //$mform->addRule('responsibleperson', get_string('required'), 'nonzero', null, 'client');
            $mform->setDefault('responsibleperson', 0);
        } else {
            $mform->addElement('hidden', 'responsibleperson');
            $mform->setType('responsibleperson', PARAM_INT);
            $mform->setDefault('responsibleperson', 0);
        }
        if (!$onlynumberstudents) {
            $mform->addElement('textarea', 'annotationtext', get_string('annotationtext', 'block_eledia_adminexamdates'),
                    array('rows' => 10, 'cols' => 80));
            $mform->setType('annotationtext', PARAM_TEXT);
        }
        $mform->addElement('hidden', 'onlynumberstudents');
        $mform->setType('onlynumberstudents', PARAM_INT);

        if ($onlynumberstudents) {
            $mform->setDefault('onlynumberstudents', true);
            $mform->freeze('department,examiner,contactperson');
        }
        $mform->addElement('hidden', 'editexamdate');
        $mform->setType('editexamdate', PARAM_INT);

        $mform->addElement('hidden', 'url');
        $mform->setType('url', PARAM_RAW);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        //if (empty($data->examiner)) {
        //            $errors['examiner'] = get_string('error_choose', 'block_eledia_adminexamdates');
        //        }
        if ($error = \block_eledia_adminexamdates\util::hasfreetimeslots2($data, false)) {
            $errors['examtimestart'] = $error;
        }
        //        $data = (object)$data;
        //        if (intval($data->contactpersonemail) && !($DB->get_record('user',
        //                array('id' => intval($data->contactpersonemail))))) {
        //            $errors['contactpersonemail'] = get_string('error_wrong_userid_email', 'block_eledia_adminexamdates');
        //        }
        //        if (!intval($data->contactpersonemail) && !validate_email($data->contactpersonemail)) {
        //            $errors['contactpersonemail'] = get_string('error_wrong_email', 'block_eledia_adminexamdates');
        //        }
        //
        //        if (intval($data->contactperson) && !($DB->get_record('user',
        //                array('id' => intval($data->contactperson))))) {
        //            $errors['contactperson'] = get_string('error_wrong_userid', 'block_eledia_adminexamdates');
        //        }

        return $errors;
    }
}

