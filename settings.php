<?php session_start();
if (isset($_SESSION['_key_']) && !empty($_SESSION['_key_'])) {
    require_once('EncDec.php');
    require_once('db.ini.php');

    $damp = explode('|', trim(EncDec::Return('dec', $_SESSION['_key_'])));
    $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=? AND login_user.screen_name=?", [$damp[0], $damp[1]])->fetchAll();
    //сменить оформление -> в будущем
    $response = [
        0 => 'Настройки',
        1 => ['Настройка звука', 'вкл', 'выкл'],//
        2 => ['Убрать разгаданные слова', $data_user[0]['AllHideShove']],//готово
        3 => ['Убрать слова разгаданные больше половины', $data_user[0]['30_HideShove']],//готово
        4 => ['Размер шрифта', 'Маленький', 'Средний', 'Большой', $data_user[0]['font_size']],//готово
        5 => ['Вид шрифта', 'Без засечек', 'С засечками', 'Рукописный', $data_user[0]['type_font']]//готово
    ];


    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}