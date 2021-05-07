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

$peer_id = $json_db->select( 'user_peer'  )
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
    $data_get = $vk->request('messages.search', ['q' => $data->text, 'count' => 1 ]) ['items'][0]; // получение инфы о сообщении (костыль)
        
    // тащим данные 
    $peer_id    = $data_get['peer_id'];
    $message_id = $data_get['id'];
    $reply_id   = $data_get['fwd_messages'][0]['from_id'];    
    
    $skull->skullSavePeers ($peer_id, $data->peer_id); // синхранизируем беседы
} else {
    $data_get = $vk->request('messages.getByConversationMessageId', ['peer_id' => $peer_id, 
                'conversation_message_ids' => $data->conversation_message ]) ['items'][0];
            
    $message_id = $data_get['id']; // ид сообщения 
    $reply_id   = $data_get['fwd_messages'][0]['from_id'];    
} 

$c_mes_id = $data->conversation_message;
$method   = $data->method;
