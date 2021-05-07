<?
require_once 'src/connect.php'; // подключение всех данных
    
/* 
$peer_id      - id чата
$message_id   - id сообщения
$c_mes_id     - id глобальный
$reply_id     - ид юзера, чье смс было пересланно

$data->method - метод от skull-а ($method)
$data->text   - сообщение из чата
*/

$message = mb_strtolower ($data->text);

if ($method == 'skullSend') {
    
    $func = mb_substr ($message, 0, 1); // получение 1-й буквы
    
    if ($message == 'чистка') { // удаление своих сообщений
        $skull->skullDelMyMsg ($peer_id);
    }
    
    if (mb_substr ($message, 0, 9) == 'чистка от') {		
	if (empty ($reply_id)) {
            $userInfo = $vk->request('users.get', ['user_ids' => mb_substr ($message, 25)]); // чистая ссылка на страницу вк
            $reply_id = $userInfo[0]['id'];	
        }
		
        if (!empty ($reply_id)) {
            $skull->skullDelFromMsg ($peer_id, $reply_id, $message_id);
        }
    }
    
    if (mb_substr ($message, 0, 6) == 'screen') {  // скриншот
    
        if (!empty (mb_substr($message, 7))) { // если написать /апи screen 1 вы сделаете скриндош в 1-ю беседу (работает с токеном vk me)
            $peer_id = mb_substr($message, 7) + 2e9;
        }
        
        $skull->skullScreen ($peer_id, $message_id);
    }
    
    
    if (mb_substr ($message, 0, 6) == 'инвайт') { // приглашение
    
        if (empty ($reply_id)) {
            $reply_id = $vk->request('users.get', ['user_ids' => mb_substr($message, 22)]) [0]['id']; // чистая ссылка на страницу вк
        }
        
        $skull->skullInvite ($peer_id, $reply_id, $message_id);
    }
    
    
    if ($func == 'н') { // н == напиши
        $skull->skullSend ($message_id, $peer_id, mb_substr ($data->text, 2));
    }  
    
    if ($func == 'е') { // е = edit = отредактируй
        $vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => mb_substr ($data->text, 2), 
        'message_id' => $message_id]);
    }  
    
    if ($func == 'д') { // д = delete = удалить (собак)
        $skull->deleteDogs($message_id, $peer_id);
    }  
    
    if ($func == 'с') { // с = статус
        $skull->setStatus ($message_id, $peer_id, mb_substr ($data->text, 2));
    } 
    
    if ($func == 'п') { // произвольное сообщение чат
        $skull->skullArbitrary ($data->text);
    }
	
    if ($message == 'чат') {
    	$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#9851; Текущий чат: \n\n&#10035; ID чата: $peer_id\n&#128311; ID сообщения: $message_id\n&#128310; ID глобальный: $c_mes_id\n&#128681; Метод: {$data->method}", 
        'message_id' => $message_id]);
    }	
    
}   

if ($method == 'skullMute') {
    if ($message == 'сообщение во время мута') {
        $vk->request('messages.delete', ['message_ids' => $message_id, 'delete_for_all' => 1]);
    }

    if ($message == 'чистка') {
        $skull->skullDelAllMsg ($peer_id);
    }
    
    echo 1; // если не вернуть 1 или true бот отключит от беседы страничного бота. (нужно, чтобы ловить ошибки)
}
