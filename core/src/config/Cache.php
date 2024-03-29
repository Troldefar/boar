<?php

namespace app\core\src\config;

class Cache {
    
    private string $cache_dir = '/tmp/cache';

    public function __construct(array $options = []) {
        $available_options = array('cache_dir');
        foreach ($available_options as $name)
            if (isset($options[$name]))
                $this->$name = $options[$name];
    }

    public function get(string $id) {
        $file_name = $this->getFileName($id);

        if (!is_file($file_name) || !is_readable($file_name)) return false;

        $lines    = file($file_name);
        $lifetime = array_shift($lines);
        $lifetime = (int) trim($lifetime);

        if ($lifetime !== 0 && $lifetime < time()) {
            @unlink($file_name);
            return false;
        }
        $serialized = join('', $lines);
        $data       = unserialize($serialized);
        return $data;
    }

    public function delete(string $id) {
        $file_name = $this->getFileName($id);
        return unlink($file_name);
    }

    public function save(string $id, $data, int $lifetime = 3600) {
        $dir = $this->getDirectory($id);
        if (!is_dir($dir)) if (!mkdir($dir, 0755, true)) return false;
        $file_name  = $this->getFileName($id);
        $lifetime   = time() + $lifetime;
        $serialized = serialize($data);
        $result     = file_put_contents($file_name, $lifetime . PHP_EOL . $serialized);
        if ($result === false) return false;
        return true;
    }

    protected function getDirectory(string $id) {
        $hash = hash('sha256', $id, false);
        $dirs = [$this->getCacheDirectory(), substr($hash, 0, 2), substr($hash, 2, 2)];
        return join(DIRECTORY_SEPARATOR, $dirs);
    }

    protected function getCacheDirectory() {
        return $this->cache_dir;
    }

    protected function getFileName(string $id) {
        $directory  = $this->getDirectory($id);
        $hash       = hash('sha256', $id, false);
        $file       = $directory . DIRECTORY_SEPARATOR . $hash . '.cache';
        return $file;
    }
}