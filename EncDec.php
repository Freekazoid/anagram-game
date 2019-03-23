<?php

/**
 * Created by PhpStorm.
 * User: Freekazoid
 * Date: 22.10.2018
 * Time: 21:22
 */
class EncDec
{
    public static function Return($key, $passw)
    {
        switch ($key) {
            case 'enc':
                return self::Encrypt($passw);
                break;
            case 'dec':
                return self::Decrypt($passw);
                break;
        }
    }

    private static function Encrypt($may_key)
    {// Encrypt
        $key_size = strlen(self::keys());# Длина ключа должна быть 16, 24 или 32 байт для AES-128, 192 и 256 соответственно
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC); # Создаем случайный инициализирующий вектор используя режим CBC
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND); # Создаем случайный инициализирующий вектор используя режим CBC
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, self::keys(), $may_key, MCRYPT_MODE_CBC, $iv);   # Создаем шифрованный текст совместимыс с AES (размер блока = 128)# Подходит только для строк не заканчивающихся на 00h# (потому как это символ дополнения по умолчанию)
        $ciphertext = $iv . $ciphertext;# Добавляем инициализирующий вектор в начало, чтобы он был доступен для расшифровки
        $ciphertext_base64 = base64_encode($ciphertext);# перекодируем зашифрованный текст в base64
        return $ciphertext_base64;
    }

    private static function keys()
    {
        return pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");//Соль ключа
    }

    private static function Decrypt($may_key)
    {//Decrypt
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC); # Создаем случайный инициализирующий вектор используя режим CBC
        $ciphertext_dec = base64_decode($may_key);
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);# Извлекаем инициализирующий вектор. Длина вектора ($iv_size) должна совпадать # с тем, что возвращает функция mcrypt_get_iv_size()
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);# Извлекаем зашифрованный текст
        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, self::keys(), $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);# Отбрасываем завершающие символы 00h
        return $plaintext_dec;
    }
}

/*
$passw = 'секретный key или id, 876';
echo 'Секретные данные которые мы передаем: &laquo; <b style="color:red;">'.$passw."</b> &raquo;<br>";
$enKey = EncDec::Return('enc', $passw);
echo 'Секретные данные в закодированном виде 128 символов: &laquo; <b style="color:red;">'.$enKey."</b> &raquo;<br>";
$returnPassw = EncDec::Return('dec', $enKey);
echo 'Секретные данные которые мы дешифруем: &laquo; <b style="color:red;">'.$returnPassw."</b> &raquo;<br>";
 */