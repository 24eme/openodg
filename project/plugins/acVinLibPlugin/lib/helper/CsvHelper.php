<?php

function escapeCSVValue($value) {

    return str_replace('"', '""', $value);
}