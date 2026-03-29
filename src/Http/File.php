<?php

namespace Parachute\Http;

class File
{
    public string $name;
    public string $tmpName = '';
    public string $path = '';
    public int $size;
    public string $type;
    public int $error = UPLOAD_ERR_OK;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->name = basename($path);
        $this->size = filesize($path);
        $this->type = mime_content_type($path);
    }

    public static function open(string $path): ?self
    {
        if (!file_exists($path)) {
            return null;
        }
        return new self($path);
    }

    public static function fromUpload(array $upload): self
    {
        $file = new self($upload['tmp_name']);
        $file->tmpName = $upload['tmp_name'];
        $file->name = $upload['name'];
        $file->size = $upload['size'];
        $file->type = $upload['type'];
        $file->error = $upload['error'];
        return $file;
    }

    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    public function getExtension(): string
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    public function move(string $destination): bool
    {
        if ($this->tmpName)
            return move_uploaded_file($this->tmpName, $destination);
        else
            return rename($this->path, $destination);
    }

    public function download(?string $name = null): Response
    {
        return new Response(
            file_get_contents($this->path),
            200,
            [
                'Content-Type' => $this->type,
                'Content-Length' => $this->size,
                'Content-Disposition' => 'attachment; filename="' . ($name ?? $this->name) . '"',
            ]
        );
    }

    public function getErrorMessage(): string
    {
        $errors = [
            UPLOAD_ERR_OK => 'There is no error, the file uploaded with success.',
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];

        return $errors[$this->error] ?? 'Unknown error.';
    }
}
