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

// <script src="calendar/dist/js/jquery-calendar.min.js"></script>
echo '  <link rel="stylesheet" href="calendar/node_modules/bootstrap/dist/css/bootstrap.min.css">
  <script src="calendar/node_modules/jquery/dist/jquery.min.js"></script>
  <script src="calendar/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
  <script src="calendar/node_modules/moment/min/moment-with-locales.min.js"></script>
  <script src="calendar/node_modules/jquery-touchswipe/jquery.touchSwipe.min.js"></script>
  
  <link rel="stylesheet" href="calendar/dist/css/jquery-calendar.min.css">
  <link rel="stylesheet" href="calendar/node_modules/@fortawesome/fontawesome-free-webfonts/css/fontawesome.css">
  <link rel="stylesheet" href="calendar/node_modules/@fortawesome/fontawesome-free-webfonts/css/fa-solid.css">';


$sql = "SELECT ad.id, a.id AS examdateid, a.examname, a.examduration, ag.blocktimestart, ad.examroom, ad.blockid, a.numberstudents, a.examiner, a.contactperson
                    FROM {eledia_adminexamdates} a
                    LEFT JOIN {eledia_adminexamdates_blocks} ag ON ag.examdateid = a.id
                    LEFT JOIN {eledia_adminexamdates_rooms} ad ON ad.blockid = ag.id";
//WHERE ag.blocktimestart > ? AND ag.blocktimestart < ?";

$dates = $DB->get_records_sql($sql);
$hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());

$rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));
foreach ($rooms as $room) {
    $roomitems = explode('|', $room);
    $roomcapacity = !empty($roomitems[2]) ? ' (max. ' . $roomitems[2] . ' TN)' : '';
    $roomnames[$roomitems[0]] = $roomitems[1] . $roomcapacity;
};
//
//echo " <script>
//    $(document).ready(function(){
//        moment.locale('de');
//        var now = moment();
//
//        /**
//         * Many events
//         */
//         var events= [
//        ";
//
//foreach ($dates as $date) {
//    $endtime = $date->blocktimestart + ($date->examduration * 60);
//    $roomname = $roomnames[$date->examroom];
//    $buttonhtml = \html_writer::start_tag('div', ['class' => 'd-inline']);
//    $url = new \moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['editexamdate' => $date->examdateid]);
//    $buttonhtml .= \html_writer::tag('a', get_string('editexamdate', 'block_eledia_adminexamdates'), ['class' => 'btn btn-secondary',
//            'href' => ($url)->out()]) . ' ';
//
//    $url = new \moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php', ['cancelexamdate' => $date->examdateid]);
//    $buttonhtml .= \html_writer::tag('a', get_string('cancelexamdate', 'block_eledia_adminexamdates'), ['class' => 'btn btn-secondary',
//            'href' => ($url)->out()]) . ' ';
//
//    if ($hasconfirmexamdatescap) {
//        $url = new \moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php', ['confirmexamdate' => $date->examdateid]);
//        $buttonhtml .= \html_writer::tag('a', get_string('confirmexamdate', 'block_eledia_adminexamdates'), ['class' => 'btn btn-secondary',
//            'href' => ($url)->out()]);
//    }
//    $buttonhtml .= \html_writer::end_tag('div');
//    echo "
//
//        {
//            start: $date->blocktimestart,
//          end: $endtime,
//          title: '$date->examname',
//          content: '<dl><dt>Anzahl der Teilnehmer: </dt><dd>$date->numberstudents</dd><dt>Dozent/ Prüfer: </dt><dd>$date->examiner</dd><dt>Ansprechpartner: </dt><dd>$date->contactperson</dd></dl><div>$buttonhtml</div>',
//          category:'$roomname'
//        },
//
//         ";
//
//}
//echo "       ];
//
//
//      /**
//       * Init the calendar
//       */
//      var calendar = $('#calendar').Calendar({
//        locale: 'de',
//        weekday: {
//        dayline: {
//                format:'dddd DD.MM'
//                },
//            timeline: {
//                intervalMinutes: 30,
//            fromHour: 8,
//            toHour:19,
//          }
//        },
//        colors: {
//
//            random: false
//            },
//        events: events
//
//      }).init();
//
//      /**
//       * Listening for events
//       */
//
//      $('#calendar').on('Calendar.init', function(event, instance, before, current, after){
//          console.log('event : Calendar.init');
//          console.log(instance);
//          console.log(before);
//          console.log(current);
//          console.log(after);
//      });
//      $('#calendar').on('Calendar.daynote-mouseenter', function(event, instance, elem){
//          console.log('event : Calendar.daynote-mouseenter');
//          console.log(instance);
//          console.log(elem);
//      });
//      $('#calendar').on('Calendar.daynote-mouseleave', function(event, instance, elem){
//          console.log('event : Calendar.daynote-mouseleave');
//          console.log(instance);
//          console.log(elem);
//      });
//      $('#calendar').on('Calendar.event-mouseenter', function(event, instance, elem){
//          console.log('event : Calendar.event-mouseenter');
//          console.log(instance);
//          console.log(elem);
//      });
//      $('#calendar').on('Calendar.event-mouseleave', function(event, instance, elem){
//          console.log('event : Calendar.event-mouseleave');
//          console.log(instance);
//          console.log(elem);
//      });
//      $('#calendar').on('Calendar.daynote-click', function(event, instance, elem, evt){
//          console.log('event : Calendar.daynote-click');
//          console.log(instance);
//          console.log(elem);
//          console.log(evt);
//      });
//      $('#calendar').on('Calendar.event-click', function(event, instance, elem, evt){
//          console.log('event : Calendar.event-click');
//          console.log(instance);
//          console.log(elem);
//          console.log(evt);
//      });
//    });
//  </script>";

$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('examdaterequest', 'block_eledia_adminexamdates'));
$PAGE->set_pagelayout('course');
echo $OUTPUT->header();
echo $OUTPUT->container_start();
$url = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1, 'url' => rawurlencode($myurl)]);
$newexamdatebutton = new single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'));
$urllist = new moodle_url('/blocks/eledia_adminexamdates/examdateslist.php');
$unconfirmed = new moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php');

echo \html_writer::start_tag('div',array('class' => 'container-fluid px-4'));
echo \html_writer::start_tag('div',array('class' => 'row'));
echo \html_writer::start_tag('div',array('class' => 'col-xs-12'));
echo \html_writer::start_tag('div',array('class' => 'singlebutton'));
echo \html_writer::tag('button', get_string('calendar_btn', 'block_eledia_adminexamdates'), array('disabled' => true, 'class' => 'btn '));
echo \html_writer::end_tag('div');
echo $OUTPUT->single_button($urllist, get_string('examdateslist_btn', 'block_eledia_adminexamdates'));
echo $OUTPUT->single_button($unconfirmed, get_string('unconfirmed_btn', 'block_eledia_adminexamdates'));
echo $OUTPUT->single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'));
echo \html_writer::end_tag('div');
echo \html_writer::end_tag('div');
echo \html_writer::start_tag('div',array('class' => 'row'));
echo \html_writer::start_tag('div',array('class' => 'col-xs-12'));
echo \html_writer::tag('h1', get_string('calendar_btn', 'block_eledia_adminexamdates'));
echo \html_writer::tag('div','',array('id' => 'calendar'));
echo \html_writer::end_tag('div');
echo \html_writer::end_tag('div');
echo \html_writer::end_tag('div');
$PAGE->requires->js_call_amd('block_eledia_adminexamdates/examdates_calendar', 'init');
echo $OUTPUT->container_end();


echo $OUTPUT->footer();