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
 * Extra install steps
 *
 * @package   block_eledia_adminexamdates
 * @copyright 2018 Davo Smith, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extra installation steps.
 */
function xmldb_block_eledia_adminexamdates_install() {
    global $CFG, $DB;

    // This version includes the extended privacy API only found in M3.4.6, M3.5.3 and M3.6+.
    if ($CFG->version > 2018051700 && $CFG->version < 2018051703) {
        // Main version.php takes care of Moodle below 3.4.6.
        die('You must upgrade to Moodle 3.5.3 (or above) before installing this version of block_eledia_adminexamdates');
    }

    // Tab: Qualitaetsmanagement //.
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (2, 'Checkpunkte bei der Qualitätssicherung', 0, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (3, 'Kurs: Klausurbezeichnung gemäß Nomenklatur anpassen, Ersteller*innenrechte -> Prüfer*in/Bewerter*in', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (4, 'Testeinstellungen: Zeitbegrenzung, automatische Abgabe aktiviert, Überprüfungsoptionen deaktiviert', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (5, 'Probelauf: Grafiken dargestellt, Maximale Anzahl Fragen pro Seite.', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (6, 'Probelauf: Archiv erstellt und auf Vollständigkeit geprüft.', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (7, 'Checkpunkte vor und nach der Endabnahme', 0, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (8, 'Kurs für Endabnahme sichtbar geschaltet', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (9, 'Kursformat: Verborgene Abschnitte vollständig unsichtbar.', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (10, 'Testeinstellungen: Safeexambrowser aktivieren.', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (11, 'Testeinstellungen: 2 Versuche -> letzter Versuch -> basiert auf vorherigem -> 1 Versuch', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (12, 'Checkpunkte vor der Klausur', 0, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (13, 'Etiketten erzeugen', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (14, ?, 1, 'qm')", ['Prüfen: Nachteilsausgleich zu berücksichtigen?']);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (15, 'SEB-Konfiguration ausgewählt', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (16, 'Klausurrechner alle (!) hochgefahren', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (17, ?, 1, 'qm')", ['Prüfen: Lüftung funktioniert?, Willkommensbildschirm aktiviert']);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (18, 'Prüfungskurs in Aktuelle Klausuren verschieben', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (19, 'Kurs sichtbar schalten', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (20, 'Testeinstellungen: Gruppenvoraussetzungen festlegen', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (21, 'Test sichtbar -> Testlauf -> Test unsichtbar, Testversuche löschen', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (22, 'Eingabegeräte freischalten', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (23, 'Checkpunkte bei jeder Gruppe', 0, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (24, ?, 1, 'qm')", ['Prüfen: Etiketten liegen bereit?']);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (25, 'Eingabegeräte deaktivieren', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (26, 'Eingabegeräte freischalten', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (27, 'Nachteilsausgleiche nach TN-Login einrichten', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (28, 'Test sichtbar schalten', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (29, 'Teilnehmerzahlen im Formular eingetragen, TN-Liste erstellen mit TN-Zahl abgleichen', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (30, ?, 1, 'qm')", ['Prüfen: Alles abgesendet (?) in Logodidact prüfen, Ergebnisse in Bewertungsübersicht vorhanden? Anzahl abgleichen']);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (31, 'Bei Dateiablage oder Popup-Ressourcen Reboot der Clients zwischen den Gruppen', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (32, 'Checkpunkte direkt nach der Klausur', 0, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (33, ?, 1, 'qm')", ['Archive vorhanden? -> signieren']);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (34, 'Teilnehmer*innen stilllegen.', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (35, 'Excel-Export und Archive für Prüfer bereitstellen und diese informieren', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (36, 'Klausurkurs und Klausur unsichtbar machen und in Bereich Prüfungsumgebung verschieben', 1, 'qm')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (37, 'Kurssicherung erstellen', 1, 'qm')", null);


    // Tab: Endabnahme //.
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (38, 'Während der Testklausur mit Dummy', 0, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (39, 'Name, Vorname und Matrikelnummer erscheinen im Bildschirmkopf', 1, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (40, 'Im SEB funktionieren Pop-Ups (PDF, Bild, Video) und Anwendungen (Taschenrechner usw.).', 1, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (41, 'Restzeitangabe funktioniert.', 1, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (42, 'Die Klausur wurde während der Endabnahme mit prüfungsnahen Eingaben und Prozeduren getestet.', 1, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (43, 'Bei Zufallsauswahl der Fragen wurden alle Fragen in einer eigenständigen Testklausur erprobt.', 1, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (44, 'Nach der Testklausur mit Dummy', 0, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (45, 'Bewertungen werden im Ergebnisbericht dargestellt.', 1, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (46, 'Wenn erforderlich: Manuelle Bewertungen wurden getestet.', 1, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (47, 'Es wurde ein für die Klausureinsicht vollständiges Archiv erstellt.', 1, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (48, 'Bestätigungen', 0, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (49, 'Das Fachgebiet ist über das Verfahren bei Bewertungsänderungen (Korrektur bei Einzelpersonen, Nachbewertung von Aufgaben für die ganze Klausur) informiert.', 1, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (50, 'Bestätigung: Das Fachgebiet übersendet dem E-Klausurteam den verwendeten Notenschlüssel.', 1, 'ea')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_my_itm} VALUES (51, 'Die rechtlichen Vorgaben zur Durchführung dieser E-Klausur wurden beachtet.', 1, 'ea')", null);


    // Tab: Termincheckliste //.
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (1, 1, 0, 'Ersteller: innenzugriff vorhanden', 1, 0, 2, -60, 0, 'black', 0, 0, 0, NULL, '', 0, 'Ersteller: innenzugriff vorhanden {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (2, 1, 0, 'Bereitstellung der Termincheckliste', 2, 0, 0, -34, 0, 'black', 0, 0, 0, NULL, '', 0, 'Bereitstellung der Termincheckliste {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (3, 1, 0, 'Prüfungskonfiguration beschrieben', 3, 0, 0, -13, 0, 'black', 0, 0, 0, NULL, '', 0, 'Prüfungskonfiguration beschrieben {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (4, 1, 0, 'Prüfungsimage vorbereitet', 4, 0, 0, -11, 0, 'black', 0, 0, 0, NULL, '', 0, 'Prüfungsimage vorbereitet {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (5, 1, 0, 'Funktionstest der Klausur durchgeführt', 5, 0, 0, -8, 0, 'black', 0, 0, 0, NULL, '', 0, 'Funktionstest der Klausur durchgeführt {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (6, 1, 0, 'Qualitätskontrolle durchgeführt', 6, 0, 0, -7, 0, 'red', 0, 0, 0, NULL, '', 0, 'Qualitätskontrolle durchgeführt {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (7, 1, 0, 'Endabnahme', 7, 0, 0, -5, 0, 'black', 0, 0, 0, NULL, '', 0, 'Endabnahme {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (8, 1, 0, 'HIS-Liste an E-Klausur-Team', 8, 0, 0, -5, 0, 'black', 0, 0, 0, NULL, '', 0, 'HIS-Liste an E-Klausur-Team {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (9, 1, 0, 'Gruppeneinteilung abgeschlossen', 9, 0, 0, -4, 0, 'black', 0, 0, 0, NULL, '', 0, 'Gruppeneinteilung abgeschlossen {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (10, 1, 0, 'Namen der Aufsichtspersonen', 10, 0, 0, -3, 0, 'black', 0, 0, 0, NULL, '', 0, 'Namen der Aufsichtspersonen {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (11, 1, 0, 'Importlisten vorbereitet', 11, 0, 0, -1, 0, 'black', 0, 0, 0, NULL, '', 0, 'Importlisten vorbereitet {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (12, 1, 0, 'TN-Import und Etikettenerstellungexam date', 12, 0, 0, 0, 0, 'black', 0, 0, 0, NULL, '', 0, 'TN-Import und Etikettenerstellungexam date {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (13, 1, 0, 'Problemfälle bearbeitet', 13, 0, 0, 2, 0, 'black', 0, 0, 0, NULL, '', 0, 'Problemfälle bearbeitet {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (15, 1, 0, 'Mitteilung über abgeschlossene Klausureinsicht', 14, 0, 0, 100, 0, 'black', 0, 0, 0, NULL, '', 0, 'Mitteilung über abgeschlossene Klausureinsicht {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (16, 1, 0, 'Zweitarchivierung und –signierung bei Änderungen', 15, 0, 0, 100, 0, 'black', 0, 0, 0, NULL, '', 0, 'Zweitarchivierung und –signierung bei Änderungen {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (17, 1, 0, 'Notenschlüssel bereitgestellt', 16, 0, 0, 100, 0, 'black', 0, 0, 0, NULL, '', 0, 'Notenschlüssel bereitgestellt {Datum}')", null);
    $DB->execute("INSERT INTO {eledia_adminexamdates_itm} VALUES (18, 1, 0, 'Klausur abgeschlossen', 17, 0, 0, 101, 0, ' 990', 0, 0, 0, NULL, '', 0, 'Klausur abgeschlossen {Datum}')", null);
}
