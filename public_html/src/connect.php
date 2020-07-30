<?
require_once 'config.php';  // подключение ключей

require_once 'vk_api.php';  // подключение библиотеки
require_once 'helper.php';  // подключения скрипта помощника 

$vk = new vk_api(token_vk, v_api); 
$skull = new Skull ($vk);

$data = json_decode ($_POST['out']); // ловим данные от сервера

$data_get = $vk->request('messages.search', ['q' => $data->text ]) ['items'][0]; // получение инфы об сообщении (костыль)
    
// тащим данные от костыля
$peer_id    = $data_get['peer_id'];
$message_id = $data_get['id'];
$c_mes_id   = $data_get['conversation_message_id'];

$method = $data->method;

// если ключ не верный проверяем если это метод проверки сервера, если не он убиваем процесс

if (skull_key != $data->key) { 
    if ($data->method == 'skullCheck') {
        echo $c_mes_id;
    } else {
        die ('error'); 
    }
}

