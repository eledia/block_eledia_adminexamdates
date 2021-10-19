<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     block_eledia_adminexamdates
 * @category    string
 * @copyright   2021 René Hansen <support@eledia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'eLeDia e-exam dates administration';
$string['privacy:metadata'] = 'The eLeDia e-exam dates administration block plugin does not store any personal data.';
$string['examdaterequest'] = 'Exam date request';
$string['time'] = 'Exam date';
$string['newexamdate'] = 'New date';
$string['examdatesschedule'] = 'Exam date scheduling';
$string['examdate_header'] = 'Exam date scheduling';
$string['examroom'] = 'Exam room';
$string['select_examroom'] = 'Select exam room';
$string['examtimestart'] = 'Beginning';
$string['examduration'] = 'Exam writing time (minutes)';
$string['examname'] = 'Exam title';
$string['examdaterequester'] = 'Requester';
$string['timecreated'] = 'Created';
$string['confirmed'] = 'Confirmed';
$string['editexamdate'] = 'Edit';
$string['cancelexamdate'] = 'Cancel';
$string['confirmexamdate'] = 'Confirm';
$string['confirmexamdatemsg'] = 'Do you want to confirm the exam date for: \'{name}\'?';
$string['cancelexamdatemsg'] = 'Do you want to cancel the exam date for: \'{name}\'?';
$string['configure_description'] = 'Here you can configure the exam schedule management.';
$string['number_students'] ='Number of students';
$string['department'] ='Department';
$string['examiner'] ='Examiner';
$string['contactperson'] ='Contact person';
$string['examrooms_default'] = 'PR1|Prüfungsraum 1|100
PR2|Prüfungsraum 2|100
AB|Administrationsbüro|0
ER|Endabnahmeraum|0';
$string['config_examrooms'] = 'Each line configures its own room. In each line there is first a unique room ID (e.g. \'PR1\'), followed by the name of the room (e.g. \'Examination room 1\'), as well as the room capacity, i.e. the maximum number of participants (e.g. \'100\'), separated by a vertical line.';
$string['examrooms'] ='Configuration of the examination rooms';
$string['summersemester'] ='Sommersemester';
$string['wintersemester'] ='Wintersemester';
$string['select_semester'] ='Semester';
$string['annotationtext'] ='Annotations';
$string['config_departments'] = 'You can choose from this selection when applying for an e-exam date.';
$string['departments'] ='Selection of departments';
$string['setting_apidomain'] = 'URL of the e-exam system';
$string['setting_apitoken'] = 'API token';
$string['config_apitoken'] = 'API token of the exam date management web service of the e-exam system';
$string['reloaddepartments'] = 'Update departments';
$string['configreloaddepartments'] = 'Please select here after changes in the course categories of the departments in the e-exam system - the above selection field "Selection of departments" will be updated after the settings have been saved.';
$string['setting_envcategoryidnumber'] = 'Course category ID of "Exam Environment"';
$string['config_envcategoryidnumber'] = 'The departments are located as sub-categories in the "Exam Environment" of the e-exam system. This course category ID should be set in the "Exam Environment" category.';
$string['setting_examcoursetemplateidnumber'] = 'Course ID of the exam course template';
$string['config_examcoursetemplateidnumber'] = 'This course ID should be set in the exam course template of the e-exam system.';
$string['calendar_btn'] = 'Exam date calendar';
$string['setting_startexam'] = 'Earliest start of the e-exam (full hours only)';
$string['setting_endexam'] = 'E-exam end at the latest (full hours only)';
$string['setting_breakbetweenblockdates'] = 'Break between two block dates (in minutes)';
$string['setting_distancebetweenblockdates'] = 'Distance between blocks (in minutes)';
$string['editsingleexamdate'] = 'Single dates';
$string['singleexamdate_header'] = 'Single exam dates scheduling';
$string['examdateslist_btn'] = 'Exam date list';
$string['tablehead_month'] = 'Month';
$string['tablehead_date'] = 'Date';
$string['tablehead_examname'] = 'Exam name';
$string['tablehead_examiner'] = 'Examiner/Lecturer';
$string['tablehead_examroom'] = 'Exam room';
$string['tablehead_supervisor1'] = 'Supervisor 1';
$string['tablehead_supervisor2'] = 'Supervisor 2';
$string['tablehead_candidates'] = 'Candidates';
$string['tablehead_status'] = 'Status';
$string['tablehead_blockid'] = 'Single date ID';
$string['dt_lenghtmenu'] = 'Display _MENU_ records per page';
$string['dt_zerorecords'] = 'Nothing found - sorry';
$string['dt_info'] = 'Showing page _PAGE_ of _PAGES_';
$string['dt_infoempty'] = 'No records available';
$string['dt_infofiltered'] = '(filtered from _MAX_ total records)';
$string['dt_emptytable'] = 'No data available in table';
$string['dt_infopostfix'] = '';
$string['dt_thousands'] = ',';
$string['dt_loadingrecords'] = 'Loading...';
$string['dt_processing'] = 'Processing...';
$string['dt_search'] = 'Search:';
$string['dt_first'] = 'First';
$string['dt_last'] = 'Last';
$string['dt_next'] = 'Next';
$string['dt_previous'] = 'Previous';
$string['dt_sortascending'] = ': activate to sort column ascending';
$string['dt_sortdescending'] = ': activate to sort column descending';
$string['block_timestart'] = 'Exam start (block date)';
$string['block_duration'] = 'Exam writing time (minutes)';
$string['room_number_students'] = 'Number of participants (room)';
$string['room_supervisor'] = 'Supervisor (room)';
$string['room_supervision'] = 'Exam supervision (room)';