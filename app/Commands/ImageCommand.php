<?php

namespace App\Commands;

use Bnussbau\TrmnlPipeline\Model;
use Bnussbau\TrmnlPipeline\Stages\ImageStage;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ImageCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'image
                           {--i|input= : Input image file path}
                           {--o|output= : Output image file path (optional)}
                           {--model= : Model name for automatic configuration (e.g., og_png)}
                           {--format= : Output format (png, bmp)}
                           {--width= : Image width in pixels}
                           {--height= : Image height in pixels}
                           {--rotation= : Rotation in degrees}
                           {--colors= : Number of colors for quantization}
                           {--bitDepth= : Bit depth (1, 2, 8)}
                           {--offsetX= : Horizontal offset in pixels}
                           {--offsetY= : Vertical offset in pixels}
                           {--dither : Enable Floyd–Steinberg dithering}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Process images for e-ink display compatibility';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $input = $this->option('input');
        $output = $this->option('output');
        $modelName = $this->option('model');

        if (! $input) {
            $this->error('Input is required. Use --input to specify image file path.');

            return;
        }

        if (! file_exists($input)) {
            $this->error("Input file not found: {$input}");

            return;
        }

        try {
            $imageStage = new ImageStage;

            if ($modelName) {
                $model = $this->getModel($modelName);
                $imageStage->configureFromModel($model);
            }

            $this->applyImageParameters($imageStage);

            if ($output) {
                $imageStage->outputPath($output);
            }

            // Process the image
            $result = $imageStage($input);

            $this->info('Image processing completed successfully!');
            $this->line("Output: {$result}");

        } catch (\Exception $e) {
            $this->error('Image processing failed: '.$e->getMessage());
            exit(1);
        }
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

        if ($this->option('dither')) {
            $imageStage->dither(true);
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
