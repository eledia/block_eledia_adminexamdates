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
 * editsingleexamdate - change room triggers to hide/show the input room group
 * @package    block_eledia_adminexamdates
 * @copyright  2021 René Hansen <support@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = () => {
    document.querySelectorAll('.form-check-input').forEach(item => {
        var itemid = item.id;
        var roomgroupid = itemid.replace(/blockexamroomscheck/i, "roomheader");
        var el = document.getElementById(roomgroupid);
        el.style.display = item.checked == 1 ? 'block' : 'none';
        el.classList.remove('collapsed');
        item.addEventListener('change', e => {
            var targetid = e.target.id;
            var roomgroupid = targetid.replace(/blockexamroomscheck/i, "roomheader");
            var el = document.getElementById(roomgroupid);
            el.style.display = e.target.checked == 1 ? 'block' : 'none';
            el.classList.remove('collapsed');
        });
    });

    var elements = document.getElementsByClassName("delsingleexamdate");
    var delbtn = document.getElementById("delsingleexamdatebtn");
    var submitFunction = function() {
        var attribute = this.getAttribute("data-examblockid");
        alert(attribute);
    };

    Array.from(elements).forEach(function(element) {
        element.addEventListener('click', submitFunction);
    });
};