# international-phone-for-cf7

Поле телефона с выбором страны для Contact Form 7 на базе International Telephone Input

## 🚀 Быстрый старт

Скачайте репозиторий и положите папку в `wp-content/plugins/`:

```
cd wp-content/plugins
git clone https://github.com/vlad-web/international-phone-for-cf7.git
```

Затем активируйте плагин **Международный Телефон для CF7** в админке WordPress.
Для работы нужен установленный и активный [Contact Form 7](https://wordpress.org/plugins/contact-form-7/).

## 📦 Что внутри

- **Тег `[intltel]` / `[intltel*]`** — поле телефона с флагом страны и выпадающим списком
- **International Telephone Input v28.1.0** — лежит в плагине, без CDN
- **Клиентская валидация** номера
- **Отдельный mail-тег** с телефонным кодом страны
- **Автоподключение к обычным полям** CF7 с нужным классом (по умолчанию `.phone`)
- **Страница настроек** — Contact → Международный Телефон
- **Генератор тега** в редакторе форм CF7

## 📁 Структура

```
international-phone-for-cf7.php   — точка входа плагина
includes/
  class-assets.php         — подключение скриптов и стилей
  class-form-tag.php       — регистрация тега [intltel]
  class-settings.php       — страница настроек
  class-tag-generator.php  — генератор тега в редакторе CF7
assets/
  css/                     — стили плагина
  js/                      — скрипт инициализации
  vendor/intl-tel-input/   — библиотека, флаги, utils.js
```

## 🛠 Использование

В форме:

```
[intltel* your-phone initialcountry:ru preferred:ru-ua-by]
```

В шаблоне письма:

```
Телефон: +[your-phone-code] [your-phone]
```

## Опции тега

Списки стран — через дефис, без пробелов и запятых.

| Опция | Что делает |
|---|---|
| `initialcountry:ru` | страна по умолчанию (код ISO-2) |
| `preferred:ru-ua-by` | страны в начале списка |
| `onlycountries:ru-ua-by` | показывать только перечисленные страны |
| `excludecountries:ru` | скрыть перечисленные страны |

## Требования

- WordPress 5.8+
- PHP 7.4+
- Contact Form 7
