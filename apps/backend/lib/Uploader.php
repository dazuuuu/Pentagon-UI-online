<?php

class Uploader
{
    private const IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/svg+xml' => 'svg',
    ];

    private const VIDEO_TYPES = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
        'video/ogg' => 'ogv',
    ];

    /**
     * Handles one uploaded file from $_FILES. $kind is 'image' or 'video'.
     * Returns the public URL on success, or throws with a user-facing message.
     */
    public static function handle(array $file, string $kind): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException(self::errorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE));
        }

        $allowed = $kind === 'video' ? self::VIDEO_TYPES : self::IMAGE_TYPES;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowed[$mime])) {
            $label = $kind === 'video' ? 'video (mp4, webm, mov, ogv)' : 'image (jpg, png, webp, gif, svg)';
            throw new RuntimeException("That file isn't a supported {$label}.");
        }

        $maxBytes = $kind === 'video' ? 100 * 1024 * 1024 : 10 * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            $maxLabel = $kind === 'video' ? '100MB' : '10MB';
            throw new RuntimeException("File is too large. Max size is {$maxLabel}.");
        }

        $dir = rtrim(config('uploads_path'), '/');
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Uploads folder is not writable.');
        }

        $ext = $allowed[$mime];
        $name = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest = $dir . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Could not save the uploaded file.');
        }

        return rtrim(config('uploads_url'), '/') . '/' . $name;
    }

    private static function errorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File exceeds the server upload size limit.',
            UPLOAD_ERR_PARTIAL => 'Upload was interrupted. Try again.',
            UPLOAD_ERR_NO_FILE => 'No file was selected.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'Server could not accept the upload.',
            default => 'Upload failed.',
        };
    }
}
