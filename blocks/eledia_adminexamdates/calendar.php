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


//<script src="calendar/dist/js/jquery-calendar.min.js"></script>
echo '  <link rel="stylesheet" href="calendar/node_modules/bootstrap/dist/css/bootstrap.min.css">
  <script src="calendar/node_modules/jquery/dist/jquery.min.js"></script>
  <script src="calendar/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
  <script src="calendar/node_modules/moment/min/moment-with-locales.min.js"></script>
  <script src="calendar/node_modules/jquery-touchswipe/jquery.touchSwipe.min.js"></script>
  <script src="calendar/dist/js/jquery-calendar.min.js"></script>
  <link rel="stylesheet" href="calendar/dist/css/jquery-calendar.min.css">
  <link rel="stylesheet" href="calendar/node_modules/@fortawesome/fontawesome-free-webfonts/css/fontawesome.css">
  <link rel="stylesheet" href="calendar/node_modules/@fortawesome/fontawesome-free-webfonts/css/fa-solid.css">';

$sql = "SELECT ad.id, a.id AS examdateid, a.examname, a.examduration, ag.blocktimestart, ad.examroom, ad.blockid, a.numberstudents, a.examiner, a.contactperson, a.confirmed, a.userid
                    FROM {eledia_adminexamdates} a
                    LEFT JOIN {eledia_adminexamdates_blocks} ag ON ag.examdateid = a.id
                    LEFT JOIN {eledia_adminexamdates_rooms} ad ON ad.blockid = ag.id";
//WHERE ag.blocktimestart > ? AND ag.blocktimestart < ?";

$dates = $DB->get_records_sql($sql);
$hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());

$rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));
$roomcolors = [];
foreach ($rooms as $room) {
    $roomitems = explode('|', $room);
    //$roomcapacity = !empty($roomitems[2]) ? ' (max. ' . $roomitems[2] . ' TN)' : '';
    $roomnames[$roomitems[0]] = trim($roomitems[1]);
    $roomcolors[trim($roomitems[1])] = $roomitems[3];
};

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
    $endtime = $date->blocktimestart + ($date->examduration * 60);
    $roomname = $roomnames[$date->examroom];
    $buttonhtml = \html_writer::start_tag('div', ['class' => 'd-inline']);
    if ($hasconfirmexamdatescap || (isset($date->confirmed) && !$date->confirmed)) {
        $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['editexamdate' => $date->examdateid]);
        $url = $url->out();
        $buttonhtml .= \html_writer::tag('a', get_string('editexamdate', 'block_eledia_adminexamdates'), ['class' => 'btn btn-secondary',
                'href' => $url]) . ' ';
    }
    if ($hasconfirmexamdatescap) {

        $url = new \moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php', ['cancelexamdate' => $date->examdateid]);
        $url = $url->out();
        $buttonhtml .= \html_writer::tag('a', get_string('cancelexamdate', 'block_eledia_adminexamdates'), ['class' => 'btn btn-secondary',
                'href' =>  $url]) . ' ';

        if (isset($date->confirmed) && !$date->confirmed) {
            $url = new \moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php', ['confirmexamdate' => $date->examdateid]);
            $url = $url->out();
            $buttonhtml .= \html_writer::tag('a', get_string('confirmexamdate', 'block_eledia_adminexamdates'), ['class' => 'btn btn-secondary',
                'href' => $url]);
        }
    }

    if(!$hasconfirmexamdatescap && isset($date->confirmed) && $date->confirmed) {
        $url = new \moodle_url('/blocks/eledia_adminexamdates/changerequest.php', ['examdateid' => $date->examdateid]);
        $url = $url->out();
        $buttonhtml .= \html_writer::tag('a', get_string('change_request_btn', 'block_eledia_adminexamdates'), ['class' => 'btn btn-secondary',
            'href' => $url]);
    }

    $buttonhtml .= \html_writer::end_tag('div');

    $myexamdate = ($date->userid == $USER->id || $date->contactperson == $USER->id) ? true : false;
    $title = ($hasconfirmexamdatescap || $myexamdate) ? $date->examname : 'Belegt';
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
    $content = ($hasconfirmexamdatescap || $myexamdate) ? "<dl><dt>Anzahl der Teilnehmer: </dt><dd>$date->numberstudents</dd><dt>Dozent/ Prüfer: </dt><dd>$examinernames</dd><dt>Ansprechpartner: </dt><dd>$contactperson</dd></dl><div>$buttonhtml</div>" : 'Dieser Raum ist bereits belegt.';

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

$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('examdaterequest', 'block_eledia_adminexamdates'));
$PAGE->set_pagelayout('course');

$mform = new \block_eledia_adminexamdates\forms\calendar_form();


echo $OUTPUT->header();
echo $OUTPUT->container_start();

$url = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1]);
$newexamdatebutton = new single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'), 'post');
$urllist = new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php');
$unconfirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php');

echo \html_writer::start_tag('div', array('class' => 'container-fluid px-4'));
echo \html_writer::start_tag('div', array('class' => 'row'));
echo \html_writer::start_tag('div', array('class' => 'col-xs-12'));
echo \html_writer::start_tag('div', array('class' => 'singlebutton'));
echo \html_writer::tag('button', get_string('calendar_btn', 'block_eledia_adminexamdates'), array('disabled' => true, 'class' => 'btn '));
echo \html_writer::end_tag('div');
if ($hasconfirmexamdatescap) {
    echo $OUTPUT->single_button($urllist, get_string('examdateslist_btn', 'block_eledia_adminexamdates'), 'post');
};
echo $OUTPUT->single_button($unconfirmed, get_string('unconfirmed_btn', 'block_eledia_adminexamdates'), 'post');
echo $OUTPUT->single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'), 'post');
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

echo $OUTPUT->container_end();


echo $OUTPUT->footer();