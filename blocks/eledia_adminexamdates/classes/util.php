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
            $dataobject->confirmed = false;
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
            } else {
                $examdateid = $formdata->editexamdate;
                $dataobject->id = $formdata->editexamdate;
                $DB->update_record('eledia_adminexamdates', $dataobject);
            }

            if ($hasconfirmexamdatescap && (!isset($dataobject->confirmed) || !$dataobject->confirmed)) {
                self::examconfirm($examdateid);
            }
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

        $confirmed = $DB->get_record('eledia_adminexamdates', ['id' => $formdata->examdateid])->confirmed;
        if (!$confirmed) {
            self::examconfirm($formdata->examdateid);
        }
        return $blockid;
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
        $examiners = explode(',', $examdate->examiner);
        $examinernames = [];
        foreach ($examiners as $examiner) {
            if ($user = \core_user::get_user($examiner)) {
                $examinernames[] = fullname($user);
            }
        }
        $text .= \html_writer::tag('dd', implode(', ', $examinernames));
        $text .= \html_writer::tag('dt', get_string('contactperson', 'block_eledia_adminexamdates') . ': ');
        $contactperson = \core_user::get_user($examdate->contactperson);
        $text .= \html_writer::tag('dd', fullname($contactperson) . ' | ' . $contactperson->email);
        $text .= \html_writer::tag('dt', get_string('responsibleperson', 'block_eledia_adminexamdates') . ': ');
        $responsibleperson = $examdate->responsibleperson ? fullname(\core_user::get_user($examdate->responsibleperson)) : '';
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
            $text .= \html_writer::tag('dd', date('d.m.Y H.i', $examblock->blocktimestart)
                    . ' - ' . date('H.i', $examblock->blocktimestart + ($examblock->blockduration * 60)) . ' ' . $viewtrash,
                    $acitveblock);
            $index++;
        }
        $text .= \html_writer::end_tag('dl');
        $text .= \html_writer::end_tag('p');

        if ($hasconfirmexamdatescap || (!$hasconfirmexamdatescap && !$examdate->confirmed)) {
            $url = new \moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php', ['cancelexamdate' => $examdate->id]);
            $text .= $OUTPUT->single_button($url, get_string('cancelexamdate', 'block_eledia_adminexamdates'), 'post');
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
        $conditions = $hasconfirmexamdatescap ? "confirmed = $confirmed" :
                "examtimestart > $now AND confirmed = $confirmed AND ( userid = $USER->id OR contactperson = $USER->id ) ";
        $sql = "SELECT *
                  FROM {eledia_adminexamdates} 
                 WHERE $conditions
                  ORDER BY examtimestart DESC";

        $adminexamdates = $DB->get_records_sql($sql);

        foreach ($adminexamdates as $adminexamdate) {
            $adminexamblocks =
                    $DB->get_records('eledia_adminexamdates_blocks', ['examdateid' => $adminexamdate->id], 'blocktimestart');
            $text .= \html_writer::start_tag('div', array('class' => 'row mt-3'));
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
            $examiners = explode(',', $adminexamdate->examiner);
            $examinernames = [];
            foreach ($examiners as $examiner) {
                if ($user = \core_user::get_user($examiner)) {
                    $examinernames[] = fullname($user);
                }
            }
            $text .= \html_writer::tag('dd', implode(', ', $examinernames));
            $text .= \html_writer::tag('dt', get_string('contactperson', 'block_eledia_adminexamdates') . ': ');
            $contactperson = \core_user::get_user($adminexamdate->contactperson);
            $text .= \html_writer::tag('dd', fullname($contactperson) . ' | ' . $contactperson->email);
            $text .= \html_writer::tag('dt', get_string('responsibleperson', 'block_eledia_adminexamdates') . ': ');
            $responsibleperson =
                    $adminexamdate->responsibleperson ? fullname(\core_user::get_user($adminexamdate->responsibleperson)) : '';
            $text .= \html_writer::tag('dd', $responsibleperson);
            $index = 1;
            foreach ($adminexamblocks as $adminexamblock) {
                $text .= \html_writer::tag('dt', $index . '. Teiltermin');
                $text .= \html_writer::tag('dd', date('d.m.Y H.i', $adminexamblock->blocktimestart)
                        . ' - ' . date('H.i', $adminexamblock->blocktimestart + ($adminexamblock->blockduration * 60)));
                $index++;
            }
            $text .= \html_writer::end_tag('dl');
            $text .= \html_writer::end_tag('p');
            //  if ($hasconfirmexamdatescap || !$adminexamdate->confirmed) {
            $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['editexamdate' => $adminexamdate->id]);

            $text .= $OUTPUT->single_button($url, get_string('editexamdate', 'block_eledia_adminexamdates'), 'post');
            //   }
            if ($hasconfirmexamdatescap || ((!$hasconfirmexamdatescap && !$adminexamdate->confirmed))) {
                $url = new \moodle_url($PAGE->url, ['cancelexamdate' => $adminexamdate->id]);

                $text .= $OUTPUT->single_button($url, get_string('cancelexamdate', 'block_eledia_adminexamdates'), 'post');
            }
            if (!$hasconfirmexamdatescap && $adminexamdate->confirmed) {
                $url = new \moodle_url('/blocks/eledia_adminexamdates/changerequest.php', ['examdateid' => $adminexamdate->id]);
                $text .= $OUTPUT->single_button($url, get_string('change_request_btn', 'block_eledia_adminexamdates'), 'post');
            }
            if ($hasconfirmexamdatescap) {
                if (!empty($adminexamdate->responsibleperson)) {
                    $url = new \moodle_url($PAGE->url, ['confirmexamdate' => $adminexamdate->id]);
                    $text .= $OUTPUT->single_button($url, get_string('confirmexamdate', 'block_eledia_adminexamdates'), 'post');
                } else {
                    $text .= \html_writer::start_tag('div', array('class' => 'singlebutton'));
                    $text .= \html_writer::tag('button', get_string('confirmexamdate', 'block_eledia_adminexamdates'),
                            array('disabled' => true, 'class' => 'btn btn-secondary'));
                    $text .= \html_writer::end_tag('div');
                }

                $url = new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php',
                        ['examdateid' => $adminexamdate->id]);
                $text .= $OUTPUT->single_button($url, get_string('editsingleexamdate', 'block_eledia_adminexamdates'), 'post');
            }
            $text .= \html_writer::end_tag('div');
            $text .= \html_writer::end_tag('div');
            $text .= \html_writer::end_tag('div');
        }
        return $text;
    }

    /**
     * get exam date table items.
     *
     */
    public
    static function getexamdatetable($semester) {
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
                ['month', 'date', 'examname', 'examiner', 'examroom', 'supervisor1', 'supervisor2', 'candidates', 'status',
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
            $text .= \html_writer::tag('td', $hiddendate . date('d.m.Y H.i', $date->blocktimestart)
                    . ' - ' . date('H.i', $date->blocktimestart + ($date->blockduration * 60)));
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
            $text .= \html_writer::tag('td', ($date->confirmed) ?
                    get_string('status_confirmed', 'block_eledia_adminexamdates') :
                    get_string('status_unconfirmed', 'block_eledia_adminexamdates'),
                    ($date->confirmed) ? array('class' => 'text-center table-success') :
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
        if (!$examdate->confirmed) {
            // Send confirmation email to contactperson and the exam team.

            $emailuser = new stdClass();
            $emailuser->email = \core_user::get_user($examdate->contactperson)->email;
            $emailuser->id = -99;

            $emailexamteam = get_config('block_eledia_adminexamdates', 'emailexamteam');
            $emailexamteam = !empty($emailexamteam) ? $emailexamteam : false;

            $subject = get_string('examconfirm_email_subject', 'block_eledia_adminexamdates',
                    ['name' => $examdate->examname]);
            $date = date('d.m.Y H.i', $examdate->examtimestart)
                    . ' - ' . date('H.i', $examdate->examtimestart + ($examdate->examduration * 60));
            $course = \html_writer::tag('a', get_string('edit'),
                    array('href' => get_config('block_eledia_adminexamdates', 'apidomain')
                            . '/course/view.php?id=' . $courseid));
            $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php',
                    ['editexamdate' => $examdateid]);
            $url = $url->out();
            $link = \html_writer::tag('a', get_string('course'), array('href' => $url));
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
        if (!$examdate->confirmed) {
            $email = ($emailuser->email != $USER->email) ? $USER->email : false;
        } else {
            $email = $examdate->responsibleperson ? \core_user::get_user($examdate->responsibleperson)->email : false;
        }
        $emailexamteam = get_config('block_eledia_adminexamdates', 'emailexamteam');
        $emailexamteam = !empty($emailexamteam) ? $emailexamteam : false;

        $subject = get_string('examcancel_email_subject', 'block_eledia_adminexamdates', ['name' => $examdate->examname]);
        $date = date('d.m.Y H.i', $examdate->examtimestart)
                . ' - ' . date('H.i', $examdate->examtimestart + ($examdate->examduration * 60));
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
        $date = date('d.m.Y H.i', $examdate->examtimestart)
                . ' - ' . date('H.i', $examdate->examtimestart + ($examdate->examduration * 60));
        $messagetext = get_string('changerequest_email_body', 'block_eledia_adminexamdates',
                ['name' => $examdate->examname, 'date' => $date, 'url' => $url, 'changerequest' => $message]);
        $htmlmessagetext = get_string('changerequest_email_body', 'block_eledia_adminexamdates',
                ['name' => $examdate->examname, 'date' => $date, 'url' => $link, 'changerequest' => $message]);
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
                ['id' => 'examdatestable-semester-form']);

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
}



