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
	
    function execute ($array, $method) {
	    
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
	
    // удаляет собак из друзей
    // 'fields' => 'sex' - чтобы вк апи прислал нормальный массив со всеми данные, а не просто айдишки
	
    function deleteDogs ($message_id, $peer_id) {
        $array_dog = $this->vk->request('friends.get', ['fields' => 'sex']);
	    
        foreach ($array_dog['items'] as $dogs) {
	    if ($dogs['deactivated']) {
                $array_dogs[] = $dogs['id'];
	    }
	}
        
        if (count ($array_dogs) > 0) {
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
	
    function skullSavePeers ($user_peer, $skull_peer) {
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
