<?php

declare(strict_types=1);

namespace Timpack\Stalenhag;

use RuntimeException;
use InvalidArgumentException;

class Fetch
{
    private const BASE_URI = 'https://simonstalenhag.se/';
    private const ADDITIONAL_URIS = [
        'https://simonstalenhag.se/es.html',
        'https://simonstalenhag.se/euromek.html',
        'https://simonstalenhag.se/labyrinth.html',
        'https://simonstalenhag.se/paleo.html',
        'https://simonstalenhag.se/tftf.html',
        'https://simonstalenhag.se/tftl.html',
    ];

    public function run(): void
    {
        $options = getopt('fv', ['path:']);
        if (!isset($options['path']) || !$path = $options['path']) {
            $this->outputError('No path given, please specify it using --path "path/to/directory"');
            return;
        }

        try {
            $path = $this->resolveTilde($path);
            $path = rtrim($path, DIRECTORY_SEPARATOR);

            $this->validatePath($path);

            $images = $this->getImageNames();
            $this->downloadImages($images, $path, $options);
        } catch (RuntimeException | InvalidArgumentException $e) {
            $this->outputError($e->getMessage());
        }
    }

    private function validatePath(string $path): void
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException("ERROR: $path is not a directory.");
        }

        if (!is_writable($path)) {
            throw new InvalidArgumentException("ERROR: $path is not writable.");
        }
    }

    private function downloadImages(array $images, string $path, array $options): void
    {
        foreach ($images as $basename => $img) {
            $filePath = $path . DIRECTORY_SEPARATOR . $basename;
            if (file_exists($filePath) && !isset($options['f'])) {
                continue;
            }

            $imgUrl = self::BASE_URI . $img;
            try {
                $contents = $this->fetchContent($imgUrl);
                $this->saveFile($filePath, $contents);
                
                if (isset($options['v'])) {
                    echo "Saved $filePath\n";
                }
            } catch (RuntimeException $e) {
                echo "Failed to fetch $imgUrl: " . $e->getMessage() . "\n";
                continue;
            }
        }
    }

    private function fetchContent(string $url): string
    {
        $contents = @file_get_contents($url);
        if ($contents === false) {
            throw new RuntimeException("Failed to fetch content from $url");
        }
        return $contents;
    }

    private function saveFile(string $filePath, string $contents): void
    {
        $result = file_put_contents($filePath, $contents);
        if ($result === false) {
            throw new RuntimeException("Failed to save file to $filePath");
        }
    }

    private function outputError(string $message): void
    {
        echo "ERROR: $message\n";
        exit(1);
    }

    protected function getImageNames(): array
    {
        $result = [];
        $pages = array_merge([self::BASE_URI], self::ADDITIONAL_URIS);

        foreach ($pages as $page) {
            try {
                $response = $this->fetchContent($page);
                $images = $this->extractImageUrls($response);
                $result = array_merge($result, $images);
            } catch (RuntimeException $e) {
                echo "Failed to fetch $page: " . $e->getMessage() . "\n";
                continue;
            }
        }
        return $result;
    }

    private function extractImageUrls(string $response): array
    {
        $result = [];
        $matches = [];
        preg_match_all('/<a href="([^"]+\.(jpg|png))" .*>/', $response, $matches);
        
        if (!isset($matches[1])) {
            return $result;
        }
        
        $images = array_unique($matches[1]);
        foreach ($images as $image) {
            $imageMatches = [];
            preg_match('/[\/]+(.*)/', $image, $imageMatches);
            if (isset($imageMatches[1]) && !isset($result[$imageMatches[1]])) {
                $result[$imageMatches[1]] = $image;
            }
        }
        return $result;
    }

    protected function resolveTilde(string $path): string
    {
        if (function_exists('posix_getuid') && str_starts_with($path, '~')) {
            $info = posix_getpwuid(posix_getuid());
            if ($info !== false && isset($info['dir'])) {
                $path = str_replace('~', $info['dir'], $path);
            }
        }
        return $path;
    }
}
