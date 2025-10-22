<?php
include __DIR__ . '/../../db/connect.php';
$conn = connect::getInstance()->getConnection();

function translateToVietnamese($text) {
    $text = urlencode($text);
    $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=vi&dt=t&q=$text";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
    $result = curl_exec($ch);
    curl_close($ch);
    $translated = json_decode($result, true);
    return $translated[0][0][0] ?? $text;
}

$totalPages = 4;

for ($page = 1; $page <= $totalPages; $page++) {
    $query = '
    query {
      Page(page: ' . $page . ', perPage: 25) {
        media(type: ANIME, sort: SCORE_DESC) {
          id
          title { romaji }
          coverImage { large }
          description
          episodes
          averageScore
          genres
          studios { nodes { name } }
          status
        }
      }
    }';

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => json_encode(['query' => $query])
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents('https://graphql.anilist.co', false, $context);

    if ($response === false) {
        die("❌ Lỗi gọi API tại trang $page");
    }

    $data = json_decode($response, true);

    if (!isset($data['data']['Page']['media'])) {
        die("❌ Dữ liệu không hợp lệ ở trang $page");
    }

    foreach ($data['data']['Page']['media'] as $anime) {
        $id = (int)$anime['id'];
        $title = $anime['title']['romaji'] ?? '';
        $image = $anime['coverImage']['large'] ?? '';
        $rawSynopsis = strip_tags($anime['description'] ?? '');
        $synopsis = translateToVietnamese($rawSynopsis);
        $episodes = isset($anime['episodes']) ? (int)$anime['episodes'] : 0;
        $score = isset($anime['averageScore']) ? (int)$anime['averageScore'] : 0;
        $studio = $anime['studios']['nodes'][0]['name'] ?? '';
        $genres = implode(', ', $anime['genres'] ?? []);
        $status = $anime['status'] ?? '';
        $status = translateToVietnamese($status);

        // Kiểm tra anime đã tồn tại
        $checkStmt = $conn->prepare("SELECT id FROM anime WHERE id = ?");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows === 0) {
            $insertStmt = $conn->prepare("
                INSERT INTO anime 
                (id, tieu_de, anh_bia, studio, so_tap, trang_thai, diem_trung_binh, the_loai, tom_tat) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $insertStmt->bind_param(
                "isssisiss",
                $id,
                $title,
                $image,
                $studio,
                $episodes,
                $status,
                $score,
                $genres,
                $synopsis
            );

            if (!$insertStmt->execute()) {
                error_log("❌ Lỗi khi insert anime id=$id: " . $insertStmt->error);
            }

            $insertStmt->close();
        }

        $checkStmt->close();
    }
}

echo "✅ Đã import thành công $totalPages trang dữ liệu từ AniList!";
?>
