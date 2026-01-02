# TRMNL Pipeline CLI

A command-line wrapper for the [bnussbau/trmnl-pipeline-php](https://github.com/bnussbau/trmnl-pipeline-php) package, providing easy-to-use commands for converting HTML content and images to optimized formats for e-ink devices.

## Features

- **Browser Rendering**: Convert HTML files to PNG images using headless browser rendering (using Browsershot and puppeteer)
- **Image Processing**: Advanced image manipulation for e-ink display compatibility
- **Pipeline Processing**: Combined browser rendering and image processing in a single command
- **Model Support**: Automatic configuration for 21+ different e-ink device models
- **Standalone Binary**: Pre-compiled binaries for Mac and Linux

## Installation

### Prerequisites
Browser command requires [Node.js](https://nodejs.org/en/download) and Puppeteer (globally installed). You can use the Image command without browser rendering.
```sh
npm install puppeteer --location=global
```

### Download Binary (Recommended)

[Download the latest release](https://github.com/bnussbau/trmnl-pipeline-cmd/releases/latest) for your platform:

* macOS
* Linux

Make the binary executable:
```bash
chmod +x trmnl-pipeline-{{os}}-{{arch}}
```

### PHAR Package

* requires PHP 8.4 with imagick extension installed
* platform-independent, including Windows

```bash
php trmnl-pipeline.phar --help
```

### From Source

* Deactivate ray, if installed [(Github issue)](https://github.com/laravel-zero/laravel-zero/issues/507#issuecomment-2765942970).

```bash
git clone https://github.com/your-org/trmnl-pipeline-cmd.git
cd trmnl-pipeline-cmd
composer install
php trmnl-pipeline

# Build phar
php trmnl-pipeline app:build
# Build executable
./vendor/bin/phpacker build -c ./phpacker.json --src=./builds/trmnl-pipeline.phar --php=8.4
```

## Commands

### 1. Browser Command

Convert HTML content or URLs to PNG images using browser rendering.

```bash
./trmnl-pipeline browser --input=file.html --output=output.png --model=og_png
```

**Parameters:**
- `--input, -i`: Input HTML file path or URL (required)
- `--output, -o`: Output PNG file path (optional, auto-generated if not provided)
- `--model`: Model name for automatic configuration (optional)
- `--timezone`: Browser timezone (e.g., `UTC`, `America/New_York`, `Europe/Berlin`) - useful for time/date-dependent content

**Examples:**
```bash
# Convert HTML file
./trmnl-pipeline browser --input=page.html --output=rendered.png

# Convert URL
./trmnl-pipeline browser --input=https://example.com --model=og_png

# Auto-generate output filename
./trmnl-pipeline browser --input=page.html

# Set browser timezone for time-dependent content
./trmnl-pipeline browser --input=page.html --timezone=America/New_York
```

### 2. Image Command

Process existing images for e-ink display compatibility.

```bash
./trmnl-pipeline image --input=image.png --output=processed.png --model=og_png --format=png --width=800 --height=480
```

**Parameters:**
- `--input, -i`: Input image file path (required)
- `--output, -o`: Output image file path (optional, auto-generated if not provided)
- `--model`: Model name for automatic configuration (optional)
- `--format`: Output format (png, bmp)
- `--width`: Image width in pixels
- `--height`: Image height in pixels
- `--rotation`: Rotation in degrees
- `--colors`: Number of colors for quantization
- `--bitDepth`: Bit depth (1, 2, 8)
- `--offsetX`: Horizontal offset in pixels
- `--offsetY`: Vertical offset in pixels
- `--dither`: Enable Floyd–Steinberg dithering during quantization and palette remapping
- `--palette`: Palette ID (e.g., `color-6a`, `color-7a`, `bw`, `gray-256`) - see Color Support section
- `--colormap`: Comma-separated hex colors (e.g., `#FF0000,#00FF00,#0000FF`) - custom color palette

**Examples:**
```bash
# Process with model defaults
./trmnl-pipeline image --input=photo.png --model=og_png

# Override specific parameters
./trmnl-pipeline image --input=photo.png --width=800 --height=600 --rotation=90

# Enable Floyd–Steinberg dithering
./trmnl-pipeline image --input=photo.png --dither

# Convert to BMP format
./trmnl-pipeline image --input=photo.png --format=bmp --bitDepth=1

# Use predefined color palette (for color e-ink displays)
./trmnl-pipeline image --input=photo.png --palette=color-6a

# Use custom color palette
./trmnl-pipeline image --input=photo.png --colormap="#FF0000,#00FF00,#0000FF,#FFFF00"

# Combine palette with dithering
./trmnl-pipeline image --input=photo.png --palette=color-7a --dither
```

### 3. Pipeline Command

Combined browser rendering and image processing in a single command.

```bash
./trmnl-pipeline pipeline --input=full.html --output=full.png --model=og_png --format=png --colors=2
```

**Parameters:**
Same as Image Command, plus:
- `--input, -i`: Input HTML file path or URL (required)
- `--timezone`: Browser timezone (e.g., `UTC`, `America/New_York`, `Europe/Berlin`)

**Examples:**
```bash
# Full pipeline with model
./trmnl-pipeline pipeline --input=https://example.com --model=og_png

# Custom processing
./trmnl-pipeline pipeline --input=page.html --width=800 --height=480 --colors=2 --bitDepth=1

# Set browser timezone and use color palette
./trmnl-pipeline pipeline --input=page.html --timezone=America/New_York --palette=color-6a

# Full pipeline with custom colormap and optional dithering
./trmnl-pipeline pipeline --input=page.html --colormap="#FF0000,#00FF00,#0000FF" --dither
```

## Supported Models

The CLI supports automatic configuration for the following e-ink device models:

### TRMNL
- `og_png` - TRMNL OG (1-bit PNG)
- `og_bmp` - TRMNL OG (1-bit BMP)
- `og_plus` - TRMNL OG Plus (2-bit PNG)
- `v2` - TRMNL X

### Amazon Kindle
- `amazon_kindle_2024` - Amazon Kindle 2024
- `amazon_kindle_paperwhite_6th_gen` - Kindle Paperwhite 6th Gen
- `amazon_kindle_paperwhite_7th_gen` - Kindle Paperwhite 7th Gen
- `amazon_kindle_7` - Kindle 7
- `amazon_kindle_oasis_2` - Kindle Oasis 2
- `amazon_kindle_scribe` - Amazon Kindle Scribe

### Kobo
- `kobo_libra_2` - Kobo Libra 2
- `kobo_aura_one` - Kobo Aura One
- `kobo_aura_hd` - Kobo Aura HD

### Other Devices
- `inkplate_10` - Inkplate 10
- `inky_impression_7_3` - Inky Impression 7.3"
- `inky_impression_13_3` - Inky Impression 13.3"
- `m5_paper_s3` - M5PaperS3
- `seeed_e1001` - Seeed E1001 Monochrome
- `seeed_e1002` - Seeed E1002 (2-bit)
- `waveshare_4_26` - Waveshare 4.26 (2-bit)
- `waveshare_7_5_bw` - Waveshare 7.5 B/W

## Color Support

Color palettes allow you to process images for color e-ink displays. Palettes define a limited set of colors that the image will be quantized to, which is essential for devices with limited color capabilities.

### Using Predefined Palettes

Many device models specify palette IDs in their configuration. You can use these predefined palettes:

- `bw` - Black & White (2 colors)
- `gray-4` - 4 Grays (2-bit grayscale)
- `gray-16` - 16 Grays (4-bit grayscale)
- `gray-256` - 256 Grays (8-bit grayscale)
- `color-6a` - Color palette with 6 colors (Red, Green, Blue, Yellow, Black, White)
- `color-7a` - Color palette with 7 colors (includes Orange)

**Example:**
```bash
# Use predefined palette for color display
./trmnl-pipeline image --input=photo.png --palette=color-6a
```

### Using Custom Color Palettes

You can define your own color palette by providing comma-separated hex color codes:

```bash
# Custom 4-color palette
./trmnl-pipeline image --input=photo.png --colormap="#FF0000,#00FF00,#0000FF,#FFFF00"
```

### Palette and Bit Depth

- **1-bit depth**: Typically uses 2 colors (black and white)
- **2-bit depth**: Can use up to 4 colors (e.g., `color-6a` or `color-7a` palettes)
- **8-bit depth**: Uses full grayscale (256 colors)

When using color palettes, ensure your `--bitDepth` matches the palette size. For example, `color-6a` works best with `--bitDepth=2`.

### Dithering with Color Palettes

Dithering helps create smoother color transitions when using limited color palettes. It's recommended for photos but may make text appear less sharp:

```bash
# Color palette with dithering
./trmnl-pipeline image --input=photo.png --palette=color-7a --bitDepth=2 --dither
```

## Browser Timezone

The `--timezone` option sets the timezone for the headless browser when rendering HTML content. This is particularly useful when your HTML contains JavaScript that displays time or date information, or when you need to render content that depends on the local timezone.

### Valid Timezone Identifiers

Use any valid PHP timezone identifier. Common examples:

- `UTC` - Coordinated Universal Time
- `America/New_York` - Eastern Time (US)
- `Europe/London` - British Time
- `Europe/Berlin` - Central European Time
- `Asia/Tokyo` - Japan Standard Time

You can find all valid timezone identifiers using PHP's `timezone_identifiers_list()` function or by checking the [PHP timezone documentation](https://www.php.net/manual/en/timezones.php).

### Examples

```bash
# Render HTML with New York timezone
./trmnl-pipeline browser --input=clock.html --timezone=America/New_York
```

## Usage Examples

### Basic Workflow

1. **Convert HTML to optimized image:**
   ```bash
   ./trmnl-pipeline pipeline --input=full.html --model=og_png
   ```

2. **Process existing image:**
   ```bash
   ./trmnl-pipeline image --input=photo.jpg --model=amazon_kindle_2024
   ```

3. **Convert URL with custom settings:**
   ```bash
   ./trmnl-pipeline pipeline --input=https://example.com --width=800 --height=600 --colors=2
   ```

### Advanced Usage

**Batch processing with custom parameters:**
```bash
# Process multiple files
for file in *.html; do
    ./trmnl-pipeline pipeline --input="$file" --model=og_png --output="${file%.html}_processed.png"
done
```

## Requirements

- **PHP 8.2+** (for source installation)
- **ImageMagick** extension
- **Node.js and Puppeteer** (for browser rendering)

## Technical Details

- Browser rendering uses Spatie Browsershot with Puppeteer
- Image processing uses ImageMagick for format conversion and optimization
- Model configurations are loaded from the trmnl-pipeline-php package

## Troubleshooting

1. **"Puppeteer not found"**
   - Install Node.js and run: `npm install -g puppeteer`

2. **"Invalid model name"**
   - Use the exact model names listed in the Supported Models section
   - Case-sensitive: `og_png` not `OG_PNG`

### Getting Help

```bash
# Show all available commands
./trmnl-pipeline list

# Get help for specific command
./trmnl-pipeline help browser
./trmnl-pipeline help image
./trmnl-pipeline help pipeline
```

## License

MIT License. See LICENSE file for details.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## Related Projects

- [bnussbau/trmnl-pipeline-php](https://github.com/bnussbau/trmnl-pipeline-php) - Core PHP package
- [usetrmnl/byos_laravel](https://github.com/usetrmnl/byos_laravel) - TRMNL BYOS that uses trmnl-pipeline-php
- [TRMNL Models API](https://usetrmnl.com/api/models) - Device model specifications
