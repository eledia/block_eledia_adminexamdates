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
 * The room config form.
 *
 * @package    block_eledia_adminexamdates
 * @author     René Hansen <support@eledia.de>
 * @copyright  2022 eLeDia GmbH
 */

namespace block_eledia_adminexamdates\forms;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->libdir . '/formslib.php');

class roomconfig_form extends \moodleform {

    public function definition() {

        $mform =& $this->_form;
        $isadding = $this->_customdata['isadding'];

        $mform->addElement('text', 'roomid', get_string('roomconfig_roomid', 'block_eledia_adminexamdates'), array('size' => 8));
        $mform->setType('roomid', PARAM_TEXT);
        $mform->addHelpButton('roomid', 'roomconfig_roomid', 'block_eledia_adminexamdates');
        $mform->addRule('roomid', null, 'required');

        $mform->addElement('text', 'name', get_string('roomconfig_name', 'block_eledia_adminexamdates'), array('size' => 30));
        $mform->setType('name', PARAM_TEXT);
        $mform->addHelpButton('name', 'roomconfig_name', 'block_eledia_adminexamdates');
        $mform->addRule('name', null, 'required');

        $mform->addElement('advcheckbox', 'specialroom', get_string('roomconfig_specialroom', 'block_eledia_adminexamdates'));
        $mform->addHelpButton('specialroom', 'roomconfig_specialroom', 'block_eledia_adminexamdates');

        $mform->addElement('text', 'capacity', get_string('roomconfig_capacity', 'block_eledia_adminexamdates'), array('size' => 8));
        $mform->addHelpButton('capacity', 'roomconfig_capacity', 'block_eledia_adminexamdates');
        $mform->setType('capacity', PARAM_INT);
        $mform->addRule('capacity', null, 'numeric', null, 'client');
        $mform->hideIf('capacity','specialroom', 'checked');

        $mform->addElement('text', 'color', get_string('roomconfig_color', 'block_eledia_adminexamdates'), array('size' => 8));
        $mform->setType('color', PARAM_TEXT);
        $mform->addHelpButton('color', 'roomconfig_color', 'block_eledia_adminexamdates');
        $mform->addRule('color', null, 'required');

        $submitlabel = ($isadding) ? get_string('addnewroomconfig', 'block_eledia_adminexamdates') : get_string('submit');

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}

