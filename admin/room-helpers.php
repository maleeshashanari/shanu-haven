
<?php

// ==========================================
// SHANU HAVEN - ROOM MANAGEMENT HELPERS
// ==========================================

// Admin authentication
require_once __DIR__ . '/auth.php';
requireAdminLogin();

// Database connection
require_once __DIR__ . '/../config/database.php';

// Prevent browser caching of admin pages
header('Cache-Control: no-store, private');


// ==========================================
// 1. SAFE HTML OUTPUT
// ==========================================

function roomE($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


// ==========================================
// 2. CSRF TOKEN GENERATION
// ==========================================

if (empty($_SESSION['room_csrf'])) {
    $_SESSION['room_csrf'] = bin2hex(
        random_bytes(32)
    );
}


// ==========================================
// 3. CSRF TOKEN VALIDATION
// ==========================================

function checkRoomCsrf() {

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals(
            $_SESSION['room_csrf'] ?? '',
            $token
        )
    ) {
        http_response_code(403);
        exit('Invalid security token.');
    }
}


// ==========================================
// 4. ROOM IMAGE PATH
// ==========================================

function roomImageUrl($filename) {

    $filename = basename(
        (string)$filename
    );

    // Existing room images
    $oldImages = [
        'Deluxe Room.png',
        'Ocean View Room.png',
        'Family Suite.png',
        'villa-hero.png'
    ];

    if (in_array(
        $filename,
        $oldImages,
        true
    )) {
        return '../assets/images/' .
            rawurlencode($filename);
    }

    // Newly uploaded room images
    if (preg_match(
        '/^room_[a-f0-9]{32}\.(jpg|png|webp)$/',
        $filename
    )) {
        return '../assets/images/rooms/' .
            rawurlencode($filename);
    }

    // Default image
    return '../assets/images/villa-hero.png';
}


// ==========================================
// 5. UPLOAD ROOM IMAGE
// ==========================================

function uploadRoomImage($file) {

    // No image uploaded
    if (
        !isset($file['error']) ||
        $file['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }

    // Upload error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException(
            'Image upload failed.'
        );
    }

    // Maximum file size: 5 MB
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException(
            'Image must be smaller than 5 MB.'
        );
    }

    // Validate uploaded file
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException(
            'Invalid image upload.'
        );
    }

    // Validate image dimensions
    $info = @getimagesize(
        $file['tmp_name']
    );

    if (
        !$info ||
        $info[0] > 6000 ||
        $info[1] > 6000
    ) {
        throw new RuntimeException(
            'Invalid image dimensions.'
        );
    }

    // Detect MIME type
    $mime = (new finfo(
        FILEINFO_MIME_TYPE
    ))->file($file['tmp_name']);

    // Allowed image formats
    $types = [
        'image/jpeg' => [
            'jpg',
            IMAGETYPE_JPEG
        ],
        'image/png' => [
            'png',
            IMAGETYPE_PNG
        ],
        'image/webp' => [
            'webp',
            IMAGETYPE_WEBP
        ]
    ];

    if (
        !isset($types[$mime]) ||
        $info[2] !== $types[$mime][1]
    ) {
        throw new RuntimeException(
            'Only JPG, PNG and WebP images are allowed.'
        );
    }

    // Generate a secure random filename
    $extension = $types[$mime][0];

    $filename = 'room_' .
        bin2hex(random_bytes(16)) .
        '.' . $extension;

    // Image upload directory
    $directory = __DIR__ .
        '/../assets/images/rooms/';

    // Create folder if it does not exist
    if (!is_dir($directory)) {

        if (!mkdir(
            $directory,
            0755,
            true
        ) && !is_dir($directory)) {

            throw new RuntimeException(
                'Image directory could not be created.'
            );
        }
    }

    // Full destination path
    $destination = $directory . $filename;

    // Move uploaded image
    if (!move_uploaded_file(
        $file['tmp_name'],
        $destination
    )) {
        throw new RuntimeException(
            'Could not save the image.'
        );
    }

    return $filename;
}


// ==========================================
// 6. DELETE MANAGED ROOM IMAGE
// ==========================================

function removeManagedImage($filename) {

    // Only delete images uploaded by our system
    if (!preg_match(
        '/^room_[a-f0-9]{32}\.(jpg|png|webp)$/',
        (string)$filename
    )) {
        return;
    }

    $path = __DIR__ .
        '/../assets/images/rooms/' .
        $filename;

    if (is_file($path)) {
        unlink($path);
    }
}


// ==========================================
// 7. ADMIN NOTIFICATION MESSAGE
// ==========================================

function roomNotice($message) {

    $_SESSION['room_notice'] = $message;

}

// END OF FILE

