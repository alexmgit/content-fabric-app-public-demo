# Content Fabric: демонстрационный фрагмент проекта

Этот репозиторий содержит выдернутый фрагмент реального проекта `Content Fabric`.

Он предназначен для демонстрации подходов к организации backend-кода.

В репозиторий намеренно не включены некоторые части исходного проекта. 

## Стек

- PHP / Laravel 12 / Laravel Jetstream
- PostgreSQL 16
- Очереди задач
- Нотификации (email, telegram)
- Интеграции с внешними API (Apify, Telegram, YooKassa)

## Что здесь можно посмотреть

Ниже директории отсортированы по тому, насколько они показательны для понимания структуры и подхода в проекте.

### 1. [`Services/`](Services/)

- [`Services/Apify/`](Services/Apify)
- [`Services/Source/`](Services/Source)
- [`Services/Search/`](Services/Search)
- [`Services/Payment/`](Services/Payment)
- [`Services/Plan/`](Services/Plan)

### 2. [`Jobs/`](Jobs/)

- [`Jobs/Apify/`](Jobs/Apify)
- [`Jobs/Billing/`](Jobs/Billing)
- [`Jobs/Source/`](Jobs/Source)
- [`Jobs/Search/`](Jobs/Search)

### 3. [`Http/`](Http/)

- [`Http/Controllers/`](Http/Controllers)
- [`Http/Requests/`](Http/Requests)
- [`Http/Resources/`](Http/Resources)

## Структура по доменам

Код в репозитории сгруппирован вокруг нескольких доменов:

- `Billing` — платежи, методы оплаты, транзакции, подписки, тарифы
- `Source` — источники данных, посты, события, транскрибация, processing pipeline
- `Search` — поисковые сценарии, результаты, связанные источники
- `Apify` — интеграционный слой для запуска и обработки внешних actor/job-сценариев
