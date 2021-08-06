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
 * Plugin strings are defined here.
 *
 * @package     mod_discourse
 * @category    string
 * @copyright   2021 coactum GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Discourse';

// mod_form.php
$string['modulename'] = 'Discourse';
$string['modulename_help'] = 'The discourse activity allows group discussions ... ';
$string['pluginadministration'] = 'Administration of discourse';
$string['phasecompletion'] = 'Phase completion';
$string['usedeadlines'] = 'Use deadlines for phases';
$string['modeautoswitch'] = 'Activate mode "Automatic phase switch"';
$string['autoswitch'] = 'The phases are changed automatically at the times specified below';
$string['deadlinephaseone'] = 'Completion of the solo phase';
$string['deadlinephasetwo'] = 'Completion of the 1st group phase';
$string['deadlinephasethree'] = 'Completion of the 2nd group phase';
$string['deadlinephasefour'] = 'Completion of the collaborative phase';
$string['phaseshints'] = 'Hints for the phases';
$string['hintphaseone'] = 'Note on the solo phase';
$string['hintphasetwo'] = 'Note on the 1st group phase';
$string['hintphasethree'] = 'Note on the 2nd group phase';
$string['hintphasefour'] = 'Note on the collaborative phase';

// index.php
$string['modulenameplural'] = 'Discourses';
$string['nonewmodules'] = 'No new modules';

// lib.php
$string['deletealluserdata'] = 'Delete all user data';
$string['resetting_data'] = 'Participants and submissions deleted';

// locallib.php
$string['groupingdescription'] = 'Grouping for discourse activity {$a}';
$string['phaseone'] = 'Solo phase';
$string['phasetwo'] = '1st group phase';
$string['phasethree'] = '2nd group phase';
$string['phasefour'] = 'Collaborative phas';
$string['groupfor'] = 'Group for {$a}';
$string['group'] = 'Group';

// discourse_view.mustache
$string['activephase'] = 'Active phase';
$string['switchto'] = 'Switch to';
$string['hint'] = 'Hint';
$string['sheduledstart'] = 'Sheduled start';
$string['toggleallgroups'] = 'Toggle all groups in phase {$a}';
$string['opengroup'] = 'Maximize group {$a}';
$string['closegroup'] = 'Minimize group {$a}';
$string['groupparticipants'] = 'Participants';
$string['submission'] = 'Submission';
$string['nosubmission'] = 'No submission yet';
$string['submissionreceived'] = 'Submission handed in';
$string['nogroups'] = 'No groups available';
$string['noautoswitch'] = 'The automatic phase switch is deactivated. The phases must therefore be changed manually.';
$string['shouldswitchphaseto'] = 'The automatic phase switch is deactivated. The next phase should be activated by now.';

// view.php
$string['view'] = 'Overview';

// groupview.php
$string['groupview'] = 'Group view';
$string['submissionstate'] = 'State';
$string['submittet'] = 'Submittet at';
$string['notsubmittet'] = 'Not submittet yet';
$string['updated'] = 'Updated at';
$string['submitsubmission'] = 'Hand in submission';
$string['editsubmission'] = 'Edit submission';
$string['currentversion'] = 'Current version';
$string['submissioncontent'] = 'Content of submission';
$string['errfilloutfield'] = 'Please fill out this field';

// capabilities
$string['discourse:addinstance'] = 'Add new discourse';
$string['discourse:viewdiscourseteacher'] = 'View disocurse as teacher';
$string['discourse:viewdiscoursestudent'] = 'View disocurse as student';
$string['discourse:editsubmission'] = 'Submit or edit group text';
$string['discourse:editphase'] = 'Edit phase information';
$string['discourse:switchphase'] = 'Toogle discourse phase';
$string['discourse:viewallgroups'] = 'View all groups';
$string['discourse:viewgroupparticipants'] = 'View group participants';

// errors
$string['groupinvalid'] = 'Group not found';
$string['useridinvalid'] = 'User ID invalid';
$string['nogroupmember'] = 'Not possible because not a group member';
$string['submissionfaileddoubled'] = 'Submission failed. Another group member has already made a submission recently.';
$string['groupingmaybedeleted'] = 'The grouping of the discourse was deleted or is invalid. All course groups (from other discourses or activities as well) will be displayed.';

// task
$string['task_switchphases'] = 'Automatic phase switch';

// privacy
$string['privacy:metadata:discourse_participants'] = 'Contains the groups of all discourse participants.';
$string['privacy:metadata:discourse_submissions'] = 'Contains data of all discourse submissions.';
$string['privacy:metadata:discourse_participants:userid'] = 'User ID of the participant';
$string['privacy:metadata:discourse_participants:discourse'] = 'ID of the discourse of the participant';
$string['privacy:metadata:discourse_participants:groupids'] = 'IDs of the discourse groups of the participant';
$string['privacy:metadata:discourse_submissions:discourse'] = 'ID of the discourse in which the submission was made';
$string['privacy:metadata:discourse_submissions:groupid'] = 'ID of the group from which the submission was made';
$string['privacy:metadata:discourse_submissions:submission'] = 'Submission content';
$string['privacy:metadata:discourse_submissions:currentversion'] = 'Current version of the submission';
$string['privacy:metadata:discourse_submissions:format'] = 'Submission format';
$string['privacy:metadata:discourse_submissions:timecreated'] = 'Date on which the submission was made';
$string['privacy:metadata:discourse_submissions:timemodified'] = 'Time of the last revision of the submission';
// $string['privacy:metadata:core_message'] = 'The discourse plugin sends messages to users and saves their content in the database.';
