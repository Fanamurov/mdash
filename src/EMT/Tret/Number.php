<?php

namespace EMT\Tret;

class Number extends AbstractTret
{
    public $title = 'Числа, дроби, математические знаки';

    public $rules = [
        'minus_between_nums' => [
            'description' => 'Расстановка знака минус между числами',
            'pattern' => '/(\d+)\-(\d)/i',
            'replacement' => '\1&minus;\2',
        ],
        'minus_in_numbers_range' => [
            'description' => 'Расстановка знака минус между диапозоном чисел',
            'pattern' => '/(^|\s|\&nbsp\;)(\&minus\;|\-)(\d+)(\.\.\.|\&hellip\;)(\s|\&nbsp\;)?(\+|\-|\&minus\;)?(\d+)/ie',
            'replacement' => '$m[1] ."&minus;".$m[3] . $m[4].$m[5].($m[6]=="+"?$m[6]:"&minus;").$m[7]',
        ],
        'auto_times_x' => [
            'description' => 'Замена x на символ × в размерных единицах',
            'cycled' => true,
            'pattern' => '/([^a-zA-Z><]|^)(\&times\;)?(\d+)(\040*)(x|х)(\040*)(\d+)([^a-zA-Z><]|$)/u',
            'replacement' => '\1\2\3&times;\7\8',
        ],
        'numeric_sub' => [
            'description' => 'Нижний индекс',
            'pattern' => '/([a-zа-яё0-9])\_([\d]{1,3})([^а-яёa-z0-9]|$)/ieu',
            'replacement' => '$m[1] . $this->tag($this->tag($m[2],"small"),"sub") . $m[3]',
        ],
        'numeric_sup' => [
            'description' => 'Верхний индекс',
            'pattern' => '/([a-zа-яё0-9])\^([\d]{1,3})([^а-яёa-z0-9]|$)/ieu',
            'replacement' => '$m[1] . $this->tag($this->tag($m[2],"small"),"sup") . $m[3]',
        ],
        'simple_fraction' => [
            'description' => 'Замена дробей 1/2, 1/4, 3/4 на соответствующие символы',
            'pattern' => ['/(^|\D)1\/(2|4)(\D)/', '/(^|\D)3\/4(\D)/'],
            'replacement' => ['\1&frac1\2;\3', '\1&frac34;\2'],
        ],
        'math_chars' => [
            'description' => 'Математические знаки больше/меньше/плюс минус/неравно',
            'pattern' => ['/!=/', '/\<=/', '/([^=]|^)\>=/', '/~=/', '/\+-/'],
            'replacement' => ['&ne;', '&le;', '\1&ge;', '&cong;', '&plusmn;'],
        ],
        /*
    'split_number_to_triads' => array(
            'description'	=> 'Разбиение числа на триады',
            'cycled'		=> true,
            'pattern' 		=> '/([0-9])([0-9]{3})([^0-9]|$)/u',
            'replacement' 	=> '\1&thinsp;\2\3'
        ),
        */
        'thinsp_between_number_triads' => [
            'description' => 'Объединение триад чисел полупробелом',
            'pattern' => '/([0-9]{1,3}( [0-9]{3}){1,})(.|$)/ue',
            'replacement' => '($m[3]=="-"? $m[0]:str_replace(" ","&thinsp;",$m[1]).$m[3])',
        ],
        'thinsp_between_no_and_number' => [
            'description' => 'Пробел между симоволом номера и числом',
            'pattern' => '/(№|\&#8470\;)(\s|&nbsp;)*(\d)/iu',
            'replacement' => '&#8470;&thinsp;\3',
        ],
        'thinsp_between_sect_and_number' => [
            'description' => 'Пробел между параграфом и числом',
            'pattern' => '/(§|\&sect\;)(\s|&nbsp;)*(\d+|[IVX]+|[a-zа-яё]+)/ui',
            'replacement' => '&sect;&thinsp;\3',
        ],
    ];
}
