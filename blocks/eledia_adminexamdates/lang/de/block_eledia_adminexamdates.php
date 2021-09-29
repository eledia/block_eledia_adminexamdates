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
$string['newexamdate'] = 'Neuer Termin';
$string['examdatesschedule'] = 'Prüfungsterminplanung';
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
$string['number_students'] ='Anzahl der Teilnehmer';
$string['department'] ='Fachbereich';
$string['examiner'] ='Dozent/ Prüfer';
$string['contactperson'] ='Ansprechpartner';
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











