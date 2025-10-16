<?php

namespace App\Support;

class BrowsershotHelper
{
    /**
     * Get the path to the browser.cjs file, copying it from vendor if needed
     */
    public static function getBinPath(): string
    {
        $targetDir = sys_get_temp_dir().'/trmnl-browsershot';
        $targetPath = $targetDir.'/browser.cjs';

        if (! file_exists($targetPath)) {
            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Try to copy from vendor directory first (development mode)
            $sourceFile = base_path('vendor/spatie/browsershot/bin/browser.cjs');

            if (file_exists($sourceFile)) {
                // Development mode - direct file copy
                if (! copy($sourceFile, $targetPath)) {
                    throw new \RuntimeException("Failed to copy browser.cjs to temp directory: {$targetPath}");
                }
            } else {
                // PHAR mode - extract from embedded vendor files
                self::extractFromPhar($targetPath);
            }
        }

        return $targetPath;
    }

    /**
     * Extract browser.cjs from PHAR archive
     */
    private static function extractFromPhar(string $targetPath): void
    {
        // Get the PHAR file path
        $pharPath = \Phar::running(false);

        if (empty($pharPath)) {
            throw new \RuntimeException('Cannot determine PHAR path for browser.cjs extraction');
        }

        // Try to extract from the PHAR
        $phar = new \Phar($pharPath);
        $browserCjsPath = 'vendor/spatie/browsershot/bin/browser.cjs';

        if (! $phar->offsetExists($browserCjsPath)) {
            throw new \RuntimeException("browser.cjs not found in PHAR at: {$browserCjsPath}");
        }

        // Extract the file
        $content = $phar[$browserCjsPath]->getContent();

        if (file_put_contents($targetPath, $content) === false) {
            throw new \RuntimeException("Failed to extract browser.cjs from PHAR to: {$targetPath}");
        }
    }
}
