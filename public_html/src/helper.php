<?

/*
* @author Skull - Manager
* @version 1.0
* @package Skull
*/

class Skull {
    private $vk, $jdb;
    
    public function __construct ($vk, $jdb) {
	    $this->vk = $vk;
	    $this->jdb = $jdb;
    } 
	
    //execute для отправки множества запросов за 1-2 раза без нагрузки и лимитов вк апи
	
    function execute ($array, $method) { // ф-ция, чтобы делать много запросов и не упереться в лимиты =/
	    
        if ($method == 'deletedDogs') {
            foreach ($array as $dogs_del) { 
    		    $code[] = 'API.friends.delete({"user_id": '."{$dogs_del}".' });' . "\n";   		 		  		  	
            }
        }
	    
        // делим массив $code на 25 запросов для 1-го execute (25 максимум)
	    
        $elem = 25;
    	$len  = ceil(count($code)/$elem);
    	$out  = [];
    
    	for($i = 0; $i < $len; $i++) {
    	    $offset = $i * $elem;
    	    $out[] = implode (array_slice ($code, $offset, $elem));
    	}
    		
    	$con = count ($out);
    
    	for ($q = 0; $q <= $con; $q++) {
    	    $this->vk->request('execute', ['code' => $out[$q]]);
    		
    	    if ($con > 1) {			   
    	        sleep (2); # вызываем с паузой, чтобы не словить лимит запросов (если вышло более 25 запросов)	
    	    }
    	}
		
	    return count ($code);
    }
    
    function ids_construct ($id) { // это чтобы красиво указывать что от кого удалено (от группы или юзверя)
        if ($id < 0) {
            return "[club".mb_substr ($id, 1)."|группы]";
        } else {
            return "[id$id|пользователя]";
        }
    }
	
    // удаляет собак из друзей
    // 'fields' => 'sex' - чтобы вк апи прислал нормальный массив со всеми данные, а не просто айдишки
	
    function deleteDogs ($message_id, $peer_id) {
        $array_dog = $this->vk->request('friends.get', ['fields' => 'sex']);
	    
        foreach ($array_dog['items'] as $dogs) {
	        if ($dogs['deactivated']) {
                	$array_dogs[] = $dogs['id'];
	        }
	    }
        
        if (count ($array_dogs) > 0) { // тут обитают драконы
    	    $this->vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => '&#9989; | Начинаю удаление собак...', 'message_id' => $message_id]);
    	    $count = $this->execute ($array_dogs, 'deletedDogs');
    	    $this->vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#9989; Удалено из друзей : $count собак.", 'message_id' => $message_id]);
    	} else {	
            $this->vk->request('messages.delete', ['message_ids' => $message_id, 'delete_for_all' => 1]);
    	}
                 
    }
	
    function skullSend ($message_id, $peer_id, $text) { 
        $request = 'API.messages.delete({"message_ids": '.$message_id.', "delete_for_all": 1 }); 
                    API.messages.send({"peer_id": '.$peer_id.', "message": " '.$text.' " });  ';   
        
        $this->vk->request('execute', ['code' => $request]);
    }
	
    function setStatus ($message_id, $peer_id, $text) {
	    $request = 'API.status.set({"text": "'.$text.'"}); 
                    API.messages.edit({"peer_id": '.$peer_id.', "message": "&#9989; | Новый статус задан!", "message_id" : '.$message_id.' });  ';   
        
        $this->vk->request('execute', ['code' => $request]);
    }	
    
    function skullArbitrary ($msg) { // произвольное сообщение  в любой чат
        $chat = explode (' ', mb_substr ($msg, 2));
        $this->vk->sendMessage($chat [0], mb_substr ($msg, strlen ($chat[0]) + 2));
    }
    
    function skullDelMyMsg ($peer_id) { // удаление своих сообщений
        $all_clear = $this->vk->request('messages.getHistory', ['peer_id' => $peer_id, 'count' => 200]); 
        $userInfo  = $this->vk->request('users.get');
            
        foreach ($all_clear['items'] as $id_msg) {					
        	if ($id_msg['from_id'] == $userInfo[0]['id']) {
            	$all[] = "{$id_msg['id']}";
            }
        }
        
        $all_msg = implode (', ', $all);
        $this->vk->request('messages.delete', ['message_ids' => "$all_msg", 'delete_for_all' => 1]);
    }
    
    function skullDelFromMsg ($peer_id, $userId, $message_id) { // удаление сообщений от...
        $all_clear = $this->vk->request('messages.getHistory', ['peer_id' => $peer_id, 'count' => 200]); 
        
        foreach ($all_clear['items'] as $id_msg) {					
        	if ($id_msg['from_id'] == $userId) {
            	$all[] = "{$id_msg['id']}";
            }
        }
        
        $all_msg = implode (', ', $all);
        $del = $this->vk->request('messages.delete', ['message_ids' => "$all_msg", 'delete_for_all' => 1]) ['error']['error_msg']; // кавычки для айдишек обязательны, иначе вк бубнит
        
        if (!empty ($del)) {
            $this->vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#10060; | $del", 'message_id' => $message_id]); // если сообщение удалить невозможно или прошло > 24ч с момента отправки
	    sleep (3);
            $this->vk->request('messages.delete', ['message_ids' => $message_id, 'delete_for_all' => 1]); // удаляем свое сообщение, чтобы было красиво)
        } else {
            $userId = $this->ids_construct ($userId);
            $this->vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#9989; | Сообщение от $userId удалены.", 'message_id' => $message_id]); 
	    /* 
	    sleep (3); // можно убрать комментарии, чтобы уведомление удалялось в любом случае =( 
            $this->vk->request('messages.delete', ['message_ids' => $message_id, 'delete_for_all' => 1]); // удаляем свое сообщение, чтобы было красиво) */
        }
    }
    
    function skullDelAllMsg ($peer_id) { // удаление сообщение от всех юзверей в беседе (200 шт)
        $all_clear = $this->vk->request('messages.getHistory', ['peer_id' => $peer_id, 'count' => 200]); 
        $arr_users = $this->vk->request('messages.getConversationMembers', ['peer_id' => $peer_id]);	
        	
        foreach ($arr_users['items'] as $item) {
        	if($item['is_admin'])  {
        		$admin_list [ ]  = $item['member_id'];  // айдишки админов
        	}
        }
        		
        foreach ($all_clear['items'] as $id_msg) {					
        	if (!in_array ($id_msg['from_id'], $admin_list) ) { // собираем айдишки не админов беседы
            		$all[] = "{$id_msg['id']}";
            }
        }
                
        $all_msg = implode (', ', $all);
        $this->vk->request('messages.delete', ['message_ids' => "$all_msg", 'delete_for_all' => 1]);
    }
    
    
    function skullInvite ($peer_id, $user_id, $message_id) {
        $invite = $this->vk->request('messages.addChatUser', ['chat_id' => $peer_id - 2e9, 'user_id' => $user_id]) ['error']['error_msg'];
        
        if (!empty ($invite)) {
            $this->vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#10060; | $invite", 'message_id' => $message_id]); // тут причина ошибки (надо понимать англ)
	    sleep (3); // 
            $this->vk->request('messages.delete', ['message_ids' => $message_id, 'delete_for_all' => 1]); // удаляем свое сообщение, чтобы было красиво)
        }
    }
    
    function skullScreen ($peer_id, $m_id) { // работает с токеном vk me
        $request = 'API.messages.delete({"message_ids": '.$m_id.', "delete_for_all" : 1}); 
                    API.messages.sendService({"peer_id": '.$peer_id.', "action_type": "chat_screenshot"}); ';   
        
        $this->vk->request('execute', ['code' => $request]); // удаляем свое сообщение и скриним за 1 запрос     
    }
	
	
    function skullSavePeers ($user_peer, $skull_peer) { // записываем наши айдишки бесед
    	if (!empty ($user_peer)) { // чтобы не записывало null, если пользователь зашел на страницу сайта	    
	        $peer = $this->jdb->select( 'user_peer'  )
                ->from( 'conversations.json' )
                ->where( [ 'skull_peer' => $skull_peer ], 'AND' )
                ->get()[0]['user_peer'];
                	
            if (empty ($peer)) {
               $this->jdb->insert( 'conversations.json',[ 
                	'user_peer' => $user_peer, 
                	'skull_peer' => $skull_peer
                ] );
                     
            } 		
	} 
    }
    

    
}
