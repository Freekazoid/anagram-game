<?php session_start();
/**
 * Created by PhpStorm.
 * User: Freekazoid
 * Date: 13.11.2018
 * Time: 21:46
 */


if (isset($_SESSION['_key_']) && !empty($_SESSION['_key_'])) {
    require_once('EncDec.php');
    require_once('db.ini.php');

    $damp = explode('|', trim(EncDec::Return('dec', $_SESSION['_key_'])));
    $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=? AND login_user.screen_name=?", [$damp[0], $damp[1]])->fetchAll();

    if (!empty($data_user) && !empty($_POST['id'])) { //Загрузка меню
        $response[0] = ['Приз Бонус'];
        $response[1] = ['Вы можете добавить Свое слово в игру.', 'Слово не должно быть меньше 10 букв и не больше 15.', 'Ваше слово...', 'Добавить'];
        $response[2] = ['Или получить 1000 очков', 'Получить'];
        $response[3] = ['Назад'];
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        /*
           <h4>Приз Бонус</h4>
           <p>Вы можете добавить Свое слово в игру.<br>Слово не должно быть меньше 10 букв и не больше 15.</p>
           <div id="now"><!-- Добавление нового слова -->
           Слово: <input type="text" id="words" name="words"><br>
               <div id="now_note"></div>
               <input type="button" id="send" value="Добавить">
               <br>
               <h3>или получить 1000 очков</h3>
               <input type="button" id="money" value="Получить">
           </div><!-- Добавление нового слова -->
           <br>
           <span class="back" onclick="reload_menu();">Назад</span>
        */
    }
}
?>