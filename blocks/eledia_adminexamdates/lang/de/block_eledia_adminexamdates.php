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
$string['examtimestart'] = 'Prüfungsbeginn';
$string['examduration'] = 'Klausur-Schreibzeit (Minuten)';
$string['examname'] = 'Klausurbezeichnung';
$string['examdaterequester'] = 'Beantragung';
$string['timecreated'] = 'Erstellt';
$string['confirmed'] = 'Bestätigt';
$string['editexamdate'] = 'Bearbeiten';
$string['cancelexamdate'] = 'Stornieren';
$string['confirmexamdate'] = 'Bestätigen';
$string['confirmexamdatemsg'] = 'Wollen Sie die den Prüfungstermin bestätigen für: \'{$a->name}\'?';
$string['cancelexamdatemsg'] = 'Wollen Sie die den Prüfungstermin stornieren für: \'{$a->name}\'?';
$string['configure_description'] = 'Hier können Sie die Prüfungstermin-Verwaltung konfigurieren.';
$string['number_students'] ='Anzahl der Teilnehmenden';
$string['department'] ='Fachbereich';
$string['examiner'] ='Dozent/ Prüfer';
$string['contactperson'] ='Ansprechpartner';
$string['contactpersonemail'] ='E-Mail des Ansprechpartners';
$string['responsibleperson'] ='SCL Verantwortlicher';
$string['examrooms_default'] = 'PR1|Prüfungsraum 1|100
PR2|Prüfungsraum 2|100
AB|Administrationsbüro|0
ER|Endabnahmeraum|0';
$string['config_examrooms'] = 'Jede Zeile konfiguriert einen eigenen Prüfungsraum. In jeder Zeile steht zunächst eine eindeutige Raum-ID (z.B. \'PR1\'), dann der Name des Raumes (z.B. \'Prüfungsraum 1\') sowie die Raumkapazität, also die maximale Teilnehmerzahl (z.B. \'100\'), getrennt durch jeweils einen senkrechten Strich.';
$string['examrooms'] ='Konfiguration der Prüfungsräume';
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
$string['setting_examcoursetemplateidnumber'] = 'Kurs-ID der Klausur-Kursvorlage';
$string['config_examcoursetemplateidnumber'] = 'Diese Kurs-ID sollte in der Klausur-Kursvorlage des Prüfungssystems gesetzt sein.';
$string['calendar_btn'] = 'Prüfungstermin-Kalender';
$string['unconfirmed_btn'] = 'Unbestätigte Prüfungstermine';
$string['setting_startexam'] = 'Frühester E-Klausur Beginn (nur volle Stunden)';
$string['setting_endexam'] = 'Spätestens E-Klausur Ende (nur volle Stunden)';
$string['setting_breakbetweenblockdates'] = 'Pause zwischen zwei Blockterminen (in Minuten)';
$string['setting_distancebetweenblockdates'] = 'Abstand zwischen Blöcken (in Minuten)';
$string['editsingleexamdate'] = 'Einzeltermine';
$string['singleexamdate_header'] = 'Einzeltermin Planen';
$string['newsingleexamdate'] = 'Neuer Einzeltermin';
$string['examdateslist_btn'] = 'Prüfungstermin-Liste';
$string['tablehead_month'] = 'Monat';
$string['tablehead_date'] = 'Datum';
$string['tablehead_examname'] = 'Bezeichnung Klausur';
$string['tablehead_examiner'] = 'Prüfer/Dozent';
$string['tablehead_examroom'] = 'Prüfungsraum';
$string['tablehead_supervisor1'] = 'Betreuer 1';
$string['tablehead_supervisor2'] = 'Betreuer 2';
$string['tablehead_candidates'] = 'Prüflinge';
$string['tablehead_status'] = 'Status';
$string['tablehead_blockid'] = 'Einzeltermin ID';
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
$string['room_supervisor'] = 'Betreuer (Raum)';
$string['room_supervision'] = 'Klausuraufsicht (Raum)';
$string['checklistlink'] = '/mod/checklist/tabtermin.php?id=60&examid=';
$string['partialdate'] = 'Teiltermin';
$string['examdateedit'] = 'Prüfungstermin bearbeiten';
$string['status_confirmed'] = 'Bestätigt';
$string['status_unconfirmed'] = 'Beantragt';
$string['newpartialdate'] = 'Neuer Teiltermin';
$string['setting_emailexamteam'] = 'E-Mail des Prüfungsteams';
$string['change_request_btn'] = 'Änderungsanfrage';
$string['changerequest_header'] = 'Änderungsanfrage an das Prüfungsteam';
$string['changerequesttext'] = 'Eingabe der Änderungsanfrage';
$string['changerequest_header'] = 'Änderungsanfrage an das Prüfungsteam';
$string['send_email'] ='Sende E-Mail';
$string['examdaterooms'] ='Prüfungsräume';
$string['eledia_adminexamdates:addinstance'] = 'Neuen eLeDia E-Klausur Termin-Verwaltungs-Block hinzufügen';
$string['eledia_adminexamdates:myaddinstance'] = 'Neuen eLeDia E-Klausur Termin-Verwaltungs-Block zum Dashboard hinzufügen';
$string['eledia_adminexamdates:view'] = 'Anzeigen des eLeDia E-Klausur Termin-Verwaltungs-Block';
$string['eledia_adminexamdates:confirmexamdates'] = 'Bestätigen der E-Klausur Termine im eLeDia E-Klausur Termin-Verwaltungs-Block';







