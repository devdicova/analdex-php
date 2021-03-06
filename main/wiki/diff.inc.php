<?php
/**
 * CLAROLINE.
 *
 * @version 1.7 $Revision: 1.12 $
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @author Frederic Minne <zefredz@gmail.com>
 *
 * @package Wiki
 */
define("DIFF_EQUAL", "=");
define("DIFF_ADDED", "+");
define("DIFF_DELETED", "-");
define("DIFF_MOVED", "M");

/**
 * Get difference between two strings.
 *
 * @param string old first string
 * @param string new second string
 * @param bool show_equals set to true to see line that are equal between
 *      the two strings (default true)
 * @param string format_line_function callback function to format line
 *      (default 'format_line')
 *
 * @return string formated diff output
 */
function diff(
    $old,
    $new,
    $show_equals = false,
    $format_line_function = 'format_line'
) {
    $oldArr = str_split_on_new_line($old);
    $newArr = str_split_on_new_line($new);

    $oldCount = count($oldArr);
    $newCount = count($newArr);

    $max = max($oldCount, $newCount);

    //get added and deleted lines

    $deleted = array_diff_assoc($oldArr, $newArr);
    $added = array_diff_assoc($newArr, $oldArr);

    $moved = [];

    foreach ($added as $key => $candidate) {
        foreach ($deleted as $index => $content) {
            if ($candidate == $content) {
                $moved[$key] = $candidate;
                unset($added[$key]);
                unset($deleted[$index]);
                break;
            }
        }
    }

    $output = '';

    for ($i = 0; $i < $max; $i++) {
        // line changed
        if (isset($deleted[$i]) && isset($added[$i])) {
            $output .= $format_line_function($i, DIFF_DELETED, $deleted[$i]);
            $output .= $format_line_function($i, DIFF_ADDED, $added[$i]);
        } elseif (isset($deleted[$i]) && !isset($added[$i])) {
            // line deleted
            $output .= $format_line_function($i, DIFF_DELETED, $deleted[$i]);
        } elseif (isset($added[$i]) && !isset($deleted[$i])) {
            // line added
            $output .= $format_line_function($i, DIFF_ADDED, $added[$i]);
        } elseif (isset($moved[$i])) {
            // line moved
            $output .= $format_line_function($i, DIFF_MOVED, $newArr[$i]);
        } elseif ($show_equals) {
            // line unchanged
            $output .= $format_line_function($i, DIFF_EQUAL, $newArr[$i]);
        }
    }

    return $output;
}

/**
 * Split strings on new line.
 */
function str_split_on_new_line($str)
{
    $content = [];

    if (api_strpos($str, "\r\n") !== false) {
        $content = explode("\r\n", $str);
    } elseif (api_strpos($str, "\n") !== false) {
        $content = explode("\n", $str);
    } elseif (api_strpos($str, "\r") !== false) {
        $content = explode("\r", $str);
    } else {
        $content[] = $str;
    }

    return $content;
}

/**
 * Default and prototype format line function.
 *
 * @param int line line number
 * @param mixed type line type, must be one of the following :
 *      DIFF_EQUAL, DIFF_MOVED, DIFF_ADDED, DIFF_DELETED
 * @param string value line content
 * @param bool skip_empty skip empty lines (default false)
 *
 * @return string formated diff line
 */
function format_line($line, $type, $value, $skip_empty = false)
{
    if (trim($value) == "" && $skip_empty) {
        return "";
    } elseif (trim($value) == "") {
        $value = '&nbsp;';
    }

    switch ($type) {
        case DIFF_EQUAL:
            // return $line. ' : ' . ' = <span class="diffEqual" >' . $value . '</span><br />' . "\n" ;
            return '<span class="diffEqual" >'.$value.'</span><br />'."\n"; //juan carlos muestra solo color
            break;
        case DIFF_MOVED:
            //return $line. ' : ' . ' M <span class="diffMoved" >' . $value . '</span><br />' . "\n" ; //juan carlos ra???a  la sustitye la inverior
            return '<span class="diffMoved" >'.$value.'</span><br />'."\n"; //juan carlos muestra solo color
            break;
        case DIFF_ADDED:
            //return $line . ' : ' . ' + <span class="diffAdded" >' . $value . '</span><br />' . "\n" ;
            return '<span class="diffAdded" >'.$value.'</span><br />'."\n"; //juan carlos muestra solo color
            break;
        case DIFF_DELETED:
            //return $line . ' : ' . ' - <span class="diffDeleted" >' . $value . '</span><br />' . "\n" ; //juan carlos ra???a  la sustitye la inverior
            return '<span class="diffDeleted" >'.$value.'</span><br />'."\n"; //juan carlos muestra solo color
            break;
    }
}

/**
 * Table format line function.
 *
 * @see format_line
 */
function format_table_line($line, $type, $value, $skip_empty = false)
{
    if (trim($value) == "" && $skip_empty) {
        return "";
    } elseif (trim($value) == "") {
        $value = '&nbsp;';
    }

    switch ($type) {
        case DIFF_EQUAL:
            return '<tr><td></td><td bgcolor="#FFFFFF">'.$value.'</td></tr>'."\n";
            //juan carlos muestra solo color (no tambi???n la l???nea).
            // Adem???s EN IEXPLORER VA BIEN PERO EN FIREFOX 3 la etiqueta span no muestra el color de fondo que
            // est??? definido en la hoja de estilos como background-color, aceptando s???lo la propiedad color
            // pero esta solo da color al texto con lo cual los cambios quedan poco resaltados, adem???s
            // los cambios de otros objetos que no sean texto no se indican por ej. a???adir una imagen,
            // por esta raz???n doy el color de fondo al td directamente.
            break;
        case DIFF_MOVED:
            return '<tr><td></td><td bgcolor="#FFFFAA">'.$value.'</td></tr>'."\n";
            //juan carlos muestra solo color (no tambi???n la l???nea). Adem???s EN IEXPLORER VA BIEN PERO EN FIREFOX 3
            // la etiqueta span no muestra el color de fondo que est??? definido en la hoja de estilos como
            // background-color, aceptando s???lo la propiedad color pero esta solo da color al texto con lo cual
            // los cambios quedan poco resaltados, adem???s los cambios de otros objetos que no sean texto no se indican
            // por ej. a???adir una imagen, por esta raz???n doy el color de fondo al td directamente.
            break;
        case DIFF_ADDED:
            return '<tr><td></td><td bgcolor="#CCFFCC">'.$value.'</td></tr>'."\n";
            //juan carlos muestra solo color (no tambi???n la l???nea). Adem???s EN IEXPLORER VA BIEN
            // PERO EN FIREFOX 3 la etiqueta span no muestra el color de fondo que est??? definido en la
            // hoja de estilos como background-color, aceptando s???lo la propiedad color pero esta solo
            // da color al texto con lo cual los cambios quedan poco resaltados, adem???s los cambios de
            // otros objetos que no sean texto no se indican por ej. a???adir una imagen, por esta raz???n
            // doy el color de fondo al td directamente.
            break;
        case DIFF_DELETED:
            return '<tr><td></td><td bgcolor="#FFAAAA">'.$value.'</td></tr>'."\n";
            //juan carlos muestra solo color (no tambi???n la l???nea). Adem???s EN IEXPLORER VA BIEN PERO EN FIREFOX 3
        // la etiqueta span no muestra el color de fondo que est??? definido en la hoja de estilos como background-color,
        // aceptando s???lo la propiedad color pero esta solo da color al texto con lo cual los cambios quedan poco
        // resaltados, adem???s los cambios de otros objetos que no sean texto no se indican por ej. a???adir una imagen,
        // por esta raz???n doy el color de fondo al td directamente.
    }
}
