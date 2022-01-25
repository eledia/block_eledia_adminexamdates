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
 * @package    block_eledia_adminexamdates
 * @copyright  2021 René Hansen <support@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

global $USER, $CFG, $PAGE, $OUTPUT, $DB;

$context = context_system::instance();

require_login();

//$month = optional_param('month', 0, PARAM_INT);
//$year = optional_param('year', 0, PARAM_INT);
$displaydate = optional_param('displaydate', 0, PARAM_INT);
//$monthnow = date('m');
//$yearnow = date('Y');
if (is_array($displaydate)) {
    $displaydate = mktime(0, 0, 0, $displaydate['month'], $displaydate['day'], $displaydate['year']);
}
// <script src="calendar/node_modules/jquery/dist/jquery.min.js"></script>

//  <script src="calendar/src/js/jquery-calendar.js"></script>
//<script src="calendar/dist/js/jquery-calendar.min.js"></script>
echo '  <link rel="stylesheet" href="calendar/node_modules/bootstrap/dist/css/bootstrap.min.css">
<script src="calendar/node_modules/jquery/dist/jquery.min.js"></script>
  <script src="calendar/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
  <script src="calendar/node_modules/moment/min/moment-with-locales.min.js"></script>
  <script src="calendar/node_modules/jquery-touchswipe/jquery.touchSwipe.min.js"></script>
<script src="amd/build/jquery-calendar.min.js"></script>

  <link rel="stylesheet" href="calendar/src/css/jquery-calendar.css">
  <link rel="stylesheet" href="calendar/node_modules/@fortawesome/fontawesome-free-webfonts/css/fontawesome.css">
  <link rel="stylesheet" href="calendar/node_modules/@fortawesome/fontawesome-free-webfonts/css/fa-solid.css">';
$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('calendar_btn', 'block_eledia_adminexamdates'));

//<script src="calendar/node_modules/jquery/dist/jquery.min.js"></script>
$PAGE->set_pagelayout('course');


$sql = "SELECT ar.id, a.id AS examdateid, a.id AS examdateid, a.examname, ab.blocktimestart,ab.blockduration, ar.examroom, ar.blockid, a.numberstudents, a.examiner,a.responsibleperson, a.contactperson, a.confirmed, a.userid
                    FROM {eledia_adminexamdates} a
                    LEFT JOIN {eledia_adminexamdates_blocks} ab ON ab.examdateid = a.id
                    LEFT JOIN {eledia_adminexamdates_rooms} ar ON ar.blockid = ab.id
                    ORDER BY ab.blocktimestart, ar.examroom DESC";

$sqlspecial = "SELECT ar.id, ar.blockid,ab.blocktimestart, ab.blockduration, ar.examroom, ar.roomannotationtext 
                    FROM {eledia_adminexamdates_blocks} ab 
                    LEFT JOIN {eledia_adminexamdates_rooms} ar ON ar.blockid = ab.id
                    WHERE ab.examdateid IS NULL
                    ORDER BY ab.blocktimestart, ar.examroom DESC";
//WHERE ag.blocktimestart > ? AND ag.blocktimestart < ?";

$dates = $DB->get_records_sql($sql);

$hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());

if ($hasconfirmexamdatescap) {
    $specialroomdates = $DB->get_records_sql($sqlspecial);
}

$rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));
$roomcategories = [];
$roomcategorycolors = [];
$roomswithcapacity = [];
foreach ($rooms as $room) {
    $roomitems = explode('|', $room);
    //$roomcapacity = !empty($roomitems[2]) ? ' (max. ' . $roomitems[2] . ' TN)' : '';
    if (!empty($roomitems[2])) {
        array_push($roomswithcapacity, $roomitems[0]);
    }
    $roomnames[$roomitems[0]] = trim($roomitems[1]);
    $roomcolors[trim($roomitems[1])] = $roomitems[3];
    $object = new stdClass();
    $object->category = trim($roomitems[1]);
    $object->color = trim($roomitems[3]);
    $roomcategorycolors[] = $object;
    $roomcategories[] = trim($roomitems[1]);
};

$roomscount = $hasconfirmexamdatescap ? count($roomcategories) : count($roomswithcapacity);

//$roomcategorycolors=array_reverse($roomcategorycolors);
//$roomcategories=array_reverse($roomcategories);
echo " <script>
    $(document).ready(function(){
        moment.locale('de');
        var now = moment();
        
        /**
         * Many events
         */
         var events= [
        ";

$hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());
foreach ($dates as $date) {
    if (!$hasconfirmexamdatescap && !in_array($date->examroom, $roomswithcapacity)) {
        continue;
    }
    $endtime = $date->blocktimestart + ($date->blockduration * 60);
    $roomname = $roomnames[$date->examroom];
    $buttonhtml = \html_writer::start_tag('div', ['class' => 'd-inline']);
    if ($hasconfirmexamdatescap || !$date->confirmed) {
        $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['editexamdate' => $date->examdateid]);
        $url = $url->out();
        $buttonhtml .= \html_writer::tag('a', get_string('editexamdate', 'block_eledia_adminexamdates'),
                        ['class' => 'btn btn-secondary',
                                'href' => $url]) . ' ';
    }
    if ($hasconfirmexamdatescap) {

        $url = new \moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php', ['cancelexamdate' => $date->examdateid]);
        $url = $url->out();
        $buttonhtml .= \html_writer::tag('a', get_string('cancelexamdate', 'block_eledia_adminexamdates'),
                        ['class' => 'btn btn-secondary',
                                'href' => $url]) . ' ';

            $url = new \moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php',
                    ['confirmexamdate' => $date->examdateid]);
            $url = $url->out();
            $buttonhtml .= \html_writer::tag('a', get_string('confirmexamdate', 'block_eledia_adminexamdates'),
                    ['class' => 'btn btn-secondary',
                            'href' => $url]);
    }

    if (!$hasconfirmexamdatescap && $date->confirmed) {
        $url = new \moodle_url('/blocks/eledia_adminexamdates/changerequest.php', ['examdateid' => $date->examdateid]);
        $url = $url->out();
        $buttonhtml .= \html_writer::tag('a', get_string('change_request_btn', 'block_eledia_adminexamdates'),
                ['class' => 'btn btn-secondary',
                        'href' => $url]);
    }

    $buttonhtml .= \html_writer::end_tag('div');

    $myexamdate = ($date->userid == $USER->id || $date->contactperson == $USER->id) ? true : false;
    $title = ($hasconfirmexamdatescap || $myexamdate) ? $date->examname : get_string('room_occupied', 'block_eledia_adminexamdates',['room'=>$roomnames[$date->examroom]]);
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
    $content = ($hasconfirmexamdatescap || $myexamdate) ?
            "<dl><dt>".get_string('number_students', 'block_eledia_adminexamdates').
            "</dt><dd>$date->numberstudents</dd><dt>".get_string('examiner', 'block_eledia_adminexamdates').
            "</dt><dd>$examinernames</dd><dt>".get_string('contactperson', 'block_eledia_adminexamdates').
            "</dt><dd>$contactperson</dd></dl><div>$buttonhtml</div>" :
            get_string('room_already_occupied', 'block_eledia_adminexamdates',['room'=>$roomnames[$date->examroom]]);

    echo "     
      
        {
            start: $date->blocktimestart,
          end: $endtime,
          title: '$title',
          content: '$content' ,
          category:'$roomname'
        },
        
         ";

}
if ($hasconfirmexamdatescap) {
    foreach ($specialroomdates as $specialroomdate) {
        $endtime = $specialroomdate->blocktimestart + ($specialroomdate->blockduration * 60);
        $roomname = $roomnames[$specialroomdate->examroom];
        $buttonhtml = \html_writer::start_tag('div', ['class' => 'd-inline']);
        $url = new \moodle_url('/blocks/eledia_adminexamdates/specialrooms.php',
                ['blockid' => $specialroomdate->blockid]);
        $url = $url->out();
        $buttonhtml .= \html_writer::tag('a', get_string('specialrooms_btn', 'block_eledia_adminexamdates'),
                        ['class' => 'btn btn-secondary',
                                'href' => $url]) . ' ';
        $url = new \moodle_url('/blocks/eledia_adminexamdates/specialrooms.php',
                ['cancelspecialrooms' => $specialroomdate->blockid]);
        $url = $url->out();
        $buttonhtml .= \html_writer::tag('a', get_string('cancelspecialrooms', 'block_eledia_adminexamdates'),
                        ['class' => 'btn btn-secondary',
                                'href' => $url]) . ' ';

        $buttonhtml .= \html_writer::end_tag('div');

        $title = $roomnames[$specialroomdate->examroom];
        $roomannotationtext = trim(preg_replace('/\s\s+/', ' ', text_to_html($specialroomdate->roomannotationtext)));

        $content = "<dl><dt>" . get_string('annotationtext', 'block_eledia_adminexamdates')
                . ": </dt><dd>" . $roomannotationtext .
                "</dd></dl><div>$buttonhtml</div>";

        echo "     
      
        {
           start: $specialroomdate->blocktimestart,
          end: $endtime,
          title: '$roomname',
          content: '$content' ,
          category:'$roomname'
        },
        
         ";

    }
}
$fromhour = get_config('block_eledia_adminexamdates', 'startexam');
$tohour = get_config('block_eledia_adminexamdates', 'endexam');
echo "       ];


       /**
       * Init the calendar
       */
      var calendar = $('#calendar').Calendar({
              colors: {
            random: false,
            events: ['#E91E63', '#3F51B5','#009688', '#6D4C41'],
            },
            rooms: $roomscount,
        locale: 'de',
        weekday: {
        dayline: {
                format:'dddd DD.MM'
                },
            timeline: {
                intervalMinutes: 30,
            fromHour: $fromhour,
            toHour: $tohour,
          }
        },
        events: events,
unixTimestamp: $displaydate
      }).init();


      /**
       * Listening for events
       */

      $('#calendar').on('Calendar.init', function(event, instance, before, current, after){
          console.log('event : Calendar.init');
          console.log(instance);
          console.log(before);
          console.log(current);
          console.log(after);
      });
      $('#calendar').on('Calendar.daynote-mouseenter', function(event, instance, elem){
          console.log('event : Calendar.daynote-mouseenter');
          console.log(instance);
          console.log(elem);
      });
      $('#calendar').on('Calendar.daynote-mouseleave', function(event, instance, elem){
          console.log('event : Calendar.daynote-mouseleave');
          console.log(instance);
          console.log(elem);
      });
      $('#calendar').on('Calendar.event-mouseenter', function(event, instance, elem){
          console.log('event : Calendar.event-mouseenter');
          console.log(instance);
          console.log(elem);
      });
      $('#calendar').on('Calendar.event-mouseleave', function(event, instance, elem){
          console.log('event : Calendar.event-mouseleave');
          console.log(instance);
          console.log(elem);
      });
      $('#calendar').on('Calendar.daynote-click', function(event, instance, elem, evt){
          console.log('event : Calendar.daynote-click');
          console.log(instance);
          console.log(elem);
          console.log(evt);
      });
          var test=0;
      $('#calendar').on('Calendar.event-click', function(event, instance, elem, evt){
      test=1; 
      
          console.log('event : Calendar.event-click');
          console.log(instance);
          console.log(elem);
          console.log(evt);
      });
  
      $('#calendar').on('click', '.calendar-events-day',function(event){     
           
      console.log(event);
     
      var examtimestart = parseInt(this.getAttribute('data-time')) + ($fromhour*60*60) + (Math.trunc((event.offsetY)/50)*60*30);
       
       var editexamdateform = $(\"#editexamdate\");
        editexamdateform.find(\"input[name=\'examtimestart\']\").val(examtimestart);
        if(!test){
        editexamdateform.find(\"form\").submit();
        }
      test=0;
      });
    });
  </script>";



$mform = new \block_eledia_adminexamdates\forms\calendar_form();

echo $OUTPUT->header();
echo $OUTPUT->container_start();

$url = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1]);
$newexamdatebutton = new single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'), 'post');
$urllist = new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php');
$unconfirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php');
$confirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesconfirmed.php');
$statistics = new moodle_url('/blocks/eledia_adminexamdates/statistics.php');

echo \html_writer::start_tag('div', array('class' => 'container-fluid px-4'));
echo \html_writer::start_tag('div', array('class' => 'row'));
echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
echo \html_writer::start_tag('div', array('class' => 'singlebutton'));
echo \html_writer::tag('button', get_string('calendar_btn', 'block_eledia_adminexamdates'),
        array('disabled' => true, 'class' => 'btn '));
echo \html_writer::end_tag('div');
if ($hasconfirmexamdatescap) {
    echo $OUTPUT->single_button($urllist, get_string('examdateslist_btn', 'block_eledia_adminexamdates'), 'post');
};
echo $OUTPUT->single_button($unconfirmed, get_string('unconfirmed_btn', 'block_eledia_adminexamdates'), 'post');
if (!$hasconfirmexamdatescap) {
    echo $OUTPUT->single_button($confirmed, get_string('confirmed_btn', 'block_eledia_adminexamdates'), 'post');
}
echo $OUTPUT->single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'), 'post');
if ($hasconfirmexamdatescap) {
    echo $OUTPUT->single_button($statistics, get_string('statistics', 'block_eledia_adminexamdates'), 'post');
    $urlReport = new moodle_url('/mod/elediachecklist/terminreport.php');
    echo $OUTPUT->single_button($urlReport, get_string('report_button', 'elediachecklist'), 'get');

}
echo \html_writer::end_tag('div');
echo \html_writer::end_tag('div');
echo \html_writer::start_tag('div', array('class' => 'row'));
echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
echo \html_writer::tag('p', '&nbsp;');
$mform->display();
echo \html_writer::tag('div', '', array('id' => 'calendar'));
echo \html_writer::end_tag('div');
echo \html_writer::end_tag('div');
echo \html_writer::end_tag('div');

$urleditexamdate = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1, 'examtimestart' => '']);
echo $OUTPUT->box($OUTPUT->single_button($urleditexamdate, '', 'post'), 'd-none', 'editexamdate');

$roomcategories = json_encode($roomcategories);
$roomcategorycolors = json_encode($roomcategorycolors);
echo \html_writer::tag('div', '',
        ['id' => 'calendar-roomcategories', 'class' => 'd-none', 'data-calendar-roomcategories' => $roomcategories,
                'data-calendar-roomcolors' => $roomcategorycolors]);

echo $OUTPUT->container_end();

echo $OUTPUT->footer();