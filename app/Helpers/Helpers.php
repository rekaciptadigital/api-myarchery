<?php

    /**
     * Translate date to Indonesia format
     *
     * @param date $date with format l-d-F-Y (e.g. Saturday-02-July-2022)
     * @return string with format (e.g. Sabtu, 2 Juli 2019)
    */
    function dateFormatTranslate($date)
    {
        $date_explode   = explode("-", $date);
        
        $months = ["January" => "Januari", "February" => "Februari", "March" => "Maret", "April" => "April", "May" => "Mei", "June" => "Juni", "July" => "Juli", "August" => "Agustus", "September" => "September", "October" => "Oktober", "November" => "November", "December" => "Desember"];
        $days   = array('Monday' => "Senin", 'Tuesday' => "Selasa", 'Wednesday' => "Rabu", 'Thursday' => "Kamis", 'Friday' => "Jumat", 'Saturday' => "Sabtu", 'Sunday' => "Minggu");
        
        $date_ina   = $days[$date_explode[0]] . ", " . $date_explode[1] . " " . $months[$date_explode[2]] . " " . $date_explode[3];
        
        return $date_ina;
    }