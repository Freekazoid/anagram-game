<?php

class dictionary
{
    public static function getResult($it)
    {//Проверка на соответствие от 10 до 15 букв
        $num = preg_replace('/[^0-9]/', '', $it);
        if ((mb_strlen($it, 'UTF-8') >= 10 && mb_strlen($it, 'UTF-8') <= 15) && $num == '') {
            $arr = self::getWord($it);
            if (!empty($arr))
                return self::saveDB($arr, $it);
            else return 3;
        } else return 2;
    }

    private static function getWord($tis_word)
    {//Нахождение слов в словаре Арифморов
        set_time_limit(0);
        $RESULT = [];
        $readFile = self::DBread();
        $str = mb_strtolower(trim($tis_word), 'utf-8');
        $str = trim(strip_tags(preg_replace("[^абвгдеёжзийклмнопрстуфхцчшщъыьэюя]", "", $str)));
        $str1 = self::mbStringToArray($str);
        $limit1 = count($str1);
        $numeric = 0;
        foreach ($readFile as $key => $item) {
            $flag = 0;
            $key = trim($key);
            if ((strlen($str) >= strlen($key)) && !empty($key)) {
                $search = self::mbStringToArray($key);
                $limit2 = count($search);
                for ($j = 0; $j < $limit1; $j++) {
                    if (in_array($str1[$j], $search)) {
                        $temp = $str1;
                        for ($z = 0; $z < $limit2; $z++) {
                            if (in_array($search[$z], $temp) && !empty($temp)) {
                                unset($temp[array_search($search[$z], $temp)]);
                                $flag = 1;
                            } else {
                                $flag = 0;
                                break;
                            }
                        }
                    }
                }
                if ($flag == 1 && $limit2) {
                    if (strlen($key) != strlen($str) && self::chastrechiRUS($key)[0] < 4 && strlen($key) > 2)
                        $RESULT[$numeric++] = $key;
                }
            }
        }
        return $RESULT;
    }

    private static function DBread()
    {//Чтение файла словоря
        require_once('db.ini.php');
        $dictionary = DB::run("SELECT * FROM vocabulary")->fetchAll();
        $dict_word = [];
        foreach ($dictionary as $key => $item) {
            $dict_word[$item['_word_']] = $item['description'];
        }
        return $dict_word;
    }

    private static function mbStringToArray($str = '')
    {//преобразую строку в массив
        return preg_split('/(?!^)(?=.)/u', $str);
    }

    private static function chastrechiRUS($string)
    {//Проверка на принадлежность части речи
        $groups = [/* Группы окончаний: 1. глагол 2. существительное 3. предлог */
            1 => ['ила', 'ыла', 'ена', 'ейте', 'уйте', 'ите', 'или', 'ыли', 'ей', 'уй', 'ил', 'ыл', 'им', 'ым', 'ен',
                'ило', 'ыло', 'ено', 'ят', 'ует', 'уют', 'ит', 'ыт', 'ены', 'ить', 'ыть', 'ишь', 'ую', 'ю', 'ла', 'на', 'ете', 'йте',
                'ли', 'й', 'л', 'ем', 'н', 'ло', 'ет', 'ют', 'ны', 'ть', 'ешь', 'нно'],
            2 => ['а', 'ев', 'ов', 'ье', 'иями', 'ями', 'ами', 'еи', 'ии', 'и', 'ией', 'ей', 'ой', 'ий', 'й', 'иям', 'ям', 'ием', 'ем', 'от', 'ат',
                'ам', 'ом', 'о', 'у', 'ах', 'иях', 'ях', 'ы', 'ь', 'ию', 'ью', 'ю', 'ия', 'ья', 'я', 'ок', 'мва', 'яна', 'ровать', 'ег', 'ги', 'га', 'сть', 'нит', 'сти'],
            3 => ['на', 'по', 'из', 'то'],
            4 => ['']
        ];
        $res = [];
        $string = mb_strtolower($string);
        $words = explode(' ', $string);
        foreach ($words as $wk => $w) {
            foreach ($groups as $gk => $g) {
                foreach ($g as $part) {
                    $len_part = mb_strlen($part);
                    if (mb_substr($w, -$len_part) == $part || mb_substr($w, 0, $len_part) == $part) {
                        if ($w != $part) $res[$wk][$gk] = mb_strlen($part); else $res[$wk][$gk] = 99;
                    }
                }
            }
            if (!isset($res[$wk][$gk])) $res[$wk][$gk] = 0;
        }
        $result = array();
        foreach ($res as $r) {
            arsort($r);
            array_push($result, key($r));
        }
        return $result;
    }

    private static function saveDB($word, $it)
    {
        require_once('db.ini.php');
        $result_id = '';
        $result_word = '';
        $id = null;
        foreach ($word as $i => $item) {
            $result_id .= DB::run("SELECT id FROM vocabulary WHERE _word_=?", [$item])->fetch()['id'] . '|';
            $result_word .= $item . '|';
        }
        if (empty(DB::run("SELECT id FROM game_dictionary WHERE word_=?", [$it])->fetch()['id'])) {
            DB::prepare("INSERT INTO game_dictionary(word_, count_, value_, value_id) VALUES(?,?,?,?)")->execute([$it, count($word), substr($result_word, 0, -1), substr($result_id, 0, -1)]);
            $id = DB::lastInsertId();
        }
        return empty($id) ? 4 : 1;
    }

}//dictionary
?>