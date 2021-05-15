<?
require_once 'src/connect.php'; // подключение всех данных
    
/* зарезервированные переменные (которые не стоит объявлять повторно, но можно использовать в данном файле)
$peer_id      - id чата
$message_id   - id сообщения
$c_mes_id     - id глобальный
$reply_id     - ид юзера, чье смс было пересланно
$url_photo    - ссылка на вложение фото
$reply_peer   - ид чата с которого переслано сообщение

$data->method - метод от skull-а ($method)
$data->text   - сообщение из чата
*/

$message = mb_strtolower ($data->text);
$need_peer = ($reply_peer != '') ? $reply_peer : $peer_id; // теперь если пересылать сообщения из другого чата, команда будет выполнена в нем даже если бота нет в беседе

if ($method == 'skullSend') {
    
    $func = mb_substr ($message, 0, 1); // получение 1-й буквы
    
    if ($message == 'чистка') { // удаление своих сообщений
        $skull->skullDelMyMsg ($need_peer);
    }
    
    if (mb_substr ($message, 0, 9) == 'чистка от') {		
	if (empty ($reply_id)) {
            $userInfo = $vk->request('users.get', ['user_ids' => mb_substr ($message, 25)]); // чистая ссылка на страницу вк
            $reply_id = $userInfo[0]['id'];	
        }
		
        if (!empty ($reply_id)) {
            $skull->skullDelFromMsg ($need_peer, $reply_id, $message_id);
        }
    }
    
    if (mb_substr ($message, 0, 6) == 'screen') {  // скриншот
    
        if (!empty (mb_substr($message, 7))) { // если написать /апи screen 1 вы сделаете скриндош в 1-ю беседу (работает с токеном vk me)
            $peer_id = mb_substr($message, 7) + 2e9;
        }
        
        $skull->skullScreen ($need_peer, $message_id);
    }
    
    
    if (mb_substr ($message, 0, 6) == 'инвайт') { // приглашение
    
        if (empty ($reply_id)) {
            $reply_id = $vk->request('users.get', ['user_ids' => mb_substr($message, 22)]) [0]['id']; // чистая ссылка на страницу вк
        }
        
        $skull->skullInvite ($peer_id, $reply_id, $message_id);
    }
	
    if ($message == 'ава') {
        $skull->updateChatPhoto ($peer_id, $url_photo, $message_id);
    }
    
    if ($func == 'н') { // н == напиши
        $skull->skullSend ($message_id, $need_peer, mb_substr ($data->text, 2));
    }  
    
    if ($func == 'е') { // е = edit = отредактируй
        $vk->request('messages.edit', ['peer_id' => $need_peer, 'message' => mb_substr ($data->text, 2), 
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
    	$vk->request('messages.edit', ['peer_id' => $need_peer, 'message' => "&#9851; Текущий чат: \n\n&#10035; ID чата: $need_peer\n&#128311; ID сообщения: $message_id\n&#128310; ID глобальный: $c_mes_id\n&#128681; Метод: {$data->method}", 
        'message_id' => $message_id]);
    }	
	
    if ($message == 'беседа') {
    	$chat_info = $vk->request('messages.getConversationsById', ['peer_ids' => $need_peer])['items'][0];
    	
    	$admin_list = $chat_info['chat_settings']['admin_ids'];
    	$admin_count = count ($admin_list);
    	
    	$l = 0;
    	foreach ($admin_list as $bots_admin) {
    		if ($bots_admin < 0) {
    			$l++;
    		}
    	}
    	
    	$admin_online = (in_array ($chat_info['chat_settings']['owner_id'], $chat_info['chat_settings']['active_ids']) ) ? '(Активен)' : '(Не активен)';
    	$online = count ($chat_info['chat_settings']['active_ids']);
    	
    	$change_info  = $skull->is_true ($chat_info['chat_settings']['acl']['can_change_info']);
    	$link_peer    = $skull->is_true ($chat_info['chat_settings']['acl']['can_change_invite_link']);
    	$pin_info     = $skull->is_true ($chat_info['chat_settings']['acl']['can_change_pin']);
    	$invate_info  = $skull->is_true ($chat_info['chat_settings']['acl']['can_invite']);
    	$can_moderate = $skull->is_true ($chat_info['chat_settings']['acl']['can_moderate']);
    	$mass_link    = $skull->is_true ($chat_info['chat_settings']['acl']['can_use_mass_mentions']);
    	
    	$chat_msg = "&#9851; Информация о текущей беседе: \n\n&#128681; ИД чата: $peer_id\n&#128311; [id{$chat_info['chat_settings']['owner_id']}|Создатель] $admin_online\n&#10055; Название: {$chat_info['chat_settings']['title']}\n&#128312; Кол-во участников: {$chat_info['chat_settings']['members_count']}\n&#128313; Кол-во админов: $admin_count\n&#128160; Из них боты-админы: $l\n&#128309; Активных: $online чел.\n\n&#9881; Права в беседе &#9881;\n\n&#128221; Изменение информации: $change_info\n&#128206; Изменение ссылки на приглашение: $link_peer\n&#128467; Доступна ссылка на приглашение: $link_peer\n&#128391; Изменение закрепа: $pin_info\n&#128483; Массовые упоминания: $mass_link\n&#128100; Администратирование: $can_moderate\n&#128101; Приглашение в беседу: $invate_info";
    	$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "$chat_msg", 'message_id' => $message_id, 'dont_parse_links' => 1, 'disable_mentions' => 1]);
    }	
	
    // метод messages.setMemberRole в любом случае возвращает true (даже при неудачи) -> делаем проверку на случай, если ВК выздоровеет, а пока соблюдаем нужнгые требования (вы админ в беседе)
    
    if ($message == 'admin set') {
    	if (!empty($reply_id)) {
    		$admin = $skull->admin_manager ($reply_id, $need_peer, true); // назначаем админом пользователя
    		if ($admin) {
    			$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#9989; [id$reply_id|Пользователь] назначен администратором беседы", 
        'message_id' => $message_id]);
    		} else {
    			$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#10060; [id$reply_id|Пользователя] не удалось назначить администратором беседы", 
        'message_id' => $message_id]);
    		}
    	}
    }
    
    if ($message == 'admin unset') {
    	if (!empty($reply_id)) {
    		$admin = $skull->admin_manager ($reply_id, $need_peer, false); // снимаем админа
    		if ($admin) {
    			$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#9989; ".$skull->ids_construct ($reply_id)." разжалован", 
        'message_id' => $message_id]);
    		} else {
    			$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#10060; ".$skull->ids_construct ($reply_id)." не удалось разжаловать", 
        'message_id' => $message_id]);
    		}
    	}
    }	
	
    if (mb_substr ($message, 0, 2) == 'гс' ) {
    	$title = mb_strtolower ( mb_substr ($message, 3) );

    	if ($title != 'лист' AND mb_substr ($message, 0, 7) != 'гс save' AND mb_substr ($message, 0, 9) != 'гс rename' AND mb_substr ($message, 0, 6) != 'гс del') {
	    	$get_file = $skull->get_voice ($title);
	    	
	    	if ($get_file != '') {
	    		$vk->request('messages.delete', ['message_ids' => $message_id, 'delete_for_all' => 1]);
	    		$vk->sendVoice ($need_peer, __DIR__ . '/src/audio/'.$get_file);
	    	} else {
	    		$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#10060; Файл с таким названием не найден..", 
	        'message_id' => $message_id]);
	    	}
    	}
    }
    
    if (mb_substr ($message, 0, 7) == 'гс save') {
    	if ( !empty ($gs_link) ) {
    		$title = mb_strtolower ( mb_substr ($message, 8) );
    		
    		if ($skull->save_on_server ($gs_link, 'v_msg', $title, $message_id, $peer_id)) { // загружаем гс на сервер
    			$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#9989; | Файл залит на сервер", 
        'message_id' => $message_id]);
    		} else {
    			$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#10060; Не удалось залить файл на сервер...", 
        'message_id' => $message_id]);
    		}
    	}
    }
    
    if ($message == 'гс лист') {
    	$list_gs = $skull->get_gs_all (); // возвращает список названий гс
    	
    	if ($list_gs != false) {
    		$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#9851; Список моих голосовых: \n\n$list_gs", 
        'message_id' => $message_id]);
    	} else {
    		$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#9888; Пока что нет голосовых в базе данных..", 
        'message_id' => $message_id]);
    	}
    }
    
    if (mb_substr ($message, 0, 9) == 'гс rename') {
    	$title = mb_strtolower ( mb_substr ($message, 10) );
    	$explode = explode (' - ', $title);
    	
    	if ($skull->gs_rename ($explode[0], $explode[1]) != 2) { // старое и новое имя файла
    		$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#9989; | Файл '{$explode[0]}' изменен на: {$explode[1]}", 
        	'message_id' => $message_id]);
    	} else {
    		$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#10060; Не далось найти файл с таким названием", 
        'message_id' => $message_id]);
    	}
    }
    
    if (mb_substr ($message, 0, 6) == 'гс del') {
    	$title = mb_strtolower ( mb_substr ($message, 7) );
    	
    	if ($skull->del_gs ($title) != 2) {
    		$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#9989; | Файл '$title' удален", 
        	'message_id' => $message_id]);
    	} else {
    		$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#10060; Не далось найти файл с таким названием", 
        'message_id' => $message_id]);
    	}
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
?>
