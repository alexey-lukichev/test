Сопроводительная документация:

1. Описание проекта:
   - Проект предназначен для продажи билетов на события. Заказы хранятся в MySQL с учетом различных типов билетов.

2. Структура базы данных:
   - Таблица `orders`: содержит информацию о каждом заказе.
   - Таблица `tickets`: содержит информацию о каждом купленном билете.
   - Таблица `ticket_types`: содержит информацию о типах билетов.

- Создаем таблицу `ticket_types`, которая будет хранить информацию о типах билетов:

CREATE TABLE ticket_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price INT NOT NULL
);

- Создаем таблицу `tickets`, которая будет хранить информацию о каждом купленном билете, включая уникальный баркод:

CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    ticket_type_id INT NOT NULL,
    barcode VARCHAR(120) UNIQUE NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id)
);

- Таблица `orders` будет содержать информацию о каждом заказе:

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    event_date DATETIME NOT NULL,
    created DATETIME DEFAULT CURRENT_TIMESTAMP
    -- Удаленные поля ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity
    -- Мы перенесли информацию о билетах в отдельную таблицу tickets
);

Объяснение решения:

- Теперь каждый тип билета имеет свое отношение, что позволяет легко добавлять новые типы билетов без изменения структуры таблицы заказов. Каждому билету можно присвоить уникальный баркод, что позволяет легко проверять каждый билет индивидуально.
- Разделение билетов и заказов на разные таблицы делает данные более управляемыми и гибкими.

3. Функции:
   - `checkBarcode()`: проверяет уникальность баркода.
   - `generateBarcode()`: генерирует уникальный баркод.
   - `sendRequest()`: имитирует отправку запроса на внешний API.
   - `confirmOrder()`: имитирует подтверждение заказа.
   - `saveOrder()`: сохраняет заказ в БД.
   - `addOrder()`: добавляет новый заказ, обрабатывая все необходимые проверки.
