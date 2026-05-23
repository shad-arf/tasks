<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;

class WhatsAppAccountSynchronizer
{
    public function sync(string $account): void
    {
        $account = trim($account);

        if ($account === '') {
            return;
        }

        Config::set('services.whatsapp.account', $account);

        if (app()->environment('testing')) {
            return;
        }

        $path = base_path('.env');

        if (! is_file($path) || ! is_readable($path) || ! is_writable($path)) {
            return;
        }

        $contents = file_get_contents($path);

        if (! is_string($contents)) {
            return;
        }

        $line = 'WHATSAPP_ACCOUNT='.$this->escapeEnvValue($account);

        if (preg_match('/^WHATSAPP_ACCOUNT=.*/m', $contents) === 1) {
            $updatedContents = preg_replace('/^WHATSAPP_ACCOUNT=.*/m', $line, $contents, 1);

            if (! is_string($updatedContents)) {
                return;
            }
        } else {
            $separator = str_ends_with($contents, PHP_EOL) ? '' : PHP_EOL;
            $updatedContents = $contents.$separator.$line.PHP_EOL;
        }

        if ($updatedContents !== $contents) {
            file_put_contents($path, $updatedContents);
        }
    }

    private function escapeEnvValue(string $value): string
    {
        if (preg_match('/\s/', $value) !== 1 && ! str_contains($value, '"')) {
            return $value;
        }

        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
    }
}
