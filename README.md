# Ваш страничный бот на основе работы Skull - API

Введение :
1) [Для чего нужен страничный бот?](https://github.com/Skull-Manager/page-bot/blob/master/README.md#%D0%B4%D0%BB%D1%8F-%D1%87%D0%B5%D0%B3%D0%BE-%D0%BD%D1%83%D0%B6%D0%B5%D0%BD-%D1%81%D1%82%D1%80%D0%B0%D0%BD%D0%B8%D1%87%D0%BD%D1%8B%D0%B9-%D0%B1%D0%BE%D1%82)
2) [Формат запроса?](https://github.com/Skull-Manager/page-bot/blob/master/README.md#%D1%84%D0%BE%D1%80%D0%BC%D0%B0%D1%82-%D0%B7%D0%B0%D0%BF%D1%80%D0%BE%D1%81%D0%B0-)
3) [Как подключить?](https://github.com/Skull-Manager/page-bot/blob/master/README.md#%D0%BA%D0%B0%D0%BA-%D0%BF%D0%BE%D0%B4%D0%BA%D0%BB%D1%8E%D1%87%D0%B8%D1%82%D1%8C)
4) [Безопастность](https://github.com/Skull-Manager/page-bot/blob/master/README.md#%D0%B1%D0%B5%D0%B7%D0%BE%D0%BF%D0%B0%D1%81%D1%82%D0%BD%D0%BE%D1%81%D1%82%D1%8C-)

## Для чего нужен страничный бот?
- Страничный бот нужен для удобного управления своей беседой и страницей.

## Формат запроса :
На ваш сервер отправляется подобный json-запрос. Отправляется `POST` методом, параметром `out` 

- Пример принятия на php **`$_POST['out']`**

**`{
"peer_id": 2000000000, 
"from_id": 1,
"conversation_message": 11
"text": "Test Message",
"method": "skullSend",
"key": "хххх"
}`**

```json
{
  "peer_id": 2000000000, 
  "from_id": 1,
  "conversation_message": 11,
  "text": "Test Message",
  "method": "skullSend",
  "key": "хххх"
}
```

**`Skull - Manager`** отправляет на **`02.08.20`** всего 2 метода :
1) **`skullSend`** - когда в чате написали /апи (текст).
2) **`skullCheck`** - метод для проверки подключения сервера.

## Как подключить?
- Чтобы подключить бота нужно иметь сервер и немного нывыков работы с языком поддерживающий `HTTP.`
- В качестве первичного подключения можете использовать бесплатные хостинги, такие как :

1. <https://ru.000webhost.com>
2. <https://heroku.com>
3. <https://www.pythonanywhere.com> - только для питона

### Платные :
- [https://sprinthost.ru (спринтхост)](https://sprinthost.ru/s34130)
- [https://timeweb.com (таймвеб)](https://timeweb.com/ru/services/hosting?utm_source=cp96337&utm_medium=timeweb&utm_campaign=timeweb-bring-a-friend)


На предложенных платных хостингах также есть тестовый период, на 1-м он 30 дней, на 2-м всего 10.

## Безопастность :
Сервер находится на `вашей стороне` и ваш личный токен мы не требуем и тем более `не можем` получить переписки или что-то подобное. Действия на вашей странице вызывает `Skull - Manager`, но за эти `действия` отвечает только `ваш сервер.`

Настоятельно **`не рекомендуем`** разглашать адрес вашего сервера, так как сторонние сервера могут имитировать отправку запросов skull-manager.

Для наибольшей безопасности `Skull - Manager` отправляет `key` - секретный ключ, который был `сгенерирован` в личных сообщениях с ботом. 
#### Раскрывать свой `key` еще более настоятельно `не рекомендуем.`

## Пример подключения на спринтхост-е :
После регистрации аккаунта у вас автоматически будет создан тестовый сайт, он нам и нужен.
Переходим в **`файловый менеджер -> domains -> нужный сайт -> загрузить файл -> выбираете скачанный архив -> разархивировать.`**

-**Там уже будет папка `public_html`, просто удалите ее и разархивируйте скаченный архив**

Как найти сервер, который нужно указывать боту?
На спринхосте вы копируете ваш `url-адрес сайта`, вставляете в строку, переходите, там будет `hello skull`. В строке браузера к ссылке вашего сайта прибавляете `/bot.php` - это и есть нужный адрес для бота.


# Я залил файлы на сервер, что дальше?
Нужно получить свой vk - token на сайте <https://vkhost.github.io/> и обязательно через приложение `Kate Mobile` -> `Скопируйте часть адресной строки от access_token= до &expires_in` и вставьте токен `(token_vk)` в скрипт `src/config.php`, а также свой секретный ключ `(skull_key)`:

```php
define('token_vk', '');  // токен от страницы
define('skull_key', ''); // ключ от скулла (полученный в лс)
```

Далее пишем в лс боту `/апи (адрес для бота)`, если все правильно сделали, бот выдаст ваш ключ доступа, его копируете и вставляете в константу `skull_key.` (перед подтверждением сервера убедитесь, что вставили валидный токен в константу `token_vk`

`P.S если нужна помощь в установке, то обратитесь в беседу бота, ссылка на которую есть в группе.`

# Другое :
- В репозитории предложено использовать `JsonDB` - это база данных на json-e. 

**Почему не mySQL и тп?**


В 1-ю очередь это упросит задачу `менее продвинутым` пользователям, им не нужно будет подключать бд и тп процессы.
Также в случае `переноса данных на другой сервер / хостинг`, вы можете просто скачать свой сформированный архив, это будет быстрее и проще, чем копировать или `переносить базу данных.`

Ссылка на репозиторий JsonDB : <https://github.com/donjajo/php-jsondb> 
