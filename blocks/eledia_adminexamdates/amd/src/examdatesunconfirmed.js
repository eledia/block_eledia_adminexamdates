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
 * @copyright  2022 René Hansen <support@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// import ModalFactory from "../../../../lib/amd/src/modal_factory";
//
// define(['jquery', 'core/str', 'core/modal_factory'], function ($, String, ModalFactory) {
//
//     $('.annotation-text-link').on('click', function (e) {
//
//         ModalFactory.create(
//             {
//                 type: ModalFactory.types.DEFAULT,
//                 title: String.get_string('annotationtext', 'block_eledia_adminexamdates'),
//                 body: '',
//             })
//             .then(modal => {
//                 modal.show();
//                 return modal;
//             });
//     });
// });

define(['jquery', 'core/str', 'core/modal_factory', 'core/notification'], function ($, str, ModalFactory, Notification) {

    return /** @alias module:block_eledia_adminexamdates/examdatesunconfirmed */ {

        /**
         * Prepares a modal for annotation text.
         *
         * @method annotationText
         */
        annotationText: function () {

            str.get_string('annotationtext', 'block_eledia_adminexamdates').done(
                function (annotationtext) {
                    $('[data-annotation-text]').on('click', function (e) {
                        e.preventDefault();
                        var clickedLink = $(e.currentTarget);
                        ModalFactory.create({
                            title: annotationtext + ' - ' +
                                clickedLink.data("annotation-text").examname,
                            body: clickedLink.data("annotation-text").text.replace(/(?:\r\n|\r|\n)/g, "<br>"),
                        })
                            .then(function (modal) {
                                modal.show();
                            });
                    });
                }).fail(Notification.exception);
            // var atext = $('[data-annotation-text]');
            // str.get_string('annotationtext', 'block_eledia_adminexamdates').then(function(langString) {
            //     return ModalFactory.create({
            //         title: langString + ' - '+ atext.data("annotation-text").examname,
            //         body: atext.data("annotation-text").text.replace(/(?:\r\n|\r|\n)/g, "<br>"),
            //     }, atext).done(function(modal) {
            //         modal.getRoot().find('a').on('click', function () {
            //             modal.hide();
            //         });
            //     });
            // }).catch(Notification.exception);
        }
    };
});
