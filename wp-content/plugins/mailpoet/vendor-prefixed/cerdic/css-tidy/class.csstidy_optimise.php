<?php
namespace MailPoetVendor;
if (!defined('ABSPATH')) exit;
class csstidy_optimise
{
 public $parser;
 public function __construct($css)
 {
 $this->parser = $css;
 $this->css =& $css->css;
 $this->sub_value =& $css->sub_value;
 $this->at =& $css->at;
 $this->selector =& $css->selector;
 $this->property =& $css->property;
 $this->value =& $css->value;
 }
 public function postparse()
 {
 if ($this->parser->get_cfg('reverse_left_and_right') > 0) {
 foreach ($this->css as $medium => $selectors) {
 if (\is_array($selectors)) {
 foreach ($selectors as $selector => $properties) {
 $this->css[$medium][$selector] = $this->reverse_left_and_right($this->css[$medium][$selector]);
 }
 }
 }
 }
 if ($this->parser->get_cfg('preserve_css')) {
 return;
 }
 if ((int) $this->parser->get_cfg('merge_selectors') === 2) {
 foreach ($this->css as $medium => $value) {
 if (\is_array($value)) {
 $this->merge_selectors($this->css[$medium]);
 }
 }
 }
 if ($this->parser->get_cfg('discard_invalid_selectors')) {
 foreach ($this->css as $medium => $value) {
 if (\is_array($value)) {
 $this->discard_invalid_selectors($this->css[$medium]);
 }
 }
 }
 if ($this->parser->get_cfg('optimise_shorthands') > 0) {
 foreach ($this->css as $medium => $value) {
 if (\is_array($value)) {
 foreach ($value as $selector => $value1) {
 $this->css[$medium][$selector] = $this->merge_4value_shorthands($this->css[$medium][$selector]);
 $this->css[$medium][$selector] = $this->merge_4value_radius_shorthands($this->css[$medium][$selector]);
 if ($this->parser->get_cfg('optimise_shorthands') < 2) {
 continue;
 }
 $this->css[$medium][$selector] = $this->merge_font($this->css[$medium][$selector]);
 if ($this->parser->get_cfg('optimise_shorthands') < 3) {
 continue;
 }
 $this->css[$medium][$selector] = $this->merge_bg($this->css[$medium][$selector]);
 if (empty($this->css[$medium][$selector])) {
 unset($this->css[$medium][$selector]);
 }
 }
 }
 }
 }
 }
 public function value()
 {
 $shorthands =& $this->parser->data['csstidy']['shorthands'];
 // optimise shorthand properties
 if (isset($shorthands[$this->property])) {
 $temp = $this->shorthand($this->value);
 // FIXME - move
 if ($temp != $this->value) {
 $this->parser->log('Optimised shorthand notation (' . $this->property . '): Changed "' . $this->value . '" to "' . $temp . '"', 'Information');
 }
 $this->value = $temp;
 }
 // Remove whitespace at ! important
 if ($this->value != $this->compress_important($this->value)) {
 $this->parser->log('Optimised !important', 'Information');
 }
 }
 public function shorthands()
 {
 $shorthands =& $this->parser->data['csstidy']['shorthands'];
 if (!$this->parser->get_cfg('optimise_shorthands') || $this->parser->get_cfg('preserve_css')) {
 return;
 }
 if ($this->property === 'font' && $this->parser->get_cfg('optimise_shorthands') > 1) {
 $this->css[$this->at][$this->selector]['font'] = '';
 $this->parser->merge_css_blocks($this->at, $this->selector, $this->dissolve_short_font($this->value));
 }
 if ($this->property === 'background' && $this->parser->get_cfg('optimise_shorthands') > 2) {
 $this->css[$this->at][$this->selector]['background'] = '';
 $this->parser->merge_css_blocks($this->at, $this->selector, $this->dissolve_short_bg($this->value));
 }
 if (isset($shorthands[$this->property])) {
 $this->parser->merge_css_blocks($this->at, $this->selector, $this->dissolve_4value_shorthands($this->property, $this->value));
 if (\is_array($shorthands[$this->property])) {
 $this->css[$this->at][$this->selector][$this->property] = '';
 }
 }
 }
 public function subvalue()
 {
 $replace_colors =& $this->parser->data['csstidy']['replace_colors'];
 $this->sub_value = \trim($this->sub_value);
 if ($this->sub_value == '') {
 // caution : '0'
 return;
 }
 $important = '';
 if ($this->parser->is_important($this->sub_value)) {
 $important = '!important';
 }
 $this->sub_value = $this->parser->gvw_important($this->sub_value);
 // Compress font-weight
 if ($this->property === 'font-weight' && $this->parser->get_cfg('compress_font-weight')) {
 if ($this->sub_value === 'bold') {
 $this->sub_value = '700';
 $this->parser->log('Optimised font-weight: Changed "bold" to "700"', 'Information');
 } elseif ($this->sub_value === 'normal') {
 $this->sub_value = '400';
 $this->parser->log('Optimised font-weight: Changed "normal" to "400"', 'Information');
 }
 }
 $temp = $this->compress_numbers($this->sub_value);
 if (\strcasecmp($temp, $this->sub_value) !== 0) {
 if (\strlen($temp) > \strlen($this->sub_value)) {
 $this->parser->log('Fixed invalid number: Changed "' . $this->sub_value . '" to "' . $temp . '"', 'Warning');
 } else {
 $this->parser->log('Optimised number: Changed "' . $this->sub_value . '" to "' . $temp . '"', 'Information');
 }
 $this->sub_value = $temp;
 }
 if ($this->parser->get_cfg('compress_colors')) {
 $temp = $this->cut_color($this->sub_value);
 if ($temp !== $this->sub_value) {
 if (isset($replace_colors[$this->sub_value])) {
 $this->parser->log('Fixed invalid color name: Changed "' . $this->sub_value . '" to "' . $temp . '"', 'Warning');
 } else {
 $this->parser->log('Optimised color: Changed "' . $this->sub_value . '" to "' . $temp . '"', 'Information');
 }
 $this->sub_value = $temp;
 }
 }
 $this->sub_value .= $important;
 }
 public function shorthand($value)
 {
 $important = '';
 if ($this->parser->is_important($value)) {
 $values = $this->parser->gvw_important($value);
 $important = '!important';
 } else {
 $values = $value;
 }
 $values = \explode(' ', $values);
 switch (\count($values)) {
 case 4:
 if ($values[0] == $values[1] && $values[0] == $values[2] && $values[0] == $values[3]) {
 return $values[0] . $important;
 } elseif ($values[1] == $values[3] && $values[0] == $values[2]) {
 return $values[0] . ' ' . $values[1] . $important;
 } elseif ($values[1] == $values[3]) {
 return $values[0] . ' ' . $values[1] . ' ' . $values[2] . $important;
 }
 break;
 case 3:
 if ($values[0] == $values[1] && $values[0] == $values[2]) {
 return $values[0] . $important;
 } elseif ($values[0] == $values[2]) {
 return $values[0] . ' ' . $values[1] . $important;
 }
 break;
 case 2:
 if ($values[0] == $values[1]) {
 return $values[0] . $important;
 }
 break;
 }
 return $value;
 }
 public function compress_important(&$string)
 {
 if ($this->parser->is_important($string)) {
 $important = $this->parser->get_cfg('space_before_important') ? ' !important' : '!important';
 $string = $this->parser->gvw_important($string) . $important;
 }
 return $string;
 }
 public function cut_color($color)
 {
 $replace_colors =& $this->parser->data['csstidy']['replace_colors'];
 // if it's a string, don't touch !
 if (\strncmp($color, "'", 1) == 0 || \strncmp($color, '"', 1) == 0) {
 return $color;
 }
 if (\strpos($color, '(') !== \false && \strncmp($color, 'rgb(', 4) != 0) {
 // on ne touche pas aux couleurs dans les expression ms, c'est trop sensible
 if (\stripos($color, 'progid:') !== \false) {
 return $color;
 }
 \preg_match_all(",rgb\\([^)]+\\),i", $color, $matches, \PREG_SET_ORDER);
 if (\count($matches)) {
 foreach ($matches as $m) {
 $color = \str_replace($m[0], $this->cut_color($m[0]), $color);
 }
 }
 \preg_match_all(",#[0-9a-f]{6}(?=[^0-9a-f]),i", $color, $matches, \PREG_SET_ORDER);
 if (\count($matches)) {
 foreach ($matches as $m) {
 $color = \str_replace($m[0], $this->cut_color($m[0]), $color);
 }
 }
 return $color;
 }
 // rgb(0,0,0) -> #000000 (or #000 in this case later)
 if (\strncasecmp($color, 'rgb(', 4) == 0) {
 $color_tmp = \substr($color, 4, \strlen($color) - 5);
 $color_tmp = \explode(',', $color_tmp);
 for ($i = 0; $i < \count($color_tmp); $i++) {
 $color_tmp[$i] = \trim($color_tmp[$i]);
 if (\substr($color_tmp[$i], -1) === '%') {
 $color_tmp[$i] = \round(255 * $color_tmp[$i] / 100);
 }
 if ($color_tmp[$i] > 255) {
 $color_tmp[$i] = 255;
 }
 }
 $color = '#';
 for ($i = 0; $i < 3; $i++) {
 if ($color_tmp[$i] < 16) {
 $color .= '0' . \dechex($color_tmp[$i]);
 } else {
 $color .= \dechex($color_tmp[$i]);
 }
 }
 }
 // Fix bad color names
 if (isset($replace_colors[\strtolower($color)])) {
 $color = $replace_colors[\strtolower($color)];
 }
 // #aabbcc -> #abc
 if (\strlen($color) == 7) {
 $color_temp = \strtolower($color);
 if ($color_temp[0] === '#' && $color_temp[1] == $color_temp[2] && $color_temp[3] == $color_temp[4] && $color_temp[5] == $color_temp[6]) {
 $color = '#' . $color[1] . $color[3] . $color[5];
 }
 }
 switch (\strtolower($color)) {
 case 'black':
 return '#000';
 case 'fuchsia':
 return '#f0f';
 case 'white':
 return '#fff';
 case 'yellow':
 return '#ff0';
 case '#800000':
 return 'maroon';
 case '#ffa500':
 return 'orange';
 case '#808000':
 return 'olive';
 case '#800080':
 return 'purple';
 case '#008000':
 return 'green';
 case '#000080':
 return 'navy';
 case '#008080':
 return 'teal';
 case '#c0c0c0':
 return 'silver';
 case '#808080':
 return 'gray';
 case '#f00':
 return 'red';
 }
 return $color;
 }
 public function compress_numbers($subvalue)
 {
 $unit_values =& $this->parser->data['csstidy']['unit_values'];
 $color_values =& $this->parser->data['csstidy']['color_values'];
 // for font:1em/1em sans-serif...;
 if ($this->property === 'font') {
 $temp = \explode('/', $subvalue);
 } else {
 $temp = array($subvalue);
 }
 for ($l = 0; $l < \count($temp); $l++) {
 // if we are not dealing with a number at this point, do not optimise anything
 $number = $this->AnalyseCssNumber($temp[$l]);
 if ($number === \false) {
 return $subvalue;
 }
 // Fix bad colors
 if (\in_array($this->property, $color_values)) {
 $temp[$l] = '#' . $temp[$l];
 continue;
 }
 if (\abs($number[0]) > 0) {
 if ($number[1] == '' && \in_array($this->property, $unit_values, \true)) {
 $number[1] = 'px';
 }
 } elseif ($number[1] != 's' && $number[1] != 'ms') {
 $number[1] = '';
 }
 $temp[$l] = $number[0] . $number[1];
 }
 return \count($temp) > 1 ? $temp[0] . '/' . $temp[1] : $temp[0];
 }
 public function AnalyseCssNumber($string)
 {
 // most simple checks first
 if (\strlen($string) == 0 || \ctype_alpha($string[0])) {
 return \false;
 }
 $units =& $this->parser->data['csstidy']['units'];
 $return = array(0, '');
 $return[0] = \floatval($string);
 if (\abs($return[0]) > 0 && \abs($return[0]) < 1) {
 if ($return[0] < 0) {
 $return[0] = '-' . \ltrim(\substr($return[0], 1), '0');
 } else {
 $return[0] = \ltrim($return[0], '0');
 }
 }
 // Look for unit and split from value if exists
 foreach ($units as $unit) {
 $expectUnitAt = \strlen($string) - \strlen($unit);
 if (!($unitInString = \stristr($string, $unit))) {
 // mb_strpos() fails with "false"
 continue;
 }
 $actualPosition = \strpos($string, $unitInString);
 if ($expectUnitAt === $actualPosition) {
 $return[1] = $unit;
 $string = \substr($string, 0, -\strlen($unit));
 break;
 }
 }
 if (!\is_numeric($string)) {
 return \false;
 }
 return $return;
 }
 public function merge_selectors(&$array)
 {
 $css = $array;
 foreach ($css as $key => $value) {
 if (!isset($css[$key])) {
 continue;
 }
 $newsel = '';
 // Check if properties also exist in another selector
 $keys = array();
 // PHP bug (?) without $css = $array; here
 foreach ($css as $selector => $vali) {
 if ($selector == $key) {
 continue;
 }
 if ($css[$key] === $vali) {
 $keys[] = $selector;
 }
 }
 if (!empty($keys)) {
 $newsel = $key;
 unset($css[$key]);
 foreach ($keys as $selector) {
 unset($css[$selector]);
 $newsel .= ',' . $selector;
 }
 $css[$newsel] = $value;
 }
 }
 $array = $css;
 }
 public function discard_invalid_selectors(&$array)
 {
 $invalid = array('+' => \true, '~' => \true, ',' => \true, '>' => \true);
 foreach ($array as $selector => $decls) {
 $ok = \true;
 $selectors = \array_map('trim', \explode(',', $selector));
 foreach ($selectors as $s) {
 $simple_selectors = \preg_split('/\\s*[+>~\\s]\\s*/', $s);
 foreach ($simple_selectors as $ss) {
 if ($ss === '') {
 $ok = \false;
 }
 // could also check $ss for internal structure,
 // but that probably would be too slow
 }
 }
 if (!$ok) {
 unset($array[$selector]);
 }
 }
 }
 public function dissolve_4value_shorthands($property, $value, $shorthands = null)
 {
 if (\is_null($shorthands)) {
 $shorthands =& $this->parser->data['csstidy']['shorthands'];
 }
 if (!\is_array($shorthands[$property])) {
 $return[$property] = $value;
 return $return;
 }
 $important = '';
 if ($this->parser->is_important($value)) {
 $value = $this->parser->gvw_important($value);
 $important = '!important';
 }
 $values = \explode(' ', $value);
 $return = array();
 if (\count($values) == 4) {
 for ($i = 0; $i < 4; $i++) {
 $return[$shorthands[$property][$i]] = $values[$i] . $important;
 }
 } elseif (\count($values) == 3) {
 $return[$shorthands[$property][0]] = $values[0] . $important;
 $return[$shorthands[$property][1]] = $values[1] . $important;
 $return[$shorthands[$property][3]] = $values[1] . $important;
 $return[$shorthands[$property][2]] = $values[2] . $important;
 } elseif (\count($values) == 2) {
 for ($i = 0; $i < 4; $i++) {
 $return[$shorthands[$property][$i]] = $i % 2 != 0 ? $values[1] . $important : $values[0] . $important;
 }
 } else {
 for ($i = 0; $i < 4; $i++) {
 $return[$shorthands[$property][$i]] = $values[0] . $important;
 }
 }
 return $return;
 }
 public function dissolve_4value_radius_shorthands($property, $value)
 {
 $shorthands =& $this->parser->data['csstidy']['radius_shorthands'];
 if (!\is_array($shorthands[$property])) {
 $return[$property] = $value;
 return $return;
 }
 if (\strpos($value, '/') !== \false) {
 $values = $this->explode_ws('/', $value);
 if (\count($values) == 2) {
 $r[0] = $this->dissolve_4value_shorthands($property, \trim($values[0]), $shorthands);
 $r[1] = $this->dissolve_4value_shorthands($property, \trim($values[1]), $shorthands);
 $return = array();
 foreach ($r[0] as $p => $v) {
 $return[$p] = $v;
 if ($r[1][$p] !== $v) {
 $return[$p] .= ' ' . $r[1][$p];
 }
 }
 return $return;
 }
 }
 $return = $this->dissolve_4value_shorthands($property, $value, $shorthands);
 return $return;
 }
 public function explode_ws($sep, $string, $explode_in_parenthesis = \false)
 {
 $status = 'st';
 $to = '';
 $output = array(0 => '');
 $num = 0;
 for ($i = 0, $len = \strlen($string); $i < $len; $i++) {
 switch ($status) {
 case 'st':
 if ($string[$i] == $sep && !$this->parser->escaped($string, $i)) {
 ++$num;
 } elseif ($string[$i] === '"' || $string[$i] === '\'' || !$explode_in_parenthesis && $string[$i] === '(' && !$this->parser->escaped($string, $i)) {
 $status = 'str';
 $to = $string[$i] === '(' ? ')' : $string[$i];
 isset($output[$num]) ? $output[$num] .= $string[$i] : ($output[$num] = $string[$i]);
 } else {
 isset($output[$num]) ? $output[$num] .= $string[$i] : ($output[$num] = $string[$i]);
 }
 break;
 case 'str':
 if ($string[$i] == $to && !$this->parser->escaped($string, $i)) {
 $status = 'st';
 }
 isset($output[$num]) ? $output[$num] .= $string[$i] : ($output[$num] = $string[$i]);
 break;
 }
 }
 return $output;
 }
 public function merge_4value_shorthands($array, $shorthands = null)
 {
 $return = $array;
 if (\is_null($shorthands)) {
 $shorthands =& $this->parser->data['csstidy']['shorthands'];
 }
 foreach ($shorthands as $key => $value) {
 if ($value !== 0 && isset($array[$value[0]]) && isset($array[$value[1]]) && isset($array[$value[2]]) && isset($array[$value[3]])) {
 $return[$key] = '';
 $important = '';
 for ($i = 0; $i < 4; $i++) {
 $val = $array[$value[$i]];
 if ($this->parser->is_important($val)) {
 $important = '!important';
 $return[$key] .= $this->parser->gvw_important($val) . ' ';
 } else {
 $return[$key] .= $val . ' ';
 }
 unset($return[$value[$i]]);
 }
 $return[$key] = $this->shorthand(\trim($return[$key] . $important));
 }
 }
 return $return;
 }
 public function merge_4value_radius_shorthands($array)
 {
 $return = $array;
 $shorthands =& $this->parser->data['csstidy']['radius_shorthands'];
 foreach ($shorthands as $key => $value) {
 if (isset($array[$value[0]]) && isset($array[$value[1]]) && isset($array[$value[2]]) && isset($array[$value[3]]) && $value !== 0) {
 $return[$key] = '';
 $a = array();
 for ($i = 0; $i < 4; $i++) {
 $v = $this->explode_ws(' ', \trim($array[$value[$i]]));
 $a[0][$value[$i]] = \reset($v);
 $a[1][$value[$i]] = \end($v);
 }
 $r = array();
 $r[0] = $this->merge_4value_shorthands($a[0], $shorthands);
 $r[1] = $this->merge_4value_shorthands($a[1], $shorthands);
 if (isset($r[0][$key]) and isset($r[1][$key])) {
 $return[$key] = $r[0][$key];
 if ($r[1][$key] !== $r[0][$key]) {
 $return[$key] .= ' / ' . $r[1][$key];
 }
 for ($i = 0; $i < 4; $i++) {
 unset($return[$value[$i]]);
 }
 }
 }
 }
 return $return;
 }
 public function dissolve_short_bg($str_value)
 {
 // don't try to explose background gradient !
 if (\stripos($str_value, 'gradient(') !== \false) {
 return array('background' => $str_value);
 }
 $background_prop_default =& $this->parser->data['csstidy']['background_prop_default'];
 $repeat = array('repeat', 'repeat-x', 'repeat-y', 'no-repeat', 'space');
 $attachment = array('scroll', 'fixed', 'local');
 $clip = array('border', 'padding');
 $origin = array('border', 'padding', 'content');
 $pos = array('top', 'center', 'bottom', 'left', 'right');
 $important = '';
 $return = array('background-image' => null, 'background-size' => null, 'background-repeat' => null, 'background-position' => null, 'background-attachment' => null, 'background-clip' => null, 'background-origin' => null, 'background-color' => null);
 if ($this->parser->is_important($str_value)) {
 $important = ' !important';
 $str_value = $this->parser->gvw_important($str_value);
 }
 $str_value = $this->explode_ws(',', $str_value);
 for ($i = 0; $i < \count($str_value); $i++) {
 $have['clip'] = \false;
 $have['pos'] = \false;
 $have['color'] = \false;
 $have['bg'] = \false;
 if (\is_array($str_value[$i])) {
 $str_value[$i] = $str_value[$i][0];
 }
 $str_value[$i] = $this->explode_ws(' ', \trim($str_value[$i]));
 for ($j = 0; $j < \count($str_value[$i]); $j++) {
 if ($have['bg'] === \false && (\substr($str_value[$i][$j], 0, 4) === 'url(' || $str_value[$i][$j] === 'none')) {
 $return['background-image'] .= $str_value[$i][$j] . ',';
 $have['bg'] = \true;
 } elseif (\in_array($str_value[$i][$j], $repeat, \true)) {
 $return['background-repeat'] .= $str_value[$i][$j] . ',';
 } elseif (\in_array($str_value[$i][$j], $attachment, \true)) {
 $return['background-attachment'] .= $str_value[$i][$j] . ',';
 } elseif (\in_array($str_value[$i][$j], $clip, \true) && !$have['clip']) {
 $return['background-clip'] .= $str_value[$i][$j] . ',';
 $have['clip'] = \true;
 } elseif (\in_array($str_value[$i][$j], $origin, \true)) {
 $return['background-origin'] .= $str_value[$i][$j] . ',';
 } elseif ($str_value[$i][$j][0] === '(') {
 $return['background-size'] .= \substr($str_value[$i][$j], 1, -1) . ',';
 } elseif (\in_array($str_value[$i][$j], $pos, \true) || \is_numeric($str_value[$i][$j][0]) || $str_value[$i][$j][0] === null || $str_value[$i][$j][0] === '-' || $str_value[$i][$j][0] === '.') {
 $return['background-position'] .= $str_value[$i][$j];
 if (!$have['pos']) {
 $return['background-position'] .= ' ';
 } else {
 $return['background-position'] .= ',';
 }
 $have['pos'] = \true;
 } elseif (!$have['color']) {
 $return['background-color'] .= $str_value[$i][$j] . ',';
 $have['color'] = \true;
 }
 }
 }
 foreach ($background_prop_default as $bg_prop => $default_value) {
 if ($return[$bg_prop] !== null) {
 $return[$bg_prop] = \substr($return[$bg_prop], 0, -1) . $important;
 } else {
 $return[$bg_prop] = $default_value . $important;
 }
 }
 return $return;
 }
 public function merge_bg($input_css)
 {
 $background_prop_default =& $this->parser->data['csstidy']['background_prop_default'];
 // Max number of background images. CSS3 not yet fully implemented
 $number_of_values = @\max(\count($this->explode_ws(',', $input_css['background-image'])), \count($this->explode_ws(',', $input_css['background-color'])), 1);
 // Array with background images to check if BG image exists
 $bg_img_array = @$this->explode_ws(',', $this->parser->gvw_important($input_css['background-image']));
 $new_bg_value = '';
 $important = '';
 // if background properties is here and not empty, don't try anything
 if (isset($input_css['background']) && $input_css['background']) {
 return $input_css;
 }
 for ($i = 0; $i < $number_of_values; $i++) {
 foreach ($background_prop_default as $bg_property => $default_value) {
 // Skip if property does not exist
 if (!isset($input_css[$bg_property])) {
 continue;
 }
 $cur_value = $input_css[$bg_property];
 // skip all optimisation if gradient() somewhere
 if (\stripos($cur_value, 'gradient(') !== \false) {
 return $input_css;
 }
 // Skip some properties if there is no background image
 if ((!isset($bg_img_array[$i]) || $bg_img_array[$i] === 'none') && ($bg_property === 'background-size' || $bg_property === 'background-position' || $bg_property === 'background-attachment' || $bg_property === 'background-repeat')) {
 continue;
 }
 // Remove !important
 if ($this->parser->is_important($cur_value)) {
 $important = ' !important';
 $cur_value = $this->parser->gvw_important($cur_value);
 }
 // Do not add default values
 if ($cur_value === $default_value) {
 continue;
 }
 $temp = $this->explode_ws(',', $cur_value);
 if (isset($temp[$i])) {
 if ($bg_property === 'background-size') {
 $new_bg_value .= '(' . $temp[$i] . ') ';
 } else {
 $new_bg_value .= $temp[$i] . ' ';
 }
 }
 }
 $new_bg_value = \trim($new_bg_value);
 if ($i != $number_of_values - 1) {
 $new_bg_value .= ',';
 }
 }
 // Delete all background-properties
 foreach ($background_prop_default as $bg_property => $default_value) {
 unset($input_css[$bg_property]);
 }
 // Add new background property
 if ($new_bg_value !== '') {
 $input_css['background'] = $new_bg_value . $important;
 } elseif (isset($input_css['background'])) {
 $input_css['background'] = 'none';
 }
 return $input_css;
 }
 public function dissolve_short_font($str_value)
 {
 $font_prop_default =& $this->parser->data['csstidy']['font_prop_default'];
 $font_weight = array('normal', 'bold', 'bolder', 'lighter', 100, 200, 300, 400, 500, 600, 700, 800, 900);
 $font_variant = array('normal', 'small-caps');
 $font_style = array('normal', 'italic', 'oblique');
 $important = '';
 $return = array('font-style' => null, 'font-variant' => null, 'font-weight' => null, 'font-size' => null, 'line-height' => null, 'font-family' => null);
 if ($this->parser->is_important($str_value)) {
 $important = '!important';
 $str_value = $this->parser->gvw_important($str_value);
 }
 $have['style'] = \false;
 $have['variant'] = \false;
 $have['weight'] = \false;
 $have['size'] = \false;
 // Detects if font-family consists of several words w/o quotes
 $multiwords = \false;
 // Workaround with multiple font-family
 $str_value = $this->explode_ws(',', \trim($str_value));
 $str_value[0] = $this->explode_ws(' ', \trim($str_value[0]));
 for ($j = 0; $j < \count($str_value[0]); $j++) {
 if ($have['weight'] === \false && \in_array($str_value[0][$j], $font_weight)) {
 $return['font-weight'] = $str_value[0][$j];
 $have['weight'] = \true;
 } elseif ($have['variant'] === \false && \in_array($str_value[0][$j], $font_variant)) {
 $return['font-variant'] = $str_value[0][$j];
 $have['variant'] = \true;
 } elseif ($have['style'] === \false && \in_array($str_value[0][$j], $font_style)) {
 $return['font-style'] = $str_value[0][$j];
 $have['style'] = \true;
 } elseif ($have['size'] === \false && (\is_numeric($str_value[0][$j][0]) || $str_value[0][$j][0] === null || $str_value[0][$j][0] === '.')) {
 $size = $this->explode_ws('/', \trim($str_value[0][$j]));
 $return['font-size'] = $size[0];
 if (isset($size[1])) {
 $return['line-height'] = $size[1];
 } else {
 $return['line-height'] = '';
 // don't add 'normal' !
 }
 $have['size'] = \true;
 } else {
 if (isset($return['font-family'])) {
 $return['font-family'] .= ' ' . $str_value[0][$j];
 $multiwords = \true;
 } else {
 $return['font-family'] = $str_value[0][$j];
 }
 }
 }
 // add quotes if we have several qords in font-family
 if ($multiwords !== \false) {
 $return['font-family'] = '"' . $return['font-family'] . '"';
 }
 $i = 1;
 while (isset($str_value[$i])) {
 $return['font-family'] .= ',' . \trim($str_value[$i]);
 $i++;
 }
 // Fix for 100 and more font-size
 if ($have['size'] === \false && isset($return['font-weight']) && \is_numeric($return['font-weight'][0])) {
 $return['font-size'] = $return['font-weight'];
 unset($return['font-weight']);
 }
 foreach ($font_prop_default as $font_prop => $default_value) {
 if ($return[$font_prop] !== null) {
 $return[$font_prop] = $return[$font_prop] . $important;
 } else {
 $return[$font_prop] = $default_value . $important;
 }
 }
 return $return;
 }
 public function merge_font($input_css)
 {
 $font_prop_default =& $this->parser->data['csstidy']['font_prop_default'];
 $new_font_value = '';
 $important = '';
 // Skip if not font-family and font-size set
 if (isset($input_css['font-family']) && isset($input_css['font-size']) && $input_css['font-family'] != 'inherit') {
 // fix several words in font-family - add quotes
 if (isset($input_css['font-family'])) {
 $families = \explode(',', $input_css['font-family']);
 $result_families = array();
 foreach ($families as $family) {
 $family = \trim($family);
 $len = \strlen($family);
 if (\strpos($family, ' ') && !($family[0] === '"' && $family[$len - 1] === '"' || $family[0] === "'" && $family[$len - 1] === "'")) {
 $family = '"' . $family . '"';
 }
 $result_families[] = $family;
 }
 $input_css['font-family'] = \implode(',', $result_families);
 }
 foreach ($font_prop_default as $font_property => $default_value) {
 // Skip if property does not exist
 if (!isset($input_css[$font_property])) {
 continue;
 }
 $cur_value = $input_css[$font_property];
 // Skip if default value is used
 if ($cur_value === $default_value) {
 continue;
 }
 // Remove !important
 if ($this->parser->is_important($cur_value)) {
 $important = '!important';
 $cur_value = $this->parser->gvw_important($cur_value);
 }
 $new_font_value .= $cur_value;
 // Add delimiter
 $new_font_value .= $font_property === 'font-size' && isset($input_css['line-height']) ? '/' : ' ';
 }
 $new_font_value = \trim($new_font_value);
 // Delete all font-properties
 foreach ($font_prop_default as $font_property => $default_value) {
 if ($font_property !== 'font' || !$new_font_value) {
 unset($input_css[$font_property]);
 }
 }
 // Add new font property
 if ($new_font_value !== '') {
 $input_css['font'] = $new_font_value . $important;
 }
 }
 return $input_css;
 }
 public function reverse_left_and_right($array)
 {
 $return = array();
 // change left <-> right in properties name and values
 foreach ($array as $propertie => $value) {
 if (\method_exists($this, $m = 'reverse_left_and_right_' . \str_replace('-', '_', \trim($propertie)))) {
 $value = $this->{$m}($value);
 }
 // simple replacement for properties
 $propertie = \str_ireplace(array('left', 'right', "\1"), array("\1", 'left', 'right'), $propertie);
 // be careful for values, not modifying protected or quoted valued
 foreach (array('left' => "\1", 'right' => 'left', "\1" => 'right') as $v => $r) {
 if (\strpos($value, $v) !== \false) {
 // attraper les left et right separes du reste (pas au milieu d'un mot)
 if (\in_array($v, array('left', 'right'))) {
 $value = \preg_replace(",\\b{$v}\\b,", "\0", $value);
 } else {
 $value = \str_replace($v, "\0", $value);
 }
 $value = $this->explode_ws("\0", $value . ' ', \true);
 $value = \rtrim(\implode($r, $value));
 $value = \str_replace("\0", $v, $value);
 }
 }
 $return[$propertie] = $value;
 }
 return $return;
 }
 public function reverse_left_and_right_4value_shorthands($property, $value)
 {
 $shorthands =& $this->parser->data['csstidy']['shorthands'];
 if (isset($shorthands[$property])) {
 $property_right = $shorthands[$property][1];
 $property_left = $shorthands[$property][3];
 $v = $this->dissolve_4value_shorthands($property, $value);
 if ($v[$property_left] !== $v[$property_right]) {
 $r = $v[$property_right];
 $v[$property_right] = $v[$property_left];
 $v[$property_left] = $r;
 $v = $this->merge_4value_shorthands($v);
 if (isset($v[$property])) {
 return $v[$property];
 }
 }
 }
 return $value;
 }
 public function reverse_left_and_right_4value_radius_shorthands($property, $value)
 {
 $shorthands =& $this->parser->data['csstidy']['radius_shorthands'];
 if (isset($shorthands[$property])) {
 $v = $this->dissolve_4value_radius_shorthands($property, $value);
 if ($v[$shorthands[$property][0]] !== $v[$shorthands[$property][1]] or $v[$shorthands[$property][2]] !== $v[$shorthands[$property][3]]) {
 $r = array($shorthands[$property][0] => $v[$shorthands[$property][1]], $shorthands[$property][1] => $v[$shorthands[$property][0]], $shorthands[$property][2] => $v[$shorthands[$property][3]], $shorthands[$property][3] => $v[$shorthands[$property][2]]);
 $v = $this->merge_4value_radius_shorthands($r);
 if (isset($v[$property])) {
 return $v[$property];
 }
 }
 }
 return $value;
 }
 public function reverse_left_and_right_margin($value)
 {
 return $this->reverse_left_and_right_4value_shorthands('margin', $value);
 }
 public function reverse_left_and_right_padding($value)
 {
 return $this->reverse_left_and_right_4value_shorthands('padding', $value);
 }
 public function reverse_left_and_right_border_color($value)
 {
 return $this->reverse_left_and_right_4value_shorthands('border-color', $value);
 }
 public function reverse_left_and_right_border_style($value)
 {
 return $this->reverse_left_and_right_4value_shorthands('border-style', $value);
 }
 public function reverse_left_and_right_border_width($value)
 {
 return $this->reverse_left_and_right_4value_shorthands('border-width', $value);
 }
 public function reverse_left_and_right_border_radius($value)
 {
 return $this->reverse_left_and_right_4value_radius_shorthands('border-radius', $value);
 }
 public function reverse_left_and_right__moz_border_radius($value)
 {
 return $this->reverse_left_and_right_4value_radius_shorthands('border-radius', $value);
 }
 public function reverse_left_and_right__webkit_border_radius($value)
 {
 return $this->reverse_left_and_right_4value_radius_shorthands('border-radius', $value);
 }
 public function reverse_left_and_right_background($value)
 {
 $values = $this->dissolve_short_bg($value);
 if (isset($values['background-position']) and $values['background-position']) {
 $v = $this->reverse_left_and_right_background_position($values['background-position']);
 if ($v !== $values['background-position']) {
 if ($value == $values['background-position']) {
 return $v;
 } else {
 $values['background-position'] = $v;
 $x = $this->merge_bg($values);
 if (isset($x['background'])) {
 return $x['background'];
 }
 }
 }
 }
 return $value;
 }
 public function reverse_left_and_right_background_position_x($value)
 {
 return $this->reverse_left_and_right_background_position($value);
 }
 public function reverse_left_and_right_background_position($value)
 {
 // multiple background case
 if (\strpos($value, ',') !== \false) {
 $values = $this->explode_ws(',', $value);
 if (\count($values) > 1) {
 foreach ($values as $k => $v) {
 $values[$k] = $this->reverse_left_and_right_background_position($v);
 }
 return \implode(',', $values);
 }
 }
 // if no explicit left or right value
 if (\stripos($value, 'left') === \false and \stripos($value, 'right') === \false) {
 $values = $this->explode_ws(' ', \trim($value));
 $values = \array_map('trim', $values);
 $values = \array_filter($values, function ($v) {
 return \strlen($v);
 });
 $values = \array_values($values);
 if (\count($values) == 1) {
 if (\in_array($value, array('center', 'top', 'bottom', 'inherit', 'initial', 'unset'))) {
 return $value;
 }
 return "left {$value}";
 }
 if ($values[1] == 'top' or $values[1] == 'bottom') {
 if ($values[0] === 'center') {
 return $value;
 }
 return 'left ' . \implode(' ', $values);
 } else {
 $last = \array_pop($values);
 if ($last === 'center') {
 return $value;
 }
 return \implode(' ', $values) . ' left ' . $last;
 }
 }
 return $value;
 }
}
