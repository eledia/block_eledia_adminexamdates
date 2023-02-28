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

    if ($oldversion < 2021110300) {

        // Define field contactpersonemail to be added to eledia_adminexamdates.
        $table = new xmldb_table('eledia_adminexamdates');
        $field = new xmldb_field('contactpersonemail', XMLDB_TYPE_CHAR, '300', null, null, null, null, 'contactperson');

        // Conditionally launch add field contactpersonemail.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eledia_adminexamdates savepoint reached.
        upgrade_block_savepoint(true, 2021110300, 'eledia_adminexamdates');
    }

    if ($oldversion < 2021111600) {

        // Define field contactpersonid to be added to eledia_adminexamdates.
        $table = new xmldb_table('eledia_adminexamdates');
        $field = new xmldb_field('contactpersonid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'contactpersonemail');

        // Conditionally launch add field contactpersonid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eledia_adminexamdates savepoint reached.
        upgrade_block_savepoint(true, 2021111600, 'eledia_adminexamdates');
    }

    if ($oldversion < 2021112300) {

        // Define field contactpersonemail to be dropped from eledia_adminexamdates.
        $table = new xmldb_table('eledia_adminexamdates');
        $field = new xmldb_field('contactpersonemail');

        // Conditionally launch drop field contactpersonemail.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('contactpersonid');

        // Conditionally launch drop field contactpersonid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('category', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'annotationtext');

        // Conditionally launch add field category.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Eledia_adminexamdates savepoint reached.
        upgrade_block_savepoint(true, 2021112300, 'eledia_adminexamdates');
    }

    if ($oldversion < 2022111500) {

        // Changing nullability of field responsibleperson on table eledia_adminexamdates to null.
        $table = new xmldb_table('eledia_adminexamdates');
        $field = new xmldb_field('responsibleperson', XMLDB_TYPE_CHAR, '300', null, null, null, null, 'contactperson');

        // Launch change of nullability for field responsibleperson.
        $dbman->change_field_notnull($table, $field);

        // Eledia_adminexamdates savepoint reached.
        upgrade_block_savepoint(true, 2022111500, 'eledia_adminexamdates');
    }

    if ($oldversion < 2023022700) {
        $checklisttable = new xmldb_table('elediachecklist_check');
        $table = new xmldb_table('eledia_adminexamdates_chk');
        if ($dbman->table_exists($checklisttable)) {
            $dbman->rename_table($checklisttable, 'eledia_adminexamdates_chk');
        } else if (!$dbman->table_exists($table)) {

            // Adding fields to table eledia_adminexamdates_chk.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('item', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('usertimestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('teachermark', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('teachertimestamp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('teacherid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

            // Adding keys to table eledia_adminexamdates_chk.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            // Adding indexes to table eledia_adminexamdates_chk.
            $table->add_index('item', XMLDB_INDEX_NOTUNIQUE, ['item']);
            $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);

            $dbman->create_table($table);
        }

        $checklisttable = new xmldb_table('elediachecklist_comment');
        $table = new xmldb_table('eledia_adminexamdates_cmt');
        if ($dbman->table_exists($checklisttable)) {
            $dbman->rename_table($checklisttable, 'eledia_adminexamdates_cmt');
        } else if (!$dbman->table_exists($table)) {

            // Adding fields to table eledia_adminexamdates_cmt.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('commentby', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('text', XMLDB_TYPE_TEXT, null, null, null, null, null);

            // Adding keys to table eledia_adminexamdates_cmt.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            // Adding indexes to table eledia_adminexamdates_cmt.
            $table->add_index('checklist_item_user', XMLDB_INDEX_UNIQUE, ['itemid', 'userid']);

            $dbman->create_table($table);
        }

        $checklisttable = new xmldb_table('elediachecklist_item');
        $table = new xmldb_table('eledia_adminexamdates_itm');
        if ($dbman->table_exists($checklisttable)) {
            $dbman->rename_table($checklisttable, 'eledia_adminexamdates_itm');
        } else if (!$dbman->table_exists($table)) {

            // Adding fields to table eledia_adminexamdates_itm.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('checklist', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('displaytext', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
            $table->add_field('position', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('indent', XMLDB_TYPE_INTEGER, '8', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('itemoptional', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('duetime', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('eventid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('colour', XMLDB_TYPE_CHAR, '15', null, XMLDB_NOTNULL, null, 'black');
            $table->add_field('moduleid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('hidden', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('groupingid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('linkcourseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('linkurl', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('openlinkinnewwindow', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('emailtext', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);

            // Adding keys to table eledia_adminexamdates_itm.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('linkcourseid', XMLDB_KEY_FOREIGN, ['linkcourseid'], 'course', ['id']);

            // Adding indexes to table eledia_adminexamdates_itm.
            $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
            $table->add_index('checklist', XMLDB_INDEX_NOTUNIQUE, ['checklist']);
            $table->add_index('item_module', XMLDB_INDEX_NOTUNIQUE, ['moduleid']);

            $dbman->create_table($table);
        }

        $checklisttable = new xmldb_table('elediachecklist_item_date');
        $table = new xmldb_table('eledia_adminexamdates_itm_d');
        if ($dbman->table_exists($checklisttable)) {
            $dbman->rename_table($checklisttable, 'eledia_adminexamdates_itm_d');
        } else if (!$dbman->table_exists($table)) {

            // Adding fields to table eledia_adminexamdates_itm_d.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('examid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('checkid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('checkdate', XMLDB_TYPE_INTEGER, '19', null, XMLDB_NOTNULL, null, null);

            // Adding keys to table eledia_adminexamdates_itm_d.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            $dbman->create_table($table);
        }

        $checklisttable = new xmldb_table('elediachecklist_my_check');
        $table = new xmldb_table('eledia_adminexamdates_my_chk');
        if ($dbman->table_exists($checklisttable)) {
            $dbman->rename_table($checklisttable, 'eledia_adminexamdates_my_chk');
        } else if (!$dbman->table_exists($table)) {
            // Adding fields to table eledia_adminexamdates_my_chk.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('id_item', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('id_checklist', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
            $table->add_field('id_exam', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

            // Adding keys to table eledia_adminexamdates_my_chk.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            $dbman->create_table($table);
        }

        $checklisttable = new xmldb_table('elediachecklist_my_item');
        $table = new xmldb_table('eledia_adminexamdates_my_itm');
        if ($dbman->table_exists($checklisttable)) {
            $dbman->rename_table($checklisttable, 'eledia_adminexamdates_my_itm');
        } else if (!$dbman->table_exists($table)) {

            // Adding fields to table eledia_adminexamdates_my_itm.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('displaytext', XMLDB_TYPE_CHAR, '250', null, null, null, null);
            $table->add_field('is_checkbox', XMLDB_TYPE_INTEGER, '3', null, null, null, '0');
            $table->add_field('type', XMLDB_TYPE_CHAR, '5', null, null, null, null);

            // Adding keys to table eledia_adminexamdates_my_itm.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            $dbman->create_table($table);
        }

    }

    return true;
}