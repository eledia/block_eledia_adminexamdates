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
 * The exam date confirmed form.
 *
 * @package     block_eledia_adminexamdates
 * @copyright   2021 René Hansen <support@eledia.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_eledia_adminexamdates\forms;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->libdir . '/formslib.php');

class examdatesconfirmed_form extends \moodleform {

    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('date_selector', 'displaydatefrom',
                get_string('exam_dates_confirmed_start_date', 'block_eledia_adminexamdates'));
        $mform->setDefault('displaydatefrom', time());
        $mform->setType('displaydatefrom', PARAM_INT);

        $mform->addElement('date_selector', 'displaydateto',
                get_string('exam_dates_confirmed_end_date', 'block_eledia_adminexamdates'), ['optional' => true]);
        $mform->setDefault('displaydateto', time());
        $mform->setType('displaydateto', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('choose'));
        //$buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        //$mform->closeHeaderBefore('buttonar');
    }
}