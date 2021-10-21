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
 * This file keeps track of upgrades to the eledia_adminexamdates block
 *
 * @package    block_eledia_adminexamdates
 * @copyright  2021 René Hansen <support@eledia.de>
 * @copyright  2013 eLeDia GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * @param int $oldversion
 * @param object $block
 * @return bool
 */
function xmldb_block_eledia_adminexamdates_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2021102100) {

        // Define field responsibleperson to be added to eledia_adminexamdates.
        $table = new xmldb_table('eledia_adminexamdates');
        $field = new xmldb_field('responsibleperson', XMLDB_TYPE_CHAR, '300', null, XMLDB_NOTNULL, null, null, 'contactperson');

        // Conditionally launch add field responsibleperson.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eledia_adminexamdates savepoint reached.
        upgrade_block_savepoint(true, 2021102100, 'eledia_adminexamdates');
    }

    return true;
}