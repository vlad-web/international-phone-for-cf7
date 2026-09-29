=== Международный Телефон для CF7 ===
Contributors: buzway
Tags: contact-form-7, phone, intl-tel-input
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Добавляет для Contact Form 7 поле телефона с выбором страны на основе International Telephone Input.

== Описание ==

Добавляет новый тег формы `[intltel]` / `[intltel*]`, который выводит поле телефона с флагом
страны и выпадающим списком (International Telephone Input v28.1.0), клиентской проверкой
корректности номера и отдельным mail-тегом с телефонным кодом страны.

Пример использования в форме:

	[intltel* your-phone initialcountry:ru preferred:ru-ua-by]

В шаблоне письма:

	Телефон: [your-phone-code] [your-phone]

Опции тега (списки стран — через дефис, без пробелов и запятых):

* `initialcountry:ru` — страна по умолчанию (код ISO-2)
* `preferred:ru-ua-by` — страны в начале списка
* `onlycountries:ru-ua-by` — показывать только перечисленные страны
* `excludecountries:ru` — скрыть перечисленные страны

Если на сайте уже есть формы, где телефон сделан обычным полем CF7 с классом `.phone`
(без нового тега), плагин подключит к ним intl-tel-input автоматически — список классов
настраивается в admin: Contact → Международный Телефон.

== Changelog ==

= 1.0.0 =
* Первый релиз.
