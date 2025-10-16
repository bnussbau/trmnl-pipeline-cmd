<?php

namespace App\Commands;

use App\Support\BrowsershotHelper;
use Bnussbau\TrmnlPipeline\Model;
use Bnussbau\TrmnlPipeline\Stages\BrowserStage;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Spatie\Browsershot\Browsershot;

class BrowserCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'browser
                            {--i|input= : Input HTML file path or URL}
                            {--o|output= : Output PNG file path (optional)}
                            {--model= : Model name for automatic configuration (e.g., og_png)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Convert HTML content or URL to PNG image using browser rendering';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $input = $this->option('input');
        $output = $this->option('output');
        $modelName = $this->option('model');

        if (! $input) {
            $this->error('Input is required. Use --input to specify HTML file or URL.');

            return;
        }

        try {
            // Get HTML content
            $html = $this->getHtmlContent($input);

            // Create Browsershot instance with phar bin path
            $browsershot = new Browsershot;
            $browsershot->setBinPath(BrowsershotHelper::getBinPath());

            $browserStage = new BrowserStage($browsershot);
            $browserStage->html($html);

            if ($modelName) {
                $model = $this->getModel($modelName);
                $browserStage->configureFromModel($model);
            }

            if (! $output) {
                $output = $this->generateOutputPath($input);
            }

            // Process the HTML
            $tempFile = $browserStage(null);

            // Move to final output location
            if ($tempFile !== $output) {
                if (! copy($tempFile, $output)) {
                    throw new \RuntimeException("Failed to copy image to output location: {$output}");
                }
                unlink($tempFile);
            }

            $this->info('Browser rendering completed successfully!');
            $this->line("Output: {$output}");

        } catch (\Exception $e) {
            $this->error('Browser rendering failed: '.$e->getMessage());
            exit(1);
        }
    }

    /**
     * Get HTML content from input (file or URL)
     */
    private function getHtmlContent(string $input): string
    {
        // Check if it's a URL
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            $this->info("Fetching content from URL: {$input}");
            $content = file_get_contents($input);

            if ($content === false) {
                throw new \RuntimeException("Failed to fetch content from URL: {$input}");
            }

            return $content;
        }

        if (! file_exists($input)) {
            throw new \RuntimeException("Input file not found: {$input}");
        }

        $content = file_get_contents($input);
        if ($content === false) {
            throw new \RuntimeException("Failed to read input file: {$input}");
        }

        return $content;
    }

    /**
     * Get model instance from string
     */
    private function getModel(string $modelName): Model
    {
        try {
            return Model::from($modelName);
        } catch (\ValueError $e) {
            throw new \RuntimeException("Invalid model name: {$modelName}. Available models: ".implode(', ', array_map(fn ($case) => $case->value, Model::cases())));
        }
    }

    /**
     * Generate output path based on input
     */
    private function generateOutputPath(string $input): string
    {
        $pathInfo = pathinfo($input);
        $filename = $pathInfo['filename'] ?? 'output';
        $dirname = $pathInfo['dirname'] ?? '.';

        return $dirname.'/'.$filename.'_browser.png';
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // No scheduling needed for this command
    }
}
