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
 * @package    block_eledia_adminexamdates
 * @copyright  2021 René Hansen <support@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

global $USER, $CFG, $PAGE, $OUTPUT, $DB;
require_once($CFG->libdir . '/filelib.php');
$urlparam= "&criteria[0][key]=id";
$urlparam .= "&criteria[0][value]=1";


$param = ['wsfunction' => 'core_user_get_users'];
$curl = new \curl();
//$param['wstoken'] = '8bfd98a63773edc933a066992bbf0c1f';
$response = $curl->post('https://one-training-suite.com/webservice/rest/server.php?moodlewsrestformat=json' . $urlparam, $param);
$results = json_decode($response);
print_r('####$response:');
print_r($response);
print_r('####$results:');
print_r($results);