<?php

// This file is used for automatically detecting if the webserver is sering the test directory
echo file_get_contents('rails_test_ping_uuid.txt');

