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
$string['newexamdate'] = 'New exam date';
$string['editexamdate_header'] = 'Edit exam date';
$string['examdatesunconfirmed'] = 'Exam date scheduling';
$string['examdate_header'] = 'Exam date scheduling';
$string['examroom'] = 'Exam room';
$string['select_examroom'] = 'Select exam room';
$string['examtimestart'] = 'Exam time {$a}';
$string['examduration'] = 'Exam writing time (minutes)';
$string['booktimestart'] = 'Booking start';
$string['bookduration'] = 'Booking duration (minutes)';
$string['select_specialroom'] = 'Select special room';
$string['examname'] = 'Exam title';
$string['examname_help'] = 'Please use the following nomenclature when naming the exam: "YYYYMMDD Department examiner/lecturer free text". Example: "20230731 FB08 Mustermann Physiology Basics".';
$string['examdaterequester'] = 'Requester';
$string['timecreated'] = 'Created';
$string['confirmed'] = 'Confirmed';
$string['editexamdate'] = 'Edit';
$string['cancelexamdate'] = 'Cancel';
$string['confirmexamdate'] = 'Confirm';
$string['confirmexamdatemsg'] = 'Do you want to confirm the exam date for: \'{name}\'?';
$string['cancelexamdatemsg'] = 'Do you want to cancel the exam date for: \'{name}\'?';
$string['configure_description'] = 'Here you can configure the exam schedule management.';
$string['number_students'] ='Expected number of students';
$string['department'] ='Department';
$string['examiner'] ='Examiner';
$string['examiner_help'] = 'Select one or more instructors from the list. The input of lecturers names that are not in the list will not be accepted.';
$string['contactperson'] ='Contact person';
$string['contactpersonemail'] ='Contact person\'s email';
$string['responsibleperson'] ='SCL responsible person';
$string['examrooms_default'] = 'PR1|Prüfungsraum 1|100|#AF7B84
PR2|Prüfungsraum 2|100|#3F51B5
AB|Administrationsbüro|0|#FA9F37
ER|Endabnahmeraum|0|#987D71';
$string['config_examrooms'] = 'Each line configures its own room. In each line there is first a unique room ID (e.g. \'PR1\'), followed by the name of the room (e.g. \'Examination room 1\'), as well as the room capacity, i.e. the maximum number of participants (e.g. \'100\') and the displayed room color (e.g. \'#3F51B5\'), separated by a vertical line.';
$string['examrooms'] ='Configuration of the examination rooms';
$string['config_responsiblepersons'] = 'List of the user IDs of those responsible for the SCL, each separated by a comma. (e.g. \'2,4,5,12\')';
$string['responsiblepersons'] ='Configuration of SCL responsible persons';
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
$string['setting_archivecategoryidnumber'] = 'Course category ID of the exam archive';
$string['config_archivecategoryidnumber'] = 'The canceled exam courses are archived in a course category of the e-exam system. This course category ID should be set in the corresponding archive course category.';
$string['setting_examcoursetemplateidnumber'] = 'Course ID of the exam course template';
$string['config_examcoursetemplateidnumber'] = 'This course ID should be set in the exam course template of the e-exam system.';
$string['calendar_btn'] = 'Exam date calendar';
$string['unconfirmed_btn'] = 'Unconfirmed exam dates';
$string['confirmed_btn'] = 'Confirmed exam dates';
$string['setting_startexam'] = 'Earliest start time of the e-exam';
$string['setting_endexam'] = 'Latest end time of the e-exam';
$string['setting_startcalendar'] = 'Display calendar start time (only full hours)';
$string['setting_endcalendar'] = 'Display calendar end time (only full hours)';
$string['setting_breakbetweenblockdates'] = 'Break between two block dates (in minutes)';
$string['setting_distancebetweenblockdates'] = 'Distance between blocks (in minutes)';
$string['editsingleexamdate'] = 'Single exam dates';
$string['singleexamdate_header'] = 'Single exam date scheduling';
$string['newsingleexamdate'] = 'New single exam date';
$string['examdateslist_btn'] = 'Exam date list';
$string['tablehead_month'] = 'Month';
$string['tablehead_date'] = 'Date';
$string['tablehead_examname'] = 'Exam name';
$string['tablehead_examiner'] = 'Examiner/Lecturer';
$string['tablehead_contactperson'] = 'Contact person';
$string['tablehead_examroom'] = 'Exam room';
$string['tablehead_supervisor1'] = 'Supervisor 1';
$string['tablehead_supervisor2'] = 'Supervisor 2';
$string['tablehead_candidates'] = 'Candidates';
$string['tablehead_status'] = 'Status';
$string['tablehead_blockid'] = 'Single date ID';
$string['tablehead_examid'] = 'Exam ID';
$string['tablehead_links'] = '';
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
$string['partialdate'] = 'partial date';
$string['examdateedit'] = 'Edit exam date';
$string['status_confirmed'] = 'Confirmed';
$string['status_unconfirmed'] = 'Unconfirmed';
$string['newpartialdate'] = 'New partial date';
$string['setting_emailexamteam'] = 'Email of the e-exam team';
$string['change_request_btn'] = 'Change request';
$string['changerequest_header'] = 'Change request to the e-exam team';
$string['changerequesttext'] = 'Enter the change request';
$string['send_email'] ='Send email';
$string['examdaterooms'] ='Exam rooms';
$string['eledia_adminexamdates:addinstance'] = 'Add a new eLeDia e-exam dates administration block';
$string['eledia_adminexamdates:confirmexamdates'] = 'Confirm the e-exam dates in the eLeDia e-exam dates administration block';
$string['delete'] ='Delete';
$string['confirm_delete_singleexamdate_msg'] = 'Do you really want to delete for the exam: \'{$a->name}\' the {$a->index}. single exam date?';
$string['error_examdate_already_taken']  = 'This exam date is already taken. Please look for another date!';
$string['error_startexamtime'] = 'The earliest possible exam date is {$a->start}. The latest possible time for the completion of an exam is {$a->end}.';
$string['autocomplete_placeholder']  = 'Search or enter with the Enter key ';
$string['error_email'] = 'Please enter a valid email address!';
$string['pleasechoose'] = 'Please choose ...';
$string['error_choose'] = 'Please choose!';
$string['error_choose_or_enter'] = 'Please select or enter with the enter key!';
$string['error_wrong_userid'] = 'Please enter names and no numbers!';
$string['config_select_calendar_month'] = 'Month';
$string['config_select_calendar_year'] = 'Year';
$string['calendar_date'] = 'Selection of a date';
$string['confirm_save_singleexamdate_msg'] = 'The {$a->index} single exam date of the exam: \' {$a->name} \' has been saved.';
$string['confirm_save_examdate_msg'] = 'The exam : \' {$a->name} \' has been saved.';
$string['error_wrong_email'] = 'Please enter a correct e-mail address with the Enter key - or search in selection!';
$string['error_wrong_userid_email'] = 'Please enter a correct e-mail address and no numbers!';
$string['examconfirm_email_subject'] = 'Confirmation of the exam date: {$a->name}';
$string['examconfirm_email_body'] = 'The exam date is confirmed for: 

{$a->name}, 
{$a->date} o\'clock, 
{$a->course},
{$a->url} 

';
$string['request_email_subject'] = 'Request exam date: {$a->name}';
$string['request_email_body'] = 'The exam date was requested for:

{$a->name}
{$a->date}

Annotations:
{$a->annotation} 

{$a->url}

';
$string['examcancel_email_subject'] = 'Cancellation of the date for the exam: {$a->name}';
$string['examcancel_email_body'] = 'The exam date will be canceled for: 

{$a->name}, {$a->date} o\'clock.';
$string['changerequest_email_subject'] = 'Change request to the exam team for: {$a->name}';
$string['changerequest_email_body'] = 'Change request to the exam team

Exam: 
{$a->name}, 
{$a->date} o\'clock, 
{$a->url} 

Request: 
{$a->changerequest}

';
$string['checklist_btn'] = 'Checklist';
$string['editexamdate_btn'] = 'Edit exam date';
$string['singleexamdate_btn'] = 'Single exam date';
$string['category_regularexam'] = 'Regular exam';
$string['category_semestertest'] = 'Semester test';
$string['selection_exam_category'] = 'Exam category';
$string['specialrooms_btn'] = 'Edit';
$string['chooseroomcategory_msg'] = 'Do you want to create a new exam date or book special rooms?';
$string['cancelspecialrooms_msg'] = 'Do you want to cancel {$a->rooms} for {$a->date} o\'clock?';
$string['cancelspecialrooms'] = 'Cancel';
$string['book_specialrooms'] = 'Book special rooms';
$string['room_occupied'] = '{$a->room} occupied';
$string['room_already_occupied'] = '{$a->room} is already occupied during this time.';
$string['checklist_table_title'] = 'processing status';
$string['checklist_table_topic'] = 'Topic';
$string['checklist_table_topicdate'] = 'Date';
$string['calendarlink'] = 'Calendar view';
$string['select_frommonth'] = 'From:';
$string['select_tomonth'] = 'To:';
$string['statistics'] = 'Exam date statistics';
$string['select_period'] = 'Period';
$string['period_semester'] = 'Semester';
$string['period_date'] = 'Date';
$string['datestart'] = 'From';
$string['dateend'] = 'To';
$string['statistics_title'] = 'Exam date statistics';
$string['period'] = 'Period';
$string['numberstudents'] = 'Number of students';
$string['examnumber'] = 'Number of exams';
$string['blocknumber'] = 'Number of partial dates';
$string['hour'] = '';
$string['error_pastexamtime'] = 'The exam date must not be in the past.';
$string['setting_bordercolor_unconfirmed_dates'] = 'Border color 1';
$string['config_bordercolor_unconfirmed_dates'] = 'Border color value for unconfirmed dates the calendar view of exam dates admins.';
$string['setting_bordercolor_unavailable_dates'] = 'Border color 2';
$string['config_bordercolor_unavailable_dates'] = 'Border color value for unavailable dates in the calendar view of exam dates manager.';
$string['config_holidays'] = 'Each line configures a holiday. In each line there is first a date (e.g. \'01.05.2023\'), followed by the name of the holiday (e.g. \'Tag der Arbeit\'), separated by a vertical line.';
$string['holidays'] ='Configuration of the holidays';
$string['modal_title_weekend_not_available'] = 'Weekend: ';
$string['modal_body_weekend_not_available'] ='Weekend dates are not available. Please look for another date!';
$string['modal_title_holiday_not_available'] = 'Holiday: ';
$string['modal_body_holiday_not_available'] ='Dates on a holiday are not available. Please look for another date!';
$string['modal_title_past_not_available'] = 'Past';
$string['modal_body_past_not_available'] ='Past dates are not available. Please find another date!';
$string['exam_dates_confirmed_start_date'] = 'Start Date';
$string['exam_dates_confirmed_end_date'] = 'End Date';
$string['setting_examinercohorts'] = 'Cohorts of examiners';
$string['config_examinercohorts'] = 'Users who are in these cohorts will be available in the examiner selection in the exam date form.';
$string['setting_instanceofmodelediachecklist'] = 'Activity eLeDia Checklist';
$string['config_instanceofmodelediachecklist'] = 'Selection of an instance of the eLeDia Checklist activity linked as a checklist.';
$string['setting_instanceofmodproblemdb'] = 'Problem database activity';
$string['config_instanceofmodproblemdb'] = 'Select an instance of activity database linked as problem database.';
$string['progressbar_confirmed_course_create'] = 'Creation of a course for the exam date on the exam system.';
$string['progressbar_confirmed_email'] = 'Confirmation of exam date - email sent to contact person and exam team.';
$string['progressbar_confirmed_finished'] = 'Confirmation of the exam date has been completed.';
$string['progressbar_cancelled_finished'] = 'Exam date cancellation has been completed.';
$string['setting_icalexporttoken'] = 'iCal Calendar Export Token';
$string['config_icalexporttoken'] = 'In this field configure a token for the export of exam dates as URL. <br/>
This iCal exam calendar URL <br/>{$a->url} <br/>
provides a dynamic link for importing exam dates into other calendars. <br/>
All new, changed or deleted exam dates are reflected in the other calendars. <br/>
The value of the month-parameter e.g.: "&month=12" controls the period of the exported exam dates, this is the number of months before and after the current date.<br/>
With the optional parameter "&special=0" exam dates of the special rooms are not exported.';