<?php session_start();
if (isset($_SESSION['_key_']) && !empty($_SESSION['_key_'])) {
    require_once('EncDec.php');
    require_once('db.ini.php');

    $damp = explode('|', trim(EncDec::Return('dec', $_SESSION['_key_'])));
    $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=? AND login_user.screen_name=?", [$damp[0], $damp[1]])->fetchAll();

    if (!empty($data_user) && !empty($_POST['id'])) {//Загрузка меню
        $data_id = strip_tags(trim($_POST['id']));
        $id_word = ((int)$data_id - 1);
        $response = [];
        if ($data_id > count($data_user)) {
            DB::prepare("INSERT INTO data_user(part_id,menu_id) VALUES(?,?)")->execute([$data_user[0]['part_id'], (count($data_user) + 1)]);
            $new = DB::lastInsertId();
            $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=? AND login_user.screen_name=?", [$damp[0], $damp[1]])->fetchAll();
            $g_dict = DB::run("SELECT count_ FROM game_dictionary")->fetchAll();
            $menu_count = DB::run("SELECT id,menu_count,30_procent FROM data_user WHERE part_id=?", [$data_user[0]['part_id']])->fetchAll();
            foreach ($g_dict as $item_g_dict) {
                foreach ($menu_count as $item) {
                    if ((((int)$item['menu_count'] / (int)$item_g_dict['count_']) * 100) . PHP_EOL >= 30 && (int)$item['30_procent'] < 1)
                        $id_30_procent = (int)$item['id'];
                    if ((int)$item['menu_count'] == (int)$item_g_dict['count_'] && (int)$item['30_procent'] == 1)
                        $id_en_procent = (int)$item['id'];
                }
            }
            !empty($id_30_procent) ? DB::run("UPDATE data_user SET 30_procent=? WHERE part_id=? AND id=?", [1, $data_user[0]['part_id'], $id_30_procent]) : '';
            !empty($id_en_procent) ? DB::run("UPDATE data_user SET 30_procent=? WHERE part_id=? AND id=?", [2, $data_user[0]['part_id'], $id_en_procent]) : '';
        }

        $game_diction = DB::run("SELECT word_,count_,value_ FROM game_dictionary WHERE id=?", [$data_id])->fetch();
        $str = preg_split('/(?!^)(?=.)/u', $game_diction['word_']);
        $list = explode('|', $game_diction['value_']);
        $layr_list = '';
        $user_word = explode('|', $data_user[$id_word]['word']);
        // Количество символов в строке
        for ($i = 0; $i < count($str); $i++) {
            $response[$i] = $str[$i];
            //echo '<button  id="'.$str[$i].'" class="word">'.$str[$i].'</button>';
        }
        $response['count'] = [$data_user[$id_word]['menu_count'], $game_diction['count_']];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        //echo '$<div class="count" id="count">Разгадано слов <br>'.$data_user[$id_word]['menu_count'].' из '.$game_diction['count_'].'</div>';
    }
}
?>