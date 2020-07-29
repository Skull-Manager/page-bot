<?
require ('vk_api.php'); // подключение библиотеки
require ('helper.php'); // подключения скрипта помощника 

$data = json_decode ($_POST['out']); // ловим данные от сервера

if ($data->key != '') { 
    die(); // ваш ключ безопастного принятия запроса, если ключ не верный, то процесс убивается.
}

$token = ""; // ваш токен от кейт мобаил
$v = "5.85"; // можно указать 5.120 (современная версия)

$vk = new vk_api($token, $v); 
$skull = new Skull ($vk);

$data_get = $vk->request('messages.search', ['q' => $data->text ]) ['items'][0]; // получение инфы о сообщении (костыль)

// тащим данные от костыля
$peer_id    = $data_get['peer_id']; // индификатор беседы
$message_id = $data_get['id'];      // айди сообщения
$c_mes_id   = $data_get['conversation_message_id']; // глобальный айди сообщения


// ниже стряпано на быструю руку

if ($data->method == 'skullEdit') { // редактирует сообщение (/апи е)
    $vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => $data->text, 'message_id' => $message_id]);
} elseif ($data->method == 'skullSend') { // отправляет сообщение с удалением старого (/апи с)
    $vk->request('messages.delete', ['message_ids' => $message_id, 'delete_for_all' => 1]);
    $vk->sendMessage($peer_id, $data->text);
} elseif ($data->method == 'skullCheck') { // проверка сервера
    echo $c_mes_id;
} elseif ($data->method == 'skullDelDogs') { // удаление собак
    $request = $skull->deleteDogs();
    $vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => $request, 'message_id' => $message_id]);
}
