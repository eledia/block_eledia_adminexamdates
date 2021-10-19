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

if (!has_capability('block/eledia_adminexamdates:view', $context)) {
    print_error(' only users with rights to view admin exam dates allowed');
}

$confirmexamdate = optional_param('confirmexamdate', 0, PARAM_INT);
$cancelexamdate = optional_param('cancelexamdate', 0, PARAM_INT);
$confirmexamdateyes = optional_param('confirmexamdateyes', 0, PARAM_INT);
$cancelexamdateyes = optional_param('cancelexamdateyes', 0, PARAM_INT);

$myurl = new \moodle_url($FULLME);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_title(get_string('examdaterequest', 'block_eledia_adminexamdates'));
$PAGE->set_pagelayout('course');

if (!empty($confirmexamdate)) {
    $examdatename=$DB->get_record('eledia_adminexamdates',['id'=>$confirmexamdate],'examname');
    $message = get_string('confirmexamdatemsg', 'block_eledia_adminexamdates', ['name' => $examdatename->examname]);
    $formcontinue = new single_button(new moodle_url($PAGE->url, ['confirmexamdateyes' => $confirmexamdate]), get_string('yes'), 'post');
    $formcancel = new single_button(new moodle_url($PAGE->url), get_string('no'));
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    echo $OUTPUT->confirm($message, $formcontinue, $formcancel);
    echo $OUTPUT->box_end();

} else if (!empty($cancelexamdate)) {
    $examdatename=$DB->get_record('eledia_adminexamdates',['id'=>$cancelexamdate],'examname');
    $message = get_string('cancelexamdatemsg', 'block_eledia_adminexamdates', ['name' => $examdatename->examname]);
    $formcontinue = new single_button(new moodle_url($PAGE->url, ['cancelexamdateyes' => $cancelexamdate]), get_string('yes'), 'post');
    $formcancel = new single_button(new moodle_url($PAGE->url), get_string('no'));
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    echo $OUTPUT->confirm($message, $formcontinue, $formcancel);
    echo $OUTPUT->box_end();

} else {
    if (!empty($confirmexamdateyes)) {
        block_eledia_adminexamdates\util::examconfirm($confirmexamdateyes);
    }
    if (!empty($cancelexamdateyes)) {
        block_eledia_adminexamdates\util::examcancel($confirmexamdateyes);
    }
    echo $OUTPUT->header();
    echo $OUTPUT->container_start();

    $url = new moodle_url('/blocks/eledia_adminexamdates/editexamdate.php', ['newexamdate' => 1]);
    $newexamdatebutton = new single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'), 'post');
    $urlcalendar = new moodle_url('/blocks/eledia_adminexamdates/calendar.php');


    echo $OUTPUT->single_button($url, get_string('newexamdate', 'block_eledia_adminexamdates'), 'post');
    echo $OUTPUT->single_button($urlcalendar, get_string('calendar_btn', 'block_eledia_adminexamdates'), 'post');
//    echo block_eledia_adminexamdates\util::getexamdateitems();
    $urleditsingleexamdate = new moodle_url('/blocks/eledia_adminexamdates/editsingleexamdate.php', ['blockid' => '']);
    echo $OUTPUT->box($OUTPUT->single_button($urleditsingleexamdate, '', 'post'),'d-none','examdate');

  //  echo '<link rel="stylesheet" type="text/css" href="datatables/datatables.min.css"/>';
    echo '<style>

</style>';
    echo '<script type="text/javascript" src="datatables/datatables.min.js"></script>';

    echo block_eledia_adminexamdates\util::getexamdatetable();

    echo '<script type="text/javascript">';
    echo '$(document).ready(function() {
     var groupColumn = 0;
         var table = $("#examdatestable").DataTable( {
         "buttons": [
        "copy", "excel", "pdf"
    ],
          "columnDefs": [
            { "visible": false,"searchable": false, "targets": groupColumn },
            {"targets": [ 4 ], "searchable": false},
            {"targets": [ 7 ], "searchable": false},
            {"targets": [ 8 ], "searchable": false},
            {"targets": [ 9 ], "searchable": false, "visible": false}
        ],
        "stateSave": true,
        "order": [[ groupColumn, "asc" ],[1,"asc"]],
        "displayLength": 25,
        "drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:"current"} ).nodes();
            var last=null;
 
            api.column(groupColumn, {page:"current"} ).data().each( function ( group, i ) {
                if ( last !== group ) {
                    $(rows).eq( i ).before(
                        \'<tr class="group table-primary font-weight-bold"><td colspan="8">\'+group+\'</td></tr>\'
                    );
 
                    last = group;
                }
            } );
        },
        "language": {
            "lengthMenu": "'.get_string('dt_lenghtmenu','block_eledia_adminexamdates').'",
            "zeroRecords": "'.get_string('dt_zerorecords','block_eledia_adminexamdates').'",
            "info": "'.get_string('dt_info','block_eledia_adminexamdates').'",
            "infoEmpty": "'.get_string('dt_infoempty','block_eledia_adminexamdates').'",
            "infoFiltered": "'.get_string('dt_infofiltered','block_eledia_adminexamdates').'",
                        "emptyTable": "'.get_string('dt_emptytable','block_eledia_adminexamdates').'",
            "infoPostFix": "'.get_string('dt_infopostfix','block_eledia_adminexamdates').'",
            "thousands": "'.get_string('dt_thousands','block_eledia_adminexamdates').'",
            "loadingRecords": "'.get_string('dt_loadingrecords','block_eledia_adminexamdates').'",
            "processing": "'.get_string('dt_processing','block_eledia_adminexamdates').'",
                        "search": "'.get_string('dt_search','block_eledia_adminexamdates').'",
                        "paginate": {
            "first": "'.get_string('dt_first','block_eledia_adminexamdates').'",
            "last": "'.get_string('dt_last','block_eledia_adminexamdates').'",
            "next": "'.get_string('dt_next','block_eledia_adminexamdates').'",
            "previous": "'.get_string('dt_previous','block_eledia_adminexamdates').'",
                        },
                         "aria": {
                         "sortAscending": "'.get_string('dt_sortascending','block_eledia_adminexamdates').'",
            "sortDescending": "'.get_string('dt_sortdescending','block_eledia_adminexamdates').'",
            }
        }
    } );
    $("#examdatestable").removeClass("dataTable");
  $("#examdatestable tbody").on("click", "tr", function () {
        var data = table.row( this ).data();
        var editsingleexamdateform = $("#editsingleexamdate");
        editsingleexamdateform.find("input[name=\'blockid\']").val(data[9]);
        editsingleexamdateform.find("form").submit();
    } );
 
} );
    </script>';
    echo $OUTPUT->container_end();
}


echo $OUTPUT->footer();


//$("#examdatestable tbody").on( "click", "tr.group", function () {
//    var currentOrder = table.order()[0];
//    if ( currentOrder[0] === groupColumn && currentOrder[1] === "asc" ) {
//        table.order( [ groupColumn, "desc" ] ).draw();
//    }
//    else {
//        table.order( [ groupColumn, "asc" ] ).draw();
//    }
//} );

