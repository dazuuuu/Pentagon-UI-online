<?php

return function (PDO $db): void {
    $now = date('Y-m-d H:i:s');

    // Sample travels
    $travels = [
        ['Maasai Mara Safari', 'maasai-mara-safari', 'Maasai Mara', 'Kenya', 'Witness the Great Migration and big cats on the open savannah.', 'published', 1],
        ['Diani Coast Escape', 'diani-coast-escape', 'Diani Beach', 'Kenya', 'White sands, turquoise waters, and coastal relaxation.', 'published', 1],
        ['Dubai City Break', 'dubai-city-break', 'Dubai', 'UAE', 'Modern skyline, desert adventures, and luxury shopping.', 'published', 0],
    ];
    $tStmt = Database::isMysql()
        ? $db->prepare(
            'INSERT IGNORE INTO travels (title, slug, location, country, summary, status, featured, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        )
        : $db->prepare(
            'INSERT OR IGNORE INTO travels (title, slug, location, country, summary, status, featured, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
    foreach ($travels as $t) {
        $tStmt->execute([...$t, $now]);
    }

    $getTravel = $db->prepare('SELECT id FROM travels WHERE slug = ?');
    $getTravel->execute(['maasai-mara-safari']);
    $maraId = (int)$getTravel->fetchColumn();
    $getTravel->execute(['diani-coast-escape']);
    $dianiId = (int)$getTravel->fetchColumn();
    $getTravel->execute(['dubai-city-break']);
    $dubaiId = (int)$getTravel->fetchColumn();

    $tours = [
        [$maraId, '3 Days Maasai Mara Bush Package', '3-days-maasai-mara-bush', 'Game drives, lodge stay, and park fees included.', 3, '3 Days / 2 Nights', 45000, 'KES', 'Maasai Mara', 'published', 1],
        [$dianiId, '5 Days Diani Summer Deals', '5-days-diani-summer', 'Beach resort stay with transfers from Nairobi.', 5, '5 Days / 4 Nights', 38000, 'KES', 'Diani', 'published', 1],
        [$dubaiId, '5 Days Dubai Low Season', '5-days-dubai-low-season', 'Flights, hotel, and guided city highlights.', 5, '5 Days / 4 Nights', 95000, 'KES', 'Dubai', 'published', 0],
    ];
    $tourStmt = Database::isMysql()
        ? $db->prepare(
            'INSERT IGNORE INTO tours (travel_id, title, slug, description, duration_days, duration_label, price, currency, location, status, featured, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )
        : $db->prepare(
            'INSERT OR IGNORE INTO tours (travel_id, title, slug, description, duration_days, duration_label, price, currency, location, status, featured, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
    foreach ($tours as $tour) {
        $tourStmt->execute([...$tour, $now]);
    }
};
