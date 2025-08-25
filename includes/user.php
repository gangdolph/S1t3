<?php
function username_with_avatar(mysqli $conn, int $user_id, ?string $username = null): string {
    if ($username === null) {
        if ($stmt = $conn->prepare('SELECT username FROM users WHERE id = ?')) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->bind_result($username);
            $stmt->fetch();
            $stmt->close();
        }
    }
    $avatar = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIj48Y2lyY2xlIGN4PSI1MCIgY3k9IjUwIiByPSI1MCIgZmlsbD0iI2NjYyIvPjwvc3ZnPg==';
    if ($stmt = $conn->prepare('SELECT avatar_path FROM profiles WHERE user_id = ?')) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($path);
        if ($stmt->fetch() && $path) {
            if (strpos($path, '/') !== false) {
                $candidate = $path;
                $fileCheck = $path[0] === '/' ? __DIR__ . '/../' . ltrim($path, '/') : $path;
            } else {
                $candidate = '/assets/avatars/' . $path;
                $fileCheck = __DIR__ . '/../assets/avatars/' . $path;
            }
            if (is_file($fileCheck)) {
                $avatar = $candidate;
            }
        }
        $stmt->close();
    }
    return '<span class="user-display"><img src="' . htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') . '" alt="" class="avatar-sm">' .
           htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8') . '</span>';
}
?>
