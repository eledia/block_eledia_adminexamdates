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
 * The exam date calendar form.
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

class calendar_form extends \moodleform
{

    public function definition()
    {
        $mform =& $this->_form;

    /*    $years = [];
        $yearnow = date('Y');
        for ($i = date('Y', strtotime('-10 years')); $i <= date('Y', strtotime('+10 years')); $i++) {
            $years[$i] = $i;
        }
        $months = [];
        $monthnow = date('m');
        for ($i = 1 ; $i <= 12 ; $i++){
            $months[$i] =  utf8_encode(strftime('%B', mktime(0, 0, 0, $i)));
        }

        $mform->addElement('select', 'month',
            get_string('config_select_calendar_month', 'block_eledia_adminexamdates'), $months);
        $mform->setDefault('month', $monthnow);

        $mform->addElement('select', 'year',
            get_string('config_select_calendar_year', 'block_eledia_adminexamdates'), $years);
        $mform->setDefault('year', $yearnow);*/
        $mform->addElement('date_selector', 'selectdisplaydate', get_string('calendar_date', 'block_eledia_adminexamdates'));
        $mform->setDefault('selectdisplaydate', time());
        $mform->setType('selectdisplaydate', PARAM_INT);
       // $mform->setDefault('date', $yearnow)
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('choose'));
        //$buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
       //$mform->closeHeaderBefore('buttonar');
    }
}