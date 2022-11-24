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

class singleexamdate_form extends \moodleform
{

    public function definition()
    {
        global $DB;
        $hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());

        $mform =& $this->_form;

        // $mform->addElement('header', '', get_string('singleexamdate_header', 'block_eledia_adminexamdates'));


        $options = [];
        $rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));

        $roomcapacity = [];
        foreach ($rooms as $room) {
            $roomitems = explode('|', $room);
            if (!empty($roomitems[2])) {
                array_push($roomcapacity, $roomitems[0]);
            }
            $options[$roomitems[0]] = $roomitems[1];
        };

        // $blocks =& $this->_customdata['blocks'];
        // print_R( $blocks);exit;
        // $blocksnumber=count($blocks);
        // $mform->toHtml('<ul class="nav nav-pills" role="tablist">');
        // foreach ($options as $key => $val) {
        //
        //     $mform->toHtml('<li class="nav-item">');
        //     $mform->toHtml('<button class="nav-link" data-toggle="pill" href="#room' . $key . '">' . $val . '</button>');
        //     $mform->toHtml('</li>');
        // }
        //
        // $mform->toHtml('</ul>');
        // $mform->toHtml('<div class="tab-content">');
        // $mform->toHtml('<div id="room' . $key . '" class="container tab-pane"><br>');
        //  for($i = 0; $i < $blocksnumber; $i++) {
        //foreach ($blocks as $block) {
        //  $mform->toHtml('<div id="termin' . $index . '" class="container tab-pane ' . $activecssclass . '"><br>');
        $mform->addElement('date_time_selector', "blocktimestart", get_string('block_timestart', 'block_eledia_adminexamdates'));
        $mform->addRule("blocktimestart", null, 'required');

        $mform->addElement('text', "blockduration", get_string('block_duration', 'block_eledia_adminexamdates'), array('size' => 4));
        $mform->setType("blockduration", PARAM_INT);
        //  }

        /* $options = [];
         $rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));

         foreach ($rooms as $room) {
             $roomitems = explode('|', $room);
             $roomcapacity = !empty($roomitems[2]) ? ' (max. ' . $roomitems[2] . ' TN)' : '';
             $options[$roomitems[0]] = $roomitems[1] . $roomcapacity;
         };*/


        /*        $settings = array('multiple' => 'multiple');
                if ($hasconfirmexamdatescap) {
                    $mform->addElement('select', 'examroom',
                        get_string('select_examroom', 'block_eledia_adminexamdates'), $options, $settings);
                    $mform->addRule('examroom', null, 'required');
                } else {
                    $mform->addElement('hidden', 'examroom');
                }
                $mform->setType('examroom', PARAM_RAW);
                $mform->setDefault('examroom', 'PR1');*/

        /* $years = [];
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
         $mform->setDefault('semester', $defaultsemester);*/

        /* $options=[];
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
         $mform->addRule('examname', null, 'required');*/
//        $blocks =& $this->_customdata['blocks'];
//        //print_r( '$examdateid'.$examdateid);
//
//        $active = 1;
//        $i = 1;
//        $mform->toHtml('<ul class="nav nav-pills" role="tablist">');
//        foreach ($blocks as $index => $block) {
//            $activecssclass = $active ? 'active' : '';
//            $mform->toHtml('<li class="nav-item">');
//            $mform->toHtml('<a class="nav-link ' . $activecssclass . '" data-toggle="pill" href="#termin' . $index . '">Termin ' . $i . '</a>');
//            $mform->toHtml('</li>');
//            if ($active) {
//                $active = 0;
//            }
//            $i++;
//        }
//        $mform->toHtml('</ul>');
//        $mform->toHtml('<div class="tab-content">');
//        $active = 1;
//        $settings = array('multiple' => 'multiple');
//
//        foreach ($blocks as $index => $block) {
//            $activecssclass = $active ? 'active' : 'fade';
//            $mform->toHtml('<div id="termin' . $index . '" class="container tab-pane ' . $activecssclass . '"><br>');
//            $mform->addElement('date_time_selector', "blocktimestart[$index]", get_string('block_timestart', 'block_eledia_adminexamdates'));
//            $mform->addRule("blocktimestart[$index]", null, 'required');
//
//            $mform->addElement('text', "blockduration[$index]", get_string('block_duration', 'block_eledia_adminexamdates'), array('size' => 4));
//            $mform->setType("blockduration[$index]", PARAM_INT);
        //   $mform->addRule("blockduration[$index]", null, 'required');

//            $mform->addElement('text', "blocknumberstudents[$index]", get_string('block_number_students', 'block_eledia_adminexamdates'), array('size' => 4));
//            $mform->setType("blocknumberstudents[$index]", PARAM_INT);
        //  $mform->addRule("blocknumberstudents[$index]", null, 'required');

//            $mform->addElement('select', "blockexamrooms[$index]",
//                get_string('select_blockexamrooms', 'block_eledia_adminexamdates'), $options, $settings);
//            $mform->addRule("blockexamrooms[$index]", null, 'required');
//            $mform->setType("blockexamrooms[$index]", PARAM_RAW);
//            $mform->setDefault("blockexamrooms[$index]", 'PR1');
        $checkboxes = [];
        //  for($i = 0; $i < $blocksnumber; $i++) {
        foreach ($options as $key => $val) {
            $checkboxes[] =
                $mform->createElement('advcheckbox', "blockexamroomscheck[{$key}]", '', $val);
        }
        //   }
        $mform->addGroup($checkboxes, '', get_string('examdaterooms', 'block_eledia_adminexamdates'), ['<br>'], false);

        $sql = "SELECT *
                  FROM {user} 
                 WHERE deleted = 0
                  ORDER BY lastname, firstname";

        $users = $DB->get_records_sql($sql);
        $userlist = [];
        foreach ($users as $id => $user) {
            if ($user->id > 2 && !is_siteadmin($user)) {
                //$userfields = get_object_vars($user);
                //array_shift($userfields);
                $userlist[$id] = fullname($user);
            }
        }

        $autocompleteoptions = [
            'multiple' => true,
            'tags' => true,
            'placeholder' => get_string('autocomplete_placeholder', 'block_eledia_adminexamdates')
        ];

        foreach ($options as $key => $val) {

            //$mform->toHtml('<div id="room' . $key . '" class="container tab-pane"><br>');

            $mform->addElement('header', "roomheader[{$key}]", $val);
            $mform->setExpanded("roomheader[{$key}]", true, true);

            if (in_array($key, $roomcapacity)) {

                $mform->addElement('text', "roomnumberstudents[{$key}]", get_string('room_number_students', 'block_eledia_adminexamdates'), array('size' => 4));
                $mform->setType("roomnumberstudents[{$key}]", PARAM_INT);

                $mform->addElement('autocomplete', "roomsupervisor1[{$key}]", get_string('room_supervisor', 'block_eledia_adminexamdates') . '&nbsp;1',
                    $userlist, $autocompleteoptions);
                $mform->setType("roomsupervisor1[{$key}]", PARAM_RAW);

                $mform->addElement('autocomplete', "roomsupervisor2[{$key}]", get_string('room_supervisor', 'block_eledia_adminexamdates') . '&nbsp;2',
                    $userlist, $autocompleteoptions);
                $mform->setType("roomsupervisor2[{$key}]", PARAM_RAW);

                $mform->addElement('autocomplete', "roomsupervision1[{$key}]", get_string('room_supervision', 'block_eledia_adminexamdates') . '&nbsp;1',
                    $userlist, $autocompleteoptions);
                $mform->setType("roomsupervision1[{$key}]", PARAM_RAW);

                $mform->addElement('autocomplete', "roomsupervision2[{$key}]", get_string('room_supervision', 'block_eledia_adminexamdates') . '&nbsp;2',
                    $userlist, $autocompleteoptions);
                $mform->setType("roomsupervision2[{$key}]", PARAM_RAW);
            } else {
                $mform->addElement('textarea', "roomannotationtext[{$key}]", get_string('annotationtext', 'block_eledia_adminexamdates'), array('rows' => 10, 'cols' => 40));
                $mform->setType("roomannotationtext[{$key}]", PARAM_RAW);
            }
        }
        // $mform->toHtml('</div>');
//
//            $mform->toHtml(' </div>');
//
//
//            if ($active) {
//                $active = 0;
//            }
//        }
//        $mform->toHtml(' </div>');
        /*        $mform->addElement('text', 'examduration', get_string('examduration', 'block_eledia_adminexamdates'));
                $mform->setType('examduration', PARAM_INT);
                $mform->addRule('examduration', null, 'required');*/

        /* $mform->addElement('text', 'examiner', get_string('examiner', 'block_eledia_adminexamdates'), array('size' => 50));
         $mform->setType('examiner', PARAM_TEXT);
         $mform->addRule('examiner', null, 'required');

         $mform->addElement('text', 'contactperson', get_string('contactperson', 'block_eledia_adminexamdates'), array('size' => 50));
         $mform->setType('contactperson', PARAM_TEXT);
         $mform->addRule('contactperson', null, 'required');

         $mform->addElement('textarea', 'annotationtext', get_string('annotationtext', 'block_eledia_adminexamdates'), array('rows' => 10, 'cols' => 80));
         $mform->setType('annotationtext', PARAM_RAW);*/


        /* $string['department'] = 'Department';
         $string['examiner'] = 'Examiner';
         $string['contactperson'] = 'Contact person';


         $mform->addElement('hidden', 'examdateid');
         $mform->setType('examdateid', PARAM_INT);*/
        // $mform->toHtml('</div></div>');
        $mform->addElement('hidden', 'save');
        $mform->setType('save', PARAM_INT);
        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);
        $mform->addElement('hidden', 'examdateid');
        $mform->setType('examdateid', PARAM_INT);
        $mform->addElement('hidden', 'url');
        $mform->setType('url', PARAM_RAW);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

    }

    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        return $errors;
    }
}

