<?php

namespace app\core\src;

final class File {

    protected array $allowedFileExtensions = ['jpg', 'jpeg', 'webp', 'png', 'xml'];

    public const INVALID_EXTENSION  = 'Invalid file extension';
    public const INVALID_FILE_NAME  = 'Invalid file name';
    public const INVALID_FILE_SIZE  = 'File is to big';
    public const INVALID_FILE  = 'File not found';
    public const TPL_FILE_EXTENSION = '.tpl.php';
    public const VIEWS_FOLDER       = '/views/';

    protected const FILE_NOT_FOUND     = 'File not found';
    protected const MAXIMUM_FILE_SIZE  = 10000000;

    public function __construct(
        protected $file,
        protected $fileDirectory = null
    ) {
        $this->fileDirectory ??= dirname(__DIR__, 2).'/uploads/';
    }

    public function moveFile(): bool {
        if (!$this->checkFileType()) throw new \Exception(self::INVALID_EXTENSION);
        if (!$this->validateFileName()) throw new \Exception(self::INVALID_FILE_NAME);
        if (!$this->validateSize()) throw new \Exception(self::INVALID_FILE_SIZE);
        $destination = $this->fileDirectory.(strtotime('now').'-'.$this->file['name']);
        return move_uploaded_file($this->file['tmp_name'], $destination);
    }

    public function validateSize(): bool {
        return $this->file['size'] < self::MAXIMUM_FILE_SIZE;
    }

    protected function checkFileType(): bool {
        $fileType = preg_replace('~.*' . preg_quote('/', '~') . '~', '', $this->file['type']);
        return in_array($fileType, $this->allowedFileExtensions);
    }

    public function validateFileName(): bool {
        $fileName = preg_replace('~\..*~', '', $this->file['name']);
        return preg_match('/[a-zA-Z0-9]/', $fileName);  
    }

    public function unlinkFile(): bool {
        return unlink($this->fileDirectory . $this->file['name']);
    }

    public function getFile() {
        if (!$this->exists()) return self::INVALID_FILE;
    }

    public function exists() {
        return file_exists($this->fileDirectory .'/'. $this->file);
    }

}
