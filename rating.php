<?php session_start();
if (isset($_SESSION['_key_']) && !empty($_SESSION['_key_'])) {
    require_once('EncDec.php');
    require_once('db.ini.php');

    $damp = explode('|', trim(EncDec::Return('dec', $_SESSION['_key_'])));
    $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=? AND login_user.screen_name=?", [$damp[0], $damp[1]])->fetchAll();

    if (!empty($data_user)) {
        $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id ")->fetchAll();
        $part_id = [];
        $data = [];
        $count_w = [];
        $max_word = 0;
        $response = [];
        $c = 0;
        /* Формируем вывод данных */
        foreach ($data_user as $key => $arr) {
            if (array_key_exists($arr['part_id'], $part_id)) {
                $part_id[$arr['part_id']] .= (string)$key . '|';
            } else $part_id[$arr['part_id']] = (string)$key . '|';
        }
        foreach ($part_id as $i => $ke) {
            $part_id[$i] = explode('|', $ke);
            $part_id[$i] = array_diff($part_id[$i], array(''));

        }
        foreach ($part_id as $arr) {
            $data[$c] = [];
            foreach ($arr as $i) {
                $cou = count(explode('|', $data_user[$i]['word']));
                $new = ((int)$data_user[$i]['30_procent'] == 3 ? 1 : 0);
                $data[$c]['count_w'] = (empty($data[$c]['count_w']) ? 0 : $data[$c]['count_w']) + $cou;
                $data[$c]['max_word'] = (int)($max_word > (int)$data_user[$i]['menu_id'] ? $max_word : $data_user[$i]['menu_id']);
                $data[$c]['screen_name'] = $data_user[$i]['screen_name'];
                $data[$c]['point'] = (int)$data_user[$i]['point'];
                $data[$c]['new'] = (empty($data[$c]['new']) ? 0 : $data[$c]['new']) + $new;
            }
            $c++;
        }

        /* Сортировка массива по количеству разгаданных слов */
        foreach ($data as $key => $row) {
            $count_w[$key] = $row['count_w'];
        }
        array_multisort($count_w, SORT_DESC, $data);
        /* Сортировка массива по количеству разгаданных слов */


        //echo '<h4>Список игроков.</h4><div class="table_head"> <span>№</span> | <span style="width:70px;  text-align: center;">имя</span> | <span>очки</span> | <span>разгад слова</span> | <span>отк слов</span> | <span>Добавил слов</span>|</div>';
        foreach ($data as $key => $item) {
            $response[$key] = [$item['screen_name'], $item['point'], ($item['count_w'] - $item['max_word']), $item['max_word'], $item['new']];
            //echo '<div class="rating_name">' . $key . '<span>' . $item['screen_name'] . '</span>|<span>' . $item['point'] . '</span>|<span>' . ($item['count_w'] - $item['max_word']) . '</span>|<span>' . $item['max_word'] . '</span>|<span>' . $item['new'] . '</span>|' . "</div>";
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
?>