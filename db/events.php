<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Event subscriptions for local_h5pchapter.
 *
 * @package    local_h5pchapter
 * @copyright  2026 Matheus Mathias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname'   => '\core\event\course_module_created',
        'callback'    => 'local_h5pchapter\observer::course_module_created',
    ],
    [
        'eventname'   => '\core\event\course_module_updated',
        'callback'    => 'local_h5pchapter\observer::course_module_updated',
    ],
    [
        'eventname'   => '\core\event\course_module_deleted',
        'callback'    => 'local_h5pchapter\observer::course_module_deleted',
    ],
];
