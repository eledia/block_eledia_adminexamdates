<?php
global $CFG, $DB, $PAGE;
require_once('../../config.php');
//require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->libdir . '/bennu/bennu.inc.php');

$authtoken = required_param('authtoken', PARAM_ALPHANUM);
$month = optional_param('month', '12', PARAM_INT);
$special = optional_param('special', '1', PARAM_INT);

if ($authtoken != trim(get_config('block_eledia_adminexamdates', 'icalexporttoken'))) {
    die('Invalid authentication');
}

$PAGE->set_context(context_system::instance());

$limitnum = 0;

$ical = new iCalendar;
$ical->add_property('method', 'PUBLISH');
$ical->add_property('prodid', '-//Moodle Pty Ltd//NONSGML Moodle Version ' . $CFG->version . '//EN');

$roomswithcapacity = [];
$specialroomitems = [];

$examrooms = $DB->get_records('eledia_adminexamdates_cfg_r',null, 'specialroom,roomid');
foreach ($examrooms as $examroom){
    if($examroom->specialroom){
        array_push($specialroomitems, $examroom->roomid);
    } else {
        array_push($roomswithcapacity, $examroom->roomid);
    }
    $roomnames[$examroom->roomid] = $examroom->name;
}

$timestart = strtotime("- $month month", time());
$timeend = strtotime("+ $month month", time());

$sql = "SELECT a.*, ab.blockduration,ar.roomnumberstudents,ar.id as roomid,
       ab.blocktimestart, ar.examroom, ar.blockid, ar.roomnumberstudents, ar.roomsupervisor1,
        ar.roomsupervisor2, ar.roomannotationtext, ab.timemodified as blocktimemodified, ar.timemodified as roomtimemodified
                FROM {eledia_adminexamdates} a
                JOIN {eledia_adminexamdates_blocks} ab ON ab.examdateid = a.id
                JOIN {eledia_adminexamdates_rooms} ar ON ar.blockid = ab.id
                WHERE ab.examdateid IS NOT NULL AND ar.blockid IS NOT NULL AND a.confirmed != 0 AND ab.blocktimestart > {$timestart} AND ab.blocktimestart < {$timeend}  
                ORDER BY ab.blocktimestart, ar.examroom DESC";

$dates = $DB->get_recordset_sql($sql);

$events = [];
$admins = get_admins();
$admin = reset($admins);

foreach ($dates as $date) {

    $title = str_replace("'", '`', $date->examname);
    $examiners = explode(',', $date->examiner);
    $examinernames = [];
    foreach ($examiners as $examiner) {
        if ($user = \core_user::get_user($examiner)) {
            $examinernames[] = fullname($user);
        }
    }
    $examinernames = implode(', ', $examinernames);
    $contactperson = \core_user::get_user($date->contactperson);
    $contactperson = fullname($contactperson) . ' | ' . $contactperson->email;
    $content = "<dl><dt>" . get_string('number_students', 'block_eledia_adminexamdates') .
            "</dt><dd>$date->numberstudents</dd><dt>" . get_string('examiner', 'block_eledia_adminexamdates') .
            "</dt><dd>$examinernames</dd><dt>" . get_string('contactperson', 'block_eledia_adminexamdates') .
            "</dt><dd>$contactperson</dd>";

    $content .= \html_writer::tag('dt', get_string('responsibleperson', 'block_eledia_adminexamdates'));
    $responsibleperson = $date->responsibleperson ? fullname(\core_user::get_user($date->responsibleperson)) : '-';
    $content .= \html_writer::tag('dd', $responsibleperson);

    if (!empty($date->annotationtext)) {
        $content .= \html_writer::tag('dt', get_string('annotationtext', 'block_eledia_adminexamdates'));
        $content .= \html_writer::tag('dd', $date->annotationtext);
    }

    $string['tablehead_supervisor1'] = 'Betreuer:in 1';
    $string['tablehead_supervisor2'] = 'Betreuer:in 2';

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

    if (!empty($roomsupervisors1)) {
        $content .= \html_writer::tag('dt', get_string('tablehead_supervisor1', 'block_eledia_adminexamdates'));
        $content .= \html_writer::tag('dd', $roomsupervisors1);
    }

    if (!empty($roomsupervisors2)) {
        $content .= \html_writer::tag('dt', get_string('tablehead_supervisor2', 'block_eledia_adminexamdates'));
        $content .= \html_writer::tag('dd', $roomsupervisors2);
    }

    if (!empty($date->roomannotationtext)) {
        $content .= \html_writer::tag('dt', get_string('annotationtext', 'block_eledia_adminexamdates'));
        $content .= \html_writer::tag('dd', $date->roomannotationtext);
    }

    $examurl = new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php',
            ['blockid' => $date->blockid]);

    $content .= \html_writer::tag('a', get_string('exam_booking_link', 'block_eledia_adminexamdates'),
            array('href' => $examurl));

    if (!empty($date->courseid)) {
        $content .= \html_writer::tag('a', get_string('exam_course_link', 'block_eledia_adminexamdates'),
                array('href' => get_config('block_eledia_adminexamdates', 'apidomain')
                        . '/course/view.php?id=' . $date->courseid));
    }

    $content .= "</dl>";
    $roomname = $roomnames[$date->examroom];
    $timemodified = max($date->timecreated, $date->timemodified, $date->blocktimemodified, $date->roomtimemodified);

    $events[] = (object) [
            'id' => $date->id . '_' . $date->blockid . '_' . $date->roomid,
            'name' => $title,
            'description' => $content,
            'format' => 1,
            'location' => $roomname,
            'courseid' => 0,
            'userid' => $admin->id,
            'eventtype' => 'user',
            'timestart' => $date->blocktimestart,
            'timeduration' => $date->blockduration * 60,
            'timesort' => $date->blocktimestart,
            'timeusermidnight' => 0,
            'visible' => 1,
            'timemodified' => $timemodified,
            'sequence' => 1,
    ];
}

if ($special && !empty($specialroomitems)) {
    list($in_sql, $in_params) = $DB->get_in_or_equal($specialroomitems);

    $sqlspecial = "SELECT ar.id, ar.blockid,ab.blocktimestart, ab.blockduration, ar.examroom, ar.roomannotationtext,ar.id as roomid,
       ab.timemodified as blocktimemodified, ar.timemodified as roomtimemodified
                    FROM {eledia_adminexamdates_blocks} ab 
                    LEFT JOIN {eledia_adminexamdates_rooms} ar ON ar.blockid = ab.id
                    WHERE ab.blocktimestart > {$timestart} AND ab.blocktimestart < {$timeend} AND  ar.examroom $in_sql
                    AND ab.examdateid IS NULL
                    AND ar.blockid IS NOT NULL
                    ORDER BY ab.blocktimestart, ar.examroom DESC";
    $specialroomdates = $DB->get_records_sql($sqlspecial, $in_params);
    foreach ($specialroomdates as $specialroomdate) {

        if (!empty($specialroomdate->blocktimestart) &&
                !empty($specialroomdate->blockduration) && !empty($specialroomdate->blockid)) {
            $roomname = $roomnames[$specialroomdate->examroom];
            $title = $roomnames[$specialroomdate->examroom];

            $roomannotationtext = trim(preg_replace('/\s\s+/', ' ', text_to_html($specialroomdate->roomannotationtext)));

            $content = "<dl><dt>" . get_string('annotationtext', 'block_eledia_adminexamdates')
                    . ": </dt><dd>" . $roomannotationtext .
                    "</dd>";
            $examurl = new \moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php',
                    ['blockid' => $specialroomdate->blockid]);

            $content .= \html_writer::tag('a', get_string('exam_booking_link', 'block_eledia_adminexamdates'),
                    array('href' => $examurl));
            $content .= "</dl>";

            $timemodified = max($specialroomdate->blocktimemodified, $specialroomdate->roomtimemodified);
            $events[] = (object) [
                    'id' => $specialroomdate->blockid . '_' . $specialroomdate->roomid,
                    'name' => $title,
                    'description' => $content,
                    'format' => 1,
                    'location' => $roomname,
                    'courseid' => 0,
                    'userid' => 2,
                    'eventtype' => 'user',
                    'timestart' => $specialroomdate->blocktimestart,
                    'timeduration' => $specialroomdate->blockduration * 60,
                    'timesort' => $specialroomdate->blocktimestart,
                    'timeusermidnight' => 0,
                    'visible' => 1,
                    'timemodified' => $timemodified,
                    'sequence' => 1,
            ];
        }
    }
}

foreach ($events as $event) {
    $hostaddress = str_replace('http://', '', $CFG->wwwroot);
    $hostaddress = str_replace('https://', '', $hostaddress);

    $me = new calendar_event($event); // To use moodle calendar event services.
    $ev = new iCalendar_event; // To export in ical format.
    $ev->add_property('uid', $event->id . '@' . $hostaddress);

    // Set iCal event summary from event name.
    $ev->add_property('summary', format_string($event->name, true, ['context' => $me->context]));

    // Format the description text.
    $description = format_text($me->description, $me->format, ['context' => $me->context]);
    // Then convert it to plain text, since it's the only format allowed for the event description property.
    // We use html_to_text in order to convert <br> and <p> tags to new line characters for descriptions in HTML format.
    $description = html_to_text($description, 0);
    $ev->add_property('description', $description);

    $ev->add_property('class', 'PUBLIC'); // PUBLIC / PRIVATE / CONFIDENTIAL
    $ev->add_property('last-modified', Bennu::timestamp_to_datetime($event->timemodified));

    if (!empty($event->location)) {
        $ev->add_property('location', $event->location);
    }

    $ev->add_property('dtstamp', Bennu::timestamp_to_datetime()); // now
    if ($event->timeduration > 0) {
        //dtend is better than duration, because it works in Microsoft Outlook and works better in Korganizer
        $ev->add_property('dtstart', Bennu::timestamp_to_datetime($event->timestart)); // when event starts.
        $ev->add_property('dtend', Bennu::timestamp_to_datetime($event->timestart + $event->timeduration));
    } else if ($event->timeduration == 0) {
        // When no duration is present, the event is instantaneous event, ex - Due date of a module.
        // Moodle doesn't support all day events yet. See MDL-56227.
        $ev->add_property('dtstart', Bennu::timestamp_to_datetime($event->timestart));
        $ev->add_property('dtend', Bennu::timestamp_to_datetime($event->timestart));
    } else {
        // This can be used to represent all day events in future.
        throw new coding_exception("Negative duration is not supported yet.");
    }
    //if ($event->courseid != 0) {
    //    $coursecontext = context_course::instance($event->courseid);
    //    $ev->add_property('categories', format_string($courses[$event->courseid]->shortname, true, array('context' => $coursecontext)));
    //}
    $ical->add_component($ev);
}

$serialized = $ical->serialize();
if (empty($serialized)) {
    // TODO
    die('bad serialization');
}

$filename = 'icalexport.ics';

header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . 'GMT');
header('Pragma: no-cache');
header('Accept-Ranges: none'); // Comment out if PDFs do not work...
header('Content-disposition: attachment; filename=' . $filename);
header('Content-length: ' . strlen($serialized));
header('Content-type: text/calendar; charset=utf-8');

echo $serialized;
