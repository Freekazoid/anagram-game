<?php session_start();
if (isset($_SESSION['_key_']) && !empty($_SESSION['_key_'])) {
    require_once('EncDec.php');
    require_once('db.ini.php');

    $damp = explode('|', trim(EncDec::Return('dec', $_SESSION['_key_'])));
    $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=? AND login_user.screen_name=?", [$damp[0], $damp[1]])->fetchAll();

    $response = [];
    $response[1] = 'Анагра́мма';
    $response[2] = 'Слово или словосочетание, образованное путём перестановки букв,';
    $response[3] = 'составляющих другое слово. "Википедия"';
    $response[4] = 'Слова - отгадки могут быть существительными единственного числа в';
    $response[5] = 'именительном падеже.';
    $response[6] = 'Бывают конечно и исключения (клещи). Аббревиатуры (ООН, ЕС, НДС)';
    $response[7] = 'не включены.';
    $response[8] = 'Слова могут быть и во множественном числе, если слово употребляется';
    $response[9] = 'только во множественном числе (клещи), или если множество имеет';
    $response[10] = 'другое значение и т.п.';
    $response[11] = 'За каждое отгаданное слово Вы получите столько монет,';
    $response[12] = 'сколько букв есть в данном слове.';
    $response[13] = 'Нажав на отгаданное слово, Вы увидите его значение.';
    $response[14] = 'Если вы подобрали существующее слово, а оно не воспринимается игрой.';
    $response[15] = 'Вы очень поможете улучшить игру, написав об этом на web-26@ya.ru';
    $response[16] = 'Желаем успехов, не только в игр !!!';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>