<?php session_start();
/**
 * Created by PhpStorm.
 * User: Freekazoid
 * Date: 14.11.2018
 * Time: 18:21
 */
//header('Content-Type: text/html; charset=utf-8');
header('Content-Type: application/json; charset=utf-8');
if (isset($_POST['words']) && !empty($_POST['words']) && isset($_SESSION['_key_']) && !empty($_SESSION['_key_'])) {
    $new_words = htmlspecialchars(str_replace(" ", "", strtolower(strip_tags(trim($_POST['words'])))));
    require_once('EncDec.php');
    require_once('db.ini.php');

    $damp = explode('|', trim(EncDec::Return('dec', $_SESSION['_key_'])));
    $data_user = DB::run("SELECT login_user.*,data_user.*  FROM login_user LEFT JOIN data_user ON data_user.part_id=login_user.id WHERE login_user.id=? AND login_user.screen_name=?", [$damp[0], $damp[1]])->fetch();
    $search = DB::run("SELECT id FROM data_user WHERE 30_procent=? AND part_id=?", ['2', $data_user['part_id']])->fetch()['id'];

    if (!empty($search)) {
        require_once('dictionary-v2.php');
        $response = dictionary::getResult($new_words);
        if ($response == 1)
            DB::run("UPDATE data_user SET 30_procent=? WHERE id=? AND part_id=?", [3, $search, $data_user['part_id']]);

        echo json_encode($response, JSON_UNESCAPED_UNICODE);//Добавление своего слова в игру
    }
}