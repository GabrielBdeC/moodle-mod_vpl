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
 * List utility class
 *
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

defined( 'MOODLE_INTERNAL' ) || die();
class vpl_list_util {
    static protected $fields; // Field to compare.
    static protected $ascending; // Value to return when ascending or descending order.
    // Compare two submission fields.
    static public function cpm($avpl, $bvpl) {
        $a = $avpl->get_instance();
        $b = $bvpl->get_instance();
        foreach (self::$fields as $field) {
            $avalue = $a->$field;
            $bvalue = $b->$field;
            if ($avalue == $bvalue) {
                continue;
            } else if ($avalue < $bvalue) {
                return self::$ascending;
            } else {
                return - self::$ascending;
            }
        }
        return 0;
    }

    /**
     * Check and set data to sort return comparation function $field field to compare $descending order
     */
    static public function set_order($field, $ascending = true) {
        $sortfields = array (
                'name' => array (
                        'name'
                ),
                'startdate' => array (
                        'startdate',
                        'duedate',
                        'name'
                ),
                'duedate' => array (
                        'duedate',
                        'startdate',
                        'name'
                ),
                'automaticgrading' => array (
                        'automaticgrading',
                        'duedate',
                        'name'
                )
        );
        if (isset( $sortfields [$field] )) {
            self::$fields = $sortfields [$field];
        } else { // Unknow field.
            self::$fields = $sortfields ['duedate'];
        }
        if ($ascending) {
            self::$ascending = - 1;
        } else {
            self::$ascending = 1;
        }
    }
    static public function vpl_list_arrow($burl, $sort, $instanceselection, $selsort, $seldir) {
        global $OUTPUT;
        $newdir = 'down'; // Dir to go if clicked.
        $url = vpl_url_add_param( $burl, 'sort', $sort );
        $url = vpl_url_add_param( $url, 'selection', $instanceselection );
        if ($sort == $selsort) {
            $sortdir = $seldir;
            if ($sortdir == 'up') {
                $newdir = 'down';
            } else if ($sortdir == 'down') {
                $newdir = 'up';
            } else { // Unknow sortdir.
                $sortdir = 'down';
            }
            $url = vpl_url_add_param( $url, 'sortdir', $newdir );
        } else {
            $sortdir = 'move';
        }
        return '<a href="' . $url . '">' . ($OUTPUT->pix_icon( 't/' . $sortdir, get_string( $sortdir ) )) . '</a>';
    }

    // Count submissions graded.
    static public function count_graded($vpl) {
        $numsubs = 0;
        $numgraded = 0;
        $subs = $vpl->all_last_user_submission( 's.dategraded, s.userid' );
        if ($vpl->is_group_activity()) { // Fixes group activity userid.
            foreach ($subs as $sub) {
                $group = $vpl->get_group_members($sub->groupid);
                if ( count($group) ) {
                    $user = reset($group);
                    $sub->userid = $user->id;
                }
            }
        }
        $students = $vpl->get_students();
        foreach ($subs as $sub) {
            if (isset( $students [$sub->userid] )) {
                $numsubs ++;
                if ($sub->dategraded > 0) { // Is graded.
                    $numgraded ++;
                }
            }
        }
        return array (
                'submissions' => $numsubs,
                'graded' => $numgraded
        );
    }
}
