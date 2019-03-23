<?php empty($_SESSION) ? session_start() : '';
if (isset($_POST['name']) && !empty($_POST['name'])) {
    session_start();
    require_once('EncDec.php');
    require_once('db.ini.php');

    $name = strip_tags(trim($_POST['name']));
    $id = DB::run("SELECT id FROM login_user WHERE screen_name=?", [$name])->fetch()['id'];

    if (!empty($id) && !empty($name)) {//Вход
        $_SESSION['_key_'] = EncDec::Return('enc', $id . '|' . $name);
        DB::run("UPDATE login_user SET active=?, sess_id=? WHERE id=?", [1, $_SESSION['_key_'], $id]);
        echo json_encode(2, JSON_UNESCAPED_UNICODE);
    } elseif (empty($id)) {//Регистрация
        DB::prepare("INSERT INTO login_user(screen_name,point,active) VALUES(?,?,?)")->execute([$name, 0, 3]);
        $id = DB::lastInsertId();
        for ($i = 1; $i <= 3; $i++)
            DB::prepare("INSERT INTO data_user(part_id,menu_id) VALUES(?,?)")->execute([$id, $i]);
        $_SESSION['_key_'] = EncDec::Return('enc', $id . '|' . $name);
        DB::run("UPDATE login_user SET sess_id=? WHERE id=?", [$_SESSION['_key_'], $id]);
        echo json_encode(1, JSON_UNESCAPED_UNICODE);
    }
}
//echo $_SESSION['_key_'].'\n';
//echo EncDec::Return('dec', $_SESSION['_key_']);
?>