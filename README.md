### Установка
0. Отредактировать .env файл, закинуть туда хост, логин, пароль и название базы данных
1. Выбрать и раскомментировать в .env файле для почты транспортёр который нравится и вписать доступ.
2. Установить зависимости через composer
```composer install```
3. Создать миграцию для базы данных.
```php bin/console make:migration```
4. Выполнить миграцию для базы данных.
```php bin/console doctrine:migrations:migrate```
5. Заполнить базу данных при помощи Fixtures (создаст тестовые 20 рейсов с id от 1 до 20)
```php bin/console doctrine:fixtures:load```
Рабочей папкой является public. На нее следует направить apache/nginx. Либо же, если установлен symfony, просто запустить веб-сервер так:
```symfony server:start```
	
### API
##Booking
```http://127.0.0.1:8000/api/v1/Booking```
Принимает параметры:
flight_id - идентификатор рейса
email - EMail пользователя
seat - Посадочное место

Возвращает в случае ошибки:
{
    "status": "fail",
    "text": "текст ошибки"
}

Возвращает в случае успеха:
{
    "status": "success",
    "booking_id": "1"
    "email": "email@email.ru"
}
	
##CancelBooking
```http://127.0.0.1:8000/api/v1/CancelBooking```
Принимает параметры:
booking_id - идентификатор брони

Возвращает в случае ошибки:
{
    "status": "fail",
    "text": "текст ошибки"
}
Возвращает в случае успеха:
{
    "status": "success",
    "booking_id": "1"
    "text": "текст о том, что все норм"
}
	
##BuyBooking
```http://127.0.0.1:8000/api/v1/BuyBooking```
Принимает параметры:
booking_id - идентификатор брони

Возвращает в случае ошибки:
{
    "status": "fail",
    "text": "текст ошибки"
}
Возвращает в случае успеха:
{
    "status": "success",
    "booking_id": "1"
    "text": "текст о том, что все норм"
}
	
##BuyedCancelBooking
```http://127.0.0.1:8000/api/v1/BuyedCancelBooking```
Принимает параметры:
booking_id - идентификатор брони

Возвращает в случае ошибки:
{
    "status": "fail",
    "text": "текст ошибки"
}
Возвращает в случае успеха:
{
    "status": "success",
    "booking_id": "1"
    "text": "текст о том, что все норм"
}
	
### Events
```http://127.0.0.1:8000/api/v1/callback/events```
	
Принимает в качестве параметра RAW-строку с JSON вида:
{
	"data": {	
		"flight_id":1,
		"triggered_at":1585012345,
		"event":"flight_ticket_sales_completed",
		"secret_key":"a1b2c3d4e5f6a1b2c3d4e5f6"
	}
}

Поддерживает только два события. 
flight_ticket_sales_completed - продажа билетов закрыта
flight_canceled - рейс отменён

Возвращает JSON вида
{"status":"ok"}
	
