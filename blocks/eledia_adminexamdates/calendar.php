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

require_login();

// Get course
$courseid = $DB->get_field('course_modules', 'course',
        array('id' => get_config('block_eledia_adminexamdates', 'instanceofmodelediachecklist')));
$course = $DB->get_record('course', array('id' => $courseid));
if (!$course) {
    print_error('invalidcourseid');
}
require_login($course);

$context = context_course::instance($course->id);

$displaydate = optional_param('displaydate', time(), PARAM_INT);

//$displaydate = (is_array($displaydate)) ? mktime(0, 0, 0, $displaydate['month'], $displaydate['day'], $displaydate['year']) : 0;

// <script src="calendar/node_modules/jquery/dist/jquery.min.js"></script>

//  <script src="calendar/src/js/jquery-calendar.js"></script>
//<script src="calendar/dist/js/jquery-calendar.min.js"></script>
//  <script src="amd/build/jquery-calendar.min.js"></script>

echo '  <link rel="stylesheet" href="calendar/node_modules/bootstrap/dist/css/bootstrap.min.css">
<script src="calendar/node_modules/jquery/dist/jquery.min.js"></script>
  <script src="calendar/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
  <script src="calendar/node_modules/moment/min/moment-with-locales.min.js"></script>
  <script src="calendar/node_modules/jquery-touchswipe/jquery.touchSwipe.min.js"></script>
<script src="amd/build/jquery-calendar-2804202301.min.js"></script>

  <link rel="stylesheet" href="calendar/src/css/jquery-calendar.css">
  <link rel="stylesheet" href="calendar/node_modules/@fortawesome/fontawesome-free-webfonts/css/fontawesome.css">
  <link rel="stylesheet" href="calendar/node_modules/@fortawesome/fontawesome-free-webfonts/css/fa-solid.css">';
$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('calendar_btn', 'block_eledia_adminexamdates'));

//<script src="calendar/node_modules/jquery/dist/jquery.min.js"></script>
$PAGE->set_pagelayout('course');

$hasconfirmexamdatescap = (has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance())) ? 1 : 0;

$roomcategories = [];
$roomcategorycolors = [];
$roomswithcapacity = [];
$specialroomitems = [];

$examrooms = $DB->get_records('eledia_adminexamdates_cfg_r',null,'specialroom,roomid');

foreach ($examrooms as $examroom) {
    if (!$examroom->specialroom) {
        array_push($roomswithcapacity, $examroom->roomid);
    } else {
        array_push($specialroomitems, $examroom->roomid);
    }
    $roomnames[$examroom->roomid] = $examroom->name;
    $roomcolors[$examroom->name] = $examroom->color;
    $object = new stdClass();
    $object->category = $examroom->name;
    $object->color = $examroom->color;
    $roomcategorycolors[] = $object;
    $roomcategories[] = $examroom->name;
};

$roomscount = $hasconfirmexamdatescap ? count($roomcategories) : count($roomswithcapacity);

$sql = "SELECT ar.id, a.id AS examdateid, a.id AS examdateid, a.examname, ab.blocktimestart,ab.blockduration, ar.examroom, ar.blockid, a.numberstudents, a.examiner,a.responsibleperson, a.contactperson, a.confirmed, a.userid
                    FROM {eledia_adminexamdates} a
                    LEFT JOIN {eledia_adminexamdates_blocks} ab ON ab.examdateid = a.id
                    LEFT JOIN {eledia_adminexamdates_rooms} ar ON ar.blockid = ab.id
                    WHERE ab.examdateid IS NOT NULL
                    AND ar.blockid IS NOT NULL 
                    ORDER BY ab.blocktimestart, ar.examroom DESC";
$dates = $DB->get_records_sql($sql);

$specialroomdates = [];
if ($hasconfirmexamdatescap && !empty($specialroomitems)) {
    list($in_sql, $in_params) = $DB->get_in_or_equal($specialroomitems);

    $sqlspecial = "SELECT ar.id, ar.blockid,ab.blocktimestart, ab.blockduration, ar.examroom, ar.roomannotationtext 
                    FROM {eledia_adminexamdates_blocks} ab 
                    LEFT JOIN {eledia_adminexamdates_rooms} ar ON ar.blockid = ab.id
                    WHERE ar.examroom $in_sql
                    AND ab.examdateid IS NULL
                    AND ar.blockid IS NOT NULL
                    ORDER BY ab.blocktimestart, ar.examroom DESC";
    $specialroomdates = $DB->get_records_sql($sqlspecial, $in_params);
}
//WHERE ag.blocktimestart > ? AND ag.blocktimestart < ?";


$holidaylines = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'holidays'));
$holidays = [];
foreach ($holidaylines as $holidayline) {
    $holidayitems = explode('|', $holidayline);
    if (($holidaytime = strtotime($holidayitems[0])) && !empty($holidayitems[1])) {
        $holidays[$holidaytime] = trim($holidayitems[1]);
    }
}
$holidaysjson = json_encode($holidays);
//$roomcategorycolors=array_reverse($roomcategorycolors);
//$roomcategories=array_reverse($roomcategories);
echo " <script>
    $(document).ready(function(){
        moment.locale('de');
        var now = moment();
        
        var holidays = $holidaysjson;
        /**
         * Many events
         */
         var events= [
        ";

foreach ($dates as $date) {

    if (!empty($date->blocktimestart) &&
            !empty($date->blockduration) && !empty($date->examdateid) && array_key_exists($date->examroom, $roomnames)) {

        $endtime = $date->blocktimestart + ($date->blockduration * 60);
        $roomname = $roomnames[$date->examroom];
        $buttonhtml = \html_writer::start_tag('div', ['class' => 'd-inline']);

        if ($hasconfirmexamdatescap || !in_array($date->examroom, $specialroomitems)) {

            $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['editexamdate' => $date->examdateid]);
            $url = $url->out();
            $buttonhtml .= \html_writer::tag('a', get_string('editexamdate', 'block_eledia_adminexamdates'),
                            ['class' => 'btn btn-secondary',
                                    'href' => $url]) . ' ';

            if ($hasconfirmexamdatescap || !$date->confirmed) {

                $url = new \moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php',
                        ['cancelexamdate' => $date->examdateid]);
                $url = $url->out();
                $buttonhtml .= \html_writer::tag('a', get_string('cancelexamdate', 'block_eledia_adminexamdates'),
                                ['class' => 'btn btn-secondary',
                                        'href' => $url]) . ' ';
            }
            if ($hasconfirmexamdatescap && !$date->confirmed) {
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
            $title = ($hasconfirmexamdatescap || $myexamdate) ? str_replace("'", '`', $date->examname) :
                    get_string('room_occupied', 'block_eledia_adminexamdates', ['room' => $roomnames[$date->examroom]]);
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
                    "<dl><dt>" . get_string('number_students', 'block_eledia_adminexamdates') .
                    "</dt><dd>$date->numberstudents</dd><dt>" . get_string('examiner', 'block_eledia_adminexamdates') .
                    "</dt><dd>$examinernames</dd><dt>" . get_string('contactperson', 'block_eledia_adminexamdates') .
                    "</dt><dd>$contactperson</dd></dl><div>$buttonhtml</div>" :
                    get_string('room_already_occupied', 'block_eledia_adminexamdates', ['room' => $roomnames[$date->examroom]]);
            $notmyevent = (!$hasconfirmexamdatescap && !$myexamdate) ? true : false;
            $notconfirmed = ($hasconfirmexamdatescap && !$date->confirmed) ? true : false;
            echo "     
      
        {
          start: '$date->blocktimestart',
          end: '$endtime',
          title: '$title',
          content: '$content' ,
          category:'$roomname',
          notconfirmed:'$notconfirmed',
          notmyevent:'$notmyevent'
        },
        
         ";
        }
    }
}
if ($hasconfirmexamdatescap) {
    foreach ($specialroomdates as $specialroomdate) {
        if (!empty($specialroomdate->blocktimestart) &&
                !empty($specialroomdate->blockduration) && !empty($specialroomdate->blockid) &&
                array_key_exists($specialroomdate->examroom, $roomnames)) {
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
            //$notconfirmed = !$date->confirmed;
            echo "     
                {
                  start: '$specialroomdate->blocktimestart',
                  end: '$endtime',
                  title: '$roomname',
                  content: '$content',
                  category:'$roomname',
                  notconfirmed:0,
                  notmyevent:'0'
                },
                ";
        }
    }
}
$fromhour = get_config('block_eledia_adminexamdates', 'startcalendar');
$tohour = get_config('block_eledia_adminexamdates', 'endcalendar');
$bordercolor1 = get_config('block_eledia_adminexamdates', 'bordercolor1');
$bordercolor2 = get_config('block_eledia_adminexamdates', 'bordercolor2');
$date = strtotime('today');
$mform = new \block_eledia_adminexamdates\forms\calendar_form();
if ($formdata = $mform->get_data()) {
    $displaydate = (!empty($formdata->selectdisplaydate)) ? $formdata->selectdisplaydate : $displaydate;
}

$roomlist = "'" . implode("', '", $roomnames) . "'";
echo "       ];


       /**
       * Init the calendar
       */
      var calendar = $('#calendar').Calendar({
              colors: {
            random: false,
            events: ['#E91E63', '#3F51B5','#009688', '#6D4C41'],
            border1: '$bordercolor1',
            border2: '$bordercolor2',
            },
            rooms: $roomscount,
            roomlist: [$roomlist],
        locale: 'de',
        weekday: {
        dayline: {
                format:'dddd DD.MM.'
                },
            timeline: {
                intervalMinutes: 60,
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
      var weekend = (this.getAttribute('data-weekend') === 'true');
      var day = $(this).find('.weektoday').text();
       var datatime = parseInt(this.getAttribute('data-time'));
       if((datatime < $date) && !($hasconfirmexamdatescap)){
       var modal = $('#calendar-modal-past');
       modal.find('.modal-title-past').html(' - '+day);
        modal.modal('show');
      } else if((datatime in holidays) && !($hasconfirmexamdatescap)){
       var modal = $('#calendar-modal-holidays');
       modal.find('.modal-title-holiday').html(' - '+day+', '+holidays[datatime]);
        modal.modal('show');
      } else if (weekend && !($hasconfirmexamdatescap)){
       var modal = $('#calendar-modal-weekend');
        modal.find('.modal-title-weekend').html(' - '+day);
        modal.modal('show');
      } else {
       var examtimestart = datatime + ($fromhour*60*60) + (Math.trunc((event.offsetY)/50)*60*60);
       
       var editexamdateform = $(\"#editexamdate\");
        editexamdateform.find(\"input[name=\'examtime\']\").val(examtimestart);
        if(!test){
        editexamdateform.find(\"form\").submit();
        }
      test=0;
      }
      
      
      });
      
 $('#user-menu-toggle').on('click', function(e) {
    e.preventDefault();
     e.stopPropagation();
      $('#user-action-menu').toggleClass('show'); 
    
    });
    
   
    });
 
  </script>";

echo $OUTPUT->header();
echo $OUTPUT->container_start();

$url = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1]);
$newexamdatebutton = new single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'));
$urllist = new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php');
$unconfirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php');
$confirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesconfirmed.php');
$statistics = new moodle_url('/blocks/eledia_adminexamdates/statistics.php');

echo \html_writer::start_tag('div', array('class' => 'container-fluid px-4'));
echo \html_writer::start_tag('div', array('class' => 'row'));
echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
echo \html_writer::start_tag('div', array('class' => 'singlebutton mb-3'));
echo \html_writer::tag('button', get_string('calendar_btn', 'block_eledia_adminexamdates'),
        array('disabled' => true, 'class' => 'btn '));
echo \html_writer::end_tag('div');
if ($hasconfirmexamdatescap) {
    echo $OUTPUT->single_button($urllist, get_string('examdateslist_btn', 'block_eledia_adminexamdates'));
};
echo $OUTPUT->single_button($unconfirmed, get_string('unconfirmed_btn', 'block_eledia_adminexamdates'));
if (!$hasconfirmexamdatescap) {
    echo $OUTPUT->single_button($confirmed, get_string('confirmed_btn', 'block_eledia_adminexamdates'));
}
if ($hasconfirmexamdatescap) {
    echo $OUTPUT->single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'));
    echo $OUTPUT->single_button($statistics, get_string('statistics', 'block_eledia_adminexamdates'));
    $urlReport = new moodle_url('/mod/elediachecklist/terminreport.php');
    echo $OUTPUT->single_button($urlReport, get_string('report_button', 'elediachecklist'));

}
echo \html_writer::end_tag('div');
echo \html_writer::end_tag('div');

echo \html_writer::start_tag('div', array('class' => 'row'));
echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
echo \html_writer::start_tag('div', array('class' => 'card-deck mt-3'));
echo \html_writer::start_tag('div', array('class' => 'card'));
echo \html_writer::start_tag('div', array('class' => 'card-body'));
echo \html_writer::start_tag('p', array('class' => 'card-text'));

$data = new stdClass();
$data->selectdisplaydate = (!empty($displaydate)) ? $displaydate : time();
$mform->set_data($data);
$mform->display();

//echo \html_writer::end_tag('p');
//echo \html_writer::end_tag('div');
//echo \html_writer::end_tag('div');
//echo \html_writer::end_tag('div');
//
////echo \html_writer::tag('p','&nbsp;');
//
//echo \html_writer::start_tag('div', array('class' => 'card-deck mt-3'));
//echo \html_writer::start_tag('div', array('class' => 'card'));
//echo \html_writer::start_tag('div', array('class' => 'card-body'));
//echo \html_writer::start_tag('p', array('class' => 'card-text'));

echo \html_writer::tag('div', '', array('id' => 'calendar', 'class' => 'mt-4'));

echo \html_writer::end_tag('p');
echo \html_writer::end_tag('div');
echo \html_writer::end_tag('div');
echo \html_writer::end_tag('div');

echo \html_writer::end_tag('div');
echo \html_writer::end_tag('div');

$urleditexamdate = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php',
        ['newexamdate' => 1, 'examtime' => '']);
echo $OUTPUT->box($OUTPUT->single_button($urleditexamdate, ''), 'd-none', 'editexamdate');

$roomcategories = json_encode($roomcategories);
$roomcategorycolors = json_encode($roomcategorycolors);
echo \html_writer::tag('div', '',
        ['id' => 'calendar-roomcategories', 'class' => 'd-none', 'data-calendar-roomcategories' => $roomcategories,
                'data-calendar-roomcolors' => $roomcategorycolors]);

echo '<div class="modal fade" id="calendar-modal-weekend" tabindex="-1" role="dialog">';
echo '<div class="modal-dialog" role="document">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<h3 class="modal-title">' . get_string('modal_title_weekend_not_available', 'block_eledia_adminexamdates') .
        '<span class="modal-title-weekend"></span></h3>';
echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button>';
echo '</div>';
echo '<div class="modal-body">' . get_string('modal_body_weekend_not_available', 'block_eledia_adminexamdates');
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="modal fade" id="calendar-modal-holidays" tabindex="-1" role="dialog">';
echo '<div class="modal-dialog" role="document">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<h3 class="modal-title">' . get_string('modal_title_holiday_not_available', 'block_eledia_adminexamdates') .
        '<span class="modal-title-holiday"></span></h3>';
echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button>';
echo '</div>';
echo '<div class="modal-body">' . get_string('modal_body_holiday_not_available', 'block_eledia_adminexamdates');
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="modal fade" id="calendar-modal-past" tabindex="-1" role="dialog">';
echo '<div class="modal-dialog" role="document">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<h3 class="modal-title">' . get_string('modal_title_past_not_available', 'block_eledia_adminexamdates') .
        '<span class="modal-title-past"></span></h3>';
echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"></span></button>';
echo '</div>';
echo '<div class="modal-body">' . get_string('modal_body_past_not_available', 'block_eledia_adminexamdates');
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo $OUTPUT->container_end();

echo $OUTPUT->footer();