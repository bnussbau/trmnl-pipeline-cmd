<?php

namespace App\Commands;

use App\Support\BrowsershotHelper;
use Bnussbau\TrmnlPipeline\Model;
use Bnussbau\TrmnlPipeline\Stages\BrowserStage;
use Bnussbau\TrmnlPipeline\Stages\ImageStage;
use Bnussbau\TrmnlPipeline\TrmnlPipeline;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Spatie\Browsershot\Browsershot;

class PipelineCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'pipeline
                            {--i|input= : Input HTML file path or URL}
                            {--o|output= : Output image file path (optional)}
                            {--model= : Model name for automatic configuration (e.g., og_png)}
                            {--format= : Output format (png, bmp)}
                            {--width= : Image width in pixels}
                            {--height= : Image height in pixels}
                            {--rotation= : Rotation in degrees}
                            {--colors= : Number of colors for quantization}
                            {--bitDepth= : Bit depth (1, 2, 8)}
                            {--offsetX= : Horizontal offset in pixels}
                            {--offsetY= : Vertical offset in pixels}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Convert HTML content or URL to optimized e-ink image (browser + image processing)';

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

        $tempFile = null;

        try {
            // Get HTML content
            $html = $this->getHtmlContent($input);

            $pipeline = new TrmnlPipeline;

            if ($modelName) {
                $model = $this->getModel($modelName);
                $pipeline->model($model);
            }

            // Create Browsershot instance with proper phar bin path
            $browsershot = new Browsershot;
            $browsershot->setBinPath(BrowsershotHelper::getBinPath());

            // Add browser stage
            $browserStage = new BrowserStage($browsershot);
            $browserStage->html($html);

            $pipeline->pipe($browserStage);

            // Add image stage
            $imageStage = new ImageStage;
            $this->applyImageParameters($imageStage);

            if ($output) {
                $imageStage->outputPath($output);
            }

            $pipeline->pipe($imageStage);

            $result = $pipeline->process();

            $this->info('Pipeline processing completed successfully!');
            $this->line("Output: {$result}");

        } catch (\Exception $e) {
            $this->error('Pipeline processing failed: '.$e->getMessage());
            exit(1);
        } finally {
            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
            }
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
     * Apply image processing parameters from CLI options
     */
    private function applyImageParameters(ImageStage $imageStage): void
    {
        if ($this->option('format')) {
            $imageStage->format($this->option('format'));
        }

        if ($this->option('width')) {
            $imageStage->width((int) $this->option('width'));
        }

        if ($this->option('height')) {
            $imageStage->height((int) $this->option('height'));
        }

        if ($this->option('rotation')) {
            $imageStage->rotation((int) $this->option('rotation'));
        }

        if ($this->option('colors')) {
            $imageStage->colors((int) $this->option('colors'));
        }

        if ($this->option('bitDepth')) {
            $imageStage->bitDepth((int) $this->option('bitDepth'));
        }

        if ($this->option('offsetX')) {
            $imageStage->offsetX((int) $this->option('offsetX'));
        }

        if ($this->option('offsetY')) {
            $imageStage->offsetY((int) $this->option('offsetY'));
        }
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
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // No scheduling needed for this command
    }
}
