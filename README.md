# TRMNL Pipeline CLI

A command-line wrapper for the [bnussbau/trmnl-pipeline-php](https://github.com/bnussbau/trmnl-pipeline-php) package, providing easy-to-use commands for converting HTML content and images to optimized formats for e-ink devices.

## Features

- **Browser Rendering**: Convert HTML files or URLs to PNG images using headless browser rendering
- **Image Processing**: Advanced image manipulation for e-ink display compatibility
- **Pipeline Processing**: Combined browser rendering and image processing in a single command
- **Model Support**: Automatic configuration for 12+ different e-ink device models
- **Standalone Binary**: Pre-compiled binaries for Mac and Linux

## Installation

### Prerequisites
Browser command requires Node.js and Puppeteer (globally installed).
You can use the Image command without browser rendering.

### Download Binary (Recommended)

Download the latest release for your platform:

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

```bash
git clone https://github.com/your-org/trmnl-pipeline-cmd.git
cd trmnl-pipeline-cmd
composer install
php trmnl-pipeline
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

**Examples:**
```bash
# Convert HTML file
./trmnl-pipeline browser --input=page.html --output=rendered.png

# Convert URL
./trmnl-pipeline browser --input=https://example.com --model=og_png

# Auto-generate output filename
./trmnl-pipeline browser --input=page.html
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

**Examples:**
```bash
# Process with model defaults
./trmnl-pipeline image --input=photo.png --model=og_png

# Override specific parameters
./trmnl-pipeline image --input=photo.png --width=800 --height=600 --rotation=90

# Convert to BMP format
./trmnl-pipeline image --input=photo.png --format=bmp --bitDepth=1
```

### 3. Pipeline Command

Combined browser rendering and image processing in a single command.

```bash
./trmnl-pipeline pipeline --input=full.html --output=full.png --model=og_png --format=png --colors=2
```

**Parameters:**
Same as Image Command, plus:
- `--input, -i`: Input HTML file path or URL (required)

**Examples:**
```bash
# Full pipeline with model
./trmnl-pipeline pipeline --input=https://example.com --model=og_png

# Custom processing
./trmnl-pipeline pipeline --input=page.html --width=800 --height=480 --colors=2 --bitDepth=1
```

## Supported Models

The CLI supports automatic configuration for the following e-ink device models:

### TRMNL
- `og_png` - TRMNL OG (1-bit PNG)
- `og_bmp` - TRMNL OG (1-bit BMP)
- `og_plus` - TRMNL OG Plus (2-bit PNG)

### Amazon Kindle
- `amazon_kindle_2024` - Amazon Kindle 2024
- `amazon_kindle_paperwhite_6th_gen` - Kindle Paperwhite 6th Gen
- `amazon_kindle_paperwhite_7th_gen` - Kindle Paperwhite 7th Gen
- `amazon_kindle_7` - Kindle 7
- `amazon_kindle_oasis_2` - Kindle Oasis 2

### Kobo
- `kobo_libra_2` - Kobo Libra 2
- `kobo_aura_one` - Kobo Aura One
- `kobo_aura_hd` - Kobo Aura HD

### Other Devices
- `inkplate_10` - Inkplate 10
- `inky_impression_7_3` - Inky Impression 7.3"
- `inky_impression_13_3` - Inky Impression 13.3"

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
- [usetrmnl/byos_laravel](https://github.com/usetrmnl/byos_laravel) - Laravel integration
- [TRMNL Models API](https://usetrmnl.com/api/models) - Device model specifications
