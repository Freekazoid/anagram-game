<?php session_start();
if (isset($_SESSION['_key_']) && !empty($_SESSION['_key_'])) {
    require_once('EncDec.php');
    require_once('db.ini.php');

    $damp = explode('|', trim(EncDec::Return('dec', $_SESSION['_key_'])));
    $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=? AND login_user.screen_name=?", [$damp[0], $damp[1]])->fetchAll();

    if (!empty($data_user)) {//Загрузка меню
        $count_ = DB::run("SELECT count_ FROM game_dictionary")->fetchAll();
        $response = [];
        $menu = [];
        foreach ($data_user as $i => $menu_id) {
            $menu[$menu_id['part_id']] = DB::run("SELECT word_,count_,value_ FROM game_dictionary WHERE id=?", [$menu_id['menu_id']])->fetch();

            if ((int)$menu_id['30_procent'] == (int)$data_user[$i]['30_HideShove'] || (int)$menu_id['30_procent'] == (int)$data_user[$i]['AllHideShove']) {//Если в настройках скрыты слова больше 30% или полностью открытые слова
                //echo $data_user[$i]['30_HideShove'].' - - - '.$data_user[$i]['AllHideShove']."\n";
            } else {

                if ($menu_id['30_procent'] == 2)
                    $response[(int)$menu_id['menu_id']] = [(int)$menu_id['menu_id'], $menu[$menu_id['part_id']]['word_'], $menu_id['menu_count'], $menu[$menu_id['part_id']]['count_'], 'new'];
                //echo '<div class="menu" data-id="new"><span class="new" id="new">предложить Своё слово</span><span class="head2">'.$menu[$menu_id['part_id']]['word_'].'</span><span class="count">Разгаданно '. $menu_id['menu_count'] .' / '. $menu[$menu_id['part_id']]['count_'] .'</span></div>';
                else
                    $response[(int)$menu_id['menu_id']] = [(int)$menu_id['menu_id'], $menu[$menu_id['part_id']]['word_'], $menu_id['menu_count'], $menu[$menu_id['part_id']]['count_']];
                //echo '<div class="menu" data-id="'.$menu_id['menu_id'].'"><span class="head1">'.$menu[$menu_id['part_id']]['word_'].'</span><span class="count">Разгаданно '. $menu_id['menu_count'] .' / '. $menu[$menu_id['part_id']]['count_'] .'</span></div>';
            }
        }
        foreach ($data_user as $key => $item) {
            if ((int)$menu_id['30_procent'] == (int)$data_user[$i]['30_HideShove'] || (int)$menu_id['30_procent'] == (int)$data_user[$i]['AllHideShove']) {//Если в настройках скрыты слова больше 30% или полностью открытые слова
                //echo $data_user[$i]['30_HideShove'].' - - - '.$data_user[$i]['AllHideShove']."\n";
            } else if (((((int)$item['menu_count'] / (int)$count_[$key]['count_']) * 100) . PHP_EOL >= 30) && (int)$item['30_procent'] < 1) {
                DB::run("UPDATE login_user SET active=? WHERE id=?", [(count($data_user) + 1), $data_user[0]['part_id']]);
                $response[($menu_id['menu_id'] + 1)] = [($menu_id['menu_id'] + 1), '?.?.?.?.?', 'Открыть слово'];
                //echo '<div class="menu" data-id="'.($menu_id['menu_id']+1).'"><span class="head1">?.?.?</span><span class="count">Открыть слово</span></div>';
            }
        }
        array_multisort($response, SORT_ASC, SORT_NUMERIC);//Сортируем вывод переопределяем ключи
        echo json_encode($response, JSON_UNESCAPED_UNICODE);//Вывод всех данных
    }
}
?>