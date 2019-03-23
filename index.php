<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Game Word</title>

    <meta http-equiv="cleartype" content="on">
    <meta name="MobileOptimized" content="320">
    <meta name="HandheldFriendly" content="True">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width,user-scalable=no">

    <script src="/js/jquery-3.2.1.js" type="text/javascript"></script>
    <script src="/js/CanvasInput.min.js" type="text/javascript"></script>
    <script async src="/js/window.js" type="text/javascript"></script>
    <script defer src="js/FloatingLetters.js" type="text/javascript"></script>
    <!--<script src="https://vk.com/js/api/xd_connection.js?2"  type="text/javascript"></script>-->

    <style>
        body {
            -moz-user-select: none;
            -ms-user-select: none;
            -o-user-select: none;
            -webkit-user-select: none;
            padding: 0;
            margin: 0;
        }
        content {
            display: flex;
            /*flex-direction: row;*/
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-width: 320px;
            min-height: 568px;
            height: 100vh;
        }
        img, canvas {
            user-select: none;
            -ms-user-select: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -webkit-touch-callout: none;
            -webkit-user-drag: none;
        }
    </style>
</head>
<body>
<?php if (empty($_SESSION['_key_'])): ?>
    <content>
        <div><!-- Блок регистрации / входа -->
            <h2>Представьтесь</h2>
            Имя: <input type="text" id="name_input" name="name_input">
            <br><br>
            <input type="button" id="enter" value="Вход">
        </div><!-- Блок регистрации / входа -->
        <script>
            /*
            VK.init(function() {
                console.log('data.response');
                // API initialization succeeded
                // Your code here
            }, function() {
                console.log('data.response NOT')
                // API initialization failed
                // Can reload page here
            }, '5.87');
            */
            (function () {
                $('#enter').on('click touch', function () {
                    $.post("ajax/login.php", {name: $('#name_input').val()}).done(function (data) {
                        if (data.length >= 1)
                            window.location.reload();
                    });
                })
            })();
        </script>
    </content>
<?php elseif (isset($_SESSION['_key_']) && !empty($_SESSION['_key_'])):
    require_once('ajax/EncDec.php');
    require_once('ajax/db.ini.php');
    $damp = explode('|', trim(EncDec::Return('dec', $_SESSION['_key_'])));
    $user = DB::run("SELECT font_size,type_font FROM login_user WHERE id=? AND screen_name=?", [$damp[0], $damp[1]])->fetch();
    if (!empty($user) && !is_bool($user)) {
?>
    <content>

    </content>
<script>
    /* Глобальные переменные */
    var cnv = document.createElement("canvas"),//Получаем холст игры
        ctx = cnv.getContext('2d'),//Берем его контекст формат 2d
        width = 807,//Задаем ширину
        height = 730,//Задаем высоту
        i = 0,//Просто инкримент
        showWindow = 0,//Какое окно отображается пользователю
        data_id = 0,//id слова в меню
        mess = [],//id введегых букв пользователя
        _point_ = 0,//Очки игрока
        input = [],//Массив кнопок и букв слова
        _name_ = '<?=$damp[1]?>',//Имя игрока
        _id_ = '<?=$damp[0]?>',//ID игрока
        IdNewWord = ['head', 'word', 'point', 'exit'],//Идентификаторы в окне нового слова
        button = [],//Кнопки основного меню
        inputWord = '',//Водимые буквы пользователем
        InputNewWord = '',//Водимое Новое слово пользователем
        noRefreshList = 0, //Блокирование списка слов
        showWindNotice = 0,//Всплывающее окно в игровом окне
        pointing = 0,//Вознаграждение пользователя при разгадывания слова. Блокируем повторное нажатие
        user_font = "<?=$user['font_size']?>px <?=$user['type_font']?>",//Размер шрифта и Тип шрифта выбранный пользователем
        Notice,//Всплывающее окно Уведомление в игровом окне
        PreResources = <?=json_encode(array_slice(scandir('img'), 2));?>,//Прелоудер ресурсов (Картинок файлов и прочего что дальше будет использоваться)
        //loadId = 0;//Сумма отображаемой загрузки
        counts = 0;// Количество элементов загрузки элементов
    /* Глобальные переменные */


    document.body.children[0].appendChild(cnv);//Создаем игровой Холст
    cnv.width = width;//Задаем ширину Холсту
    cnv.height = height;//Задаем высоту Холсту
    ctx.font = user_font;//Задаем размер шрифта и сам шрифт
    //cnv.style.background = '#111828';//Задний фон Холста
    cnv.lineJoin = 'round'//Скругление углов
    ctx.lineWidth = 1;//Толщина обводки



    /* функция отрисовки анимации и всего холста без нагрузки на ресурс */
    window.requestAnimFrame = (function(){
        return  window.requestAnimationFrame   ||
            window.webkitRequestAnimationFrame ||
            window.mozRequestAnimationFrame    ||
            window.oRequestAnimationFrame      ||
            window.msRequestAnimationFrame     ||
            function(callback, element){
                window.setTimeout(callback, 1000 / 60);
            };
    })();
    /* функция отрисовки анимации и всего холста без нагрузки на ресурс */





    function slowdown() {//Функция отложенного перехода в Меню
        showWindow = 1;
        menu = _WordMenu();//Формирования списка меню
        for(i in NewWord)
            NewWord[i].showLock();
    }
    function slowdown2() {//Функция отложенного закрытия увведомлений
        showWindNotice = 0;
        Notice.showLock();
    }


    var IntutText = new CanvasInput({//Строка для ввода INPUT
        canvas: document.getElementById('canvas'),
        x: 70,
        y: 215,
        fontSize: 18,
        fontFamily: 'Tahoma',
        fontColor: '#212121',
        fontWeight: 'bold',
        width: 230,
        padding: 8,
        borderWidth: 1,
        borderColor: '#000',
        borderRadius: 3,
        boxShadow: '1px 1px 0px #fff',
        innerShadow: '0px 0px 5px rgba(0, 0, 0, 0.5)',
        placeHolder: 'Ваше слово...'
    })
    var Name = function (n) {//Вывод имени игрока
        ctx.fillStyle = '#ff8115';
        ctx.fillRect(0, 0, (n.length * 20) + 130, 50);
        ctx.fillStyle = '#aaaaaa';
        ctx.textAlign = "left";
        ctx.textBaseline = "middle";
        ctx.fillText('Привет: ' + n, 10, 25);
    }
    var Point = function (id, reset='undefined') {//Вывод очков игрока
        reset = typeof reset !== 'undefined' ?  reset : 1;
        let length = String(_point_).length,
            str = (length==1)?'0':_point_,
            int = (width - ((length==1)?90:length * 40));
        if (_point_ <= 0 || reset == 'reload') {
            $.get("ajax/point.php", {id: id}).done(function (data) {
                _point_ = Number(data);
            })
        }
        ctx.fillStyle = '#ff8115';
        ctx.fillRect(int-80, 0, (length * 21) + 120, 50);
        ctx.fillStyle = '#aaaaaa';
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText('Очки: ' + str, int, 25);
    }
    var buttonMenu = function (id, x, y, w, h, text, color, tcolor) {//Отрисовка навигационных кнопок
        this.id = id;
        this.x = x;
        this.y = y;
        this.w = w;
        this.h = h;
        this.color = color;
        this.tcolor = tcolor;
        this.ftext = text;
        this.selected = false;
    }
    buttonMenu.prototype = {//Отрисовка кнопок меню
        draw: function () {
            ctx.fillStyle = this.color;
            ctx.fillRect(this.x, this.y, this.w, this.h);
            this.filtext();
        },
        filtext: function () {
            ctx.fillStyle = this.tcolor;
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(this.ftext, this.x + 25, this.y + 25);
        },
        select: function () {
            this.selected = !this.selected;
        }
    }
    button.push(new buttonMenu('settings', 20, 650, 50, 50, 'Н', '#ff8115', '#fffd16'));//Кнопка настроек
    button.push(new buttonMenu('rating', 80, 650, 50, 50, 'Р', '#ff8115', '#fffd16'));//Кнопка рейтинг
    button.push(new buttonMenu('home', 140, 650, 50, 50, 'М', '#ff8115', '#fffd16'));//Кнопка назад
    button.push(new buttonMenu('help', 200, 650, 50, 50, 'П', '#ff8115', '#fffd16'));//Кнопка помощи
    button.push(new buttonMenu('about', 260, 650, 50, 50, 'О', '#ff8115', '#fffd16'));//Кнопка о нас
    var Input = function (id, x, y, w, h, text, color) {//Отрисовка поля ввода слов
        this.id = id;
        this.x = x;
        this.y = y;
        this.w = w;
        this.h = h;
        this.color = color;
        this.ftext = text;
        this.selected = false;
    }
    Input.prototype = {//Отрисовка поля ввода слов
        draw: function () {
            ctx.fillStyle = this.color;
            ctx.fillRect(this.x, this.y, this.w, this.h);
            this.filtext();
        },
        fillText: function (text, x, y) {
            ctx.fillStyle = '#fffd16';
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(text, x, y);
        },
        stroke: function () {
            ctx.strokeStyle = '#ff0048';
            ctx.strokeRect(this.x, this.y, this.w, this.h);
        },
        filtext: function () {
            this.fillText(this.ftext, this.x + (this.w / 2), this.y + (this.h / 2));
        },
        select: function () {
            this.selected = !this.selected;
        }
    }
    input.push(new Input('delete', 50, 50, 50, 50, 'X', '#ff0003'));// Кнопка удалить все что есть
    input.push(new Input('input', 150, 50, 500, 50, '', '#737172'));// Строка ввода букв
    input.push(new Input('backspace', 700, 50, 50, 50, '<', '#00ff27'));// Кнопка удалить одну букву
    var Word = function (id, x, y, w, h, text) {//Отрисовка массива слова по букве
        this.id = id;
        this.x = x;
        this.y = y;
        this.w = w;
        this.h = h;
        this.ftext = text;
        this.selected = false;
    }
    Word.prototype = {//Отрисовка массива слова по букве
        draw: function () {
            ctx.fillStyle = '#0c15ff';
            ctx.fillRect(this.x, this.y, this.w, this.h);
            this.filtext();
        },
        stroke: function () {
            ctx.fillStyle = '#B4B4B4';
            ctx.fillRect(this.x, this.y, this.w, this.h);
        },
        fillText: function (text, x, y) {
            ctx.fillStyle = '#fffd16';
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(text, x, y);
        },
        filtext: function () {
            this.fillText(this.ftext, this.x + (this.w / 2), this.y + (this.h / 2));
        },
        select: function () {
///                inputWord += this.ftext;
            this.selected = !this.selected;
        }
    }
    var Menu = function (id, newWord, x, y, w, h, text) {//Отрисовка снопок меню
        this.id = id;
        this.newWord = newWord;
        this.x = x;
        this.y = y;
        this.w = w;
        this.h = h;
        this.ftext = text;
    }
    Menu.prototype = {//Отрисовка снопок меню
        draw: function () {
            ctx.fillStyle = '#0c15ff';
            ctx.fillRect(this.x, this.y, this.w, this.h);
            this.filtext();
        },
        fillText: function (text, x, y) {
            ctx.fillStyle = '#fffd16';
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(text, x, y);
        },
        stroke: function () {
            ctx.strokeStyle = '#ff0048';
            ctx.strokeRect(this.x, this.y, this.w, this.h);
        },
        filtext: function () {
            let lines = this.ftext.split('\n');
            if (lines.length > 1) {//Проверяем нужнали вторая строка
                for (; i < lines.length; i++)
                    this.fillText(lines[i].toUpperCase(), this.x + (this.w / 2), this.y + (this.h / 2) - 15 + (i * 20));//Делаем текст из двух строк
            } else {
                this.fillText(this.ftext.toUpperCase(), this.x + (this.w / 2), this.y + (this.h / 2));
            }
        },
        select: function () {
            showWindow = 2;
            this.selected = !this.selected;
        }
    }
    var _WordMenu = function () {//Запрос списка Меню
        let res = [];
        $.ajax({
            url: "ajax/menu.php",
            type: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            success: function (data) {
                //console.log("ajax/menu.php", data);
                for (i in data) {//Формирование меню слов
                    if (typeof data[i][4] !== 'undefined')
                        res.push(new Menu(data[i][0],data[i][4], 40, 40 + i * (50 + 10), 400, 50, data[i][4] + " \n" + data[i][1]));
                    else
                        res.push(new Menu(data[i][0], '', 40, 40 + i * (50 + 10), 400, 50, data[i][1]));
                }
            }
        });
        return res;
    }
    var menu = _WordMenu();//Формирования списка меню
    var _WordArr = function () {//Активное слово и количество разгаданных составных слов
        let res = [];
        $.ajax({
            url: 'ajax/word.php',
            type: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: {id: data_id},
            success: function (data) {
                //console.log('ajax/word.php', data);
                for (i in data) {//Формирование слова и количество разгаданных
                    if (i !== 'count')
                        res.push(new Word(i, 85 + i * (50 + 10), 130, 50, 50, data[i][0]));
                    else
                        res.push(new Word(i, 180, 200, 400, 50, 'Разгадано слов: ' + data[i][0] + ' / ' + data[i][1]));
                }
            }
        });
        return res;
    }



    var List = function (id, x, y, w, h, ftext, color_t, color_s, color_f) {//Отрисовка списка загаданных слов
        this.id = id;
        this.x = x;
        this.y = y;
        this.w = w;
        this.h = h;
        this.color_f = color_f;
        this.color_s = color_s;
        this.color_t = color_t;
        this.ftext = ftext;
        this.selected = false;
    }
    List.prototype = {//Отрисовка списка загаданных слов
        draw: function () {
            this.filtext();
            this.fill();
        },
        filtext: function () {
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillStyle = this.color_t;
            ctx.fillText(this.ftext, this.x + (this.w / 2), this.y + (this.h / 2));
        },
        fill: function () {
            if(this.x == 0 && this.y == 0 || this.y == height-80){
                ctx.fillStyle = this.color_f;
                ctx.fillRect(this.x, this.y, this.w, this.h);
            }
        },
        stroke: function () {
            ctx.lineWidth = 1;
            ctx.strokeStyle = this.color_s;
            ctx.strokeRect(this.x - (this.w / 2), this.y, this.w*2, this.h);
        },
        select: function () {
            this.selected = !this.selected;
        }
    }
    var _List = function (e) {//Список загаданных слов
        let res = [], resurs = [];
        var listTimer = setInterval(function () {
            if (resurs.length == 0) {
                $.ajax({
                    url: 'ajax/list.php',
                    type: 'POST',
                    async: false,
                    cache: false,
                    dataType: 'json',
                    data: {id: data_id},
                    success: function (data) {
                        //console.log('ajax/list.php', data);
                        let size = 4; //размер подмассива
                        for (i = 0; i < Math.ceil(data.length / size); i++)
                            resurs[i] = data.slice((i * size), (i * size) + size);
                    }
                })
            } else if(e>1) {
                for (i in resurs) {
                    for (p = 0; p < resurs[i].length; p++)
                        res.push(new List(resurs[i][p][0], (width / 5) + (20 * p) * 8, 300 + (30 * i), resurs[i][p][1].length*5, 20, resurs[i][p][1], '#02770d', '#ff0100', ''));
                }
                res.push(new List('back', 0, 0, width, 250, '', '', '', '#aaaaa'));
                res.push(new List('back', 0, height-80, width, 80, '', '', '', '#aaaaa'));
                clearInterval(listTimer);
            }
        }, 50);
        return res;
    }
    var list = [];
    var _inputWord = function() {//Отправка введеных слов на проверку существования
        if(inputWord.length>1) {
            $.ajax({
                url: 'ajax/list.php',
                type: 'POST',
                async: false,
                cache: false,
                dataType: 'json',
                data: {id: data_id, word: inputWord},
                success: function (data) {
                    //console.log('ajax/list.php', data);
                    let arr = [];
                    switch (data) {
                        case 4:
                            arr = ['Cлово добавлено в список разгаданных'];
                            list = _List(showWindow);//Обновляем списка загаданных слов
                            word = _WordArr();
                            input[1] = new Input([], input[1].x, input[1].y, input[1].w, input[1].h, '', input[1].color);
                            inputWord = '';
                            Point(_id_, 'reload');//Вывод очков игрока
                            break;
                        case 5:
                            arr = ['Cлово уже разгадано'];
                            break;
                        case 6:
                            arr = ['Нет такого слова'];
                            break;
                    };
                    showWindNotice = 1;
                    Notice = new WindNotice(arr, 150, 400, 500, 50, '#ff0100', '#aaaaaa', '#02770d');
                }
            })
        }
    }
    WindNotice = function(text, x, y, w, h, StColor, FiColor, TeColor) {//Всплывающее окно Уведомление в игровом окне
        this.text = text;
        this.x = x;
        this.y = y;
        this.w = w;
        this.h = h;
        this.StColor = StColor;
        this.FiColor = FiColor;
        this.TeColor = TeColor;
        this.showing = false;
        this.selected = false;
    }
    WindNotice.prototype = {//Всплывающее окно Уведомление в игровом окне
        draw: function(){
            if(this.showing === true) {
                this.fill(this.x, this.y, this.w, this.h);
                this.stroke(this.x, this.y, this.w, this.h);
                this.filtext(this.text, this.x, this.y);//Делаем текст из двух строк

            }
        },
        fill: function(x,y,w,h,color=this.FiColor){
            ctx.fillStyle = color;
            ctx.fillRect(x, y, w, h);
        },
        stroke: function(x, y, w, h, color=this.StColor){
            ctx.strokeStyle = color;
            ctx.strokeRect(x, y, w, h);
        },
        filtext: function(text, x, y, color=this.TeColor) {
            ctx.fillStyle = color;
            ctx.textAlign = "left";
            ctx.textBaseline = "top";
            ctx.fillText(text, x, y);
        },
        showLock: function(){
            this.showing = !this.showing;//Переключаем вид на это окно
        },
        select: function () {
            this.selected = !this.selected;
        }
    }
    var _WindHint = function(hint) {//Окно вывода запроса на попупку подсказки или слова при наличии достаточно очков. Или же окно вывода обьяснения слова
        let res = [];
        $.ajax({
            url: 'ajax/hint.php',
            type: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: {id: data_id, hint: hint},
            success: function (data) {
                //console.log('ajax/hint.php', data);
                let p = 100;
                for (i in data) {
                    let id = data[i][0];
                    data[i].splice(0, 1);
                    res.push( new WindHint(id,hint,data[i], 70, 50,  25, '#aaaaaa', '#ad0001', '#1c28ff', p) );
                    p+=200;
                }
            }
        })
        return res;
    }
    var WindHint = function(point,hint,data,x,y,h,fcolor,scolor,tcolor,p){//Окно вывода запроса на попупку подсказки или слова при наличии достаточно очков. Или же окно вывода обьяснения слова
        this.point = point;
        this.hint = hint;
        this.data = data;
        this.x = x;
        this.y = y;
        this.w = 0;
        this.h = h;
        this.p = p;
        this.scolor = scolor;
        this.fcolor = fcolor;
        this.tcolor = tcolor;
        this.showing = false;
        this.selected = false;
    }
    WindHint.prototype = {//Окно вывода запроса на попупку подсказки или слова при наличии достаточно очков. Или же окно вывода обьяснения слова
        draw: function(){
            if(this.showing == true) {
                let s = 0;
                for (i in this.data) {
                    this.w = this.data[i].length * 10;
                    this.y = this.p + (parseInt(i) * 10) + (s * this.h);

                    this.fill(this.x, this.y, this.w, this.h);
                    this.filtext(this.data[i], this.x, this.y+5);//Делаем текст из двух строк
                    s++;
                }
            }
        },
        fill: function(x,y,w,h,fcolor=this.fcolor){
            ctx.fillStyle = fcolor;
            ctx.fillRect(x, y, w, h);
        },
        stroke: function(x, y, w, h, scolor=this.scolor){
            ctx.strokeStyle = scolor;
            ctx.strokeRect(x, y, w, h);
        },
        filtext: function(x, y, w, h, tcolor=this.tcolor) {
            ctx.fillStyle = tcolor;
            ctx.textAlign = "left";
            ctx.textBaseline = "top";
            ctx.fillText(x, y, w, h);
        },
        showLock: function(){
            this.showing = !this.showing;//Переключаем вид на это окно
        },
        select: function () {
            this.selected = !this.selected;
        }
    }
    var Hint;
    var _WindPurchase = function(hint,point) {//Окно Покупки помощи
        let res = [];
        $.ajax({
            url: 'ajax/point.php',
            type: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: {id: data_id, hint: hint, point:point},
            success: function (data) {
                //console.log('ajax/point.php', data);
                if(point == 10){
                    Hint[0] =  new WindHint(point,hint,data, 70, 50,  25, '#aaaaaa', '#ad0001', '#1c28ff', 100);
                } else if(point == 30){
                    Hint[1] =  new WindHint(point,hint,data, 70, 50,  25, '#aaaaaa', '#ad0001', '#1c28ff', 300);
                }
                Point(_id_, 'reload');//Вывод очков игрока
            }
        })
        return res;
    }

    var WindNewWord = function(id,data,x,y,h,fcolor,scolor,tcolor,p){//Окно Бонус Выбор очков или свое слово в игру
        this.id = id;
        this.data = data;
        this.x = x;
        this.y = y;
        this.w = 0;
        this.h = h;
        this.p = p;
        this.inputT = '';
        this.scolor = scolor;
        this.fcolor = fcolor;
        this.tcolor = tcolor;
        this.showing = false;
        this.selected = false;
    }
    WindNewWord.prototype = {//Окно Бонус Выбор очков или свое слово в игру
        draw: function(){
            if(this.showing == true) {
                let s = 0;
                for (i in this.data) {
                    this.w = this.data[i].length * 10;
                    this.y = this.p + (parseInt(i) * 10) + (s * this.h);
                    this.fill(this.x, this.y, this.w, this.h);

                    if(this.data[i] == 'Ваше слово...'){
                        this.inputText();
                    } else
                        this.filtext(this.data[i], this.x, this.y + 5);//Делаем текст из двух строк
                    s++;
                }
                if(typeof this.inputT._value != 'undefined' && this.inputT._value != 'Ваше слово...' && this.inputT._value != '')
                    InputNewWord = this.inputT._value;
            }
        },
        inputText: function(){
            if(typeof this.inputT !== 'object') {
                this.inputT = IntutText;
            } else {
                this.inputT.render();
                this.inputT.focus();
            }
        },
        fill: function(x,y,w,h,fcolor=this.fcolor){
            ctx.fillStyle = fcolor;
            ctx.fillRect(x, y, w, h);
        },
        stroke: function(x, y, w, h, scolor=this.scolor){
            ctx.strokeStyle = scolor;
            ctx.strokeRect(x, y, w, h);
        },
        filtext: function(text, x, y, tcolor=this.tcolor) {
            ctx.fillStyle = tcolor;
            ctx.textAlign = "left";
            ctx.textBaseline = "top";
            ctx.fillText(text, x, y);
        },
        showLock: function(){
            this.showing = !this.showing;//Переключаем вид на это окно
        },
        select: function () {
            this.selected = !this.selected;
        }
    }
    var NewWord;
    var _WindNewWord = function(hint) {//Окно Бонус Выбор очков или свое слово в игру
        let res = [];
        $.ajax({
            url: 'ajax/new_word.php',
            type: 'POST',
            async: false,
            cache: false,
            dataType: 'json',
            data: {id: hint},
            success: function (data) {
                //console.log('ajax/new_word.php', data);
                let p = 100, str = 550;
                for(i in data) {
                    let punct = ((i==0)?p:i*p);
                    punct = ((punct>str)?str:punct);
                    res.push(new WindNewWord(IdNewWord[i], data[i], 70, 50, 25, '#aaaaaa', '#ad0001', '#1c28ff', punct));
                    p+=50;
                }
            }
        })
        return res;
    }


    var _ResultNewWord = function(id,y) {//Обработка Окно Бонус Выбор очков или свое слово в игру
        let arr = {
            0:['Слово добавлено.', 'Cпасибо за участи в развитии проекта'],
            1:['Слово может быть от 10 до 15 букв.', 'Без цифр и прочих символов.'],
            2:['Вы не ввели слово.'],
            3:['Вы получили 1000 очков', 'Желаем удачной игры']
        };
        if(id == 'word') {
            if(InputNewWord !== '') {
                $.ajax({
                    url: 'ajax/create_word.php',
                    type: 'POST',
                    async: false,
                    cache: false,
                    dataType: 'json',
                    data: {words: InputNewWord},
                    success: function (data) {
                        //console.log('ajax/create_word.php', data);
                        switch (data) {
                            case 1:
                                NewWord[9] = new WindNewWord('msg', arr[0], 70, y, 25, '#aaaaaa', '#ad0001', '#1c28ff', 300);
                                setTimeout(slowdown, 5000);
                                break;
                            case 2:
                                NewWord[9] = new WindNewWord('msg', arr[1], 70, y, 25, '#aaaaaa', '#ad0001', '#1c28ff', 300);
                                break;
                            case 3:
                                console.log('System error 501');
                                break;
                            case 4:
                                console.log('System error 502');
                                break;
                        }
                    }
                })
            } else
                NewWord[9] = new WindNewWord('msg', arr[2], 70, y, 25, '#aaaaaa', '#ad0001', '#1c28ff', 300);
        } else if(id == 'point') {
            if(pointing == 0) {
                pointing = 1;
                $.post('ajax/point.php', {needle: id}).done(function (data) {
                    if (data == 1) {
                        Point(_id_, 'reload');//Вывод очков игрока
                        NewWord[9] = new WindNewWord('msg', arr[3], 70, y, 25, '#aaaaaa', '#ad0001', '#1c28ff', 300);
                        setTimeout(slowdown, 5000);
                    }
                })
            }
        } else if(id == 'exit') {
            slowdown();
        }
    }



























































































    var isCursorRect = function (x, y, rect) {//Определяем что курсор над элементом
        return x > rect.x && x < rect.x + rect.w && y > rect.y && y < rect.y + rect.h;
    }




    cnv.onmousewheel = function(e) {//Прокрутка в окнах
        if(showWindow == 5 && WindHelp.selected == true) {//Прокрутка в окне помощи
            if(e.deltaY > 0) {
                WindHelp.y -= 10;
            } else if(e.deltaY < 0) {
                WindHelp.y += 10;
            }
        } else if(showWindow == 2) {//Прокрутка в игровом окне слова
            let leng = list.length;
            for(i in list) {
                if (parseInt(i) < (leng - 2) || parseInt(i) < (leng - 3)) {
                    if (e.deltaY > 0)   list[i].y -= 15;
                    else if (e.deltaY < 0)  list[i].y += 15;
                }
            }
        } else if(showWindow == 1) {//Прокрутка в окне меню
            for(i in menu) {
                if (e.deltaY > 0)   menu[i].y -= 50;
                else if (e.deltaY < 0)  menu[i].y += 50;
            }
        }
        return false;
    }








    cnv.onclick = function(e) {//Отслеживаем клик мышки
        var x = e.offsetX, y = e.offsetY;


        for (i in button) {//Переключатель экранов игры кнопками меню
            if (isCursorRect(x, y, button[i]) && button[i].id == 'about') {
                button[i].select();
                showWindow = 6;
            } else if (isCursorRect(x, y, button[i]) && button[i].id == 'help') {
                button[i].select();
                showWindow = 5;
            } else if (isCursorRect(x, y, button[i]) && button[i].id == 'home') {
                button[i].select();
                input[1] = new Input([], input[1].x, input[1].y, input[1].w, input[1].h, '', input[1].color);
                menu = _WordMenu();//Формирования списка меню
                showWindow = 1;
            } else if (isCursorRect(x, y, button[i]) && button[i].id == 'rating') {
                button[i].select();
                showWindow = 4;
            } else if (isCursorRect(x, y, button[i]) && button[i].id == 'settings') {
                button[i].select();
                showWindow = 3;
            }
        }//Переключатель экранов игры кнопками меню





        if (showWindow == 1) {//Активность в окне меню
            for (i in menu) {
                if (isCursorRect(x, y, menu[i])) {
                    if(menu[i].newWord !== '') {
                        NewWord = _WindNewWord(menu[i].id);
                        showWindow = 8;
                    } else {
                        menu[i].select();
                        data_id = menu[i].id;
                        word = _WordArr();
                        list = [];//Создания списка загаданных слов
                        noRefreshList = 0;
                    }
                }
            }
        } else if (showWindow == 2) {//Активность в окне слова
            for (i in list) {//списк загаданных слов
                if (isCursorRect(x, y, list[i]) && !list[i].selected && list[i].id != 'back') {
                    list[i].select();
                    Hint = _WindHint(list[i].id);
                    showWindow = 7;
                }
            }
            for (i in word) {//Список букв состовляющих слово
                if (isCursorRect(x, y, word[i]) && !word[i].selected) {
                    word[i].select();
                    inputWord += word[i].ftext;
                    _inputWord();
                }
            }
            for (i in input) {
                if (isCursorRect(x, y, input[i]) && input[i].id == 'backspace') {//Стираем последнию введенную букву
                    let str = input[1].ftext.substring(0, input[1].ftext.length - 1), id = '';
                    input[1].ftext.replace(/[\S\s]$/, function (f) { id = f; return ""; });
                    input[1] = new Input([], input[1].x, input[1].y, input[1].w, input[1].h, str, input[1].color);
                    for (i in word) {
                        if (word[i].selected == true && word[i].ftext == id && id.length == 1) {
                            id = '';
                            word[i].select();
                            mess[i] = [];
                        }
                    };
                    inputWord = str;
                } else if (isCursorRect(x, y, input[i]) && input[i].id == 'delete') {//Удаляем все введенные буквы
                    input[1] = new Input([], input[1].x, input[1].y, input[1].w, input[1].h, '', input[1].color);
                    mess = [];
                    for (i in word) {
                        if (word[i].selected == true) {
                            word[i].select();
                        }
                    };
                    inputWord = '';
                }
            }
        } else if (showWindow == 7) {//Активность в окне покупки подсказки слова
            for(i in Hint) {
                if (isCursorRect(x, y, Hint[i]) && !Hint[i].selected) {
                    if(Hint[i].point == 0){
                        showWindow = 2;
                    } else if(Hint[i].point > 0){
                        _WindPurchase(Hint[i].hint,Hint[i].point);
                        list = _List(showWindow);//Обновляем списка загаданных слов
                    }
                }
            }
        } else if (showWindow == 8) {//Активность в окне слова
            for(i in NewWord){
                if(isCursorRect(x, y, NewWord[i])) {
                    if (IdNewWord.indexOf(NewWord[i].id) > 0 )
                        _ResultNewWord(NewWord[i].id, NewWord[i].y);
                }
            }
        }
    }//Отслеживаем клик мышки






    function render() {//Вывод на экран данных
        ctx.clearRect(0, 0, width, height);//Очистка холста

        var grd=ctx.createLinearGradient(-width,0,0,-width);
        grd.addColorStop(0, '#111828');
        grd.addColorStop(0.3, '#111828');
        grd.addColorStop(1, '#2C3D5B');
        ctx.fillStyle=grd;
        ctx.fillRect(0, 0, width, height);
        for(i in Letters){//Задний фон плавающих букв
            if (Letters[i].showing == false){
                Letters[i].draw();
            }
        }

        if (showWindow == 0) {//Показываем Окно загрузки
            if(counts === Preloader.length) {
                for (i in Preloader) {
                    if (Preloader[i].showing !== true) {
                        Preloader[i].showLock();
                        Preloader[i].draw();
                    }
                }
            }
        } else {
            if (showWindow == 1) {//Показываем Окно меню
                for (i in menu) {
                    menu[i].draw();
                    if (menu[i].selected) {
                        menu[i].stroke();
                    }
                }
            } else if (showWindow == 2) {//Показываем Окно игры
                if (noRefreshList == 0) {
                    list = _List(showWindow);//Создания списка загаданных слов
                    noRefreshList = 1;
                }
                for (i in list) {
                    list[i].draw();//Вывод списка загаданных слов
                }
                for (i in input) {//ВЫвод кнопок удалить и стереть и строку ввода
                    input[i].draw();
                }
                for (i in word) {//Вывод по буквам слова
                    word[i].draw();
                    if (word[i].selected && word[i].id != 'count') {
                        word[i].stroke();
                        if (mess[i] != word[i].id) {
                            mess[i] = word[i].id;
                            input[1] = new Input(mess, input[1].x, input[1].y, input[1].w, input[1].h, input[1].ftext + word[i].ftext, input[1].color);
                        }
                    }
                }
                if (showWindNotice == 1) {
                    setTimeout(slowdown2, 1500);
                    if (Notice.showing === false)
                        Notice.showLock();
                    Notice.draw();//Окно уведомлений
                } else if (typeof Notice !== 'undefined' && Notice.selected === true) {
                    Notice.showLock();
                }
            } else if (showWindow == 3) {//Показываем Окно настроек
                for (i in Settings) {
                    if (Settings[i].showing === false)
                        Settings[i].showLock();
                    Settings[i].draw();
                }
            } else if (showWindow == 4) {//Показываем Окно рейтинга
                WindRating.draw();
            } else if (showWindow == 5) {//Показываем Окно помощи
                WindHelp.show();
                WindHelp.draw();
            } else if (showWindow == 6) {//Показываем Окно О нас
                WindAbout.draw();
            } else if (showWindow == 7) {//Показываем Окно о покупки подсказки
                for (i in Hint) {
                    if (Hint[i].showing === false)
                        Hint[i].showLock();
                    Hint[i].draw();
                }
            } else if (showWindow == 8) {//Показываем Окно Бонус либо очки либо ввод нового слова
                for (i in NewWord) {
                    if (NewWord[i].showing === false)
                        NewWord[i].showLock();
                    NewWord[i].draw()
                }
            }


            if (showWindow != 3) {
                for (i in Settings)
                    Settings[i].showLock();
            } else if (showWindow != 8) {
                for (i in NewWord)
                    NewWord[i].showLock();
            } else if (showWindow != 7) {
                for (i in Hint)
                    Hint[i].showLock();
            } else if (showWindow != 5) {
                WindHelp.lock();
            }


            for (i in button) {//Отображаем кнопки меню окон
                button[i].draw();
            }//Отображаем кнопки меню окон


            Name(_name_);//Вывод имени игрока
            Point(_id_);//Вывод очков игрока
        }





    };
























    window.onload = function () {
        (function animloop() {//ЗАПУСК ВСЕЙ ОТРИСОВКИ НА ХОЛСТЕ
            requestAnimFrame(animloop, render());
        })();//ЗАПУСК ВСЕЙ ОТРИСОВКИ НА ХОЛСТЕ
    }
</script>

<?php };
    endif; ?>
</body>
</html>