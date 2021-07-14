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
 * Prints an instance of mod_discourse.
 *
 * @package     mod_discourse
 * @copyright   2021 coactum GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_discourse\output\discourse_view;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot . '/mod/discourse/locallib.php');

// Course_module ID.
$id = optional_param('id', null, PARAM_INT);

// Module instance ID as alternative.
$d  = optional_param('d', null, PARAM_INT);

// New phase that discourse should switch to.
$newphase  = optional_param('newphase', null, PARAM_INT);

$discourse = discourse::get_discourse_instance($id, $d);

$moduleinstance = $discourse->get_module_instance();
$course = $discourse->get_course();
$context = $discourse->get_context();
$cm = $discourse->get_course_module();

require_login($course, true, $cm);

$event = \mod_discourse\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $context
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('discourse', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/discourse/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->force_settings_menu();

$navbar = $PAGE->navbar->add(get_string('view', 'mod_discourse'), $PAGE->url);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulename', 'mod_discourse').': ' . format_string($moduleinstance->name), 3);

if ($moduleinstance->intro) {
    echo $OUTPUT->box(format_module_intro('discourse', $moduleinstance, $cm->id), 'generalbox mod_introbox', 'newmoduleintro');
}

if (isset($newphase)) {
    global $DB;
    switch ($newphase) {
        case 1:
            $moduleinstance->activephase = 1;
            $activephaseone = true;
            $activephasetwo = false;
            $activephasethree = false;
            $activephasefour = false;
            break;
        case 2:
            $moduleinstance->activephase = 2;
            $activephaseone = false;
            $activephasetwo = true;
            $activephasethree = false;
            $activephasefour = false;
            break;
        case 3:
            $moduleinstance->activephase = 3;
            $activephaseone = false;
            $activephasetwo = false;
            $activephasethree = true;
            $activephasefour = false;
            break;
        case 4:
            $moduleinstance->activephase = 4;
            $activephaseone = false;
            $activephasetwo = false;
            $activephasethree = false;
            $activephasefour = true;
            break;
        default:
            $activephaseone = true;
            $activephasetwo = false;
            $activephasethree = false;
            $activephasefour = false;
            break;
    }

    $DB->update_record('discourse', $moduleinstance);
} else {
    switch ($moduleinstance->activephase) {
        case 1:
            $activephaseone = true;
            $activephasetwo = false;
            $activephasethree = false;
            $activephasefour = false;
            break;
        case 2:
            $activephaseone = false;
            $activephasetwo = true;
            $activephasethree = false;
            $activephasefour = false;
            break;
        case 3:
            $activephaseone = false;
            $activephasetwo = false;
            $activephasethree = true;
            $activephasefour = false;
            break;
        case 4:
            $activephaseone = false;
            $activephasetwo = false;
            $activephasethree = false;
            $activephasefour = true;
            break;
        default:
            $activephaseone = true;
            $activephasetwo = false;
            $activephasethree = false;
            $activephasefour = false;
            break;
    }
}

$caneditphase = has_capability('mod/discourse:editphase', $context);
$canswitchphase = has_capability('mod/discourse:switchphase', $context);
$canviewgroupparticipants = has_capability('mod/discourse:viewgroupparticipants', $context);

if (has_capability('mod/discourse:viewallgroups', $context) || groups_get_activity_groupmode($cm, $course) == 2) {
    $canviewallgroups = true;
} else {
    $canviewallgroups = false;
}

if (time() > $moduleinstance->deadlinephasetwo && $moduleinstance->activephase == 1) {
    $shouldswitchphase = 2;
} else if (time() > $moduleinstance->deadlinephasethree && $moduleinstance->activephase == 2) {
    $shouldswitchphase = 3;
} else if (time() > $moduleinstance->deadlinephasefour && $moduleinstance->activephase == 3) {
    $shouldswitchphase = 4;
} else {
    $shouldswitchphase = false;
}

$page = new discourse_view($cm->id, $discourse->get_groups(), $moduleinstance->autoswitch, $activephaseone, $activephasetwo,
    $activephasethree, $activephasefour, $moduleinstance->hintphaseone, $moduleinstance->hintphasetwo, $moduleinstance->hintphasethree,
    $moduleinstance->hintphasefour, $moduleinstance->deadlinephaseone, $moduleinstance->deadlinephasetwo, $moduleinstance->deadlinephasethree,
    $moduleinstance->deadlinephasefour, $caneditphase, $canswitchphase, $canviewallgroups, $canviewgroupparticipants, $shouldswitchphase);

echo $OUTPUT->render($page);

echo $OUTPUT->footer();
