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

class util {

    /**
     * Save exam date request.
     *
     * @param stdClass $formdata of form.
     */
    public static function saveexamdate($formdata) {
        global $DB, $USER;
        $hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());
        $dataobject = new \stdClass();

        if ($formdata->onlynumberstudents) {

            $examdateid = $formdata->editexamdate;
            $dataobject->id = $formdata->editexamdate;
            $dataobject->numberstudents = $formdata->numberstudents;
            $dataobject->confirmed = 2;
            $DB->update_record('eledia_adminexamdates', $dataobject);
        } else {
            $dataobject->examrooms = is_array($formdata->examrooms)
                    ? implode(',', $formdata->examrooms) : $formdata->examrooms;
            $dataobject->examtimestart = $formdata->examtimestart;
            $dataobject->examduration = $formdata->examduration;
            $dataobject->department = $formdata->department;
            $dataobject->category = $formdata->category;
            $dataobject->examname = $formdata->examname;
            $dataobject->semester = $formdata->semester;
            $dataobject->numberstudents = $formdata->numberstudents;
            $dataobject->examiner = implode(',', $formdata->examiner);
            $dataobject->contactperson = $formdata->contactperson;
            //        if (!ctype_digit(strval($formdata->contactperson))) {
            //            $dataobject->contactperson = $formdata->contactperson;
            //            $dataobject->contactpersonid = null;
            //        } else {
            //            $contactperson = $DB->get_record('user',
            //                array('id' => $formdata->contactperson), '*', MUST_EXIST);
            //            $dataobject->contactpersonid = $formdata->contactperson;
            //            $dataobject->contactperson = fullname($contactperson);
            //        }
            //        if (!ctype_digit(strval($formdata->contactpersonemail))) {
            //            $dataobject->contactpersonemail = $formdata->contactpersonemail;
            //        } else {
            //            $contactperson = $DB->get_record('user',
            //                array('id' => $formdata->contactpersonemail), '*', MUST_EXIST);
            //            $dataobject->contactpersonemail = $contactperson->email;
            //        }
            $dataobject->responsibleperson = $formdata->responsibleperson;
            $dataobject->annotationtext = $formdata->annotationtext;

            if (empty($formdata->editexamdate)) {
                $dataobject->userid = $USER->id;
                $dataobject->timecreated = time();
                $examdateid = $DB->insert_record('eledia_adminexamdates', $dataobject);

                // Send confirmation email to requester if not examdates-admin.
                if (!$hasconfirmexamdatescap) {
                    $emailuser = new stdClass();
                    $emailuser->email = $USER->email;
                    $emailuser->id = -99;

                    $subject = get_string('request_email_subject', 'block_eledia_adminexamdates',
                            ['name' => $dataobject->examname]);
                    $date = date('d.m.Y, H.i', $dataobject->examtimestart)
                            . ' - ' . date('H.i', $dataobject->examtimestart + ($dataobject->examduration * 60)). get_string('time', 'block_eledia_adminexamdates');
                    $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php',
                            ['editexamdate' => $examdateid]);
                    $url = $url->out();
                    $link = \html_writer::tag('a', get_string('edit'), array('href' => $url));

                    $messagetext = get_string('request_email_body', 'block_eledia_adminexamdates',
                            ['name' => $dataobject->examname, 'date' => $date, 'url' => $link, 'annotation' => $dataobject->annotationtext]);

                    email_to_user($emailuser, $USER, $subject, $messagetext);
                }

            } else {
                $examdateid = $formdata->editexamdate;
                $dataobject->id = $formdata->editexamdate;
                $DB->update_record('eledia_adminexamdates', $dataobject);
            }

            //if ($hasconfirmexamdatescap && (!$dataobject->confirmed || $dataobject->confirmed == 2)) {
            //    self::examconfirm($examdateid);
            //}
        }
        return $examdateid;

    }

    /**
     * Save special rooms.
     *
     * @param stdClass $formdata of form.
     */
    public static function savespecialrooms($formdata) {
        global $DB;

        $dataobject = new \stdClass();
        $dataobject->blocktimestart = $formdata->booktimestart;
        $dataobject->blockduration = $formdata->bookduration;

        $specialrooms = (array) $formdata->specialrooms;

        if (empty($formdata->blockid)) {
            $blockid = $DB->insert_record('eledia_adminexamdates_blocks', $dataobject);
            foreach ($specialrooms as $specialroom) {
                $roomdataobject = new \stdClass();
                $roomdataobject->roomannotationtext = $formdata->annotationtext;
                $roomdataobject->blockid = $blockid;
                $roomdataobject->examroom = $specialroom;
                $DB->insert_record('eledia_adminexamdates_rooms', $roomdataobject);
            }
        } else {
            $roomsdata = $DB->get_records('eledia_adminexamdates_rooms', ['blockid' => $formdata->blockid]);
            $idrooms = array_column($roomsdata, 'id', 'examroom');
            $deleterooms = $idrooms;
            foreach ($specialrooms as $specialroom) {
                $roomdataobject = new \stdClass();
                $roomdataobject->roomannotationtext = $formdata->annotationtext;
                unset($deleterooms[$specialroom]);
                if (isset($idrooms[$specialroom])) {
                    $roomdataobject->id = $idrooms[$specialroom];
                    $DB->update_record('eledia_adminexamdates_rooms', $roomdataobject);
                } else {
                    $roomdataobject->blockid = $formdata->blockid;
                    $roomdataobject->examroom = $specialroom;
                    $DB->insert_record('eledia_adminexamdates_rooms', $roomdataobject);
                }
            }
            if (!empty($deleterooms)) {
                $DB->delete_records_list('eledia_adminexamdates_rooms', 'id', array_values($deleterooms));
            }

            $dataobject->id = $formdata->blockid;
            $DB->update_record('eledia_adminexamdates_blocks', $dataobject);
        }

        /*$dataobject = new \stdClass();

        $specialrooms = is_array($formdata->specialrooms)
                ? implode(',', $formdata->specialrooms) : $formdata->specialrooms;

        $dataobject->blocktimestart = $formdata->blocktimestart;
        $dataobject->blockduration = $formdata->blockduration;

        $dataobject->examtimestart = $formdata->examtimestart;
        $dataobject->examduration = $formdata->examduration;
        $dataobject->annotationtext = $formdata->annotationtext;

        $roomdataobject = new \stdClass();

        if (empty($formdata->blockid)) {
            $dataobject->userid = $USER->id;
            $dataobject->timecreated = time();
            $examdateid = $DB->insert_record('eledia_adminexam_blocks', $dataobject);
        } else {
            $dataobject->id = $formdata->blockid;
            $DB->update_record('eledia_adminexam_blocks', $dataobject);
        }*/

    }

    /**
     * Save single exam date.
     *
     * @param stdClass $formdata of form.
     */
    public
    static function savesingleexamdate($formdata) {
        global $DB;

        $dataobject = new \stdClass();
        $dataobject->blocktimestart = $formdata->blocktimestart;
        $dataobject->blockduration = $formdata->blockduration;

        $checkedrooms = $formdata->blockexamroomscheck;

        if (empty($formdata->blockid)) {
            $dataobject->examdateid = $formdata->examdateid;
            $blockid = $DB->insert_record('eledia_adminexamdates_blocks', $dataobject);
            foreach ($checkedrooms as $roomid => $checkedroom) {
                $roomdataobject = new \stdClass();
                if (!isset($formdata->roomannotationtext[$roomid])) {
                    $roomdataobject->roomnumberstudents = (!empty($formdata->roomnumberstudents[$roomid]))
                            ? $formdata->roomnumberstudents[$roomid] : null;
                    $roomdataobject->roomsupervisor1 = serialize(array_filter($formdata->roomsupervisor1[$roomid]));
                    $roomdataobject->roomsupervisor2 = serialize(array_filter($formdata->roomsupervisor2[$roomid]));
                    $roomdataobject->roomsupervision1 = serialize(array_filter($formdata->roomsupervision1[$roomid]));
                    $roomdataobject->roomsupervision2 = serialize(array_filter($formdata->roomsupervision2[$roomid]));
                } else {
                    $roomdataobject->roomannotationtext = (!empty($formdata->roomannotationtext[$roomid]))
                            ? $formdata->roomannotationtext[$roomid] : null;
                }
                if ($checkedroom) {
                    $roomdataobject->blockid = $blockid;
                    $roomdataobject->examroom = $roomid;
                    $DB->insert_record('eledia_adminexamdates_rooms', $roomdataobject);
                }
            }
        } else {
            $blockid = $formdata->blockid;
            $roomsdata = $DB->get_records('eledia_adminexamdates_rooms', ['blockid' => $formdata->blockid]);
            $idrooms = array_column($roomsdata, 'id', 'examroom');
            $deleterooms = $idrooms;
            foreach ($checkedrooms as $roomid => $checkedroom) {
                $roomdataobject = new \stdClass();
                if (!isset($formdata->roomannotationtext[$roomid])) {
                    $roomdataobject->roomnumberstudents = (!empty($formdata->roomnumberstudents[$roomid]))
                            ? $formdata->roomnumberstudents[$roomid] : null;
                    $roomdataobject->roomsupervisor1 = serialize(array_filter($formdata->roomsupervisor1[$roomid]));
                    $roomdataobject->roomsupervisor2 = serialize(array_filter($formdata->roomsupervisor2[$roomid]));
                    $roomdataobject->roomsupervision1 = serialize(array_filter($formdata->roomsupervision1[$roomid]));
                    $roomdataobject->roomsupervision2 = serialize(array_filter($formdata->roomsupervision2[$roomid]));
                } else {
                    $roomdataobject->roomannotationtext = (!empty($formdata->roomannotationtext[$roomid]))
                            ? $formdata->roomannotationtext[$roomid] : null;
                }
                if ($checkedroom) {
                    unset($deleterooms[$roomid]);
                    if (isset($idrooms[$roomid])) {
                        $roomdataobject->id = $idrooms[$roomid];
                        $DB->update_record('eledia_adminexamdates_rooms', $roomdataobject);
                    } else {
                        $roomdataobject->blockid = $formdata->blockid;
                        $roomdataobject->examroom = $roomid;
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
        $examtimestart = $DB->get_record('eledia_adminexamdates', ['id' => $formdata->examdateid])->examtimestart;
        $blocktimestart =
                $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $formdata->examdateid], 'blocktimestart');
        $blocktimestart = reset($blocktimestart)->blocktimestart;

        if ($examtimestart != $blocktimestart) {
            $dataobject = new \stdClass();
            $dataobject->id = $formdata->examdateid;
            $dataobject->examtimestart = $blocktimestart;
            $DB->update_record('eledia_adminexamdates', $dataobject);
        }

        // $confirmed = $DB->get_record('eledia_adminexamdates', ['id' => $formdata->examdateid])->confirmed;
        // if (!$confirmed || $confirmed == 2) {
        //    self::examconfirm($formdata->examdateid);
        //}
        return $blockid;
    }

    /**
     * Has free time slots.
     *
     * @param stdClass $formdata of form.
     */
    public
    static function hasfreetimeslots2($formdata, $bookings) {
        $formdata = (object) $formdata;
        global $DB;
        $examdateid = !empty($formdata->editexamdate) ? $formdata->editexamdate : false;
        $beginofday = strtotime("today", $formdata->examtimestart);
        $endofday = strtotime("tomorrow", $formdata->examtimestart) - 1;
        $startexam = $beginofday + (get_config('block_eledia_adminexamdates', 'startexam_hour') * 3600)
                + (get_config('block_eledia_adminexamdates', 'startexam_minute') * 60);
        $endexam = $beginofday + (get_config('block_eledia_adminexamdates', 'endexam_hour') * 3600)
                + (get_config('block_eledia_adminexamdates', 'endexam_minute') * 60);
        $breakbetweenblockdates = get_config('block_eledia_adminexamdates', 'breakbetweenblockdates');
        $distancebetweenblockdates = get_config('block_eledia_adminexamdates', 'distancebetweenblockdates');
        $hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());
        if (!$hasconfirmexamdatescap && (($formdata->examtimestart < time()) ||
                        (($formdata->examtimestart + ($formdata->examduration * 60)) < time()))) {
            return get_string('error_pastexamtime', 'block_eledia_adminexamdates');
        };

        if (!$bookings && (($formdata->examtimestart < $startexam) ||
                        (($formdata->examtimestart + ($formdata->examduration * 60)) > $endexam))) {
            return get_string('error_startexamtime', 'block_eledia_adminexamdates',
                    ['start' => date('H.i', $startexam), 'end' => date('H.i', $endexam)]);
        };

        $params = [$beginofday, $endofday, $examdateid];

        $sql = "SELECT ar.id, a.id AS examdateid, a.examduration, ab.blocktimestart, ab.blockduration, ar.examroom, ar.blockid
                    FROM {eledia_adminexamdates_blocks} ab 
                    JOIN {eledia_adminexamdates} a ON ab.examdateid = a.id
                    JOIN {eledia_adminexamdates_rooms} ar ON ar.blockid = ab.id
                   WHERE ab.blocktimestart > ? AND ab.blocktimestart < ? AND ab.examdateid != ?
                   ORDER BY ab.blocktimestart";

        $datesoftheday = $DB->get_records_sql($sql, $params);

        $examrooms = !is_array($formdata->examrooms) ? explode(',', $formdata->examrooms) : $formdata->examrooms;
        $rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));
        $roomcapacities = [];
        //$roomcapacitysum = 0;
        foreach ($rooms as $room) {
            $roomitems = explode('|', $room);
            if (!empty($roomitems[2])) {
                $roomcapacities[$roomitems[0]] = $roomitems[2];
                //if (in_array($roomitems[0], $examrooms)) {
                //    $roomcapacitysum += $roomitems[2];
                //}
            }
        };
        // Get from all rooms the free time capacities.
        // First initialize the last blockend time with day start exam time.
        $roomfreecapacities = [];
        foreach ($examrooms as $examroom) {
            $object = new stdClass();
            $object->lastblockend = $startexam;
            $object->freecapacities = [];
            $roomfreecapacities[$examroom] = $object;
        }
        foreach ($datesoftheday as $date) {
            if (in_array($date->examroom, $examrooms)) {
                $lastblockend = $roomfreecapacities[$date->examroom]->lastblockend;
                $blocktimeend = $date->blocktimestart + (($date->blockduration + $distancebetweenblockdates) * 60);
                if ($date->blocktimestart <= $lastblockend && $blocktimeend > $lastblockend) {
                    $roomfreecapacities[$date->examroom]->lastblockend = $blocktimeend;
                } else if ($date->blocktimestart > $lastblockend && $blocktimeend > $lastblockend) {
                    if ($blocktimeend < $endexam) {
                        $object = new stdClass();
                        $object->blockfreestart = $lastblockend;
                        $object->blockfreeduration = $date->blocktimestart - $lastblockend;
                        $roomfreecapacities[$date->examroom]->freecapacities[] = $object;
                    }
                    $roomfreecapacities[$date->examroom]->lastblockend = $blocktimeend;
                }
            }
        }
        // Last step: get the free time space from the last blockend to the end of day exam time.
        foreach ($examrooms as $examroom) {
            $lastblockend = $roomfreecapacities[$examroom]->lastblockend;
            if ($lastblockend < $endexam) {
                $object = new stdClass();
                $object->blockfreestart = $lastblockend;
                $object->blockfreeduration = $endexam - $lastblockend;
                $roomfreecapacities[$examroom]->freecapacities[] = $object;
            }
        }
        // print_r('###$datesoftheday:');
        // print_r($datesoftheday);

        //print_r('###$roomfreecapacities:');
        //print_r($roomfreecapacities);
        $numberstudents = $formdata->numberstudents;
        $bookingrooms = [];
        $bookdate = $formdata->examtimestart;
        // $firstbooking = true;
        $firstbookingdate = true;

        while ($numberstudents > 0) {
            //print_r('###WHILE!');
            $nextfreecapacity = new stdClass();
            //$timeneedbegin = $firstbooking ? $distancebetweenblockdates : $breakbetweenblockdates;
            $timeneed = $formdata->examduration * 60;
            foreach ($examrooms as $examroom) {
                //print_r('###$examroom!'.$examroom);
                foreach ($roomfreecapacities[$examroom]->freecapacities as $freecapacity) {
                    $blockfreeend = $freecapacity->blockfreestart + $freecapacity->blockfreeduration;
                    $blockfreeduration = $blockfreeend - $bookdate;
                    //print_r('###$blockfreeend:');
                    //print_r($blockfreeend);
                    //print_r('###$timeneedbegin:');

                    //print_r($timeneed);
                    ///print_r('###$bookdate:');
                    //print_r($bookdate);
                    //$freecapacity->blockfreestart <= $bookdate && $bookdate < $blockfreeend &&
                    if ($freecapacity->blockfreestart <= $bookdate && $bookdate < $blockfreeend &&
                            $blockfreeduration >= $timeneed) {

                        //print_r('###IF1!');
                        if (!isset($nextfreecapacity->blockfreestart) || (isset($nextfreecapacity->blockfreestart) &&
                                        $nextfreecapacity->blockfreestart > $freecapacity->blockfreestart)) {
                            //print_r('###IF2!');
                            $nextfreecapacity->examrooms[] = $examroom;
                            //      $blockfreestart=($freecapacity->blockfreestart<$bookdate)? $bookdate : $freecapacity->blockfreestart;
                            $nextfreecapacity->blockfreestart = $bookdate;
                            //print_r('###$nextfreecapacity:');
                            //print_r($nextfreecapacity);
                        }
                    }
                }
            }
            //print_r('##########$nextfreecapacity:');
            //print_r($nextfreecapacity);
            if (isset($nextfreecapacity->blockfreestart)) {
                //$firstbooking = false;
                //print_r('##########BOOKING!');

                foreach ($nextfreecapacity->examrooms as $examroom) {
                    $rest = $numberstudents - $roomcapacities[$examroom];
                    //print_r('###$rest:');
                    //print_r($rest);
                    if ($rest >= 0 || abs($rest) < $roomcapacities[$examroom]) {
                        $bookingrooms[$nextfreecapacity->blockfreestart][] = $examroom;
                        $numberstudents = $rest;
                        //print_r('###$bookingrooms:');
                        //print_r($bookingrooms);
                    }
                }
                $bookdate = $bookdate + ($formdata->examduration + $breakbetweenblockdates) * 60;
            } else {
                if($bookings){
                    return false;
                } else if($firstbookingdate){
                    return get_string('error_examdate_already_taken', 'block_eledia_adminexamdates');
                } else {
                    return get_string('error_startexamtime', 'block_eledia_adminexamdates',
                            ['start' => date('H.i', $startexam), 'end' => date('H.i', $endexam)]);
                }
                
                //$timeneedbegin = $firstbooking ? $distancebetweenblockdates : $breakbetweenblockdates;
                //$bookdate = $bookdate +
                // return false;
                // exit;
            }
            $firstbookingdate = false;
        }
        if ($bookings) {
            return $bookingrooms;
        };
        //return $bookingrooms;
        // print_r('###$bookingrooms:');
        //print_r($bookingrooms);
        //exit;

        //$blockdates = [];
        //
        //$dateconflict = false;
        //$blockdate = (object) [
        //        'blocktimestart' => $formdata->examtimestart,
        //        'blockduration' => $formdata->examduration,
        //        'timestart' => $formdata->examtimestart - ($distancebetweenblockdates * 60),
        //        'timeend' => $formdata->examtimestart + $formdata->examduration + ($distancebetweenblockdates * 60),
        //        'rooms' => []];
        //foreach ($datesoftheday as $date) {
        //    if (!((($blockdate->timestart <= $date->blocktimestart) && ($blockdate->timeend <= $date->blocktimestart)) ||
        //            (($blockdate->timestart >= $date->blocktimestart + ($date->blockduration * 60)) &&
        //                    ($blockdate->timeend >= $date->blocktimestart + ($date->blockduration * 60))))) {
        //        $dateconflict = true;
        //        break;
        //    }
        //}
    }

    /**
     * Has free time slots.
     *
     * @param stdClass $formdata of form.
     */
    public
    static function hasfreetimeslots($formdata) {
        $formdata = (object) $formdata;
        global $DB;
        $sql = "SELECT ar.id, a.id AS examdateid, a.examduration, ab.blocktimestart, ab.blockduration, ar.examroom, ar.blockid
                    FROM {eledia_adminexamdates_blocks} ab 
                    JOIN {eledia_adminexamdates} a ON ab.examdateid = a.id
                    JOIN {eledia_adminexamdates_rooms} ar ON ar.blockid = ab.id
                   WHERE ab.blocktimestart > ? AND ab.blocktimestart < ?
                   ORDER BY ab.blocktimestart";

        $beginofday = strtotime("today", $formdata->examtimestart);
        $endofday = strtotime("tomorrow", $formdata->examtimestart) - 1;
        $params = [$beginofday, $endofday];

        $datesoftheday = $DB->get_records_sql($sql, $params);
        $examrooms = !is_array($formdata->examrooms) ? explode(',', $formdata->examrooms) : $formdata->examrooms;
        $rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));
        $roomcapacities = [];
        $roomcapacitysum = 0;
        foreach ($rooms as $room) {
            $roomitems = explode('|', $room);
            if (!empty($roomitems[2])) {
                $roomcapacities[$roomitems[0]] = $roomitems[2];
                if (in_array($roomitems[0], $examrooms)) {
                    $roomcapacitysum += $roomitems[2];
                }
            }
        };
        $blockdates = [];
        $breakbetweenblockdates = get_config('block_eledia_adminexamdates', 'breakbetweenblockdates');
        $distancebetweenblockdates = get_config('block_eledia_adminexamdates', 'distancebetweenblockdates');
        $numberofblocks = ceil($formdata->numberstudents / $roomcapacitysum);
        for ($i = 1; $i <= $numberofblocks; $i++) {
            $numberstudents = (($i * $roomcapacitysum) > $formdata->numberstudents) ? $formdata->numberstudents % $roomcapacitysum :
                    $roomcapacitysum;
            $blocktimestart = $formdata->examtimestart + (($i - 1) * $formdata->examduration * 60)
                    + (($i - 1) * $breakbetweenblockdates * 60);

            $blockdates[$i - 1] = (object) [
                    'blocktimestart' => $blocktimestart,
                    'blockduration' => $formdata->examduration,
                    'timestart' => $blocktimestart - ($distancebetweenblockdates * 60),
                    'timeend' => $blocktimestart + $formdata->examduration + ($distancebetweenblockdates * 60),
                    'rooms' => [],
            ];

            $sumroomcapacity = 0;
            foreach ($roomcapacities as $roomid => $roomcapacity) {
                if (in_array($roomid, $examrooms)) {
                    $sumroomcapacity += $roomcapacity;
                    $isrestnumberstudents = $sumroomcapacity >= $numberstudents;
                    // $roomnumberstudents = $isrestnumberstudents ? $numberstudents - $sumroomcapacity : $roomcapacity;
                    $blockdates[$i - 1]->rooms[] = $roomid;
                    if ($isrestnumberstudents) {
                        break;
                    }
                }
            }

        }

        foreach ($blockdates as $blockdate) {
            foreach ($datesoftheday as $date) {
                if ((empty($formdata->editexamdate) || (!empty($formdata->editexamdate) && ($formdata->editexamdate
                                                != $date->examdateid)))
                        && in_array($date->examroom, $blockdate->rooms)) {
                    if (!((($blockdate->timestart <= $date->blocktimestart) && ($blockdate->timeend <= $date->blocktimestart)) ||
                            (($blockdate->timestart >= $date->blocktimestart + ($date->blockduration * 60)) &&
                                    ($blockdate->timeend >= $date->blocktimestart + ($date->blockduration * 60))))) {
                        return get_string('error_examdate_already_taken', 'block_eledia_adminexamdates');
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get free time slots.
     *
     * @param stdClass $formdata of form.
     */
    public
    static function getfreetimeslots2($examdateid, $formdata) {
        global $DB;

        $bookings = self::hasfreetimeslots2($formdata, true);
        foreach ($bookings as $blocktimestart => $bookingrooms) {
            $blockid = $DB->insert_record('eledia_adminexamdates_blocks',
                    (object) ['examdateid' => $examdateid,
                            'blocktimestart' => $blocktimestart,
                            'blockduration' => $formdata->examduration
                    ]);
            foreach ($bookingrooms as $bookingroom) {
                $datesid = $DB->insert_record('eledia_adminexamdates_rooms',
                        (object) ['blockid' => $blockid,
                                'examroom' => $bookingroom,
                        ]);
            }
        }
    }

    /**
     * Update free time slots.
     *
     * @param stdClass $formdata of form.
     */
    public
    static function updatefreetimeslots2($examdateid, $formdata) {
        global $DB;

        $bookings = self::hasfreetimeslots2($formdata, true);
        if ($bookings) {
            $examparts = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid]);
            if (!empty($examparts)) {
                $DB->delete_records_list('eledia_adminexamdates_rooms', 'blockid', array_keys($examparts));
                $DB->delete_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid]);
            }

            foreach ($bookings as $blocktimestart => $bookingrooms) {
                $blockid = $DB->insert_record('eledia_adminexamdates_blocks',
                        (object) ['examdateid' => $examdateid,
                                'blocktimestart' => $blocktimestart,
                                'blockduration' => $formdata->examduration
                        ]);
                foreach ($bookingrooms as $bookingroom) {
                    $datesid = $DB->insert_record('eledia_adminexamdates_rooms',
                            (object) ['blockid' => $blockid,
                                    'examroom' => $bookingroom,
                            ]);
                }
            }
        }
    }

    /**
     * Get free time slots.
     *
     * @param stdClass $formdata of form.
     */
    public
    static function getfreetimeslots($examdateid, $formdata) {
        global $DB, $USER;
        /*      $sql = "SELECT a.id, a.examduration, ag.blocktimestart, ad.examroom, ad.blockid
                    FROM {eledia_adminexamdates} a
                    JOIN {eledia_adminexamdates_blocks} ag ON ag.examdateid = a.id
                    JOIN {eledia_adminexamdates_rooms} ad ON ad.blockid = ag.id
                   WHERE ag.blocktimestart > ? AND ag.blocktimestart < ?";

              $params = [strtotime('07:00:00', $formdata->examtimestart),
                  strtotime('19:00:00', $formdata->examtimestart)];

              $datesoftheday = $DB->get_records_sql($sql, $params);*/
        $examrooms = !is_array($formdata->examrooms) ? explode(',', $formdata->examrooms) : $formdata->examrooms;
        $rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));
        $roomcapacities = [];
        $roomcapacitysum = 0;
        foreach ($rooms as $room) {
            $roomitems = explode('|', $room);
            if (!empty($roomitems[2])) {
                $roomcapacities[$roomitems[0]] = $roomitems[2];
                if (in_array($roomitems[0], $examrooms)) {
                    $roomcapacitysum += $roomitems[2];
                }
            }
        };

        $breakbetweenblockdates = get_config('block_eledia_adminexamdates', 'breakbetweenblockdates');
        $numberofblocks = ceil($formdata->numberstudents / $roomcapacitysum);
        for ($i = 1; $i <= $numberofblocks; $i++) {
            $numberstudents = (($i * $roomcapacitysum) > $formdata->numberstudents) ? $formdata->numberstudents % $roomcapacitysum :
                    $roomcapacitysum;
            $blocktimestart = $formdata->examtimestart + (($i - 1) * $formdata->examduration * 60)
                    + (($i - 1) * $breakbetweenblockdates * 60);
            $blockid = $DB->insert_record('eledia_adminexamdates_blocks',
                    (object) ['examdateid' => $examdateid,
                            'blocktimestart' => $blocktimestart,
                            'blockduration' => $formdata->examduration
                    ]);
            if (!empty($blockid)) {
                $sumroomcapacity = 0;
                foreach ($roomcapacities as $roomid => $roomcapacity) {
                    if (in_array($roomid, $examrooms)) {
                        $sumroomcapacity += $roomcapacity;
                        $isrestnumberstudents = $sumroomcapacity >= $numberstudents;
                        // $roomnumberstudents = $isrestnumberstudents ? $numberstudents - $sumroomcapacity : $roomcapacity;
                        $datesid = $DB->insert_record('eledia_adminexamdates_rooms',
                                (object) ['blockid' => $blockid,
                                        'examroom' => $roomid,
                                ]);
                        if ($isrestnumberstudents) {
                            break;
                        }
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
    public
    static function editexamdate($examdateid) {
        global $DB;
        $dataobject = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid], '*', MUST_EXIST);
        $formdata = new stdClass();
        $formdata->examrooms = $dataobject->examrooms;
        $formdata->examtimestart = $dataobject->examtimestart;
        $formdata->examduration = $dataobject->examduration;
        $formdata->department = $dataobject->department;
        $formdata->category = $dataobject->category;
        $formdata->examname = $dataobject->examname;
        $formdata->editexamdate = $examdateid;
        $formdata->semester = $dataobject->semester;
        $formdata->numberstudents = $dataobject->numberstudents;
        $formdata->examiner = $dataobject->examiner;
        $formdata->contactperson = $dataobject->contactperson;
        //        $formdata->contactperson = !empty($dataobject->contactpersonid) ? $dataobject->contactpersonid : $dataobject->contactperson;
        //        if (!empty($dataobject->contactpersonid)) {
        //            $formdata->contactpersonemail = $dataobject->contactpersonid;
        //        } else {
        //            $formdata->contactpersonemail = "$dataobject->contactpersonemail";
        //        }
        //        $formdata->contactperson = $dataobject->contactpersonid;
        //        $formdata->contactpersonemail = $dataobject->contactpersonemail;
        $formdata->responsibleperson = $dataobject->responsibleperson;
        $formdata->annotationtext = $dataobject->annotationtext;
        return $formdata;
    }

    /**
     * Edit special room booking.
     *
     * @param stdClass $formdata of form.
     */
    public
    static function editspecialroom($blockid) {
        global $DB;
        $block = $DB->get_record('eledia_adminexamdates_blocks', ['id' => $blockid]);
        $rooms = $DB->get_records('eledia_adminexamdates_rooms', ['blockid' => $blockid]);

        $specialrooms = array_column($rooms, 'examroom');

        $formdata = new stdClass();
        $formdata->booktimestart = $block->blocktimestart;
        $formdata->bookduration = $block->blockduration;
        $formdata->annotationtext = array_values($rooms)[0]->roomannotationtext;
        $formdata->specialrooms = $specialrooms;
        $formdata->blockid = $blockid;
        return $formdata;
    }

    /**
     * Cancel special room.
     *
     * @return array
     */
    public
    static function cancelspecialrooms($blockid) {
        global $DB;
        $DB->delete_records('eledia_adminexamdates_rooms', ['blockid' => $blockid]);
        $DB->delete_records('eledia_adminexamdates_blocks', ['id' => $blockid]);
    }

    /**
     * Edit single exam date.
     *
     * @param stdClass $formdata of form.
     */
    public
    static function editsingleexamdate($blockid, $examdateid, $newblock) {
        global $DB;
        // $dataobject = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid], '*', MUST_EXIST);
        $exampart = $DB->get_record('eledia_adminexamdates_blocks', ['id' => $blockid]);

        $examdate = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid]);
        $examblocks = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdate->id], 'blocktimestart');
        $lastexamblock = end($examblocks);
        //$exampart = array_shift($examparts);
        //print_R($examparts);
        $formdata = new stdClass();
        $formdata->examdateid = $examdateid;
        $blocktimestart = ($newblock) ? $lastexamblock->blocktimestart : $exampart->blocktimestart;
        $formdata->blocktimestart = $blocktimestart;
        $blockduration = ($newblock) ? $lastexamblock->blockduration : $exampart->blockduration;
        $formdata->blockduration = $blockduration;
        $blockid = ($newblock) ? null : $exampart->id;
        $formdata->blockid = $blockid;
        $formdata->save = true;

        //  foreach ($examparts as $index => $exampart) {
        $blockid = ($newblock) ? $lastexamblock->id : $exampart->id;
        $rooms = $DB->get_records('eledia_adminexamdates_rooms', ['blockid' => $blockid]);
        //print_r($rooms);
        //        print_r($exampart);
        //        exit;
        if (!empty($rooms)) {
            foreach ($rooms as $room) {
                $formdata->blockexamroomscheck[$room->examroom] = true;
                if (!$newblock) {
                    $formdata->roomnumberstudents[$room->examroom] =
                            !empty($room->roomnumberstudents) ? $room->roomnumberstudents : '';
                    $formdata->roomsupervisor1[$room->examroom] = unserialize($room->roomsupervisor1);
                    $formdata->roomsupervisor2[$room->examroom] = unserialize($room->roomsupervisor2);
                    $formdata->roomsupervision1[$room->examroom] = unserialize($room->roomsupervision1);
                    $formdata->roomsupervision2[$room->examroom] = unserialize($room->roomsupervision2);
                    $formdata->roomannotationtext[$room->examroom] = $room->roomannotationtext;
                }
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
    public
    static function getexamdateoverview($blockid, $examdateid, $newblock) {
        global $DB, $OUTPUT;
        $hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());
        $examdate = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid]);
        $examblocks = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdate->id], 'blocktimestart');
        $lastblock = end($examblocks);
        $examname = ($examdate->courseid) ? \html_writer::tag('a', $examdate->examname,
                array('href' => get_config('block_eledia_adminexamdates', 'apidomain')
                        . '/course/view.php?id=' . $examdate->courseid, 'class' => 'examdate-course-link',
                        'target' => '_blank')) : $examdate->examname;
        $text = '';
        $text .= \html_writer::start_tag('div', array('class' => 'card'));
        $text .= \html_writer::start_tag('div', array('class' => 'card-body'));
        $text .= \html_writer::tag('h5', $examname, array('class' => 'card-title'));
        $text .= \html_writer::start_tag('p', array('class' => 'card-text'));
        $text .= \html_writer::start_tag('dl');

        $calendarlinkstring = get_string('calendarlink', 'block_eledia_adminexamdates') . ' ' . $examdate->examname;
        $calendaricon = \html_writer::tag('i', '',
                array('class' => 'icon fa fa-calendar', 'title' => $calendarlinkstring, 'aria-label' => $calendarlinkstring));
        $calendarurl = new \moodle_url('/blocks/eledia_adminexamdates/calendar.php', ['displaydate' => $examdate->examtimestart]);
        $calendarlink = \html_writer::tag('a', $calendaricon, ['href' => $calendarurl]);
        $text .= \html_writer::tag('dt', get_string('time', 'block_eledia_adminexamdates'));
        $text .= \html_writer::tag('dd', date('d.m.Y, H.i', $examdate->examtimestart)
                . ' - ' . date('H.i', $lastblock->blocktimestart + ($lastblock->blockduration * 60)) . get_string('time', 'block_eledia_adminexamdates'). ' ' . $calendarlink);
        $text .= \html_writer::tag('dt', get_string('number_students', 'block_eledia_adminexamdates'));
        $text .= \html_writer::tag('dd', $examdate->numberstudents);
        $text .= \html_writer::tag('dt', get_string('examiner', 'block_eledia_adminexamdates'));
        $examiners = explode(',', $examdate->examiner);
        $examinernames = [];
        foreach ($examiners as $examiner) {
            if ($user = \core_user::get_user($examiner)) {
                $examinernames[] = fullname($user);
            }
        }
        $text .= \html_writer::tag('dd', implode(', ', $examinernames));
        $text .= \html_writer::tag('dt', get_string('contactperson', 'block_eledia_adminexamdates'));
        $contactperson = \core_user::get_user($examdate->contactperson);
        $text .= \html_writer::tag('dd', fullname($contactperson) . ' | ' . $contactperson->email);
        $text .= \html_writer::tag('dt', get_string('responsibleperson', 'block_eledia_adminexamdates'));
        $responsibleperson = $examdate->responsibleperson ? fullname(\core_user::get_user($examdate->responsibleperson)) : '-';
        $text .= \html_writer::tag('dd', $responsibleperson);

        $urldeletesingleexamdate =
                new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['delete' => 0, 'blockid' => $blockid]);
        $text .= $OUTPUT->box($OUTPUT->single_button($urldeletesingleexamdate, '', 'post'), 'd-none', 'delsingleexamdatebtn');

        $index = 1;
        foreach ($examblocks as $examblock) {
            $acitveblock = (($blockid == $examblock->id) && !($newblock)) ? array('class' => 'font-weight-bold') : array();
            $viewindex = (count($examblocks) > 1) ? $index . '. ' : '';
            $delstring = get_string('delete', 'block_eledia_adminexamdates') . ' ' . $viewindex .
                    get_string('partialdate', 'block_eledia_adminexamdates');
            $trash = \html_writer::tag('i', '',
                    array('class' => 'icon fa fa-trash fa-fw delsingleexamdate', 'data-examblockid' => $examblock->id,
                            'title' => $delstring, 'aria-label' => $delstring));
            $viewtrash = ($hasconfirmexamdatescap && (count($examblocks) > 1)) ? $trash : '';
            $text .= \html_writer::tag('dt', $viewindex . get_string('partialdate', 'block_eledia_adminexamdates'));
            $text .= \html_writer::tag('dd', date('d.m.Y, H.i', $examblock->blocktimestart)
                    . ' - ' . date('H.i', $examblock->blocktimestart + ($examblock->blockduration * 60)). get_string('time', 'block_eledia_adminexamdates') . ' ' . $viewtrash,
                    $acitveblock);
            $index++;
        }
        $text .= \html_writer::end_tag('dl');
        $text .= \html_writer::end_tag('p');

        if ($hasconfirmexamdatescap || (!$hasconfirmexamdatescap && !$examdate->confirmed)) {
            $url = new \moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php', ['cancelexamdate' => $examdate->id]);
            $text .= $OUTPUT->single_button($url, get_string('cancelexamdate', 'block_eledia_adminexamdates'), 'post');
        }
        if ($hasconfirmexamdatescap && !$examdate->confirmed) {
            $url = new \moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php',
                    ['confirmexamdateyes' => $examdate->id]);
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
    public
    static function getexamdateitems($confirmed) {
        $text = '';
        global $DB, $PAGE, $OUTPUT, $USER;
        $confirmed = (int) $confirmed;
        $hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());
        $now = time();
        $confirmedcondition = $confirmed ? "confirmed = 1" : "confirmed != 1 ";
        $conditions = $hasconfirmexamdatescap ? $confirmedcondition :
                "examtimestart > $now AND $confirmedcondition AND ( userid = $USER->id OR contactperson = '$USER->id' ) ";
        $sql = "SELECT *
                  FROM {eledia_adminexamdates} 
                 WHERE $conditions
                  ORDER BY examtimestart DESC";

        $adminexamdates = $DB->get_records_sql($sql);

        foreach ($adminexamdates as $adminexamdate) {
            $examname = ($adminexamdate->courseid) ? \html_writer::tag('a', $adminexamdate->examname,
                    array('href' => get_config('block_eledia_adminexamdates', 'apidomain')
                            . '/course/view.php?id=' . $adminexamdate->courseid, 'class' => 'examdate-course-link',
                            'target' => '_blank')) : $adminexamdate->examname;
            $adminexamblocks =
                    $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $adminexamdate->id], 'blocktimestart');
            $lastblock = end($adminexamblocks);
            $text .= \html_writer::start_tag('div', array('class' => 'row mt-3'));
            $text .= \html_writer::start_tag('div', array('class' => 'card mr-3'));
            $text .= \html_writer::start_tag('div', array('class' => 'card-body'));
            $text .= \html_writer::tag('h5', $examname, array('class' => 'card-title'));
            $text .= \html_writer::start_tag('p', array('class' => 'card-text'));
            $text .= \html_writer::start_tag('dl');
            $calendarlinkstring = get_string('calendarlink', 'block_eledia_adminexamdates') . ' ' . $adminexamdate->examname;
            $calendaricon = \html_writer::tag('i', '',
                    array('class' => 'icon fa fa-calendar', 'title' => $calendarlinkstring, 'aria-label' => $calendarlinkstring));
            $calendarurl =
                    new \moodle_url('/blocks/eledia_adminexamdates/calendar.php', ['displaydate' => $adminexamdate->examtimestart]);
            $calendarlink = \html_writer::tag('a', $calendaricon, ['href' => $calendarurl]);
            $text .= \html_writer::tag('dt', get_string('time', 'block_eledia_adminexamdates'));
            $text .= \html_writer::tag('dd', date('d.m.Y, H.i', $adminexamdate->examtimestart)
                    . ' - ' . date('H.i', $lastblock->blocktimestart + ($lastblock->blockduration * 60)) . get_string('time', 'block_eledia_adminexamdates'). ' ' .
                    $calendarlink);
            $text .= \html_writer::tag('dt', get_string('number_students', 'block_eledia_adminexamdates'));
            $text .= \html_writer::tag('dd', $adminexamdate->numberstudents);
            $text .= \html_writer::tag('dt', get_string('examiner', 'block_eledia_adminexamdates'));
            $examiners = explode(',', $adminexamdate->examiner);
            $examinernames = [];
            foreach ($examiners as $examiner) {
                if ($user = \core_user::get_user($examiner)) {
                    $examinernames[] = fullname($user);
                }
            }
            $text .= \html_writer::tag('dd', implode(', ', $examinernames));
            $text .= \html_writer::tag('dt', get_string('contactperson', 'block_eledia_adminexamdates'));
            $contactperson = \core_user::get_user($adminexamdate->contactperson);
            $text .= \html_writer::tag('dd', fullname($contactperson) . ' | ' . $contactperson->email);
            $text .= \html_writer::tag('dt', get_string('responsibleperson', 'block_eledia_adminexamdates'));
            $responsibleperson =
                    $adminexamdate->responsibleperson ? fullname(\core_user::get_user($adminexamdate->responsibleperson)) : '-';
            $text .= \html_writer::tag('dd', $responsibleperson);
            $index = 1;
            foreach ($adminexamblocks as $adminexamblock) {
                $viewindex = (count($adminexamblocks) > 1) ? $index . '. ' : '';
                $text .= \html_writer::tag('dt', $viewindex . get_string('partialdate', 'block_eledia_adminexamdates'));
                $text .= \html_writer::tag('dd', date('d.m.Y, H.i', $adminexamblock->blocktimestart)
                        . ' - ' . date('H.i', $adminexamblock->blocktimestart + ($adminexamblock->blockduration * 60)). get_string('time', 'block_eledia_adminexamdates'));
                $index++;
            }
            $text .= \html_writer::end_tag('dl');
            $text .= \html_writer::end_tag('p');
            //  if ($hasconfirmexamdatescap || !$adminexamdate->confirmed) {
            $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['editexamdate' => $adminexamdate->id]);

            $text .= $OUTPUT->single_button($url, get_string('editexamdate', 'block_eledia_adminexamdates'), 'post');
            //   }
            if ($hasconfirmexamdatescap || ((!$hasconfirmexamdatescap && $adminexamdate->confirmed != 1))) {
                $url = new \moodle_url($PAGE->url, ['cancelexamdate' => $adminexamdate->id]);

                $text .= $OUTPUT->single_button($url, get_string('cancelexamdate', 'block_eledia_adminexamdates'), 'post');
            }
            if (!$hasconfirmexamdatescap && $adminexamdate->confirmed == 1) {
                $url = new \moodle_url('/blocks/eledia_adminexamdates/changerequest.php', ['examdateid' => $adminexamdate->id]);
                $text .= $OUTPUT->single_button($url, get_string('change_request_btn', 'block_eledia_adminexamdates'), 'post');
            }
            if ($hasconfirmexamdatescap) {
                $url = new \moodle_url($PAGE->url, ['confirmexamdate' => $adminexamdate->id]);
                $text .= $OUTPUT->single_button($url, get_string('confirmexamdate', 'block_eledia_adminexamdates'), 'post');

                $url = new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php',
                        ['examdateid' => $adminexamdate->id]);
                $text .= $OUTPUT->single_button($url, get_string('editsingleexamdate', 'block_eledia_adminexamdates'), 'post');
            }
            $text .= \html_writer::end_tag('div');
            $text .= \html_writer::end_tag('div');
            if (!$hasconfirmexamdatescap && ($adminexamdate->confirmed == 1 || $adminexamdate->confirmed == 2)) {
                $text .= self::get_html_checklisttable($adminexamdate->id, $adminexamdate->examname);
            }
            $text .= \html_writer::end_tag('div');
        }
        return $text;
    }

    /**
     * get exam date table items.
     *
     */
    public
    static function getexamdatetable($semester, $frommonth, $fromyear, $tomonth, $toyear) {
        global $DB;
        if (!empty($semester)) {
            $year = substr($semester, 0, 4);
            if (substr($semester, -1) == 1) {
                $timestart = strtotime("1 April $year");
                $timeend = strtotime("1 October $year") - 1;
            } else {
                $timestart = strtotime("1 October $year");
                $year++;
                $timeend = strtotime("1 April $year") - 1;
            }
        } else {
            $time = time();
            if ($time < strtotime("1 April")) {
                $lastyear = date("Y", strtotime("-1 year"));
                $timestart = strtotime("1 October $lastyear");
                $timeend = strtotime("1 April") - 1;
            } else if ($time < strtotime("1 October")) {
                $timestart = strtotime("1 April");
                $timeend = strtotime("1 October") - 1;
            } else {
                $nextyear = date("Y", strtotime("+1 year"));
                $timestart = strtotime("1 October");
                $timeend = strtotime("1 April $nextyear") - 1;
            }
        }
        if (!empty($frommonth) && !empty($tomonth) && !empty($fromyear) && !empty($toyear)) {
            $timestart = make_timestamp($fromyear, $frommonth, 1);
            $timeend = strtotime("next month", make_timestamp($toyear, $tomonth, 1)) - 1;
        }
        $roomoptions = [];
        $roomswithcapacity = [];

        $rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));
        foreach ($rooms as $room) {
            $roomitems = explode('|', $room);
            if (!empty($roomitems[2])) {
                array_push($roomswithcapacity, $roomitems[0]);
            };
            $roomoptions[$roomitems[0]] = $roomitems[1];
        };

        list($inexamroomssql, $inexamroomsparams) = $DB->get_in_or_equal($roomswithcapacity, SQL_PARAMS_NAMED);

        $sql = "SELECT a.*, ab.blockduration,ar.roomnumberstudents,
       ab.blocktimestart, ar.examroom, ar.blockid, ar.roomnumberstudents, ar.roomsupervisor1,
        ar.roomsupervisor2
                FROM {eledia_adminexamdates} a
                JOIN {eledia_adminexamdates_blocks} ab ON ab.examdateid = a.id
                JOIN {eledia_adminexamdates_rooms} ar ON ar.blockid = ab.id
                WHERE ab.blocktimestart > {$timestart} AND ab.blocktimestart < {$timeend} AND ar.examroom {$inexamroomssql} 
                ORDER BY ab.blocktimestart";
        //     WHERE ag.blocktimestart > ? AND ag.blocktimestart < ?";

        //          $params = [strtotime('07:00:00', $formdata->examtimestart),
        //              strtotime('19:00:00', $formdata->examtimestart)];

        $dates = $DB->get_recordset_sql($sql, $inexamroomsparams);
        $tableheaditems =
                ['month', 'date', 'examname', 'examiner', 'contactperson', 'examroom', 'supervisor1', 'supervisor2', 'candidates',
                        'status',
                        'blockid', 'examid', 'links'];
        $text = \html_writer::start_tag('table',
                array('id' => 'examdatestable', 'class' => 'table table-striped table-bordered table-hover table-sm',
                        'style' => 'width:100%'));
        $text .= \html_writer::start_tag('thead', array('class' => 'thead-light'));
        $text .= \html_writer::start_tag('tr');
        foreach ($tableheaditems as $tableheaditem) {
            $text .= \html_writer::tag('th', get_string('tablehead_' . $tableheaditem, 'block_eledia_adminexamdates'),
                    array('scope' => 'col'));
        }
        $text .= \html_writer::end_tag('tr');
        $text .= \html_writer::end_tag('thead');
        $text .= \html_writer::start_tag('tbody');

        foreach ($dates as $date) {
            $text .= \html_writer::start_tag('tr');
            $hiddenmonth = \html_writer::tag('span', date('Ym', $date->examtimestart), array('class' => 'd-none'));
            $text .= \html_writer::tag('td', $hiddenmonth . strftime('%B %Y', date($date->examtimestart)));
            $hiddendate = \html_writer::tag('span', date('YmdHi', $date->examtimestart), array('class' => 'd-none'));
            $text .= \html_writer::tag('td', $hiddendate . date('d.m.Y, H.i', $date->blocktimestart)
                    . '&nbsp;-&nbsp;' . date('H.i', $date->blocktimestart + ($date->blockduration * 60)). get_string('time', 'block_eledia_adminexamdates'));
            $examname = ($date->courseid) ? \html_writer::tag('a', $date->examname,
                    array('href' => get_config('block_eledia_adminexamdates', 'apidomain')
                            . '/course/view.php?id=' . $date->courseid, 'class' => 'examdate-course-link',
                            'target' => '_blank')) : $date->examname;
            $text .= \html_writer::tag('td', $examname);
            $examiners = explode(',', $date->examiner);
            $examinernames = [];
            foreach ($examiners as $examiner) {
                if ($user = \core_user::get_user($examiner)) {
                    $examinernames[] = fullname($user);
                }
            }
            $text .= \html_writer::tag('td', implode(', ', $examinernames));

            $contactperson = \core_user::get_user($date->contactperson);
            $text .= \html_writer::tag('td', fullname($contactperson) . ' | ' . $contactperson->email);
            $text .= \html_writer::tag('td', $roomoptions[$date->examroom]);
            $roomsupervisors1 = '';
            if (!empty($date->roomsupervisor1)) {
                $roomsupervisors1 = (array) unserialize($date->roomsupervisor1);
                foreach ($roomsupervisors1 as $index => $roomsupervisor1) {
                    if (intval($roomsupervisor1)) {
                        $roomsupervisors1[$index] =
                                fullname($DB->get_record('user', array('id' => $roomsupervisor1), '*', MUST_EXIST));
                    }
                }
                $roomsupervisors1 = implode(', ', $roomsupervisors1);
            }
            $roomsupervisors2 = '';
            if (!empty($date->roomsupervisor2)) {
                $roomsupervisors2 = (array) unserialize($date->roomsupervisor2);
                foreach ($roomsupervisors2 as $index => $roomsupervisor2) {
                    if (intval($roomsupervisor2)) {
                        $roomsupervisors2[$index] =
                                fullname($DB->get_record('user', array('id' => $roomsupervisor2), '*', MUST_EXIST));
                    }
                }
                $roomsupervisors2 = implode(', ', $roomsupervisors2);
            }
            $roomnumberstudents = !empty($date->roomnumberstudents) ? $date->roomnumberstudents : '';
            $text .= \html_writer::tag('td', $roomsupervisors1);
            $text .= \html_writer::tag('td', $roomsupervisors2);
            $text .= \html_writer::tag('td', $roomnumberstudents);
            $text .= \html_writer::tag('td', ($date->confirmed == 1) ?
                    get_string('status_confirmed', 'block_eledia_adminexamdates') :
                    get_string('status_unconfirmed', 'block_eledia_adminexamdates'),
                    ($date->confirmed == 1) ? array('class' => 'text-center table-success') :
                            array('class' => 'text-center table-danger'));
            $text .= \html_writer::tag('td', $date->blockid);
            $text .= \html_writer::tag('td', $date->id);
            $text .= \html_writer::tag('td', '');
            $text .= \html_writer::end_tag('tr');
        }

        $text .= \html_writer::end_tag('tbody');
        $text .= \html_writer::start_tag('tfoot', array('class' => 'thead-light'));
        $text .= \html_writer::start_tag('tr');
        foreach ($tableheaditems as $tableheaditem) {
            $text .= \html_writer::tag('th', get_string('tablehead_' . $tableheaditem, 'block_eledia_adminexamdates'),
                    array('scope' => 'col'));
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
    static function examconfirm($examdateid) {
        global $DB, $USER;

        $config = get_config('block_eledia_adminexamdates');
        $examdate = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid], '*', MUST_EXIST);
        $examparts = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid], 'blocktimestart');
        // Get the template's course ID using the course idnumber.
        if (!empty($config->examcoursetemplateidnumber) && (!isset($examdate->courseid) || !$examdate->courseid)) {
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
                    $DB->update_record('eledia_adminexamdates', (object) ['id' => $examdateid,
                            'courseid' => $courseid]);
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

                    }
                }
            }
        }
        if ($examdate->confirmed != 1) {
            // Send confirmation email to contactperson and the exam team.

            $emailuser = new stdClass();
            $emailuser->email = \core_user::get_user($examdate->contactperson)->email;
            $emailuser->id = -99;

            $emailexamteam = get_config('block_eledia_adminexamdates', 'emailexamteam');
            $emailexamteam = !empty($emailexamteam) ? $emailexamteam : false;

            $subject = get_string('examconfirm_email_subject', 'block_eledia_adminexamdates',
                    ['name' => $examdate->examname]);
            $date = date('d.m.Y, H.i', $examdate->examtimestart)
                    . ' - ' . date('H.i', $examdate->examtimestart + ($examdate->examduration * 60)). get_string('time', 'block_eledia_adminexamdates');
            $course = \html_writer::tag('a', get_string('course'),
                    array('href' => get_config('block_eledia_adminexamdates', 'apidomain')
                            . '/course/view.php?id=' . $courseid));
            $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php',
                    ['editexamdate' => $examdateid]);
            $url = $url->out();
            $link = \html_writer::tag('a', get_string('edit'), array('href' => $url));
            $messagetext = get_string('examconfirm_email_body', 'block_eledia_adminexamdates',
                    ['name' => $examdate->examname, 'date' => $date, 'course' => $course, 'url' => $link]);

            email_to_user($emailuser, $USER, $subject, $messagetext);

            if ($emailexamteam) {
                $emailuser->email = $emailexamteam;
                email_to_user($emailuser, $USER, $subject, $messagetext);
            }

            // Set the 'confirmed' state and course ID to this exam date.
            $DB->update_record('eledia_adminexamdates', (object) ['id' => $examdateid,
                    'confirmed' => 1]);
        }
    }

    /**
     * Cancel exam - Delete an exam date.
     *
     * @return array
     */
    public
    static function examcancel($examdateid) {
        global $DB, $USER;
        $examdate = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid], '*', MUST_EXIST);
        $examparts = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid]);

        $emailuser = new stdClass();
        $emailuser->email = \core_user::get_user($examdate->contactperson)->email;
        $emailuser->id = -99;
        if ($examdate->confirmed == 2) {
            $email = ($emailuser->email != $USER->email) ? $USER->email : false;
        } else {
            $email = $examdate->responsibleperson ? \core_user::get_user($examdate->responsibleperson)->email : false;
        }
        $emailexamteam = get_config('block_eledia_adminexamdates', 'emailexamteam');
        $emailexamteam = !empty($emailexamteam) ? $emailexamteam : false;

        $subject = get_string('examcancel_email_subject', 'block_eledia_adminexamdates', ['name' => $examdate->examname]);
        $date = date('d.m.Y, H.i', $examdate->examtimestart)
                . ' - ' . date('H.i', $examdate->examtimestart + ($examdate->examduration * 60)). get_string('time', 'block_eledia_adminexamdates');
        $messagetext = get_string('examcancel_email_body', 'block_eledia_adminexamdates',
                ['name' => $examdate->examname, 'date' => $date]);

        // If the exam course exist - set the exam archive course category.
        if (!empty($examdate->courseid) &&
                !empty($archivecategoryidnumber = get_config('block_eledia_adminexamdates', 'archivecategoryidnumber'))) {
            $param = [
                    'wsfunction' => 'core_course_get_categories',
                    'addsubcategories' => 0,
            ];
            $criteria = "&criteria[0][key]=idnumber&criteria[0][value]=$archivecategoryidnumber";
            $results = self::get_data_from_api($criteria, $param);
            if (!empty($archivecategoryid = $results[0]->id)) {
                $param = ['wsfunction' => 'core_course_update_courses'];
                $paramcourse = "&courses[0][id]=$examdate->courseid&courses[0][categoryid]=$archivecategoryid";
                $results = self::get_data_from_api($paramcourse, $param);
            }
        }

        if (!empty($examparts) && !empty($examdate)) {
            $DB->delete_records_list('eledia_adminexamdates_rooms', 'blockid', array_keys($examparts));
            $DB->delete_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid]);
        }
        $DB->delete_records('eledia_adminexamdates', ['id' => $examdateid]);

        email_to_user($emailuser, $USER, $subject, $messagetext);

        if ($email) {
            $emailuser->email = $email;
            email_to_user($emailuser, $USER, $subject, $messagetext);
        }

        if ($emailexamteam) {
            $emailuser->email = $emailexamteam;
            email_to_user($emailuser, $USER, $subject, $messagetext);
        }
    }

    /**
     * Delete a single exam date.
     *
     * @return array
     */
    public
    static function deletesingleexamdate($blockid, $deleteblockid, $examdateid) {
        global $DB;
        $block = $DB->get_record('eledia_adminexamdates_blocks', ['id' => $deleteblockid]);
        if (!empty($block)) {
            $newblockid = ($blockid == $deleteblockid) ? true : false;
            $DB->delete_records('eledia_adminexamdates_rooms', ['blockid' => $deleteblockid]);
            $DB->delete_records('eledia_adminexamdates_blocks', ['id' => $deleteblockid]);;
            if ($newblockid) {
                $blockid =
                        $DB->get_record('eledia_adminexamdates_blocks', ['examdateid' => $examdateid], 'id', IGNORE_MULTIPLE)->id;
            }
            $examtimestart = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid])->examtimestart;
            $blocktimestart = $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid], 'blocktimestart');
            $blocktimestart = reset($blocktimestart)->blocktimestart;

            if ($examtimestart != $blocktimestart) {
                $dataobject = new \stdClass();
                $dataobject->id = $examdateid;
                $dataobject->examtimestart = $blocktimestart;
                $DB->update_record('eledia_adminexamdates', $dataobject);
            }
        }
        return $blockid;
    }

    /**
     * Send change request email.
     *
     * @return array
     */
    public
    static function sendchangerequestemail($examdateid, $message) {
        global $DB, $USER;

        $emailexamteam = get_config('block_eledia_adminexamdates', 'emailexamteam');

        $emailuser = new stdClass();
        $emailuser->email = $emailexamteam;
        $emailuser->id = -99;

        $blocks = array_values($DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $examdateid], 'blocktimestart'));
        $blockid = $blocks[0]->id;
        $url = new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['blockid' => $blockid]);
        $url = $url->out();
        $link = \html_writer::tag('a', get_string('edit'), array('href' => $url));
        $examdate = $DB->get_record('eledia_adminexamdates', ['id' => $examdateid]);
        $subject = get_string('changerequest_email_subject', 'block_eledia_adminexamdates', ['name' => $examdate->examname]);
        $date = date('d.m.Y, H.i', $examdate->examtimestart)
                . ' - ' . date('H.i', $examdate->examtimestart + ($examdate->examduration * 60)). get_string('time', 'block_eledia_adminexamdates');
        $messagetext = get_string('changerequest_email_body', 'block_eledia_adminexamdates',
                ['name' => $examdate->examname, 'date' => $date, 'url' => $url, 'changerequest' => $message]);
        $htmlmessagetext = get_string('changerequest_email_body', 'block_eledia_adminexamdates',
                ['name' => $examdate->examname, 'date' => $date, 'url' => $link, 'changerequest' => $message]);
        $success = email_to_user($emailuser, $USER, $subject, $messagetext, $htmlmessagetext);

        $emailuser = new stdClass();
        $emailuser->email = \core_user::get_user($examdate->contactperson)->email;
        $emailuser->id = -99;
        $success = email_to_user($emailuser, $USER, $subject, $messagetext, $htmlmessagetext);
    }

    /**
     * Get data from API
     *
     * @return array
     */
    public
    static function get_data_from_api($urlparam, $param) {
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
    static function get_sub_categories($idtype, $categoryidvalue) {
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
                usort($results, function($x, $y) {
                    if ($x === $y) {
                        return 0;
                    }
                    return $x < $y ? -1 : 1;
                });
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

    /**
     * Get html semester dropdown
     *
     * @return array
     */
    public
    static function get_html_select_semester($semester) {
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
        $text = \html_writer::start_tag('form',
                ['id' => 'examdatestable-semester-form', 'method' => 'post']);

        $text .= \html_writer::tag('label', get_string('select_semester', 'block_eledia_adminexamdates') . ':&nbsp;',
                ['for' => 'semester']);
        $text .= \html_writer::start_tag('select',
                ['id' => 'examdatestable-semester-select', 'name' => 'semester',
                        'class' => 'custom-select custom-select-sm form-control']);

        $semester = !empty($semester) ? $semester : $defaultsemester;
        foreach ($options as $key => $name) {
            if ($semester == $key) {
                $text .= \html_writer::tag('option', $name,
                        ['value' => $key, 'selected' => 'selected']);
            } else {
                $text .= \html_writer::tag('option', $name,
                        ['value' => $key]);
            }
        }
        $text .= \html_writer::end_tag('select');

        $text .= \html_writer::end_tag('form');

        return $text . '&nbsp;';
    }

    /**
     * Get html semester dropdown
     *
     * @return array
     */
    public
    static function get_html_select_month($frommonth, $fromyear, $tomonth, $toyear) {
        $optionsmonths = [];
        for ($i = 1; $i <= 12; $i++) {
            $optionsmonths[$i] = utf8_encode(strftime('%B', mktime(0, 0, 0, $i)));
        }
        $optionsyears = [];
        for ($i = date('Y', strtotime('- 2 years')); $i <= date('Y', strtotime('+ 5 years')); $i++) {
            $optionsyears[$i] = $i;
        }
        $text = \html_writer::start_tag('form',
                ['id' => 'examdatestable-month-form', 'method' => 'post']);

        $text .= \html_writer::tag('label', get_string('select_frommonth', 'block_eledia_adminexamdates') . '&nbsp;',
                ['for' => 'semester']);
        $text .= \html_writer::start_tag('select',
                ['id' => 'examdatestable-frommonth-select', 'name' => 'frommonth',
                        'class' => 'custom-select custom-select-sm form-control mr-1']);

        $defaultmonth = date('m', time());
        $frommonth = !empty($frommonth) ? $frommonth : $defaultmonth;
        foreach ($optionsmonths as $key => $name) {
            if ($frommonth == $key) {
                $text .= \html_writer::tag('option', $name,
                        ['value' => $key, 'selected' => 'selected']);
            } else {
                $text .= \html_writer::tag('option', $name,
                        ['value' => $key]);
            }
        }
        $text .= \html_writer::end_tag('select');
        $text .= \html_writer::end_tag('select');
        $text .= \html_writer::start_tag('select',
                ['id' => 'examdatestable-fromyear-select', 'name' => 'fromyear',
                        'class' => 'custom-select custom-select-sm form-control mr-1']);
        $defaultyear = date('Y', time());
        $fromyear = !empty($fromyear) ? $fromyear : $defaultyear;
        foreach ($optionsyears as $key => $name) {
            if ($fromyear == $key) {
                $text .= \html_writer::tag('option', $name,
                        ['value' => $key, 'selected' => 'selected']);
            } else {
                $text .= \html_writer::tag('option', $name,
                        ['value' => $key]);
            }
        }
        $text .= \html_writer::end_tag('select');
        $text .= \html_writer::tag('label', get_string('select_tomonth', 'block_eledia_adminexamdates') . '&nbsp;',
                ['for' => 'tomonth']);
        $text .= \html_writer::start_tag('select',
                ['id' => 'examdatestable-tomonth-select', 'name' => 'tomonth',
                        'class' => 'custom-select custom-select-sm form-control mr-1']);

        $tomonth = !empty($tomonth) ? $tomonth : $defaultmonth;
        foreach ($optionsmonths as $key => $name) {
            if ($tomonth == $key) {
                $text .= \html_writer::tag('option', $name,
                        ['value' => $key, 'selected' => 'selected']);
            } else {
                $text .= \html_writer::tag('option', $name,
                        ['value' => $key]);
            }
        }
        $text .= \html_writer::end_tag('select');
        $text .= \html_writer::start_tag('select',
                ['id' => 'examdatestable-toyear-select', 'name' => 'toyear',
                        'class' => 'custom-select custom-select-sm form-control mr-3']);
        $defaultyear = date('Y', time());
        $toyear = !empty($toyear) ? $toyear : $defaultyear;
        foreach ($optionsyears as $key => $name) {
            if ($toyear == $key) {
                $text .= \html_writer::tag('option', $name,
                        ['value' => $key, 'selected' => 'selected']);
            } else {
                $text .= \html_writer::tag('option', $name,
                        ['value' => $key]);
            }
        }
        $text .= \html_writer::end_tag('select');
        $text .= \html_writer::tag('button', get_string('select'), array('submit' => 'submit', 'class' => 'btn btn-secondary'));

        $text .= \html_writer::end_tag('form');

        return $text . '&nbsp;';
    }

    /**
     * Get html checklist table
     *
     * @return array
     */
    public
    static function get_html_checklisttable($examdateid, $examdatename) {
        global $DB;
        $itemskvb = explode(',', get_config('elediachecklist', 'erinnerung_kvb_name'));
        $itemsknb = explode(',', get_config('elediachecklist', 'erinnerung_knb_name'));
        $items = implode(',', array_merge($itemskvb, $itemsknb));
        //  DATE_FORMAT(DATE_ADD(from_unixtime(floor((SELECT examtimestart from {eledia_adminexamdates} exam
        //        WHERE exam.id = {$examdateid}))), INTERVAL item.duetime DAY),'%d.%m.%Y') as topicdate
        $sql = "SELECT item.id, 
       item.emailtext as topic, 
        item.duetime,
        CASE WHEN ch.item IS NULL
            THEN 0
            ELSE 1 END AS checked
        FROM {elediachecklist_item} item
        LEFT JOIN {elediachecklist_check} ch ON item.id = ch.item AND ch.teacherid = {$examdateid}
        WHERE  item.id IN ($items)
        ORDER BY item.id";
        $examtimestart =
                floor($DB->get_record('eledia_adminexamdates', ['id' => $examdateid], 'examtimestart', MUST_EXIST)->examtimestart);

        $checklistitems = $DB->get_records_sql($sql);
        $text = '';
        if (!empty($checklistitems)) {
            $text .= \html_writer::start_tag('div', array('class' => 'card'));
            $text .= \html_writer::start_tag('div', array('class' => 'card-body'));
            $text .= \html_writer::tag('h5',
                    get_string('checklist_table_title', 'block_eledia_adminexamdates')
                    . ': ' . $examdatename, array('class' => 'card-title'));
            $text .= \html_writer::start_tag('div ', array('class' => 'card-text'));
            $text .= \html_writer::start_tag('table', ['class' => 'table table-striped']);
            $text .= \html_writer::start_tag('thead');
            $text .= \html_writer::tag('th', '');
            $text .= \html_writer::tag('th', get_string('checklist_table_topic', 'block_eledia_adminexamdates'));
            $text .= \html_writer::tag('th', get_string('checklist_table_topicdate', 'block_eledia_adminexamdates'));
            $text .= \html_writer::end_tag('thead');
            $text .= \html_writer::start_tag('tbody');
            $checksquareicon = \html_writer::tag('i', '',
                    array('class' => 'icon fa fa-check-square'));
            $unchecksquareicon = \html_writer::tag('i', '',
                    array('class' => 'icon fa fa-square-o'));
            foreach ($checklistitems as $checklistitem) {
                $text .= \html_writer::start_tag('tr');
                $text .= \html_writer::start_tag('td');
                $text .= ($checklistitem->checked) ? $checksquareicon : $unchecksquareicon;
                $text .= \html_writer::end_tag('td');

                //$tp = $examtimestart + (60 * 60 * 24 * $checklistitem->duetime);
                //$date = date('d.m.Y', $tp);
                $displaytext = str_replace('{Datum}', '', $checklistitem->topic);

                $text .= \html_writer::tag('td', $displaytext);

                $text .= \html_writer::tag('td',
                        date('d.m.Y', strtotime($checklistitem->duetime . ' day', $examtimestart)),
                        ['class' => 'text-right']);
                $text .= \html_writer::end_tag('tr');
            }
            $text .= \html_writer::end_tag('tbody');
            $text .= \html_writer::end_tag('table');
            $text .= \html_writer::end_tag('div');
            $text .= \html_writer::end_tag('div');
            $text .= \html_writer::end_tag('div');
        }
        return $text;
    }

    /**
     * Get html statistics table
     *
     * @return array
     */
    public
    static function get_html_statisticstable($formdata) {
        global $DB;
        $regularexam = $formdata->category_regularexam ? 0 : 1;
        $semestertest = $formdata->category_semestertest ? 1 : 0;
        $department = implode(',', $formdata->department);

        if ($formdata->period == 0) {
            $semester = $formdata->semester;
            $year = substr($semester, 0, 4);
            if (substr($semester, -1) == 1) {
                $datestart = strtotime("1 April $year");
                $dateend = strtotime("1 October $year") - 1;
            } else {
                $datestart = strtotime("1 October $year");
                $year++;
                $dateend = strtotime("1 April $year") - 1;
            }
        } else {
            $datestart = $formdata->datestart;
            $dateend = strtotime('tomorrow', $formdata->dateend) - 1;
        }
        $sql = "SELECT  
       COUNT(DISTINCT a.id) AS examnumber,  COUNT(DISTINCT ab.id) AS blocknumber,
        SUM(ar.roomnumberstudents)  AS numberstudents
                    FROM {eledia_adminexamdates} a
                    LEFT JOIN {eledia_adminexamdates_blocks} ab ON ab.examdateid = a.id
                    LEFT JOIN {eledia_adminexamdates_rooms} ar ON ar.blockid = ab.id
                    WHERE a.examtimestart > {$datestart} AND a.examtimestart < {$dateend} AND a.confirmed = 1
                        AND ( a.category = {$regularexam} OR a.category = {$semestertest}) AND a.department IN ({$department}) ";
        $records = $DB->get_records_sql($sql);

        $text = '';
        if (!empty($records)) {
            $records = array_values($records);
            $first = array_shift($records);
            $text .= \html_writer::start_tag('div', array('class' => 'card'));
            $text .= \html_writer::start_tag('div', array('class' => 'card-body'));
            $text .= \html_writer::tag('h5',
                    get_string('statistics_title', 'block_eledia_adminexamdates')
                    . ': ', array('class' => 'card-title'));
            $text .= \html_writer::start_tag('div ', array('class' => 'card-text'));

            $text .= \html_writer::start_tag('dl');

            $period = date('d.m.Y', $datestart)
                    . ' - ' . date('d.m.Y', $dateend);
            $text .= \html_writer::tag('dt', get_string('period', 'block_eledia_adminexamdates'));
            $text .= \html_writer::tag('dd', $period);

            $categories = [];
            if ($formdata->category_regularexam) {
                $categories[] = get_string('category_regularexam', 'block_eledia_adminexamdates');
            }
            if ($formdata->category_semestertest) {
                $categories[] = get_string('category_semestertest', 'block_eledia_adminexamdates');
            }

            $text .= \html_writer::tag('dt', get_string('selection_exam_category', 'block_eledia_adminexamdates'));
            $text .= \html_writer::tag('dd', implode(', ', $categories));

            $departmentchoices = unserialize(get_config('block_eledia_adminexamdates', 'departmentchoices'));
            $departments = array_intersect_key($departmentchoices, array_flip($formdata->department));
            sort($departments);
            $text .= \html_writer::tag('dt', get_string('department', 'block_eledia_adminexamdates'));
            $text .= \html_writer::tag('dd', implode(', ', $departments));
            $text .= \html_writer::tag('dt', get_string('numberstudents', 'block_eledia_adminexamdates'));
            $text .= \html_writer::tag('dd', empty($first->numberstudents) ? 0 : $first->numberstudents);
            $text .= \html_writer::tag('dt', get_string('examnumber', 'block_eledia_adminexamdates'));
            $text .= \html_writer::tag('dd', $first->examnumber);
            $text .= \html_writer::tag('dt', get_string('blocknumber', 'block_eledia_adminexamdates'));
            $text .= \html_writer::tag('dd', $first->blocknumber);
            $text .= \html_writer::end_tag('dl');
            $text .= \html_writer::end_tag('div');
            $text .= \html_writer::end_tag('div');
            $text .= \html_writer::end_tag('div');
        }
        return $text;
    }
}



