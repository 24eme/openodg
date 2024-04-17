<?php
function getLibelleHashRevendicableParLots($hash = 'IGP') {
    return str_replace('|', '–', str_replace(['(', ')'], '', $hash));
}
