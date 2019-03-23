<?php session_start();
if (isset($_SESSION['_key_']) && !empty($_SESSION['_key_'])) {
    require_once('EncDec.php');
    require_once('db.ini.php');

    $damp = explode('|', trim(EncDec::Return('dec', $_SESSION['_key_'])));
    $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=? AND login_user.screen_name=?", [$damp[0], $damp[1]])->fetchAll();

    if (!empty($data_user) && !empty($_POST['id']) && empty($_POST['word'])) {//Загрузка списка слов
        $data_id = strip_tags(trim($_POST['id']));
        $game_diction = DB::run("SELECT word_,count_,value_,value_id FROM game_dictionary WHERE id=?", [$data_id])->fetch();
        $str = preg_split('/(?!^)(?=.)/u', $game_diction['word_']);
        $list = explode('|', $game_diction['value_']);
        $list_id = explode('|', $game_diction['value_id']);
        $list_word_id = array_combine($list_id, $list);
        $response = [];
        $dut_ = '';
        $count = 0;
        $count_2 = 0;
        $user_word = explode('|', $data_user[((int)$data_id - 1)]['word']);

        uasort($list_word_id, function ($a, $b) {
            if (strlen($a) != strlen($b))
                return (strlen($a) < strlen($b)) ? -1 : 1;
        });
        $list_word_id = array_chunk($list_word_id, 4, TRUE);
        foreach ($list_word_id as $chanc) {
            foreach ($chanc as $id_key => $item) {
                if (in_array($item, $user_word)) {
                    array_push($response, [$id_key, $item]);
                } else {
                    for ($dut = 0; $dut < iconv_strlen($item, 'utf-8'); $dut++)
                        $dut_ .= '. ';
                    array_push($response, [$id_key, $dut_]);
                    $dut_ = '';
                }
            }
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    } elseif (!empty($_POST['word']) && !empty($_POST['id'])) {//Проверяем слово в списке загаданных слов
        $word = strip_tags(trim($_POST['word']));
        $data_id = (int)strip_tags(trim($_POST['id']));
        $id_key = DB::run("SELECT id FROM vocabulary WHERE _word_=?", [$word])->fetch()['id'];
        $id_word = ((int)$_POST['id'] - 1);
        $game_diction = DB::run("SELECT word_,count_,value_ FROM game_dictionary WHERE id=?", [$data_id])->fetch();
        $list = explode('|', $game_diction['value_']);
        $user_list = explode('|', $data_user[$id_word]['word']);
        $count = ((int)$data_user[$id_word]['menu_count'] + 1);
        $c_word = (int)iconv_strlen($word, 'utf-8');
        if (in_array($word, $list) && !in_array($word, $user_list) && $c_word >= 2) {//Если есть слово в загаданных и нет в списке отгаданных слов пользователя
            $str = $data_user[$id_word]['word'] . '|' . $word;
            $str_key = $data_user[$id_word]['word_id'] . '|' . $id_key;
            DB::run("UPDATE data_user SET word=?,word_id=?,menu_count=? WHERE part_id=? AND menu_id=?", [$str, $str_key, $count, $data_user[$id_word]['part_id'], $data_user[$id_word]['menu_id']]);
            file_get_contents("http://test.loc/ajax/point.php?point=" . $c_word . "&id=" . $data_user[0]['part_id']);
            echo json_encode(4, JSON_UNESCAPED_UNICODE);
        } elseif (in_array($word, $user_list) && $c_word >= 2) {//Если слово есть в списке слов пользователя
            echo json_encode(5, JSON_UNESCAPED_UNICODE);
        } else if (!in_array($word, $list) && !in_array($word, $user_list) && $c_word >= 2) {//Если слова вообще нет
            echo json_encode(6, JSON_UNESCAPED_UNICODE);
        }
    } elseif (!empty($_POST['ids']) && empty($_POST['id']) && empty($_POST['word'])) {
        $data_id = strip_tags(trim($_POST['ids']));
        $game_diction = DB::run("SELECT word_,count_,value_ FROM game_dictionary WHERE id=?", [$data_id])->fetch();
        echo '<div class="count" id="count">Разгадано слов <br>' . $data_user[((int)$_POST['ids'] - 1)]['menu_count'] . ' из ' . $game_diction['count_'] . '</div>';
    }
}
?>