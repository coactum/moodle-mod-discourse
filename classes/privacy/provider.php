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
 * Privacy subsystem implementation for discourse.
 *
 * @package    discourse
 * @copyright  2021 coactum GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_discourse\privacy;

use \core_privacy\local\request\userlist;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\approved_userlist;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\helper;
use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\transform;

defined('MOODLE_INTERNAL') || die();

/**
 * Implementation of the privacy subsystem plugin provider for the discourse activity module.
 *
 * @copyright  2021 coactum GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin currently implements the original plugin\provider interface.
    \core_privacy\local\request\plugin\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Returns meta data about this system.
     *
     * @param   collection     $items The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $items) : collection {

        // The table 'discourse_participants' stores all discourse participants and their data.
        $items->add_database_table('discourse_participants', [
            'discourse' => 'privacy:metadata:discourse_participants:discourse',
        ], 'privacy:metadata:discourse_participants');

        // The table 'discourse_submissions' stores all group subbissions.
        $items->add_database_table('discourse_submissions', [
            'discourse' => 'privacy:metadata:discourse_submissions:discourse',
        ], 'privacy:metadata:discourse_submissions');

        // The discourse uses the messages subsystem that saves personal data.
        $items->add_subsystem_link('core_message', [], 'privacy:metadata:core_message');

        // There are no user preferences in the discourse.

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * In this case of all discourses where a user is exam participant.
     *
     * @param   int         $userid     The user to search.
     * @return  contextlist $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : \core_privacy\local\request\contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();

        $params = [
            'modulename'       => 'discourse',
            'contextlevel'  => CONTEXT_MODULE,
            'userid'        => $userid,
        ];

        // Select discourses of user.

        $sql;
        /* $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {exammanagement} e ON e.id = cm.instance
                  JOIN {exammanagement_participants} p ON p.exammanagement = e.id
                  WHERE p.moodleuserid = :userid
        "; */

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        $event = \discourse\event\log_variable::create(['other' => 'get_users_in_context: ' . 'userlist' .json_encode($userlist) .'context' . json_encode($context)]);
        $event->trigger();

        if (!is_a($context, \context_module::class)) {
            return;
        }

        $params = [
            'instanceid'    => $context->id,
            'modulename'    => 'discourse',
        ];

        // Get users.
        $sql;
        /* $sql = "SELECT p.moodleuserid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {exammanagement} e ON e.id = cm.instance
                  JOIN {exammanagement_participants} p ON p.exammanagement = e.id
                 WHERE cm.id = :instanceid"; */
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        // Discourse participants data.
        $sql;
        /* $sql = "SELECT
                    cm.id AS cmid,
                    e.id AS exammanagement,
                    e.name,
                    e.timecreated,
                    e.timemodified,
                    p.moodleuserid AS moodleuserid,
                    p.login
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid
                  JOIN {exammanagement} e ON e.id = cm.instance
                  JOIN {exammanagement_participants} p ON p.exammanagement = e.id
                 WHERE (
                    p.moodleuserid = :userid AND
                    c.id {$contextsql}
                )
        "; */

        $params = $contextparams;
        $params['userid'] = $userid;

        $discourses = $DB->get_recordset_sql($sql, $params);

        if ($discourses->valid()) {
            foreach ($discourses as $discourse) {
                if ($discourse) {
                    $context = \context_module::instance($discourse->cmid);

                    if ($discourse->timemodified == 0) {
                        $discourse->timemodified = null;
                    } else {
                        $discourse->timemodified = \core_privacy\local\request\transform::datetime($discourse->timemodified);
                    }

                    $discoursedata = [
                        'id'       => $discourse->discourse,
                        'timecreated'   => \core_privacy\local\request\transform::datetime($discourse->timecreated),
                        'timemodified' => $discourse->timemodified,
                    ];

                    $discoursedata['user data:'] = [
                        'userid' => $discourse->userid,
                    ];

                    self::export_discourse_data_for_user($discoursedata, $context, [], $user);
                }
            }
        }

        $discourses->close();
    }

    /**
     * Export the supplied personal data for a single discourse activity, along with all generic data for the activity.
     *
     * @param array $discoursedata The personal data to export for the discourse activity.
     * @param \context_module $context The context of the discourse activity.
     * @param array $subcontext The location within the current context that this data belongs.
     * @param \stdClass $user the user record
     */
    protected static function export_discourse_data_for_user(array $discoursedata, \context_module $context, array $subcontext, \stdClass $user) {
        // Fetch the generic module data for the discourse activity.
        $contextdata = helper::get_context_data($context, $user);
        // Merge with discourse data and write it.
        $contextdata = (object)array_merge((array)$contextdata, $discoursedata);
        writer::with_context($context)->export_data($subcontext, $contextdata);
        // Write generic module intro files.
        helper::export_context_files($context, $user);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context                 $context   The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        // Check that this is a context_module.
        if (!$context instanceof \context_module) {
            return;
        }

        // Get the course module.
        if (!$cm = get_coursemodule_from_id('discourse', $context->instanceid)) {
            return;
        }

        // Delete all records.
        if ($DB->record_exists('discourse_participants', ['discourse' => $cm->instance])) {
            $DB->delete_records('discourse_participants', ['discourse' => $cm->instance]);
        }

        if ($DB->record_exists('discourse_submissions', ['discourse' => $cm->instance])) {
            $DB->delete_records('discourse_submissions', ['discourse' => $cm->instance]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {

        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            // Get the course module.
            $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);

            if ($DB->record_exists('discourse_participants', ['discourse' => $cm->instance, 'userid' => $userid])) {

                $DB->delete_records('discourse_participants', [
                    'discourse' => $cm->instance,
                    'userid' => $userid,
                ]);
            }
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);

        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge(['discourseid' => $cm->instance], $userinparams);

        if ($DB->record_exists_select('discourse_participants', "discourse = :discourseid AND userid {$userinsql}", $params)) {
            $DB->delete_records_select('discourse_participants', "discourse = :discourseid AND userid {$userinsql}", $params);
        }
    }
}