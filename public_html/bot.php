<?
require_once 'src/connect.php'; // подключение всех данных
    
/* 
$peer_id      - id чата
$message_id   - id сообщения
$c_mes_id     - id глобальный

$data->method - метод от skull-а ($method)
$data->text   - сообщение из чата
*/

$message = mb_strtolower ($data->text);

if ($method == 'skullSend') {
    
    $my_func = mb_substr ($message,0,1);
    
    if ($my_func == 'н') { // н == напиши
        $skull->skullSend ($message_id, $peer_id, mb_substr ($data->text, 2));
    }  
    
    if ($my_func == 'е') { // е = edit = отредактируй
        $vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => mb_substr ($data->text, 2), 
        'message_id' => $message_id]);
    }  
    
    if ($my_func == 'д') { // д = delete = удалить (собак)
        $skull->deleteDogs($message_id, $peer_id);
    }  
    
}   
