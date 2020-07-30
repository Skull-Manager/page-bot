<?
require_once 'src/connect.php'; // подключение всех данных
    
/* 
$peer_id      - id чата
$message_id   - id сообщения
$c_mes_id     - id глобальный

$data->method - метод от skull-а ($method)
$data->text   - сообщение из чата
*/

    
if ($method == 'skullSend') {
    $skull->skullSend ($message_id, $peer_id, $data->text);
}   

if ($method == 'skullEdit') {
    $vk->request('messages.edit', ['peer_id' => $peer_id, 'message' => $data->text, 'message_id' => $message_id]);
} 

if ($method == 'skullDelDogs') {
    $skull->deleteDogs($message_id, $peer_id);
}
