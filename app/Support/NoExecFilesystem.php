<?php

namespace App\Support;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;

class NoExecFilesystem extends BaseFilesystem
{
    /**
     * Create a safe link: prefer symlink, avoid using exec if disabled, fallback to copying.
     */
    public function link($target, $link)
    {
        if (! windows_os()) {
            if (function_exists('symlink')) {
                return symlink($target, $link);
            }

            if (function_exists('exec')) {
                return exec('ln -s ' . escapeshellarg($target) . ' ' . escapeshellarg($link)) !== false;
            }

            // Fallback: copy directory or file contents
            if ($this->isDirectory($target)) {
                return $this->copyDirectory($target, $link);
            }

            return $this->copy($target, $link);
        }

        $mode = $this->isDirectory($target) ? 'J' : 'H';

        if (function_exists('exec')) {
            exec("mklink /{$mode} " . escapeshellarg($link) . ' ' . escapeshellarg($target));
            return true;
        }

        if ($this->isDirectory($target)) {
            return $this->copyDirectory($target, $link);
        }

        return $this->copy($target, $link);
    }

    /**
     * Recursively copy a directory.
     */
    protected function copyDirectory($source, $dest)
    {
        if (! is_dir($source)) {
            return false;
        }

        if (! is_dir($dest)) {
            mkdir($dest, 0777, true);
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            $targetPath = $dest . DIRECTORY_SEPARATOR . substr($item->getPathname(), strlen($source) + 1);

            if ($item->isDir()) {
                if (! is_dir($targetPath)) {
                    mkdir($targetPath, 0777, true);
                }
            } else {
                copy($item->getPathname(), $targetPath);
            }
        }

        return true;
    }
}
