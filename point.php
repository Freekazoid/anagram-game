<?php session_start();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    require_once('db.ini.php');
    $id = strip_tags(trim($_GET['id']));
    $data_user = DB::run("SELECT login_user.*,data_user.* FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=?", [$id])->fetch();
    if (!empty($data_user) && !empty($_GET['point'])) {//За разгаданное слово начисляем очки
        $point = (int)$data_user['point'] + (int)$_GET['point'];
        DB::run("UPDATE login_user SET point=? WHERE id=?", [$point, $id]);
    } elseif (!empty($data_user) && empty($_GET['point'])) {//Сколько всего очков
        echo json_encode((int)$data_user['point'], JSON_UNESCAPED_UNICODE);
    }
} elseif (!empty($_POST['hint']) && !empty($_POST['point']) && isset($_SESSION['_key_']) && !empty($_SESSION['_key_'])) {
    require_once('EncDec.php');
    require_once('db.ini.php');
    $menu_id = (int)strip_tags(trim($_POST['id']));
    $point = (int)strip_tags(trim($_POST['point']));
    $hint_id = (int)strip_tags(trim($_POST['hint']));

    $damp = explode('|', trim(EncDec::Return('dec', $_SESSION['_key_'])));
    $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=? AND login_user.screen_name=?", [$damp[0], $damp[1]])->fetchAll();
    $id_word = ((int)$_POST['id'] - 1);
    $game_diction = DB::run("SELECT _word_, description FROM vocabulary WHERE id=?", [$hint_id])->fetch();
    $count = ((int)$data_user[$id_word]['menu_count'] + 1);
    $dut_ = '';
    $response = [];
    $koin1 = 10;
    $koin2 = 30;
    $str = $data_user[$id_word]['word'] . '|' . $game_diction['_word_'];
    $str_key = $data_user[$id_word]['word_id'] . '|' . $hint_id;
    for ($dut = 0; $dut < iconv_strlen($game_diction['_word_'], 'utf-8'); $dut++)
        $dut_ .= '. ';

    $u_point = (int)$data_user[0]['point'];
    $u_id = (int)$data_user[0]['part_id'];
    if ($u_point >= $point) {
        $result = ($u_point - $point);
        DB::run("UPDATE login_user SET point=? WHERE id=?", [$result, $u_id]);
        if ($point == $koin1) {//Подсказка
            //echo '<h3>'.$dut_.'</h3><div>'.$game_diction['description'].'<span class="back" onclick="reload_list();">Назад</span></div>';
            $response = [trim($dut_), $game_diction['description']];
        } else if ($point == $koin2) {//Слово
            DB::run("UPDATE data_user SET word=?,word_id=?,menu_count=? WHERE part_id=? AND menu_id=?", [$str, $str_key, $count, $u_id, $menu_id]);
            //echo '<h3>'.$game_diction['_word_'].'</h3><div>'.$game_diction['description'].'<span class="back" onclick="reload_list();">Назад</span></div>';
            $response = [$game_diction['_word_'], $game_diction['description']];
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
} elseif (isset($_SESSION['_key_']) && !empty($_SESSION['_key_']) && !empty($_SESSION['fool']) && isset($_POST['needle']) && !empty($_POST['needle']) == 'point') {
    require_once('EncDec.php');
    require_once('db.ini.php');
    $damp = explode('|', trim(EncDec::Return('dec', $_SESSION['_key_'])));
    $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=? AND login_user.screen_name=?", [$damp[0], $damp[1]])->fetchAll();
    if (!empty($data_user)) {
        DB::run("UPDATE data_user SET 30_procent=? WHERE id=?", [3, $data_user[0]['part_id']]);
        $pdo = DB::run("UPDATE login_user SET point=? WHERE id=?", [(int)$data_user[0]['point'] + 1000, $data_user[0]['part_id']]);
        echo is_null($pdo) ? '0' : '1';
        $_SESSION['fool'] = 'true';
    }
}
?>