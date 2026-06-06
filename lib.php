<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Library functions and callbacks for local_h5pchapter.
 *
 * @package    local_h5pchapter
 * @copyright  2026 Matheus Mathias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function local_h5pchapter_coursemodule_standard_elements($formwrapper, $mform) {
    // Pega as informações da atividade que o professor está editando/criando
    $current = $formwrapper->get_current();
    
    // Verifica se é o H5P Core nativo
    if (isset($current->modulename) && $current->modulename === 'h5pactivity') {
        
        $mform->addElement('header', 'local_h5pchapter_header', 'Controle de Capítulos H5P (Plugin Local)');

        $mform->addElement('text', 'chapter_target', 'ID ou Nome do Capítulo Alvo', ['maxlength' => 255]);
        $mform->setType('chapter_target', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'block_navigation', 'Bloquear navegação para outros capítulos');
        $mform->setType('block_navigation', PARAM_BOOL);
        $mform->setDefault('block_navigation', 0);
        
        // Se já existe um ID de módulo, tenta carregar as configurações salvas
        if (!empty($current->coursemodule)) {
            global $DB;
            if ($setting = $DB->get_record('local_h5pchapter_settings', ['cmid' => $current->coursemodule])) {
                $mform->setDefault('chapter_target', $setting->chapter_target);
                $mform->setDefault('block_navigation', $setting->block_navigation);
            }
        }
    }
}

/**
 * 2. SALVA OS DADOS NO BANCO APÓS O PROFESSOR SALVAR O FORMULÁRIO
 */
function local_h5pchapter_coursemodule_edit_post_actions($data, $course) {
    global $DB;

    // Garante que só vamos interceptar o salvamento de atividades H5P
    if ($data->modulename !== 'h5pactivity' || empty($data->coursemodule)) {
        return $data;
    }

    $record = new stdClass();
    $record->cmid = $data->coursemodule;
    $record->chapter_target = $data->chapter_target ?? '';
    $record->block_navigation = !empty($data->block_navigation) ? 1 : 0;
    $record->timemodified = time();

    if ($existing = $DB->get_record('local_h5pchapter_settings', ['cmid' => $record->cmid])) {
        $record->id = $existing->id;
        $DB->update_record('local_h5pchapter_settings', $record);
    } else {
        $record->timecreated = time();
        $DB->insert_record('local_h5pchapter_settings', $record);
    }

    return $data;
}