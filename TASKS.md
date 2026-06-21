# Задачи (TASKS.md)

Текущее состояние и список задач. Этот файл подключён через `@TASKS.md`
в `CLAUDE.md`, поэтому загружается в начале каждой сессии. Держать в
актуальном виде: выполненное переносить в «Сделано», новое — в «В работе».

## Сейчас в работе

- **Витрина (storefront)** — публичная часть каталога.
  - Контроллеры: `app/Http/Controllers/Shop/` (`ShopController`,
    `ShopApiController`).
  - Страницы/компоненты: `resources/js/Pages/Shop/`,
    `resources/js/Components/Shop/`, `resources/js/Components/VariantsModal.vue`.
  - Composables: `resources/js/Composables/`.
  - Маршруты: `routes/shop.php` (главная витрины + JSON-эндпойнты
    `api/shop/...` для клиентского экранного стека).
  - Стили: `resources/css/shop.css`.
  - Статус: каркас есть, в незакоммиченном состоянии (см. `git status`).

## Бэклог / надо не забыть

- Добавить Pest-тесты на Service-методы и HTTP-флоу витрины (happy path +
  негативный кейс) — по чек-листу из `arhitecture.md`.
- Решить судьбу зависимости `vue-i18n` в `package.json` (проект одноязычный —
  возможно, удалить).
- SEO витрины: Inertia SSR, `<Head>` с уникальными title/description,
  семантическая вёрстка (по разделу SEO в `arhitecture.md`).

## Сделано

- Админская часть («Tasin mobile admin side is done», коммит `380967e`).
