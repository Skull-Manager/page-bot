<?php
class vk_api{
	/**
	* Токен
	* @var string
	*/
	private $token, $v;
	/**
	* @param string $token Токен
	*/
	public function __construct($token, $v){
		$this->token = $token;
		$this->v = $v;
	}
	/**
	* Отправить сообщение пользователю
	* @param int $sendID Идентификатор получателя
	* @param string $message Сообщение
	* @return mixed|null
	*/
	public function sendDocMessage($sendID, $id_owner, $id_doc){
	  if ($sendID != 0 and $sendID != '0') {
	    return $this->request('messages.send',array('attachment'=>"doc". $id_owner . "_" . $id_doc,'user_id'=>$sendID));
	  } else {
	    return true;
	  }
	}

	public function chat_title_update($array, $index = 0) {
	  $response = false;
	  if (!empty($array->chat_title_update)){
	    $response = (object) $array->chat_title_update[$index];
	  }
	  return $response;
	}

	public function sendMessage ($sendID, $message, $params = []) {
		if ($sendID != 0 and $sendID != '0') {
			return $this->request ('messages.send', ['message' => $message, 'peer_id' => (int) $sendID] + $params);
		} else {
			return true;
		}
	}

	public function sendOK(){
	  echo 'ok';
	  $response_length = ob_get_length();
	  // check if fastcgi_finish_request is callable
	  if (is_callable('fastcgi_finish_request')) {
	  /*
	  * This works in Nginx but the next approach not
	  */
	  session_write_close();
	  fastcgi_finish_request();

	  return;
	}

	ignore_user_abort(true);

	ob_start();
	$serverProtocole = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
	header($serverProtocole.' 200 OK');
	header('Content-Encoding: none');
	header('Content-Length: '. $response_length);
	header('Connection: close');

	ob_end_flush();
	ob_flush();
	flush();
	}

	public function sendDocuments($sendID, $selector = 'doc'){
	  if ($selector == 'doc')
	    return $this->request('docs.getMessagesUploadServer',array('type'=>'doc','peer_id'=>$sendID));
	  else
	    return $this->request('photos.getMessagesUploadServer',array('peer_id'=>$sendID));
	}

	public function saveDocuments($file, $titile){
	  return $this->request('docs.save',array('file'=>$file, 'title'=>$titile));
	}

	public function savePhoto($photo, $server, $hash){
	  return $this->request('photos.saveMessagesPhoto',array('photo'=>$photo, 'server'=>$server, 'hash' => $hash));
	}

	/**
	* Запрос к VK
	* @param string $method Метод
	* @param array $params Параметры
	* @return mixed|null
	*/

	public function request ($method, $params = []) {
		$url = 'https://api.vk.com/method/'.$method;

		$params['access_token'] = $this->token;
		$params['v'] = $this->v;
		$params['random_id'] = rand (-2147483648, 2147483647);
		$params['peer_id'] = (int) $params['peer_id'];

		if (function_exists('curl_init')) {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			  "Content-Type:multipart/form-data"
			));

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			$result = json_decode(curl_exec($ch), True);
			curl_close($ch);

	    } else {
			$result = json_decode(file_get_contents($url, true, stream_context_create(array(
		    'http' => array(
		    'method'  => 'POST',
		    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		    'content' => http_build_query($params)
		    )
		    ))), true);
	    }

		if (isset($result['response']))
			return $result['response'];
		else
			return $result;
	}


	function getUploadServerMessages ($peer_id, $selector = 'doc') {
	    $result = null;

	    if ($selector == 'doc')
		$result = $this->request('docs.getMessagesUploadServer', ['type' => 'doc', 'peer_id' => $peer_id]);
	    else if ($selector == 'photo')
		$result = $this->request('photos.getMessagesUploadServer', ['peer_id' => $peer_id]);
	    else if ($selector == 'audio_message')
		$result = $this->request('docs.getMessagesUploadServer', ['type' => 'audio_message', 'peer_id' => $peer_id]);

	    return $result;
	}

	function uploadVoice($id, $local_file_path) {
	    $upload_url = $this->getUploadServerMessages($id, 'audio_message')['upload_url'];
	    $answer_vk = json_decode($this->sendFiles($upload_url, $local_file_path, 'file'), true);

	    return $this->saveDocuments($answer_vk['file'], 'voice');
	}

	function sendVoice($id, $local_file_path, $params = []) {
	    $upload_file = $this->uploadVoice ($id, $local_file_path);
	    return $this->request('messages.send', ['attachment' => "doc" . $upload_file['audio_message']['owner_id'] . "_" . $upload_file['audio_message']['id'], 'peer_id' => $id] + $params);
	}  


	private function sendFiles($url, $local_file_path, $type = 'file') {
	  $post_fields = array(
	  $type => new CURLFile(realpath($local_file_path))
	  );

	  $ch = curl_init();
	  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	  "Content-Type:multipart/form-data"
	  ));
	  curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
	  $output = curl_exec($ch);
	  return $output;
	}

	public function sendImage($id, $local_file_path) {
	  $upload_url = $this->sendDocuments($id, 'photo')['upload_url'];

	  $answer_vk = json_decode($this->sendFiles($upload_url, $local_file_path, 'photo'), true);

	  $upload_file = $this->savePhoto($answer_vk['photo'], $answer_vk['server'], $answer_vk['hash']);

	  $this->request('messages.send', array('attachment' => "photo" . $upload_file[0]['owner_id'] . "_" . $upload_file[0]['id'], 'peer_id' => $id));

	  return 1;
	  }
}
?>












