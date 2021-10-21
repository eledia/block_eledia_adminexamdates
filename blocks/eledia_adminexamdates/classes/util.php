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
 *
 * @package    block
 * @subpackage eledia_adminexamdates
 * @author     <support@eledia.de>
 * @copyright  2021 eLeDia GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_eledia_adminexamdates;

use core_course\search\course;
use stdClass;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/calendar/lib.php');

class util
{

    /**
     * Save exam date request.
     *
     * @param stdClass $formdata of form.
     */
    public static function saveexamdate($formdata)
    {
        global $DB, $USER;

        $dataobject = new \stdClass();
        $dataobject->examroom = serialize($formdata->examroom);
        $dataobject->examtimestart = $formdata->examtimestart;
        $dataobject->examduration = $formdata->examduration;
        $dataobject->department = $formdata->department;
        $dataobject->examname = $formdata->examname;
        $dataobject->semester = $formdata->semester;
        $dataobject->numberstudents = $formdata->numberstudents;
        $dataobject->examiner = $formdata->examiner;
        $dataobject->contactperson = $formdata->contactperson;
        $dataobject->responsibleperson = $formdata->responsibleperson;
        $dataobject->annotationtext = $formdata->annotationtext;
        if (empty($formdata->examdateid)) {
            $dataobject->userid = $USER->id;
            $dataobject->timecreated = time();
            $examdateid = $DB->insert_record('eledia_adminexamdates', $dataobject);
        } else {
            $examdateid = $formdata->examdateid;
            $dataobject->id = $formdata->examdateid;
            $DB->update_record('eledia_adminexamdates', $dataobject);
        }
        return $examdateid;

    }

    /**
     * Save single exam date.
     *
     * @param stdClass $formdata of form.
     */
    public static function savesingleexamdate($formdata)
    {
        global $DB;

        $dataobject = new \stdClass();
        $singleexamdateid = $formdata->savesingleexamdate;
        $dataobject->blocktimestart = $formdata->blocktimestart;
        $dataobject->blockduration = $formdata->blockduration;

        if (empty($formdata->blockid)) {

            $singleexamdateid = $DB->insert_record('eledia_adminexamdates_blocks', $dataobject);
        } else {

            $roomsdata = $DB->get_records('eledia_adminexamdates_rooms', ['blockid' => $formdata->blockid]);
            $idrooms = array_column($roomsdata, 'id', 'examroom');
            $checkedrooms = $formdata->blockexamroomscheck;
            $deleterooms = $idrooms;
            foreach ($checkedrooms as $roomid => $checkedroom) {
                if ($checkedroom) {
                    unset($deleterooms[$roomid]);
                    $roomdataobject = new \stdClass();
                    $roomdataobject->roomnumberstudents = $formdata->roomnumberstudents[$roomid];
                    $roomdataobject->roomsupervisor1 = $formdata->roomsupervisor1[$roomid];
                    $roomdataobject->roomsupervisor2 = $formdata->roomsupervisor2[$roomid];
                    $roomdataobject->roomsupervision1 = $formdata->roomsupervision1[$roomid];
                    $roomdataobject->roomsupervision2 = $formdata->roomsupervision2[$roomid];
                    if (isset($idrooms[$roomid])) {
                        $roomdataobject->id = $idrooms[$roomid];
                        $DB->update_record('eledia_adminexamdates_rooms', $roomdataobject);
                    } else {
                        $roomdataobject->blockid = $formdata->blockid;
                        $roomdataobject->examroom=$roomid;
                        $DB->insert_record('eledia_adminexamdates_rooms', $roomdataobject);
                    }
                }
            }
            if (!empty($deleterooms)) {
                $DB->delete_records_list('eledia_adminexamdates_rooms', 'id', array_values($deleterooms));
            }


            $dataobject->id = $formdata->blockid;
            $DB->update_record('eledia_adminexamdates_blocks', $dataobject);

        }
        return $singleexamdateid;
    }

    /**
     * Get free time slots.
     *
     * @param stdClass $formdata of form.
     */
    public static function getfreetimeslots($examdateid, $formdata)
    {
        global $DB, $USER;
        /*      $sql = "SELECT a.id, a.examduration, ag.blocktimestart, ad.examroom, ad.blockid
                    FROM {eledia_adminexamdates} a
                    JOIN {eledia_adminexamdates_blocks} ag ON ag.examdateid = a.id
                    JOIN {eledia_adminexamdates_rooms} ad ON ad.blockid = ag.id
                   WHERE ag.blocktimestart > ? AND ag.blocktimestart < ?";

              $params = [strtotime('07:00:00', $formdata->examtimestart),
                  strtotime('19:00:00', $formdata->examtimestart)];

              $datesoftheday = $DB->get_records_sql($sql, $params);*/

        $rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));
        $roomcapacities = [];
        $roomcapacitysum = 0;
        foreach ($rooms as $room) {
            $roomitems = explode('|', $room);
            if (!empty($roomitems[2])) {
                $roomcapacities[$roomitems[0]] = $roomitems[2];
                $roomcapacitysum += $roomitems[2];
            }
        };
        $breakbetweenblockdates = get_config('block_eledia_adminexamdates', 'breakbetweenblockdates');
        $numberofblocks = ceil($formdata->numberstudents / $roomcapacitysum);
        for ($i = 1; $i <= $numberofblocks; $i++) {
            $numberstudents = (($i * $roomcapacitysum) > $formdata->numberstudents) ? $formdata->numberstudents % $roomcapacitysum : $roomcapacitysum;
            $blocktimestart = $formdata->examtimestart + (($i - 1) * $formdata->examduration * 60)
                + (($i - 1) * $breakbetweenblockdates * 60);
            $blockid = $DB->insert_record('eledia_adminexamdates_blocks',
                (object)['examdateid' => $examdateid,
                    'blocktimestart' => $blocktimestart,
                    'blockduration' => $formdata->examduration
                ]);
            if (!empty($blockid)) {
                $sumroomcapacity = 0;
                foreach ($roomcapacities as $roomid => $roomcapacity) {

                    $sumroomcapacity += $roomcapacity;
                    $isrestnumberstudents = $sumroomcapacity >= $numberstudents;
                    // $roomnumberstudents = $isrestnumberstudents ? $numberstudents - $sumroomcapacity : $roomcapacity;
                    $datesid = $DB->insert_record('eledia_adminexamdates_rooms',
                        (object)['blockid' => $blockid,
                            'examroom' => $roomid,
                        ]);
                    if ($isrestnumberstudents) {

                        break;
                    }
                }

            }
        }
    }

    /**
     * Save exam date request in table.
     *
     * @param stdClass $formdata of form.
     */
    public static function editexamdate($examdateid)
    {
        global $DB, $USER;
        $dataobject = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid], '*', MUST_EXIST);
        $formdata = new stdClass();
        $formdata->examroom = unserialize($dataobject->examroom);
        $formdata->examtimestart = $dataobject->examtimestart;
        $formdata->examduration = $dataobject->examduration;
        $formdata->department = $dataobject->department;
        $formdata->examname = $dataobject->examname;
        $formdata->examdateid = $examdateid;
        $formdata->semester = $dataobject->semester;
        $formdata->numberstudents = $dataobject->numberstudents;
        $formdata->examiner = $dataobject->examiner;
        $formdata->contactperson = $dataobject->contactperson;
        $formdata->responsibleperson = $dataobject->responsibleperson;
        $formdata->annotationtext = $dataobject->annotationtext;
        return $formdata;
    }

    /**
     * Edit single exam date.
     *
     * @param stdClass $formdata of form.
     */
    public static function editsingleexamdate($examdateid)
    {
        global $DB;
        $dataobject = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid], '*', MUST_EXIST);
        $examparts = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid]);
        $exampart = array_shift($examparts);
        //print_R($examparts);
        $formdata = new stdClass();
        $formdata->savesingleexamdate = $examdateid;
        $formdata->blocktimestart = $exampart->blocktimestart;
        $formdata->blockduration = $exampart->blockduration;
        $formdata->blockid = $exampart->id;


        //  foreach ($examparts as $index => $exampart) {
        $rooms = $DB->get_records('eledia_adminexamdates_rooms', ['blockid' => $exampart->id]);
//print_r($rooms);
//        print_r($exampart);
//        exit;
        if (!empty($rooms)) {
            foreach ($rooms as $room) {
                $formdata->blockexamroomscheck[$room->examroom] = true;

                $formdata->roomnumberstudents[$room->examroom] =$room->roomnumberstudents ;
                 $formdata->roomsupervisor1[$room->examroom]=$room->roomsupervisor1;
                 $formdata->roomsupervisor2[$room->examroom]=$room->roomsupervisor2 ;
                 $formdata->roomsupervision1[$room->examroom]=$room->roomsupervision1;
                 $formdata->roomsupervision2[$room->examroom]=$room->roomsupervision2 ;
            }
        }

        // $formdata->blocktimestart[0] = $exampart->blocktimestart;
        //  $formdata->blockduration[0] = $exampart->blockduration;
        //  $formdata->blockexamroomscheck[$index] = implode(array_column($rooms, 'examroom'));
        //array_values(array_column($rooms, 'examroom'));

//            foreach ($rooms as $room) {
//
//                //$formdata->roomblock[$index]["blockexamroomscheck"=>[$index][$room->id]] = 1;
//                $formdata->roomnumberstudents[$index][$room->id] = $room->roomnumberstudents;
//                $formdata->roomsupervisor1[$index][$room->id] = $room->roomsupervisor1;
//                $formdata->roomsupervisor[$index][$room->id] = $room->roomsupervisor2;
//                $formdata->roomsupervision1[$index][$room->id] = $room->roomsupervision1;
//                $formdata->roomsupervision2[$index][$room->id] = $room->roomsupervision2;
//            }
        // }

        //$formdata->examrooms =  $rooms[];
        /* $formdata->blocktimestart = $dataobject->examtimestart;
         $formdata->examduration = $dataobject->examduration;
         $formdata->department = $dataobject->department;
         $formdata->examname = $dataobject->examname;
         $formdata->examdateid = $examdateid;
         $formdata->semester = $dataobject->semester;
         $formdata->numberstudents = $dataobject->numberstudents;
         $formdata->examiner = $dataobject->examiner;
         $formdata->contactperson = $dataobject->contactperson;
         $formdata->annotationtext = $dataobject->annotationtext;*/
        return $formdata;
    }

    /**
     * get exam date overview.
     *
     */
    public static function getexamdateoverview($examdateid)
    {
        $text = '';
        global $DB, $PAGE, $OUTPUT, $USER;
        $hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());
        $examdate = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid]);
        $examblocks = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdate->id]);
        $text = '';
        $text .= \html_writer::start_tag('div', array('class' => 'card'));
        $text .= \html_writer::start_tag('div', array('class' => 'card-body'));
        $text .= \html_writer::tag('h5', $examdate->examname, array('class' => 'card-title'));
        $text .= \html_writer::start_tag('p', array('class' => 'card-text'));
        $text .= \html_writer::start_tag('dl');
        $text .= \html_writer::tag('dt', get_string('time', 'block_eledia_adminexamdates') . ': ');
        $text .= \html_writer::tag('dd', date('d.m.Y H.i', $examdate->examtimestart)
            . ' - ' . date('H.i', $examdate->examtimestart + ($examdate->examduration * 60)));
        $text .= \html_writer::tag('dt', get_string('number_students', 'block_eledia_adminexamdates') . ': ');
        $text .= \html_writer::tag('dd', $examdate->numberstudents);
        $text .= \html_writer::tag('dt', get_string('examiner', 'block_eledia_adminexamdates') . ': ');
        $text .= \html_writer::tag('dd', $examdate->examiner);
        $text .= \html_writer::tag('dt', get_string('contactperson', 'block_eledia_adminexamdates') . ': ');
        $text .= \html_writer::tag('dd', $examdate->contactperson);
        $text .= \html_writer::tag('dt', get_string('responsibleperson', 'block_eledia_adminexamdates') . ': ');
        $text .= \html_writer::tag('dd', $examdate->responsibleperson);

        $index = 1;
        foreach ($examblocks as $examblock) {
            $text .= \html_writer::tag('dt', $index . '. Teiltermin');

            $url = new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['blockid' => $examblock->id]);
            $editbutton = $OUTPUT->single_button($url, get_string('editexamdate', 'block_eledia_adminexamdates'), 'post');

            $text .= \html_writer::tag('dd', date('d.m.Y H.i', $examblock->blocktimestart)
                . ' - ' . date('H.i', $examblock->blocktimestart + ($examdate->examduration * 60)).'  '.$editbutton);
            $index++;
        }
        $text .= \html_writer::end_tag('dl');
        $text .= \html_writer::end_tag('p');

        $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['editexamdate' => $examdate->id]);

        $text .= $OUTPUT->single_button($url, get_string('editexamdate', 'block_eledia_adminexamdates'), 'post');

        $url = new \moodle_url($PAGE->url, ['cancelexamdate' => $examdate->id]);

        $text .= $OUTPUT->single_button($url, get_string('cancelexamdate', 'block_eledia_adminexamdates'), 'post');
        if ($hasconfirmexamdatescap) {
            $url = new \moodle_url($PAGE->url, ['confirmexamdate' => $examdate->id]);
            $text .= $OUTPUT->single_button($url, get_string('confirmexamdate', 'block_eledia_adminexamdates'), 'post');
        }
        $text .= \html_writer::end_tag('div');
        $text .= \html_writer::end_tag('div');
        return $text;
    }

    /**
     * get exam date request html items.
     *
     */
    public static function getexamdateitems()
    {
        $text = '';
        global $DB, $PAGE, $OUTPUT, $USER;
        $hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());
        $conditions = $hasconfirmexamdatescap ? ['confirmed' => 0] : ['confirmed' => 0, 'userid' => $USER->id];
        $adminexamdates = $DB->get_records('eledia_adminexamdates', $conditions);
        foreach ($adminexamdates as $adminexamdate) {
            $adminexamblocks = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $adminexamdate->id]);
            $text .= \html_writer::start_tag('div', array('class' => 'card'));
            $text .= \html_writer::start_tag('div', array('class' => 'card-body'));
            $text .= \html_writer::tag('h5', $adminexamdate->examname, array('class' => 'card-title'));
            $text .= \html_writer::start_tag('p', array('class' => 'card-text'));
            $text .= \html_writer::start_tag('dl');
            $text .= \html_writer::tag('dt', get_string('time', 'block_eledia_adminexamdates') . ': ');
            $text .= \html_writer::tag('dd', date('d.m.Y H.i', $adminexamdate->examtimestart)
                . ' - ' . date('H.i', $adminexamdate->examtimestart + ($adminexamdate->examduration * 60)));
            $text .= \html_writer::tag('dt', get_string('number_students', 'block_eledia_adminexamdates') . ': ');
            $text .= \html_writer::tag('dd', $adminexamdate->numberstudents);
            $text .= \html_writer::tag('dt', get_string('examiner', 'block_eledia_adminexamdates') . ': ');
            $text .= \html_writer::tag('dd', $adminexamdate->examiner);
            $text .= \html_writer::tag('dt', get_string('contactperson', 'block_eledia_adminexamdates') . ': ');
            $text .= \html_writer::tag('dd', $adminexamdate->contactperson);
            $text .= \html_writer::tag('dt', get_string('responsibleperson', 'block_eledia_adminexamdates') . ': ');
            $text .= \html_writer::tag('dd', $adminexamdate->responsibleperson);
            $index = 1;
            foreach ($adminexamblocks as $adminexamblock) {
                $text .= \html_writer::tag('dt', $index . '. Teiltermin');
                $text .= \html_writer::tag('dd', date('d.m.Y H.i', $adminexamblock->blocktimestart)
                    . ' - ' . date('H.i', $adminexamblock->blocktimestart + ($adminexamdate->examduration * 60)));;
                $index++;
            }
            $text .= \html_writer::end_tag('dl');
            $text .= \html_writer::end_tag('p');

            $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['editexamdate' => $adminexamdate->id]);

            $text .= $OUTPUT->single_button($url, get_string('editexamdate', 'block_eledia_adminexamdates'), 'post');

            $url = new \moodle_url($PAGE->url, ['cancelexamdate' => $adminexamdate->id]);

            $text .= $OUTPUT->single_button($url, get_string('cancelexamdate', 'block_eledia_adminexamdates'), 'post');
            if ($hasconfirmexamdatescap) {
                $url = new \moodle_url($PAGE->url, ['confirmexamdate' => $adminexamdate->id]);
                $text .= $OUTPUT->single_button($url, get_string('confirmexamdate', 'block_eledia_adminexamdates'), 'post');

                $url = new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['editsingleexamdate' => $adminexamdate->id]);
                $text .= $OUTPUT->single_button($url, get_string('editsingleexamdate', 'block_eledia_adminexamdates'), 'post');
            }
            $text .= \html_writer::end_tag('div');
            $text .= \html_writer::end_tag('div');
        }
        return $text;
    }

    /**
     * get exam date table items.
     *
     */
    public static function getexamdatetable()
    {
        global $DB;
        $sql = "SELECT a.*, a.examduration,ar.roomnumberstudents,
       ab.blocktimestart, ar.examroom, ar.blockid, ar.roomnumberstudents, ar.roomsupervisor1,
        ar.roomsupervisor2
                FROM {eledia_adminexamdates} a
                JOIN {eledia_adminexamdates_blocks} ab ON ab.examdateid = a.id
                JOIN {eledia_adminexamdates_rooms} ar ON ar.blockid = ab.id";
        //     WHERE ag.blocktimestart > ? AND ag.blocktimestart < ?";

//          $params = [strtotime('07:00:00', $formdata->examtimestart),
//              strtotime('19:00:00', $formdata->examtimestart)];
        $roomoptions = [];
        $rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));
        foreach ($rooms as $room) {
            $roomitems = explode('|', $room);
            $roomcapacity = !empty($roomitems[2]) ? ' (max. ' . $roomitems[2] . ' TN)' : '';
            $roomoptions[$roomitems[0]] = $roomitems[1] . $roomcapacity;
        };
        $dates = $DB->get_recordset_sql($sql);
        $tableheaditems = ['month', 'date', 'examname', 'examiner', 'examroom', 'supervisor1', 'supervisor2', 'candidates', 'status', 'blockid'];
        $text = \html_writer::start_tag('table', array('id' => 'examdatestable', 'class' => 'table table-striped table-bordered table-hover table-sm', 'style' => 'width:100%'));
        $text .= \html_writer::start_tag('thead', array('class' => 'thead-light'));
        $text .= \html_writer::start_tag('tr');
        foreach ($tableheaditems as $tableheaditem) {
            $text .= \html_writer::tag('th', get_string('tablehead_' . $tableheaditem, 'block_eledia_adminexamdates'), array('scope' => 'col'));
        }
        $text .= \html_writer::end_tag('tr');
        $text .= \html_writer::end_tag('thead');
        $text .= \html_writer::start_tag('tbody');

        foreach ($dates as $date) {
            $text .= \html_writer::start_tag('tr');
            $hiddenmonth = \html_writer::tag('span', date('Ym', $date->examtimestart), array('class' => 'd-none'));
            $text .= \html_writer::tag('td', $hiddenmonth . strftime('%B %Y', date($date->examtimestart)));
            $hiddendate = \html_writer::tag('span', date('YmdHi', $date->examtimestart), array('class' => 'd-none'));
            $text .= \html_writer::tag('td', $hiddendate . date('d.m.Y H.i', $date->examtimestart)
                . ' - ' . date('H.i', $date->examtimestart + ($date->examduration * 60)));
            $examname = ($date->courseid) ? \html_writer::tag('a', $date->examname,
                array('href' => get_config('block_eledia_adminexamdates', 'apidomain')
                    . '/course/view.php?id=' . $date->courseid, 'target' => '_blank')) : $date->examname;
            $text .= \html_writer::tag('td', $examname);
            $text .= \html_writer::tag('td', $date->examiner);
            $text .= \html_writer::tag('td', $roomoptions[$date->examroom]);
            $text .= \html_writer::tag('td', $date->roomsupervisor1);
            $text .= \html_writer::tag('td', $date->roomsupervisor2);
            $text .= \html_writer::tag('td', $date->roomnumberstudents);
            $text .= \html_writer::tag('td', ($date->confirmed) ? 'Bestätigt' : 'Beantragt');
            $text .= \html_writer::tag('td', $date->blockid);
            $text .= \html_writer::end_tag('tr');
        }

        $text .= \html_writer::end_tag('tbody');
        $text .= \html_writer::start_tag('tfoot', array('class' => 'thead-light'));
        $text .= \html_writer::start_tag('tr');
        foreach ($tableheaditems as $tableheaditem) {
            $text .= \html_writer::tag('th', get_string('tablehead_' . $tableheaditem, 'block_eledia_adminexamdates'), array('scope' => 'col'));
        }
        $text .= \html_writer::end_tag('tr');
        $text .= \html_writer::end_tag('tfoot');
        $text .= \html_writer::end_tag('table');

        return $text;
    }

    /**
     * Confirm exam - Duplicate a sample course for the exam in the e-exam system
     *
     * @return array
     */
    public
    static function examconfirm($examdateid)
    {
        global $DB, $USER;

        $config = get_config('block_eledia_adminexamdates');

        // Get the template's course ID using the course idnumber.
        if (!empty($config->examcoursetemplateidnumber)) {
            $examdate = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid], '*', MUST_EXIST);
            $examparts = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid]);

            $param = [
                'wsfunction' => 'core_course_get_courses_by_field',
                'field' => 'idnumber',
                'value' => $config->examcoursetemplateidnumber
            ];
            $results = self::get_data_from_api('', $param);

            // If the template course exists, get the sub-categories by the department categoryid.
            if (!empty($results->courses[0]->id)) {
                $templatecourseid = $results->courses[0]->id;
                $subcategories = self::get_sub_categories('id', $examdate->department);

                // Generate the string of the semester category.
                $year = substr($examdate->semester, 0, 4);
                if (substr($examdate->semester, -1) == 1) {
                    $semesterstr = get_string('summersemester', 'block_eledia_adminexamdates')
                        . ' ' . $year;
                } else {
                    $semesterstr = get_string('wintersemester', 'block_eledia_adminexamdates')
                        . ' ' . $year . '/' . ($year + 1);
                }

                // Create the semester category if it is not in the sub-category list.
                if (empty($subcategories) || !($semestercategoryid = array_search($semesterstr, $subcategories))) {
                    $param = ['wsfunction' => 'core_course_create_categories'];
                    $categories = '&categories[0][name]=' . urlencode($semesterstr)
                        . '&categories[0][parent]=' . $examdate->department;
                    $results = self::get_data_from_api($categories, $param);
                    $semestercategoryid = $results[0]->id;
                }

                // Duplicate the sample course for the exam.
                $param = [
                    'wsfunction' => 'core_course_duplicate_course',
                    'courseid' => $templatecourseid,
                    'fullname' => $examdate->examname,
                    'shortname' => $examdate->examname,
                    'categoryid' => $semestercategoryid,
                    'visible' => 0
                ];
                $results = self::get_data_from_api('', $param);

                // Get the duplicated course section data and look for the date replacement string in the names and replace.
                if (isset($results->id)) {
                    $courseid = $results->id;
                    $param = [
                        'wsfunction' => 'core_course_get_contents',
                        'courseid' => $courseid
                    ];
                    $options = '&options[0][name]=excludemodules&options[0][value]=1';
                    $results = self::get_data_from_api($options, $param);
                    if (!empty($results)) {
                        $stringtoreplace = 'TT.MM.JJJJ';
                        foreach ($results as $sectiondata) {
                            if (strpos($sectiondata->name, $stringtoreplace)) {
                                $param = [
                                    'wsfunction' => 'core_update_inplace_editable',
                                    'component' => 'format_topics',
                                    'itemtype' => 'sectionname',
                                    'itemid' => $sectiondata->id,
                                    'value' => str_replace($stringtoreplace,
                                        date('d.m.Y', $examdate->examtimestart),
                                        $sectiondata->name)
                                ];
                                self::get_data_from_api('', $param);
                                break;
                            }
                        }

                        /*                   // Generate the course blocks for each exam blocks.
                                           $blockparam = '';
                                           foreach ($examblocks as $index => $examblock) {
                                               $blockparam .= "&blocks[$index][courseid]=$courseid";
                                               $blockparam .= "&blocks[$index][name]=$examblock->blockname";
                                               $blockparam .= "&blocks[$index][description]=";
                                               $blockparam .= "&blocks[$index][descriptionformat]=1";
                                           }
                                           $param = ['wsfunction' => 'core_block_create_blocks'];
                                           $results = self::get_data_from_api($blockparam, $param);

                                           // Generate the calendar events for each exam events.
                                           if (!empty($results)) {
                                               foreach ($examblocks as $examblock) {
                                                   $examevents = $DB->get_records('eledia_adminexamdates_rooms', ['blockid' => $examblock->id]);
                                                   foreach ($examevents as $examevent) {
                                                       $roomeventscourse = $DB->get_record('course', array('idnumber' => $examevent->examroom), 'id', IGNORE_MULTIPLE);

                                                       if (!empty($roomeventscourse->id)) {

                                                           $text = \html_writer::start_tag('div', array('class' => 'card'));
                                                           $text .= \html_writer::start_tag('div', array('class' => 'card-body'));
                                                           $text .= \html_writer::start_tag('p', array('class' => 'card-text'));
                                                           $text .= \html_writer::start_tag('dl');
                                                           $text .= \html_writer::tag('dt', get_string('number_students', 'block_eledia_adminexamdates') . ': ');
                                                           $text .= \html_writer::tag('dd', $examdate->numberstudents);
                                                           $text .= \html_writer::tag('dt', get_string('examiner', 'block_eledia_adminexamdates') . ': ');
                                                           $text .= \html_writer::tag('dd', $examdate->examiner);
                                                           $text .= \html_writer::tag('dt', get_string('contactperson', 'block_eledia_adminexamdates') . ': ');
                                                           $text .= \html_writer::tag('dd', $examdate->contactperson);
                                                           $text .= \html_writer::end_tag('dl');
                                                           $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['editexamdate' => $examdateid]);
                                                           $text .= \html_writer::tag('a', $examblock->blockname, ['class' => 'btn btn-primary',
                                                               'href' => ($url)->out()]);
                                                           $text .= \html_writer::end_tag('p');
                                                           $text .= \html_writer::end_tag('div');
                                                           $text .= \html_writer::end_tag('div');

                                                           $event = [
                                                               'name' => $examblock->blockname,
                                                               'description' => $text,
                                                               'format' => 1,
                                                               'courseid' => $roomeventscourse->id,
                                                               'blockid' => 0,
                                                               'userid' => $USER->id,
                                                               'modulename' => 0,
                                                               'instance' => 0,
                                                               'eventtype' => 'course',
                                                               'timestart' => $examblock->blocktimestart,
                                                               'timeduration' => $examdate->examduration * 60,
                                                               'visible' => 1
                                                           ];
                                                           $calendarevent = \calendar_event::create($event, false);

                                                           $DB->update_record('eledia_adminexamdates_rooms',
                                                               (object)[
                                                                   'id' => $examevent->id,
                                                                   'calendareventid' => $calendarevent->id
                                                               ]);
                                                       }
                                                   }
                                               }

                                           }*/
                        // Set the 'confirmed' state and course ID to this exam date.
                        $DB->update_record('eledia_adminexamdates', (object)['id' => $examdateid,
                            'confirmed' => 1, 'courseid' => $courseid]);
                    }
                }
            }
        }
    }

    /**
     * Cancel exam - Delete an exam date.
     *
     * @return array
     */
    public
    static function examcancel($examdateid)
    {
        global $DB;

        $config = get_config('block_eledia_adminexamdates');

        // Get the template's course ID using the course idnumber.
        if (!empty($config->examcoursetemplateidnumber)) {
            $examdate = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid], '*', MUST_EXIST);
            $examparts = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid]);

        }
    }

    /**
     * Get data from API
     *
     * @return array
     */
    public
    static function get_data_from_api($urlparam, $param)
    {
        $config = get_config('block_eledia_adminexamdates');
        if (!empty($config->apitoken) && !empty($config->apidomain)) {
            $curl = new \curl();
            $param['wstoken'] = $config->apitoken;
            $response = $curl->post($config->apidomain . '/webservice/rest/server.php?moodlewsrestformat=json' . $urlparam, $param);
            $results = json_decode($response);
            if (isset($results->message) || isset($results->errorcode)) {
                $message = get_string('error') . ': ';
                $message .= isset($results->errorcode) ? $results->errorcode . ' - ' : '';
                $message .= isset($results->message) ? $results->message : '';
                \core\notification::add($message, \core\output\notification::NOTIFY_ERROR);
                return false;
            }
            return $results;
        }
    }

    /**
     * Get sub category list from API
     *
     * @return array
     */
    public
    static function get_sub_categories($idtype, $categoryidvalue)
    {
        $param = [
            'wsfunction' => 'core_course_get_categories',
            'addsubcategories' => 0,
        ];
        $criteria = '&criteria[0][key]=' . $idtype . '&criteria[0][value]=' . $categoryidvalue;
        $results = self::get_data_from_api($criteria, $param);
        if (!empty($results[0]->id)) {
            $parentcategoryid = $results[0]->id;
            $param['addsubcategories'] = 1;
            $results = self::get_data_from_api($criteria, $param);
            if (!empty($results)) {
                $subcategories = [];
                foreach ($results as $result) {
                    if ($result->parent == $parentcategoryid) {
                        $subcategories[$result->id] = $result->name;
                    }
                }
                return $subcategories;
            }
        }
    }
}



