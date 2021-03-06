#!/usr/local/bin/php -q
<?php
error_reporting(E_ALL);

/* Позволяет скрипту ожидать соединения бесконечно. */
set_time_limit(0);

/** Включает скрытое очищение вывода так, что мы видим данные
* как только они появляются.
*/
ob_implicit_flush();

// don't working
// new WebSocket('ws://flatchat/socetserver.php:80')
// $address = '192.168.1.53';
$address = '127.0.0.1';
$port = 10000;

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "Не удалось выполнить socket_create(): причина: " . socket_strerror(socket_last_error()) . "\n";
}

if (socket_bind($sock, $address, $port) === false) {
    echo "Не удалось выполнить socket_bind(): причина: " . socket_strerror(socket_last_error($sock)) . "\n";
}

if (socket_listen($sock, 5) === false) {
    echo "Не удалось выполнить socket_listen(): причина: " . socket_strerror(socket_last_error($sock)) . "\n";
}

do {
    if (($msgsock = socket_accept($sock)) === false) {
        echo "Не удалось выполнить socket_accept(): причина: " . socket_strerror(socket_last_error($sock)) . "\n";
        break;
    }
    /* Отправляем инструкции. */
    $msg = "\nДобро пожаловать на тестовый сервер PHP. \n" .
        "Чтобы отключиться, наберите 'выход'. Чтобы выключить сервер, наберите 'выключение'.\n";
    socket_write($msgsock, $msg, strlen($msg));

    do {
        if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
            echo "Не удалось выполнить socket_read(): причина: " . socket_strerror(socket_last_error($msgsock)) . "\n";
            break 2;
        }
        if (!$buf = trim($buf)) {
            continue;
        }
        if ($buf == 'выход') {
            break;
        }
        if ($buf == 'выключение') {
            socket_close($msgsock);
            break 2;
        }
        $talkback = "PHP: Вы сказали '$buf'.\n";
        socket_write($msgsock, $talkback, strlen($talkback));
        echo "$buf\n";
    } while (true);
    socket_close($msgsock);
} while (true);

socket_close($sock);
?>