<?
class Skull {
    private $vk;
    
    public function __construct ($vk) { 
	$this->vk = $vk;
    } 
	
    //exetute для отправки множества запросов за 1-2 раза без нагрузки и лимитов вк апи
	
    function exetute ($array, $method) {
    		    
	if ($method == 'deletedDogs') {
	    foreach ($array as $dogs_del) { 
	        $code[] = 'API.friends.delete({"user_id": '."{$dogs_del}".' }); '."\n";   		 		  		  	
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
	    sleep (2);						
	}
		
	return $con;
    }
	
    // удаляет собак из друзей
    // 'fields' => 'sex' - чтобы вк апи прислал нормальный массив со всеми данные, а не просто айдишки
	
    function deleteDogs () {
        $array_dog = $this->vk->request('friends.get', ['fields' => 'sex']);
	    
        foreach ($array_dog['items'] as $dogs) {
            if ($dogs['deactivated']) {
                $array_dogs[] = $dogs['id'];
            }
        }
        
        if (count ($array_dogs) > 0) {
            $count = exetute ($array_dogs, 'deletedDogs');
            return "&#9989; Удалено из друзей : $count собак.";
        } else {
            return '&#10060; Собак в друзьях не найдено.';
        }
    }
	
}
