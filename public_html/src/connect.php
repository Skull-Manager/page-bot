<?
require_once 'config.php';  // подключение ключей
require_once 'php-jsondb-master/src/JSONDB.php'; // json база данных

require_once 'vk_api.php';  // подключение библиотеки
require_once 'helper.php';  // подключения скрипта помощника 

use Jajo\JSONDB;
$json_db = new JSONDB( __DIR__ . '/jdb' );

$vk = new vk_api(token_vk, v_api); 
$skull = new Skull ($vk, $json_db);

$data = json_decode ($_POST['out']); // ловим данные от сервера
$skull->skull_key (skull_key, $data->method, $data->skull_key, $data->conversation_message); // проверяем валидность ключа безопасности

$peer_id = (int) $json_db->select( 'user_peer'  )
        ->from( 'conversations.json' )
        ->where( [ 'skull_peer' => $data->peer_id ], 'AND' )
        ->get()[0]['user_peer'];

/*
    издевательство над методами сделано ради точности данных
    если вы и кто-то другой одновременно напишите команду, то вы получите данные того, кто 2-й написал запрос
    
    по этому 1-й раз мы записываем данные в jsonDB и тащим peer_id для юзера
    использование метода messages.getByConversationMessageId с параметром conversation_message_ids вернет более правдободобные данные
    
    также такой способ реализует ответ быстрее, чем постоянный поиск из всех сообщений
*/

if (empty ($peer_id) ) {        
    $data_get = $vk->request('messages.search', ['q' => $data->text, 'count' => 1 ]) ['items'][0]; // получение инфы о сообщении (костыль) (а может и нет)
    $peer_id  = (int) $data_get['peer_id']; // ид беседы (объявляем, если не нашли в бд)

    $skull->skullSavePeers ($peer_id, $data->peer_id); // синхранизируем беседы
} else {
    $data_get = $vk->request('messages.getByConversationMessageId', ['peer_id' => $peer_id, 
                'conversation_message_ids' => $data->conversation_message ]) ['items'][0]; // если беседа найдена в бд, то получаем данные из сообщения
            
} 

$url_photo  = (is_array ($data_get ['attachments'][0]['photo']['sizes']) ) ? array_pop ($data_get ['attachments'][0]['photo']['sizes']) ['url'] : '';
    
if (empty ($url_photo)) {
	$url_photo = (is_array ($data_get ['fwd_messages'][0]['attachments'][0]['photo']['sizes']) ) ? array_pop ($data_get ['fwd_messages'][0]['attachments'][0]['photo']['sizes']) ['url'] : '';
}

# получаем переменные для скрипта bot.php 

$message_id = $data_get['id']; // ид сообщения
$reply_id   = ($data_get['fwd_messages'][0]['from_id'] != '') ? $data_get['fwd_messages'][0]['from_id'] : $data_get['reply_message']['from_id'];; // ид пользователя, чье сообщение было переслано 
$reply_peer = ($data_get['fwd_messages'][0]['peer_id'] != '') ? $data_get['fwd_messages'][0]['peer_id'] : $data_get['reply_message']['peer_id']; // ид беседы с которой было переслано сообщение
$gs_link    = ($data_get['fwd_messages'][0]['attachments'][0]['audio_message']['link_mp3'] != '') ? $data_get['fwd_messages'][0]['attachments'][0]['audio_message']['link_mp3'] : $data_get['reply_message']['attachments'][0]['audio_message']['link_mp3']; // ссылка на гс

$c_mes_id = $data->conversation_message;
$method   = $data->method; // метод, который пришел от бота
?>
