<?php

/**
 * Truncates +text+ to the length of +length+ and replaces the last three characters with the +truncate_string+
 * if the +text+ is longer than +length+.
 * mode
 */
function truncate_text_mode($text, $length = 30, $truncate_string = '...', $mode = 'end')
{
  if($mode === true) {
    $mode = 'lastspace';
  }

  if ($text == '')
  {
    return '';
  }

  $mbstring = extension_loaded('mbstring');
  if($mbstring)
  {
   $old_encoding = mb_internal_encoding();
   @mb_internal_encoding(mb_detect_encoding($text));
  }
  $strlen = ($mbstring) ? 'mb_strlen' : 'strlen';
  $substr = ($mbstring) ? 'mb_substr' : 'substr';

  if ($strlen($text) > $length)
  {
    if($mode === 'middle') {
        $first_length = round(($length - $strlen($truncate_string)) / 2);
        $last_length = $length - $strlen($truncate_string) - $first_length;
        $text = $substr($text, 0, $first_length).$truncate_string.$substr($text, $last_length*-1);
    } elseif ($mode === 'lastspace') {
        $truncate_text = $substr($text, 0, $length - $strlen($truncate_string));
        $truncate_text = preg_replace('/\s+?(\S+)?$/', '', $truncate_text);
        $text = $truncate_text.$truncate_string;
    } else {
        $text = $substr($text, 0, $length - $strlen($truncate_string)).$truncate_string;
    }
  }

  if($mbstring)
  {
   @mb_internal_encoding($old_encoding);
  }

  return $text;
}
