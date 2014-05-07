<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

/**
 * @todo Implement all methods
 */

class AkTime extends AkType
{
    public function toString($date_format = '') {
        if (!empty($date_format)) {
            $format = $date_format.'_';
        } else {
            $format = '';
        }
        $format = Ak::locale($format.'date_time_format');
        if (!$format) {
            $format = Ak::locale('date_time_format');
        }
        return date($format, $this->value);
    }

    public function ago($val) {

    }

    public function atBeginningOfDay() {

    }

    public function atBeginningOfWeek() {

    }

    public function atBeginningOfQuarter() {

    }
    public function atBeginningOfMonth() {

    }

    public function atBeginningOfYear() {

    }

    public function atMidnight() {

    }

    public function strftime($format) {
        return strftime($format, $this->value);
    }
}

