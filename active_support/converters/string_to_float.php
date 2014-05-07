<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkStringToFloat
{
    public function convert() {
        return floatval($this->source);
    }
}

