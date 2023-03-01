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

$string['pluginname'] = 'eLeDia E-Klausur Termin-Verwaltung';
$string['privacy:metadata'] = 'Das eLeDia E-Klausur Termin-Verwaltung Block Plugin speichert keine persönlichen Daten.';
$string['examdaterequest'] = 'Prüfungstermin-Anfrage';
$string['time'] = 'Termin';
$string['newexamdate'] = 'Neuer Prüfungstermin';
$string['editexamdate_header'] = 'Prüfungstermin bearbeiten';
$string['examdatesunconfirmed'] = 'Prüfungsterminplanung';
$string['examdate_header'] = 'Prüfungsterminplanung';
$string['examroom'] = 'Prüfungsraum';
$string['select_examroom'] = 'Prüfungsraum wählen';
$string['examtimestart'] = 'Prüfungszeitraum {$a}&nbsp;Uhr';
$string['examduration'] = 'Klausur-Schreibzeit (Minuten)';
$string['booktimestart'] = 'Buchungsbeginn';
$string['bookduration'] = 'Buchungsdauer (Minuten)';
$string['select_specialroom'] = 'Sonderraum wählen';
$string['examname'] = 'Klausurbezeichnung';
$string['examname_help'] = 'Bitte halten Sie sich bei der Bezeichnung der Klausur an folgende Nomenklatur: „JJJJMMTT Fachbereich Prüfer:in/Dozent:in Freier Text“. Beispiel: „20230731 FB08 Mustermann Physiologie Grundlagen“.';
$string['examdaterequester'] = 'Beantragung';
$string['timecreated'] = 'Erstellt';
$string['confirmed'] = 'Bestätigt';
$string['editexamdate'] = 'Bearbeiten';
$string['cancelexamdate'] = 'Stornieren';
$string['confirmexamdate'] = 'Bestätigen';
$string['confirmexamdatemsg'] = 'Wollen Sie die den Prüfungstermin bestätigen für: \'{$a->name}\'?';
$string['cancelexamdatemsg'] = 'Wollen Sie die den Prüfungstermin stornieren für: \'{$a->name}\'?';
$string['configure_description'] = 'Hier können Sie die Prüfungstermin-Verwaltung konfigurieren.';
$string['number_students'] ='Erwartete Anzahl der Teilnehmenden';
$string['department'] ='Fachbereich';
$string['examiner'] ='Dozent:in/ Prüfer:in';
$string['examiner_help'] = 'Wählen Sie eine oder mehrere Dozent:innen aus der Liste aus. Die Eingabe von Dozent:innennamen, die nicht in der Liste aufgeführt sind, werden nicht übernommen.';
$string['contactperson'] ='Ansprechpartner:in';
$string['contactpersonemail'] ='E-Mail des/der Ansprechpartners/Ansprechpartnerin';
$string['responsibleperson'] ='SCL Verantwortliche:r';
$string['examrooms_default'] = 'PR1|Prüfungsraum 1|100|#E91E63
PR2|Prüfungsraum 2|100|#3F51B5
AB|Administrationsbüro|0|#009688
ER|Endabnahmeraum|0|#6D4C41';
$string['config_examrooms'] = 'Jede Zeile konfiguriert einen eigenen Prüfungsraum. In jeder Zeile steht zunächst eine eindeutige Raum-ID (z.B. \'PR1\'), dann der Name des Raumes (z.B. \'Prüfungsraum 1\') sowie die Raumkapazität, also die maximale Teilnehmerzahl (z.B. \'100\') und die angezeigte Raum-Farbe (z.B. \'#3F51B5\'), getrennt durch jeweils einen senkrechten Strich.';
$string['examrooms'] ='Konfiguration der Prüfungsräume';
$string['config_responsiblepersons'] = 'Liste der User-IDs der SCL Verantwortlichen, getrennt jeweils durch ein Komma. (Beispiel: \'2,4,5,12\')';
$string['responsiblepersons'] ='Konfiguration der SCL Verantwortlichen';
$string['summersemester'] ='Sommersemester';
$string['wintersemester'] ='Wintersemester';
$string['select_semester'] ='Semester';
$string['annotationtext'] ='Anmerkungen';
$string['config_departments'] = 'Aus dieser Auswahl kann bei der Beantragung eines Prüfungstermins gewählt werden.';
$string['departments'] ='Auswahl der Fachbereiche';
$string['setting_apidomain'] = 'URL des Prüfungssystems';
$string['setting_apitoken'] = 'API Token';
$string['config_apitoken'] = 'API Token des Terminverwaltungs-Webservices des Prüfungssystems';
$string['reloaddepartments'] = 'Fachbereiche aktualisieren';
$string['configreloaddepartments'] = 'Bitte hier auswählen nach Änderungen der Kurskategorien der Fachbereiche im Prüfungssystem - das obige Auswahlfeld "Auswahl der Fachbereiche" wird nach dem Speichern der Einstellungen aktualisiert.';
$string['setting_envcategoryidnumber'] = 'Kursbereichs-ID der Prüfungsumgebung';
$string['config_envcategoryidnumber'] = 'Die Fachbereiche liegen als Unterkategorien in der "Prüfungsumgebung" des Prüfungssystems. Diese Kurskategorie-ID sollte in der Kategorie "Prüfungsumgebung" gesetzt sein.';
$string['setting_archivecategoryidnumber'] = 'Kursbereichs-ID des Prüfungsarchivs';
$string['config_archivecategoryidnumber'] = 'Die stornierten Prüfungskurse werden in einer Kurskategorie des Prüfungssystems archiviert. Diese Kurskategorie-ID sollte in der entsprechenden Archiv-Kurskategorie gesetzt werden.';
$string['setting_examcoursetemplateidnumber'] = 'Kurs-ID der Klausur-Kursvorlage';
$string['config_examcoursetemplateidnumber'] = 'Diese Kurs-ID sollte in der Klausur-Kursvorlage des Prüfungssystems gesetzt sein.';
$string['calendar_btn'] = 'Kalender - Prüfungstermine - Buchung';
$string['unconfirmed_btn'] = 'Unbestätigte Prüfungstermine';
$string['confirmed_btn'] = 'Bestätigte Prüfungstermine';
$string['setting_startexam'] = 'Frühester E-Klausur Beginn';
$string['setting_endexam'] = 'Spätestens E-Klausur Ende';
$string['setting_startcalendar'] = 'Anzeige Kalender-Beginn (nur volle Stunden)';
$string['setting_endcalendar'] = 'Anzeige Kalender-Ende (nur volle Stunden)';
$string['setting_breakbetweenblockdates'] = 'Pause zwischen zwei Blockterminen (in Minuten)';
$string['setting_distancebetweenblockdates'] = 'Abstand zwischen Blöcken (in Minuten)';
$string['editsingleexamdate'] = 'Teiltermine';
$string['singleexamdate_header'] = 'Teiltermine';
$string['newsingleexamdate'] = 'Neuer Teiltermin';
$string['examdateslist_btn'] = 'Prüfungstermin-Liste';
$string['tablehead_month'] = 'Monat';
$string['tablehead_date'] = 'Datum';
$string['tablehead_examname'] = 'Bezeichnung Klausur';
$string['tablehead_examiner'] = 'Prüfer:in/Dozent:in';
$string['tablehead_contactperson'] = 'Ansprechpartner:in';
$string['tablehead_examroom'] = 'Prüfungsraum';
$string['tablehead_supervisor1'] = 'Betreuer:in 1';
$string['tablehead_supervisor2'] = 'Betreuer:in 2';
$string['tablehead_candidates'] = 'Prüflinge';
$string['tablehead_status'] = 'Status';
$string['tablehead_blockid'] = 'Teiltermin ID';
$string['tablehead_examid'] = 'Klausur ID';
$string['tablehead_links'] = '';
$string['dt_lenghtmenu'] = 'Anzeigen von _MENU_ Prüfungsterminen pro Seite';
$string['dt_zerorecords'] = 'Nichts gefunden - Entschuldigung';
$string['dt_info'] = 'Zeige Seite _PAGE_ von _PAGES_';
$string['dt_infoempty'] = 'Keine Datensätze verfügbar';
$string['dt_infofiltered'] = '(gefiltert aus _MAX_ Gesamtdatensätzen)';
$string['dt_emptytable'] = 'Keine Daten in der Tabelle vorhanden';
$string['dt_infopostfix'] = '';
$string['dt_thousands'] = '.';
$string['dt_loadingrecords'] = 'Laden...';
$string['dt_processing'] = 'Verarbeitung...';
$string['dt_search'] = 'Suche:';
$string['dt_first'] = 'Erster';
$string['dt_last'] = 'Letzter';
$string['dt_next'] = 'Weiter';
$string['dt_previous'] = 'Zurück';
$string['dt_sortascending'] = ': aktivieren, um die Spalte aufsteigend zu sortieren';
$string['dt_sortdescending'] = ': aktivieren um die Spalte absteigend zu sortieren';
$string['block_timestart'] = 'Prüfungsbeginn (Blocktermin)';
$string['block_duration'] = 'Klausur-Schreibzeit (Minuten)';
$string['room_number_students'] = 'Anzahl der Teilnehmenden (Raum)';
$string['room_supervisor'] = 'Betreuer:in (Raum)';
$string['room_supervision'] = 'Klausuraufsicht (Raum)';
$string['partialdate'] = 'Teiltermin';
$string['examdateedit'] = 'Prüfungstermin bearbeiten';
$string['status_confirmed'] = 'Bestätigt';
$string['status_unconfirmed'] = 'Beantragt';
$string['newpartialdate'] = 'Neuer Teiltermin';
$string['setting_emailexamteam'] = 'E-Mail des Prüfungsteams';
$string['change_request_btn'] = 'Änderungsanfrage';
$string['changerequest_header'] = 'Änderungsanfrage an das Prüfungsteam';
$string['changerequesttext'] = 'Eingabe der Änderungsanfrage';
$string['send_email'] ='Sende E-Mail';
$string['examdaterooms'] ='Prüfungsräume';
$string['eledia_adminexamdates:addinstance'] = 'Neuen eLeDia E-Klausur Termin-Verwaltungs-Block hinzufügen';
$string['eledia_adminexamdates:confirmexamdates'] = 'Bestätigen der E-Klausur Termine im eLeDia E-Klausur Termin-Verwaltungs-Block';
$string['delete'] ='Löschen';
$string['confirm_delete_singleexamdate_msg'] = 'Wollen Sie in der Klausur: \'{$a->name}\' den {$a->index} Teiltermin löschen?';
$string['error_examdate_already_taken']  = 'Dieser Termin ist bereits vergeben. Bitte suchen Sie nach einem anderen  Termin!';
$string['error_startexamtime']  = 'Der frühestmögliche Klausurtermin ist {$a->start} Uhr. Der spätestmögliche Zeitpunkt für die Beendigung einer Klausur ist {$a->end} Uhr.';
$string['autocomplete_placeholder']  = 'Suche oder Eingabe mit Eingabetaste ';
$string['error_email'] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein!';
$string['pleasechoose'] = 'Bitte auswählen ...';
$string['error_choose'] = 'Bitte auswählen!';
$string['error_choose_or_enter'] = 'Bitte auswählen oder mit der Eingabetaste eingeben!';
$string['error_wrong_userid'] = 'Bitte Namen und keine Zahlen eingeben!';
$string['config_select_calendar_month'] = 'Monat';
$string['config_select_calendar_year'] = 'Jahr';
$string['calendar_date'] = 'Auswahl eines Datums';
$string['confirm_save_singleexamdate_msg'] = 'Der {$a->index} Teiltermin der Klausur: \'{$a->name}\' wurde gespeichert.';
$string['error_wrong_email'] = 'Bitte geben Sie eine korrekte E-Mail-Adresse mit der Eingabetaste ein - oder suchen Sie in der Auswahl!';
$string['error_wrong_userid_email'] = 'Bitte eine korrekte E-Mail-Adresse und keine Zahlen eingeben!';
$string['examconfirm_email_subject'] = 'Bestätigung des Prüfungstermins: {$a->name}';
$string['examconfirm_email_body'] = 'Der Prüfungstermin wurde bestätigt für: 

{$a->name}, 
{$a->date},
{$a->course},
{$a->url} 

';
$string['request_email_subject'] = 'Anfrage Prüfungstermin: {$a->name}';
$string['request_email_body'] = 'Der Prüfungstermin wurde angefragt für: 

{$a->name} 
{$a->date}

Anmerkungen:
{$a->annotation} 

{$a->url}

';
$string['examcancel_email_subject'] = 'Absage des Prüfungstermins für: {$a->name}';
$string['examcancel_email_body'] = 'Der Prüfungstermin wurde abgesagt für: 

{$a->name}, {$a->date}.';
$string['changerequest_email_subject'] = 'Änderungsanfrage an das Prüfungsteam: {$a->name}';
$string['changerequest_email_body'] = 'Änderungsanfrage an das Prüfungsteam

Prüfung: 
{$a->name}, 
{$a->date}, 
{$a->url}  

Anfrage:
{$a->changerequest}

';
$string['checklist_btn'] = 'Checkliste';
$string['editexamdate_btn'] = 'Prüfungstermin bearbeiten';
$string['singleexamdate_btn'] = 'Einzeltermin planen';
$string['category_regularexam'] = 'Reguläre Prüfung';
$string['category_semestertest'] = 'Semesterbegleitender Test';
$string['selection_exam_category'] = 'Prüfungskategorie';
$string['specialrooms_btn'] = 'Bearbeiten';
$string['chooseroomcategory_msg'] = 'Wollen Sie einen neuen Prüfungstermin erstellen oder Sonderräume buchen?';
$string['cancelspecialrooms_msg'] = 'Wollen Sie {$a->rooms} stornieren für {$a->date} Uhr?';
$string['cancelspecialrooms'] = 'Stornieren';
$string['book_specialrooms'] = 'Sonderräume buchen';
$string['room_occupied'] = '{$a->room} belegt';
$string['room_already_occupied'] = '{$a->room} ist in dieser Zeit bereits belegt.';
$string['checklist_table_title'] = 'Bearbeitungsstand';
$string['checklist_table_topic'] = 'Thema';
$string['checklist_table_topicdate'] = 'Datum';
$string['calendarlink'] = 'Kalenderansicht';
$string['select_frommonth'] = 'Von:';
$string['select_tomonth'] = 'Bis:';
$string['statistics'] = 'Prüfungstermin-Statistik';
$string['select_period'] = 'Zeitraum';
$string['period_semester'] = 'Semester';
$string['period_date'] = 'Datum';
$string['datestart'] = 'Von';
$string['dateend'] = 'Bis';
$string['statistics_title'] = 'Prüfungstermin-Statistik';
$string['period'] = 'Zeitraum';
$string['numberstudents'] = 'Anzahl Teilnehmende';
$string['examnumber'] = 'Anzahl Prüfungen';
$string['blocknumber'] = 'Anzahl Teiltermine';
$string['hour'] = ' Uhr';
$string['error_pastexamtime'] = 'Das Klausurtermin darf nicht in der Vergangenheit liegen.';
$string['setting_bordercolor_unconfirmed_dates'] = 'Rahmenfarbe 1';
$string['config_bordercolor_unconfirmed_dates'] = 'Rahmenfarbe für unbestätigte Termine in der Kalenderansicht der Prüfungstermin-Admins.';
$string['setting_bordercolor_unavailable_dates'] = 'Rahmenfarbe 2';
$string['config_bordercolor_unavailable_dates'] = 'Rahmenfarbe für nicht verfügbare Termine in der Kalenderansicht des Prüfungsterminmanagers.';
$string['config_holidays'] = 'Jede Zeile konfiguriert einen Feiertag. In jeder Zeile steht zuerst ein Datum (z. B. \'01.05.2023\'), gefolgt vom Namen des Feiertags (z.B. \'Tag der Arbeit\'), getrennt durch einen senkrechten Strich.';
$string['holidays'] ='Konfiguration der Feiertage';
$string['modal_title_weekend_not_available'] = 'Wochenende';
$string['modal_body_weekend_not_available'] ='Wochenendtermine sind nicht möglich. Bitte suchen Sie sich einen anderen Termin!';
$string['modal_title_holiday_not_available'] = 'Feiertag';
$string['modal_body_holiday_not_available'] ='Termine an Feiertagen sind nicht möglich. Bitte suchen Sie sich einen anderen Termin!';
$string['modal_title_past_not_available'] = 'Vergangenheit';
$string['modal_body_past_not_available'] ='Termine in der Vergangenheit sind nicht möglich. Bitte suchen Sie sich einen anderen Termin!';
$string['exam_dates_confirmed_start_date'] = 'Startdatum';
$string['exam_dates_confirmed_end_date'] = 'Enddatum';
$string['setting_examinercohorts'] = 'Globale Gruppen der Prüfer:in';
$string['config_examinercohorts'] = 'Nutzer die in diesen Globale Gruppen sind, stehen in der Auswahl der Prüfer:in des Prüfungsterminformulars zur Verfügung.';
$string['setting_instanceofmodelediachecklist'] = 'Aktivität eLeDia Checklist';
$string['config_instanceofmodelediachecklist'] = 'Auswahl einer Instanz der Aktivität eLeDia Checklist, die als Checkliste verlinkt ist.';
$string['setting_instanceofmodproblemdb'] = 'Aktivität Problemdatenbank';
$string['config_instanceofmodproblemdb'] = 'Auswahl einer Instanz der Aktivität Datenbank, die als Problemdatenbank verlinkt ist.';