<?php 

try {
    $connection = new PDO("mysql:host=your_host;dbname=your_db;charset=utf8", 'your_username', 'your_password');
} catch (\PDOException $e) {
    header('location: https://google.com'); //условный редирект на главную страницу гугл
}

function checkBarcode(PDO $connection, string $barcode): bool
{
    $statement = $connection->prepare('SELECT * FROM orders WHERE barcode = :barcode');
    $statement->execute([':barcode' => $barcode]);
    return $statement->fetchColumn() > 0;
}

function generateBarcode(PDO $connection, int $length = 8): string
{
    do {
        $barcode = '';
        for ($i = 0; $i < $length; $i++) {
            $barcode .= (string)random_int(0, 9);
        }
    } while (checkBarcode($connection, $barcode));

    return $barcode;
}

function sendRequest(string $url, array $data): array
{
    $responses = [
        ["message" => "order successfully booked"],
        ["error" => "barcode already exists"],
    ];

    return $responses[array_rand($responses)];
}

function confirmOrder(string $barcode): array
{
    $responses = [
        ["message" => "order successfully approved"],
        ["error" => "event cancelled"],
        ["error" => "no tickets"],
        ["error" => "no seats"],
        ["error" => "fan removed"]
    ];

    return $responses[array_rand($responses)];
}

function saveOrder(PDO $connection, array $order, int $total_price): void
{
    $sql = "INSERT INTO orders (event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity, barcode, equal_price, created) VALUES (:event_id, :event_date, :ticket_adult_price, :ticket_adult_quantity, :ticket_kid_price, :ticket_kid_quantity, :barcode, :equal_price, NOW())";
    $statement = $connection->prepare($sql);
    $statement->execute($order + ['equal_price' => $total_price]);
}

function addOrder(PDO $connection, int $event_id, string $event_date, int $ticket_adult_price, int $ticket_adult_quantity, int $ticket_kid_price, int $ticket_kid_quantity): void
{
    $total_price = ($ticket_adult_price * $ticket_adult_quantity) + ($ticket_kid_price * $ticket_kid_quantity);
    $barcode = generateBarcode($connection, 8);

    $order = [
        "event_id" => $event_id,
        "event_date" => $event_date,
        "ticket_adult_price" => $ticket_adult_price,
        "ticket_adult_quantity" => $ticket_adult_quantity,
        "ticket_kid_price" => $ticket_kid_price,
        "ticket_kid_quantity" => $ticket_kid_quantity,
    ];

    $order["barcode"] = $barcode;

    $bookingResponse = sendRequest('https://api.site.com/book', $order);

    if (isset($bookingResponse['message'])) {

        $successResponse = confirmOrder($barcode);

        if (isset($successResponse['message']) && $successResponse['message'] === 'order successfully approved') {
            saveOrder($connection, $order, $total_price);
            echo 'Order saved successfully!';
        } else {
            echo 'Error';
        }

    } elseif (isset($bookingResponse['error']) && $bookingResponse['error'] === 'barcode already exists') {
        addOrder($connection, $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity);
    }
}
