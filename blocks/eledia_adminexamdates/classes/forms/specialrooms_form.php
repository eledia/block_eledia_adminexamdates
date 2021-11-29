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
 * The special room form.
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

class specialrooms_form extends \moodleform
{

    public function definition()
    {

        $hasconfirmexamdatescap = has_capability('block/eledia_adminexamdates:confirmexamdates', \context_system::instance());

        $mform =& $this->_form;

        $options = [];
        $rooms = preg_split('/\r\n|\r|\n/', get_config('block_eledia_adminexamdates', 'examrooms'));
        foreach ($rooms as $room) {
            $roomitems = explode('|', $room);
            $roomcapacity = !empty($roomitems[2]) ? ' (max. ' . $roomitems[2] . ' TN)' : '';
            if (empty($roomcapacity)) {
                $options[$roomitems[0]] = $roomitems[1] . $roomcapacity;
            }
        };
        $settings = array('multiple' => 'multiple');

        $mform->addElement('select', 'specialrooms',
                get_string('select_specialroom', 'block_eledia_adminexamdates'), $options, $settings);
        $mform->addRule('specialrooms', null, 'required');
        $mform->setType('specialrooms', PARAM_RAW);

        $mform->addElement('date_time_selector', 'booktimestart', get_string('booktimestart', 'block_eledia_adminexamdates'));
        $mform->addRule('booktimestart', null, 'required', null, 'client');

        $mform->addElement('text', 'bookduration', get_string('bookduration', 'block_eledia_adminexamdates'));
        $mform->setType('bookduration', PARAM_INT);
        $mform->addRule('bookduration', null, 'required', null, 'client');
        $mform->addRule('bookduration', null, 'numeric', null, 'client');

        $mform->addElement('textarea', 'annotationtext', get_string('annotationtext', 'block_eledia_adminexamdates'), array('rows' => 10, 'cols' => 80));
        $mform->setType('annotationtext', PARAM_RAW);

        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    public function validation($data, $files)
    {

        $errors = parent::validation($data, $files);
        if ($error = \block_eledia_adminexamdates\util::hasfreetimeslots($data)) {
            $errors['booktimestart'] = $error;
        }

        return $errors;
    }
}

