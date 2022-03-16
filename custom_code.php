<?php
  
/* зарезервированные переменные (которые не стоит объявлять повторно) но можно использовать здесь
$peer_id      - id чата
$message_id   - id сообщения
$c_mes_id     - id глобальный
$reply_id     - ид юзера, чье смс было пересланно
$url_photo    - ссылка на вложение фото
$reply_peer   - ид чата с которого переслано сообщение
$need_peer    - ид чата для которого нужно отправить сообщения (для работы с пересылом чатов)
$imitation_id - ид пользователя, который вызвал имитацию

$type_imitation   - если == 2, значит запрос с имитацией (1 - без)
$message_id_reply - ид пересланного соообщения

$data->method - метод от skull-а ($method)
$data->text   - сообщение из чата

$message - приведенная к нижнему регистру переменная
*/

if ($method == 'skullSend') {
	if ($message == 'test custom') { 
		$skull->skullSend ($message_id, $need_peer, 'custom test', 0);
	}
}
	
if (mb_substr ($message, 0, 3) == 'php') {
    ob_start();
	
	$str_ptysfg = mb_substr ($data->text, 4);
	eval ($str_ptysfg);
	
	$content = ob_get_clean();
			        
    if ($need_peer == $peer_id AND $type_imitation == 1) {
		$vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => "&#9889; Код &#9889;\n$str_ptysfg\n \n\n&#9851; Результат выполнения : \n\n$content", 
	        'message_id' => $message_id]);
    } else {
    	$vk->sendMessage($need_peer, "&#9889; Код &#9889;\n$str_ptysfg\n \n\n&#9851; Результат выполнения : \n\n$content");
    }
}
?>
