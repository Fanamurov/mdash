<?php

namespace EMT\Tret;

use EMT\Util;

class Quote extends AbstractTret
{
    /**
     * Базовые параметры тофа.
     *
     * @var array
     */
    public $title = 'Кавычки';

    public $rules = [
        'quotes_outside_a' => [
            'description' => 'Кавычки вне тэга <a>',
            'pattern' => '/(\<%%\_\_[^\>]+\>)\"(.+?)\"(\<\/%%\_\_[^\>]+\>)/s',
            'replacement' => '"\1\2\3"',
        ],

        'open_quote' => [
            'description' => 'Открывающая кавычка',
            'pattern' => '/(^|\(|\s|\>|-)(\"|\\\")(\S+)/iue',
            'replacement' => '$m[1] . \EMT\Tret\AbstractTret::QUOTE_FIRS_OPEN . $m[3]',
        ],
        'close_quote' => [
            'description' => 'Закрывающая кавычка',
            'pattern' => '/([a-zа-яё0-9]|\.|\&hellip\;|\!|\?|\>|\)|\:)((\"|\\\")+)(\.|\&hellip\;|\;|\:|\?|\!|\,|\s|\)|\<\/|$)/uie',
            'replacement' => '$m[1] . str_repeat(\EMT\Tret\AbstractTret::QUOTE_FIRS_CLOSE, substr_count($m[2],"\"") ) . $m[4]',
        ],
        'close_quote_adv' => [
            'description' => 'Закрывающая кавычка особые случаи',
            'pattern' => [
                    '/([a-zа-яё0-9]|\.|\&hellip\;|\!|\?|\>|\)|\:)((\"|\\\"|\&laquo\;)+)(\<[^\>]+\>)(\.|\&hellip\;|\;|\:|\?|\!|\,|\)|\<\/|$| )/uie',
                    '/([a-zа-яё0-9]|\.|\&hellip\;|\!|\?|\>|\)|\:)(\s+)((\"|\\\")+)(\s+)(\.|\&hellip\;|\;|\:|\?|\!|\,|\)|\<\/|$| )/uie',
                    '/\>(\&laquo\;)\.($|\s|\<)/ui',
                    '/\>(\&laquo\;),($|\s|\<|\S)/ui',
                ],
            'replacement' => [
                    '$m[1] . str_repeat(\EMT\Tret\AbstractTret::QUOTE_FIRS_CLOSE, substr_count($m[2],"\"")+substr_count($m[2],"&laquo;") ) . $m[4]. $m[5]',
                    '$m[1] .$m[2]. str_repeat(\EMT\Tret\AbstractTret::QUOTE_FIRS_CLOSE, substr_count($m[3],"\"")+substr_count($m[3],"&laquo;") ) . $m[5]. $m[6]',
                    '>&raquo;.\2',
                    '>&raquo;,\2',
                ],
        ],
        'open_quote_adv' => [
            'description' => 'Открывающая кавычка особые случаи',
            'pattern' => '/(^|\(|\s|\>)(\"|\\\")(\s)(\S+)/iue',
            'replacement' => '$m[1] . \EMT\Tret\AbstractTret::QUOTE_FIRS_OPEN .$m[4]',
        ],
        'quotation' => [
            'description' => 'Внутренние кавычки-лапки и дюймы',
            'function' => 'build_sub_quotations',
        ],
    ];

    /**
     * @param string $text
     */
    protected function inject_in($pos, $text)
    {
        for ($i = 0; $i < strlen($text); $i++) {
            $this->_text[$pos + $i] = $text[$i];
        }
    }

    protected function build_sub_quotations()
    {
        global $__ax, $__ay;
        $okposstack = ['0'];
        $okpos = 0;
        $level = 0;
        $off = 0;
        while (true) {
            $p = Util::strpos_ex($this->_text, ['&laquo;', '&raquo;'], $off);
            if ($p === false) {
                break;
            }
            if ($p['str'] == '&laquo;') {
                if ($level > 0) {
                    if (! $this->is_on('no_bdquotes')) {
                        $this->inject_in($p['pos'], self::QUOTE_CRAWSE_OPEN);
                    }
                }
                $level++;
            }
            if ($p['str'] == '&raquo;') {
                $level--;
                if ($level > 0) {
                    if (! $this->is_on('no_bdquotes')) {
                        $this->inject_in($p['pos'], self::QUOTE_CRAWSE_CLOSE);
                    }
                }
            }
            $off = $p['pos'] + strlen($p['str']);
            if ($level == 0) {
                $okpos = $off;
                array_push($okposstack, $okpos);
            } elseif ($level < 0) { // уровень стал меньше нуля
                if (! $this->is_on('no_inches')) {
                    do {
                        $lokpos = array_pop($okposstack);
                        $k = substr($this->_text, $lokpos, $off - $lokpos);
                        $k = str_replace(self::QUOTE_CRAWSE_OPEN, self::QUOTE_FIRS_OPEN, $k);
                        $k = str_replace(self::QUOTE_CRAWSE_CLOSE, self::QUOTE_FIRS_CLOSE, $k);
                        //$k = preg_replace("/(^|[^0-9])([0-9]+)\&raquo\;/ui", '\1\2&Prime;', $k, 1, $amount);

                        $amount = 0;
                        $__ax = preg_match_all("/(^|[^0-9])([0-9]+)\&raquo\;/ui", $k, $m);
                        $__ay = 0;
                        if ($__ax) {
                            $k = preg_replace_callback("/(^|[^0-9])([0-9]+)\&raquo\;/ui",
                                create_function('$m', 'global $__ax,$__ay; $__ay++; if ($__ay==$__ax) { return $m[1].$m[2]."&Prime;";} return $m[0];'),
                                $k);
                            $amount = 1;
                        }
                    } while (($amount == 0) && count($okposstack));

                    // успешно сделали замену
                    if ($amount == 1) {
                        // заново просмотрим содержимое
                        $this->_text = substr($this->_text, 0, $lokpos).$k.substr($this->_text, $off);
                        $off = $lokpos;
                        $level = 0;
                        continue;
                    }

                    // иначе просто заменим последнюю явно на &quot; от отчаяния
                    if ($amount == 0) {
                        // говорим, что всё в порядке
                        $level = 0;
                        $this->_text = substr($this->_text, 0, $p['pos']).'&quot;'.substr($this->_text, $off);
                        $off = $p['pos'] + strlen('&quot;');
                        $okposstack = [$off];
                        continue;
                    }
                }
            }
        }
        // не совпало количество, отменяем все подкавычки
        if ($level != 0) {

            // закрывающих меньше, чем надо
            if ($level > 0) {
                $k = substr($this->_text, $okpos);
                $k = str_replace(self::QUOTE_CRAWSE_OPEN, self::QUOTE_FIRS_OPEN, $k);
                $k = str_replace(self::QUOTE_CRAWSE_CLOSE, self::QUOTE_FIRS_CLOSE, $k);
                $this->_text = substr($this->_text, 0, $okpos).$k;
            }
        }
    }
}
