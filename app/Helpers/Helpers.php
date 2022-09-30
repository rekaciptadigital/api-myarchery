<?php

/**
 * Translate date to Indonesia format
 *
 * @param date $date with format l-d-F-Y (e.g. Saturday-02-July-2022)
 * @param boolean $withDay (true for format with day, false for format without day & only d-M-Y)
 * @return string with format (e.g. Sabtu, 2 Juli 2019 or 2 Juli 2019)
 */
function dateFormatTranslate($date, $withDay = true)
{
    $date_explode   = explode("-", $date);

    $months = ["January" => "Januari", "February" => "Februari", "March" => "Maret", "April" => "April", "May" => "Mei", "June" => "Juni", "July" => "Juli", "August" => "Agustus", "September" => "September", "October" => "Oktober", "November" => "November", "December" => "Desember"];
    $days   = ['Monday' => "Senin", 'Tuesday' => "Selasa", 'Wednesday' => "Rabu", 'Thursday' => "Kamis", 'Friday' => "Jumat", 'Saturday' => "Sabtu", 'Sunday' => "Minggu"];

    if ($withDay == false) {
        $date_ina = $date_explode[0] . " " . $months[$date_explode[1]] . " " . $date_explode[2];
    } else {
        $date_ina = $days[$date_explode[0]] . ", " . $date_explode[1] . " " . $months[$date_explode[2]] . " " . $date_explode[3];
    }

    return $date_ina;
}
