<?php

namespace App\Commands;

use Bnussbau\TrmnlPipeline\Data\PaletteData;
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
                           {--dither : Enable Floyd–Steinberg dithering}
                           {--palette= : Palette ID (e.g., color-6a, color-7a, bw, gray-256)}
                           {--colormap= : Comma-separated hex colors (e.g., #FF0000,#00FF00,#0000FF)}';

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

        // Apply palette or colormap
        $colormap = $this->getColormap();
        if ($colormap !== null) {
            $imageStage->colormap($colormap);
            // Set colors to match colormap size for color palette detection
            if (! $this->option('colors')) {
                $imageStage->colors(count($colormap));
            }
            // Set format to PNG if not set (colormap only works with PNG)
            if (! $this->option('format')) {
                $imageStage->format('png');
            }
            // Set bit depth to 2 if not set (required for color palettes)
            if (! $this->option('bitDepth')) {
                $imageStage->bitDepth(2);
            }
        }
    }

    /**
     * Get colormap from palette ID or custom colormap string
     */
    private function getColormap(): ?array
    {
        $paletteId = $this->option('palette');
        $colormapStr = $this->option('colormap');

        // If both are provided, colormap takes precedence
        if ($colormapStr) {
            return $this->parseColormap($colormapStr);
        }

        if ($paletteId) {
            return $this->getPaletteColors($paletteId);
        }

        return null;
    }

    /**
     * Load palette colors from palette ID
     */
    private function getPaletteColors(string $paletteId): array
    {
        $paletteData = PaletteData::getById($paletteId);
        
        if ($paletteData->colors === null) {
            throw new \RuntimeException("Palette '{$paletteId}' has no colors defined");
        }

        return $paletteData->colors;
    }

    /**
     * Parse colormap from comma-separated string
     */
    private function parseColormap(string $colormapStr): array
    {
        $colors = array_map('trim', explode(',', $colormapStr));
        $colors = array_filter($colors, fn ($color) => ! empty($color));

        if (empty($colors)) {
            throw new \RuntimeException('Colormap cannot be empty');
        }

        return array_values($colors);
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
