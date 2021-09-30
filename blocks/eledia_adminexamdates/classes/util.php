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
     * Save exam date request in table.
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
     * Get free time slots.
     *
     * @param stdClass $formdata of form.
     */
    public static function getfreetimeslots($examdateid, $formdata)
    {
        global $DB, $USER;
        /*      $sql = "SELECT a.id, a.examduration, ag.timepartialdate, ad.examroom, ad.partid
                    FROM {eledia_adminexamdates} a
                    JOIN {eledia_adminexamdates_parts} ag ON ag.examdateid = a.id
                    JOIN {eledia_adminexamdates_dates} ad ON ad.partid = ag.id
                   WHERE ag.timepartialdate > ? AND ag.timepartialdate < ?";

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

        $numberofgroups = ceil($formdata->numberstudents / $roomcapacitysum);
        for ($i = 1; $i <= $numberofgroups; $i++) {
            $numberstudents = (($i * $roomcapacitysum) > $formdata->numberstudents) ? $formdata->numberstudents % $roomcapacitysum : $roomcapacitysum;
            $timepartialdate = $formdata->examtimestart + (($i - 1) * $formdata->examduration * 60);
            $partid = $DB->insert_record('eledia_adminexamdates_parts',
                (object)['examdateid' => $examdateid,
                    'timepartialdate' => $timepartialdate,
                    'partnumberstudents' => $numberstudents,
                    'groupname' => $formdata->examname . '_' . date('Hi', $timepartialdate)
                ]);
            if (!empty($partid)) {
                $sumroomcapacity = 0;
                foreach ($roomcapacities as $roomid => $roomcapacity) {

                    $sumroomcapacity += $roomcapacity;
                    $isrestnumberstudents = $sumroomcapacity >= $numberstudents;
                    // $roomnumberstudents = $isrestnumberstudents ? $numberstudents - $sumroomcapacity : $roomcapacity;
                    $datesid = $DB->insert_record('eledia_adminexamdates_dates',
                        (object)['partid' => $partid,
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
        $formdata->annotationtext = $dataobject->annotationtext;
        return $formdata;
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
            $adminexamgroups = $DB->get_records('eledia_adminexamdates_parts', ['examdateid' => $adminexamdate->id]);
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
            $index = 1;
            foreach ($adminexamgroups as $adminexamgroup) {
                $text .= \html_writer::tag('dt', $index . '. Teiltermin');
                $text .= \html_writer::tag('dd', date('d.m.Y H.i', $adminexamgroup->timepartialdate)
                    . ' - ' . date('H.i', $adminexamgroup->timepartialdate + ($adminexamdate->examduration * 60)));;
                $index++;
            }
            $text .= \html_writer::end_tag('dl');
            $text .= \html_writer::end_tag('p');

            $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['editexamdate' => $adminexamdate->id]);

            $text .= $OUTPUT->single_button($url, get_string('editexamdate', 'block_eledia_adminexamdates'), 'post');

            $url = new \moodle_url($PAGE->url, ['cancelexamdate' => $adminexamdate->id]);

            $text .= $OUTPUT->single_button($url, get_string('cancelexamdate', 'block_eledia_adminexamdates'), 'post');

            $url = new \moodle_url($PAGE->url, ['confirmexamdate' => $adminexamdate->id]);

            $text .= $OUTPUT->single_button($url, get_string('confirmexamdate', 'block_eledia_adminexamdates'), 'post');

            $text .= \html_writer::end_tag('div');
            $text .= \html_writer::end_tag('div');
        }
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
            $examgroups = $DB->get_records('eledia_adminexamdates_parts', ['examdateid' => $examdateid]);

            $param = [
                'wsfunction' => 'core_course_get_courses_by_field',
                'field' => 'idnumber',
                'value' => $config->examcoursetemplateidnumber
            ];
            $results = self::get_data_from_api('', $param);

            // If the template course exists, get the sub-categories by the department categoryid.
            if (!empty($results->courses[0]->id)) {
                $templatecourseid=$results->courses[0]->id;
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

                        // Generate the course groups for each exam groups.
                        $groupparam = '';
                        foreach ($examgroups as $index => $examgroup) {
                            $groupparam .= "&groups[$index][courseid]=$courseid";
                            $groupparam .= "&groups[$index][name]=$examgroup->groupname";
                            $groupparam .= "&groups[$index][description]=";
                            $groupparam .= "&groups[$index][descriptionformat]=1";
                        }
                        $param = ['wsfunction' => 'core_group_create_groups'];
                        $results = self::get_data_from_api($groupparam, $param);

                        // Generate the calendar events for each exam events.
                        if (!empty($results)) {
                            foreach ($examgroups as $examgroup) {
                                $examevents = $DB->get_records('eledia_adminexamdates_dates', ['partid' => $examgroup->id]);
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
                                        $text .= \html_writer::tag('a', $examgroup->groupname, ['class' => 'btn btn-primary',
                                            'href' => ($url)->out()]);
                                        $text .= \html_writer::end_tag('p');
                                        $text .= \html_writer::end_tag('div');
                                        $text .= \html_writer::end_tag('div');

                                        $event = [
                                            'name' => $examgroup->groupname,
                                            'description' => $text,
                                            'format' => 1,
                                            'courseid' => $roomeventscourse->id,
                                            'partid' => 0,
                                            'userid' => $USER->id,
                                            'modulename' => 0,
                                            'instance' => 0,
                                            'eventtype' => 'course',
                                            'timestart' => $examgroup->timepartialdate,
                                            'timeduration' => $examdate->examduration * 60,
                                            'visible' => 1
                                        ];
                                        $calendarevent = \calendar_event::create($event, false);

                                        $DB->update_record('eledia_adminexamdates_dates',
                                            (object)[
                                                'id' => $examevent->id,
                                                'calendareventid' => $calendarevent->id
                                            ]);
                                    }
                                }
                            }
                            // Set the 'confirmed' state to this exam date.
                            $DB->update_record('eledia_adminexamdates', (object)['id' => $examdateid, 'confirmed' => 1]);
                        }
                    }
                }
            }
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



