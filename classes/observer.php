<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_h5pchapter;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for local_h5pchapter.
 *
 * @package    local_h5pchapter
 * @copyright  2026 Matheus Mathias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * Triggered when a course module is created.
     *
     * @param \core\event\course_module_created $event
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        self::save_settings($event);
    }

    /**
     * Triggered when a course module is updated.
     *
     * @param \core\event\course_module_updated $event
     */
    public static function course_module_updated(\core\event\course_module_updated $event) {
        self::save_settings($event);
    }

    /**
     * Triggered when a course module is deleted.
     *
     * @param \core\event\course_module_deleted $event
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        global $DB;
        
        $cmid = $event->objectid;
        
        // Verify it's an h5pactivity before deleting to save queries, 
        // though deleting indiscriminately by cmid is also safe.
        $DB->delete_records('local_h5pchapter_settings', ['cmid' => $cmid]);
    }

    /**
     * Process and save the settings when a module is saved.
     *
     * @param \core\event\base $event
     */
    protected static function save_settings($event) {
        global $DB;

        $cmid = $event->objectid;
        $modulename = $event->other['modulename'] ?? '';

        // We only care about h5pactivity modules.
        if ($modulename !== 'h5pactivity') {
            return;
        }

        // Retrieve submitted data from the form.
        // We use optional_param to catch the data from the POST request.
        $chaptertarget = optional_param('chapter_target', null, PARAM_TEXT);
        $blocknavigation = optional_param('block_navigation', null, PARAM_BOOL);

        // If data is not present in the request (e.g. background tasks, restorations), skip.
        if ($chaptertarget === null && $blocknavigation === null) {
            return;
        }

        $record = $DB->get_record('local_h5pchapter_settings', ['cmid' => $cmid]);
        $now = time();

        if ($record) {
            $record->chapter_target = $chaptertarget;
            $record->block_navigation = $blocknavigation ? 1 : 0;
            $record->timemodified = $now;
            $DB->update_record('local_h5pchapter_settings', $record);
        } else {
            $record = new \stdClass();
            $record->cmid = $cmid;
            $record->chapter_target = $chaptertarget;
            $record->block_navigation = $blocknavigation ? 1 : 0;
            $record->timecreated = $now;
            $record->timemodified = $now;
            $DB->insert_record('local_h5pchapter_settings', $record);
        }
    }
}
