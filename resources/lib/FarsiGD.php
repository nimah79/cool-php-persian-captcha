<?php

/* Fixed version of FarsiGD
 * By NimaH79
 * NimaH79.ir
*/

// Just to be on the safe side
ini_set('error_reporting', 'E_ALL & ~E_NOTICE & ~E_STRICT');

class FarsiGD
{
    public $p_chars = [
        'آ' => ['ﺂ', 'ﺂ', 'آ'],
        'ا' => ['ﺎ', 'ﺎ', 'ا'],
        'ب' => ['ﺐ', 'ﺒ', 'ﺑ'],
        'پ' => ['ﭗ', 'ﭙ', 'ﭘ'],
        'ت' => ['ﺖ', 'ﺘ', 'ﺗ'],
        'ث' => ['ﺚ', 'ﺜ', 'ﺛ'],
        'ج' => ['ﺞ', 'ﺠ', 'ﺟ'],
        'چ' => ['ﭻ', 'ﭽ', 'ﭼ'],
        'ح' => ['ﺢ', 'ﺤ', 'ﺣ'],
        'خ' => ['ﺦ', 'ﺨ', 'ﺧ'],
        'د' => ['ﺪ', 'ﺪ', 'ﺩ'],
        'ذ' => ['ﺬ', 'ﺬ', 'ﺫ'],
        'ر' => ['ﺮ', 'ﺮ', 'ﺭ'],
        'ز' => ['ﺰ', 'ﺰ', 'ﺯ'],
        'ژ' => ['ﮋ', 'ﮋ', 'ﮊ'],
        'س' => ['ﺲ', 'ﺴ', 'ﺳ'],
        'ش' => ['ﺶ', 'ﺸ', 'ﺷ'],
        'ص' => ['ﺺ', 'ﺼ', 'ﺻ'],
        'ض' => ['ﺾ', 'ﻀ', 'ﺿ'],
        'ط' => ['ﻂ', 'ﻄ', 'ﻃ'],
        'ظ' => ['ﻆ', 'ﻈ', 'ﻇ'],
        'ع' => ['ﻊ', 'ﻌ', 'ﻋ'],
        'غ' => ['ﻎ', 'ﻐ', 'ﻏ'],
        'ف' => ['ﻒ', 'ﻔ', 'ﻓ'],
        'ق' => ['ﻖ', 'ﻘ', 'ﻗ'],
        'ک' => ['ﻚ', 'ﻜ', 'ﻛ'],
        'گ' => ['ﮓ', 'ﮕ', 'ﮔ'],
        'ل' => ['ﻞ', 'ﻠ', 'ﻟ'],
        'م' => ['ﻢ', 'ﻤ', 'ﻣ'],
        'ن' => ['ﻦ', 'ﻨ', 'ﻧ'],
        'و' => ['ﻮ', 'ﻮ', 'ﻭ'],
        'ی' => ['ﯽ', 'ﯿ', 'ﯾ'],
        'ك' => ['ﻚ', 'ﻜ', 'ﻛ'],
        'ي' => ['ﻲ', 'ﻴ', 'ﻳ'],
        'أ' => ['ﺄ', 'ﺄ', 'ﺃ'],
        'ؤ' => ['ﺆ', 'ﺆ', 'ﺅ'],
        'إ' => ['ﺈ', 'ﺈ', 'ﺇ'],
        'ئ' => ['ﺊ', 'ﺌ', 'ﺋ'],
        'ة' => ['ﺔ', 'ﺘ', 'ﺗ'],
    ];
    public $tahoma = ['ه' => ['ﮫ', 'ﮭ', 'ﮬ']];
    public $normal = ['ه' => ['ﻪ', 'ﻬ', 'ﻫ']];
    public $mp_chars = ['آ', 'ا', 'د', 'ذ', 'ر', 'ز', 'ژ', 'و', 'أ', 'إ', 'ؤ'];
    public $ignorelist = ['', 'ٌ', 'ٍ', 'ً', 'ُ', 'ِ', 'َ', 'ّ', 'ٓ', 'ٰ', 'ٔ', 'ﹶ', 'ﹺ', 'ﹸ', 'ﹼ', 'ﹾ', 'ﹴ', 'ﹰ', 'ﱞ', 'ﱟ', 'ﱠ', 'ﱡ', 'ﱢ', 'ﱣ'];
    public $openClose = ['>', ')', '}', ']', '<', '(', '{', '['];
    public $en_chars = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];

    public function persianText($str, $z = null, $method = 'tahoma', $farsiNumber = true)
    {
        $en_str = '';
        $output = '';
        $e_output = '';
        if ($method == 'tahoma') {
            $this->p_chars = array_merge($this->p_chars, $this->tahoma);
        } else {
            $this->p_chars = array_merge($this->p_chars, $this->normal);
        }
        $str_len = mb_strlen($str);
        preg_match_all('/./u', $str, $ar);
        for ($i = 0; $i < $str_len; $i++) {
            $gatherNumbers = false;
            $str1 = $ar[0][$i];
            if (isset($ar[0][$i + 1]) && in_array($ar[0][$i + 1], $this->ignorelist)) {
                $str_next = $ar[0][$i + 2];
                if ($i == 2) {
                    $str_back = $ar[0][$i - 2];
                }
                if ($i != 2) {
                    $str_back = $ar[0][$i - 1];
                }
            } elseif (isset($ar[0][$i - 1]) && !in_array($ar[0][$i - 1], $this->ignorelist)) {
                $str_next = $ar[0][$i + 1];
                if ($i != 0) {
                    $str_back = $ar[0][$i - 1];
                }
            } else {
                if (isset($ar[0][$i + 1]) && !empty($ar[0][$i + 1])) {
                    $str_next = $ar[0][$i + 1];
                } else {
                    $str_next = $ar[0][$i - 1];
                }
                if ($i != 0) {
                    $str_back = $ar[0][$i - 2];
                }
            }
            if (!isset($str_back)) {
                $str_back = null;
            }
            if (!in_array($str1, $this->ignorelist)) {
                if (array_key_exists($str1, $this->p_chars)) {
                    if (empty($str_back) || !array_key_exists($str_back, $this->p_chars)) {
                        if (!array_key_exists($str_back, $this->p_chars) && !array_key_exists($str_next, $this->p_chars)) {
                            $output = $str1.$output;
                        } else {
                            $output = $this->p_chars[$str1][2].$output;
                        }
                        continue;
                    } elseif (array_key_exists($str_next, $this->p_chars) && array_key_exists($str_back, $this->p_chars)) {
                        if (in_array($str_back, $this->mp_chars) && array_key_exists($str_next, $this->p_chars)) {
                            $output = $this->p_chars[$str1][2].$output;
                        } else {
                            $output = $this->p_chars[$str1][1].$output;
                        }
                        continue;
                    } elseif (array_key_exists($str_back, $this->p_chars) && !array_key_exists($str_next, $this->p_chars)) {
                        if (in_array($str_back, $this->mp_chars)) {
                            $output = $str1.$output;
                        } else {
                            $output = $this->p_chars[$str1][0].$output;
                        }
                        continue;
                    }
                } elseif ($z == 'fa') {
                    $number = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۴', '۵', '۶', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                    switch ($str1) {
                        case ')': $str1 = '(';
                            break;
                        case '(': $str1 = ')';
                            break;
                        case '}': $str1 = '{';
                            break;
                        case '{': $str1 = '}';
                            break;
                        case ']': $str1 = '[';
                            break;
                        case '[': $str1 = ']';
                            break;
                        case '>': $str1 = '<';
                            break;
                        case '<': $str1 = '>';
                            break;
                    }
                    if (in_array($str1, $number)) {
                        if ($farsiNumber) {
                            $num .= $this->fa_number($str1);
                        } else {
                            $num .= $str1;
                        }
                        $str1 = '';
                    }

                    if (!in_array($str_next, $number)) {
                        if (in_array(strtolower($str1), $this->en_chars) || (($str1 == ' ' || $str1 == '.') && $en_str != '' && !in_array($str_next, $this->p_chars))) {
                            $en_str .= $str1.$num;
                            $str1 = '';
                        } else {
                            if ($en_str != '') {
                                if ($i == $str_len - 1) {
                                    $str1 = $str1.$num;
                                } else {
                                    $en_str .= $str1.$num;
                                }
                            } else {
                                $str1 = $str1.$num;
                            }
                        }
                        $num = '';
                    }
                    if ($en_str != '' || ($str1 != '' && $i == 0 && (!array_key_exists($str_next, $this->p_chars) && $str_next != ' ')) || $gatherNumbers) {
                        if (!array_key_exists($str1, $this->p_chars)) {
                            if (!array_key_exists($str_next, $this->p_chars) && $str_next != ' ' && !in_array($str_next, $this->openClose)) {
                                $en_str = $en_str.$str1;
                            } else {
                                if (in_array($ar[0][$i + 2], $this->en_chars)) {
                                    $en_str = $en_str.$str1;
                                } else {
                                    if ($str_next == ' ' && (in_array($ar[0][$i + 2], $number) || in_array(strtolower($ar[0][$i + 2]), $this->en_chars))) {
                                        $en_str = $en_str.$str1;
                                    } else {
                                        $output = $en_str.$output;
                                        $en_str = '';
                                    }
                                }
                            }
                        } elseif ($num) {
                            $en_str = $en_str.$num;
                        } else {
                            $output = $en_str.$str1.$output;
                            $en_str = '';
                        }
                    } else {
                        if (in_array($str1, $number) && $str_next == '.' && in_array($ar[0][$i + 2], $number)) {
                            $en_str = $str1;
                        } else {
                            $output = $str1.$output;
                        }
                    }
                } else {
                    if (($str1 == '،') || ($str1 == '؟') || ($str1 == 'ء') || (array_key_exists($str_next, $this->p_chars) && array_key_exists($str_back, $this->p_chars)) or
                            ($str1 == ' ' && array_key_exists($str_back, $this->p_chars)) || ($str1 == ' ' && array_key_exists($str_next, $this->p_chars))) {
                        if ($e_output) {
                            $output = $e_output.$output;
                            $e_output = '';
                        }
                        $output = $str1.$output;
                    } else {
                        $e_output .= $str1;
                        if (array_key_exists($str_next, $this->p_chars) || $str_next == '') {
                            $output = $e_output.$output;
                            $e_output = '';
                        }
                    }
                }
            } else {
                $output = $str1.$output;
            }

            $str_next = null;
            $str_back = null;
        }

        if (!empty($en_str)) {
            $output = $en_str.$output;
        }

        return $output;
    }

    public function fa_number($num)
    {
        return str_replace(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'], $num);
    }
}
