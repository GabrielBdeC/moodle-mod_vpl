<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Enhance utils functions
 *
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Gabriel B. de Carvalho <gabrielbdec@gmail.com>
 */

require_once(dirname( __FILE__ ) . '/../vpl_submission_CE.class.php');

define("LANG_PATTERN", "/^lang_.*\.json$/");
define("MAP_LANG_PATTERN", "/^lang_.*_map\.json$/");
define("VPL_EVALUATE_PATTERN", "/^vpl_evaluate_.*\.json$/");
define("VPL_EVALUATE_LIB_PATTERN", "/^vpl_evaluate_lib_.*\.lua$/");

/**
 * Remove protected names of pattern of vpl lib files in execution files
 * 
 * @param $files array filesname key to be removed
 * 
 * @return array
 */
function remove_files_with_protected_names_lib($files){
    $pattern = constant("VPL_EVALUATE_LIB_PATTERN");
    $files = array_filter($files, function($key) use ($pattern) {
        return !preg_match($pattern, $key);
    }, ARRAY_FILTER_USE_KEY);
    return $files;
}

/**
 * Remove protected names of pattern of lang configuration files in execution files
 * 
 * @param $files array filesname key to be removed
 * 
 * @return array
 */
function remove_files_with_protected_names_lang($files){
    $pln_list = mod_vpl_submission_CE::get_all_distinct_pln_list();
    foreach ($pln_list as $pln){
        $pattern = '/^lang_' . $pln . '_.*\.json$/';
        $files = array_filter($files, function($key) use ($pattern) {
            return !preg_match($pattern, $key);
        }, ARRAY_FILTER_USE_KEY);
    }
    $files = remove_files_with_protected_names_lib($files);
    return $files;
}

/**
 * Remove protected names of pattern of vpl evaluate lang files in execution files
 * 
 * @param $files array filesname key to be removed
 * 
 * @return array
 */
function remove_files_with_protected_names_evaluate($files){
    $pattern = constant("VPL_EVALUATE_PATTERN");
    $files = array_filter($files, function($key) use ($pattern) {
        return !preg_match($pattern, $key);
    }, ARRAY_FILTER_USE_KEY);
    $files = remove_files_with_protected_names_lib($files);
    return $files;
}

/**
 * Add lang for vpl evaluate lang files in execution
 * 
 * @param $vpl object containing instance of vpl
 * 
 * @return void
 */
function add_lang_evaluate_vpl_execution_files($vpl){
    $execution_fgm = $vpl->get_execution_fgm();
    $execution_files = array_keys($execution_fgm->getallfiles());
    $execution_files_add = array();
    $langs = array_keys(get_string_manager()->get_list_of_translations());
    $path = dirname( __FILE__ ) . "/../jail/lang/";
    foreach ($langs as $lang){
        if (!in_array("vpl_evaluate_" . $lang . ".json", $execution_files) && file_exists( $path . $lang . ".json" )) {
            $execution_files_add["vpl_evaluate_" . $lang . ".json"] = file_get_contents( $path . $lang . ".json" );
        }
    }
    $execution_files_add["vpl_evaluate_en.json"] = file_get_contents( $path . "en.json" );
    $path = dirname( __FILE__ ) . "/../jail/default_scripts/";
    $file_lib = 'vpl_evaluate_lib_translate.lua';
    $execution_files_add[$file_lib] = file_get_contents( $path . $file_lib);
    $file_lib = 'vpl_evaluate_lib_utils.lua';
    $execution_files_add[$file_lib] = file_get_contents( $path . $file_lib);
    $execution_fgm->addallfiles($execution_files_add);
    $keep_file_list = array_unique(array_merge($execution_fgm->getFileKeepList(), array_keys($execution_files_add)));
    $execution_fgm->setfilekeeplist($keep_file_list);
}

/**
 * Create or remove lang configuration files in execution files accordingly from instance enhance configuration
 * 
 * @param $vpl object containing instance of vpl
 * 
 * @param $requiredfiles array with required files of vpl instance
 * 
 * @return void
 */
function set_lang_definition_execution_files($vpl, $requiredfiles){
    $instance = $vpl->get_instance();
    $execution_fgm = $vpl->get_execution_fgm();
    $execution_files = array_keys($execution_fgm->getallfiles());
    if ($instance->enhance == "1") {
        $pln_list = mod_vpl_submission_CE::get_pln_list_by_file_list($requiredfiles);
        $execution_files_add = array();
        foreach ($pln_list as $pln){
            $path = dirname( __FILE__ ) . "/../jail/default_scripts/lang/" . $pln;
            if (!in_array("lang_" . $pln . "_map.json", $execution_files) && file_exists( $path . "/map.json" )) {
                $execution_files_add["lang_" . $pln . "_map.json"] = file_get_contents($path . "/map.json");
            }
            $langs = array_keys(get_string_manager()->get_list_of_translations());
            foreach ($langs as $lang){
                if (!in_array("lang_" . $pln . "_" . $lang . ".json", $execution_files) && file_exists( $path . "/" . $lang . ".json" )) {
                    $execution_files_add["lang_" . $pln . "_" . $lang . ".json"] = file_get_contents( $path . "/" . $lang . ".json" );
                }
            }
        }
        $path = dirname( __FILE__ ) . "/../jail/default_scripts/";
        $file_lib = "vpl_evaluate_lib_enhance.lua";
        $execution_files_add[$file_lib] = file_get_contents( $path . $file_lib );
        $execution_fgm->addallfiles($execution_files_add);
        $keep_file_list = array_unique(array_merge($execution_fgm->getFileKeepList(), array_keys($execution_files_add)));
        $execution_fgm->setfilekeeplist($keep_file_list);
        $removal = array_diff(mod_vpl_submission_CE::get_all_distinct_pln_list(), $pln_list);
        remove_lang_definition_execution_files_from_pln($vpl, $removal);
    } else {
        $pln_list = mod_vpl_submission_CE::get_all_distinct_pln_list();
        remove_lang_definition_execution_files_from_pln($vpl, $pln_list);
    }
}

/**
 * Remove lang configuration file from execution files in program language list
 * 
 * @param $vpl object containing instance of vpl
 * 
 * @param $pln_list array with program language list to remove
 * 
 * @return void
 */
function remove_lang_definition_execution_files_from_pln($vpl, $pln_list){
    $execution_fgm = $vpl->get_execution_fgm();
    $execution_files = array_keys($execution_fgm->getallfiles());
    $delete_array = array();
        foreach ($pln_list as $pln){
        $pattern = '/^lang_' . $pln . '_.*\.json$/';
        $matches = array_values(preg_grep($pattern, $execution_files));
        if ($matches != null){
            $delete_array = array_merge($delete_array, $matches);
        }
    }
    $execution_fgm->deletefiles($delete_array);
}