<?php

namespace Timpack\Stalenhag;

class Fetch
{
    const BASE_URI = 'http://simonstalenhag.se/';
    const ADDITIONAL_URIS = [
        'http://simonstalenhag.se/es.html',
        'http://simonstalenhag.se/euromek.html',
        'http://simonstalenhag.se/labyrinth.html',
        'http://simonstalenhag.se/paleo.html',
        'http://simonstalenhag.se/tftf.html',
        'http://simonstalenhag.se/tftl.html',
    ];

    public function run()
    {
        $options = getopt('fv', ['path:']);
        if (!isset($options['path']) || !$path = $options['path']) {
            echo 'No path given, please specify it using --path "path/to/directory"' . "\n";
            exit(E_ERROR);
        }

        $path = $this->resolveTilde($path);
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if (!is_dir($path)) {
            echo "ERROR: $path is not a directory.\n";
            exit(E_ERROR);
        }

        if (!is_writable($path)) {
            echo "ERROR: $path is not writable.\n";
            exit(E_ERROR);
        }

        $images = $this->getImageNames();
        foreach ($images as $basename => $img) {
            $filePath = $path . DIRECTORY_SEPARATOR . $basename;
            if (file_exists($filePath) && !isset($options['f'])) {
                continue;
            }
            $imgUrl = self::BASE_URI . $img;
            $contents = @file_get_contents($imgUrl);
            if (!$contents) {
                echo "Failed to fetch $imgUrl!\n";
                continue;
            }
            $fh = fopen($filePath, 'w');
            fwrite($fh, $contents);
            fclose($fh);
            if (isset($options['v'])) {
                echo "Saved $filePath\n";
            }
        }
    }

    protected function getImageNames()
    {
        $result = [];
        $pages = array_merge([self::BASE_URI], self::ADDITIONAL_URIS);

        foreach ($pages as $page) {
            $response = file_get_contents($page);
            if (!$response) {
                echo "Failed to fetch $page!\n";
                continue;
            }

            $matches = [];
            preg_match_all('/<a href="([^"]+\.(jpg|png))" .*>/', $response, $matches);
            if (!isset($matches[1])) {
                continue;
            }
            $images = array_unique($matches[1]);
            foreach ($images as $image) {
                $imageMatches = [];
                preg_match('/[\/]+(.*)/', $image, $imageMatches);
                if (isset($result[$imageMatches[1]])) {
                    continue;
                }
                $result[$imageMatches[1]] = $image;
            }
        }
        return $result;
    }

    protected function resolveTilde($path)
    {
        if (function_exists('posix_getuid') && strpos($path, '~') === 0) {
            $info = posix_getpwuid(posix_getuid());
            $path = str_replace('~', $info['dir'], $path);
        }
        return $path;
    }
}
