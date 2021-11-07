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
 * Block eledia_adminexamdates is defined here.
 *
 * @package     block_eledia_adminexamdates
 * @copyright   2021 René Hansen <support@eledia.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * eledia_adminexamdates block.
 *
 * @package    block_eledia_adminexamdates
 * @copyright  2021 René Hansen <support@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_eledia_adminexamdates extends block_base
{

    /**
     * Initializes class member variables.
     */
    public function init()
    {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_eledia_adminexamdates');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content()
    {
        $context = context_system::instance();

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $text = '';
            if (has_capability('block/eledia_adminexamdates:view', $context)) {
                $strexamdatesunconfirmedbutton = get_string('examdatesunconfirmed', 'block_eledia_adminexamdates');
                $examdatesunconfirmedurl = new \moodle_url('/blocks/eledia_adminexamdates/examdatesunconfirmed.php');
                $text .= html_writer::link($examdatesunconfirmedurl, $strexamdatesunconfirmedbutton, array('class' => 'btn btn-primary w-100 mb-2'));
            }
        }
        $this->content->text = $text;


        return $this->content;
    }

    public function has_config()
    {
        return true;
    }

}
