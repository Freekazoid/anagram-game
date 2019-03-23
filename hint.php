<?php session_start();
if (isset($_SESSION['_key_']) && !empty($_SESSION['_key_'])) {
    require_once('EncDec.php');
    require_once('db.ini.php');

    $damp = explode('|', trim(EncDec::Return('dec', $_SESSION['_key_'])));
    $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=? AND login_user.screen_name=?", [$damp[0], $damp[1]])->fetchAll();

    if (!empty($data_user) && !empty($_POST['hint'])) {//Помучаем id слова которое хотим получить подсказку или купить его
        $hint_id = strip_tags(trim($_POST['hint']));
        $id_word = ((int)$_POST['id'] - 1);
        $user_id_wird = explode('|', $data_user[$id_word]['word_id']);
        $dut_ = '';
        $response = [];
        $koin1 = 10;
        $koin2 = 30;
        $user_list = explode('|', $data_user[$id_word]['word']);
        $game_diction = DB::run("SELECT _word_,description FROM vocabulary WHERE id=?", [$hint_id])->fetch();
        $load = (int)file_get_contents("http://test.loc/ajax/point.php?id=" . $data_user[0]['part_id']);//Сумма на счету.

        if (in_array($hint_id, $user_id_wird)) {//Если слово разгадано то даем описание
            for ($dut = 0; $dut < iconv_strlen($game_diction['_word_'], 'utf-8'); $dut++)
                $dut_ .= '. ';

            $word = in_array($game_diction['_word_'], $user_list) ? $game_diction['_word_'] : $dut_;
            //echo '<h3>'.$word.'</h3><div>'.$game_diction['description'].'</div>';
            $response[0] = [0, $word, $game_diction['description']];
        } elseif ($load >= $koin1) {//Если на счете денег хватает на подсказку предлогаем купить описание к загаданномму слову
            //echo '<h3>Подсказка</h3><div class="hint">Вы можете купить подсказку за очки.<br>Стоимость подсказки 20 очков.<span class="pay_point" data-point="20">Купить подсказку</span></div>';
            $response[1] = [$koin1, 'Подсказка', 'Вы можете купить подсказку за очки.', 'Стоимость подсказки ' . $koin1 . ' очков.', 'Купить подсказку'];

            if ($load >= $koin2) {//Если на счете хватает денег на покупку самого слова то предлогаем его открыть
                //echo '<h3>Слово</h3><div class="hint">Вы можете купить слова за очки.<br>Стоимость слова 30 очков.<span class="pay_point" data-point="30">Купить слово</span></div>';
                $response[2] = [$koin2, 'Слово', 'Вы можете купить слово за очки.', 'Стоимость слова ' . $koin2 . ' очков.', 'Купить слово'];
            }
        } else {//Если на счете нехватает ни на что то говорим об этом
            //echo '<h3>Извините</h3><div>У вас недостаточно очков для покупки подсказки.</div>';
            $response[3] = [0, 'Извините', 'У вас недостаточно очков для покупки подсказки.'];
        }
        //echo '<br><br>';
        //echo ' <span class="back" onclick="reload_list();">Назад</span>';
        $response[4] = [0, 'Назад']; //Кнопка выхода
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
?>