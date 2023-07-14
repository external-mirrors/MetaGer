<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LaravelLocalization;

class LinkTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'localization:link_translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Goes through all supported locales and creates symbolic links i.e. from en-US to en';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            // Check if translations exist for locale itself
            $path = lang_path($locale);
            if (file_exists($path)) {
                continue;
            }
            if (preg_match("/^([a-z]{2})-[A-Z]{2}/", $locale, $matches)) {
                $short_path = lang_path($matches[1]);
                if (file_exists($short_path)) {
                    symlink($short_path, $path);
                }
            }
        }

        return Command::SUCCESS;
    }
}