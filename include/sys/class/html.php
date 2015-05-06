<?php

class html {

    static function a($text,array $params= array()) {
        return self::mktag("a", $text, $params);
    }
    
    static function mktag($tag,$text,array $params= array()){
        $a = array();
        foreach ($params as $k => $v) {
            $v = self::chars($v);
            $a[] = "{$k}=\"{$v}\"";
        }
        $a = "<$tag " . implode(" ", $a) . ">" . ($text!==FALSE?"{$text}</$tag>":"");
        return $a;
    }

    public static function chars($value, $double_encode = TRUE) {
        return htmlspecialchars((string) $value, ENT_QUOTES, SYSCHARSET, $double_encode);
    }

    public static function entities($value, $double_encode = TRUE) {
        return htmlentities((string) $value, ENT_QUOTES, SYSCHARSET, $double_encode);
    }

    public static function obfuscate($string) {
        $safe = '';
        foreach (str_split($string) as $letter) {
            switch (rand(1, 3)) {
                // HTML entity code
                case 1:
                    $safe .= '&#' . ord($letter) . ';';
                    break;

                // Hex character code
                case 2:
                    $safe .= '&#x' . dechex(ord($letter)) . ';';
                    break;

                // Raw (no) encoding
                case 3:
                    $safe .= $letter;
            }
        }

        return $safe;
    }

}
