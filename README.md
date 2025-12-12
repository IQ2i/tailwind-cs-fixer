# Tailwind CS Fixer

A PHP tool to automatically sort Tailwind CSS classes in your HTML and Twig files, without any JavaScript dependencies.

## Features

- ✅ Sorts Tailwind classes according to the official order (like `prettier-plugin-tailwindcss`)
- ✅ HTML and Twig support
- ✅ Handles variants (hover:, focus:, md:, lg:, etc.)
- ✅ Supports arbitrary values (`w-[42px]`)
- ✅ Preserves dynamic Twig expressions
- ✅ Dry-run mode to preview changes
- ✅ Diff display
- ✅ No Node.js dependency

## Installation

```bash
composer require --dev iq2i/tailwind-cs-fixer
```

## Usage

### Basic command

```bash
# Fix all files in the current directory
vendor/bin/tailwind-cs-fixer

# Fix a specific directory
vendor/bin/tailwind-cs-fixer templates/

# Fix a specific file
vendor/bin/tailwind-cs-fixer templates/base.html.twig
```

### Options

```bash
# Dry-run mode (preview without modification)
vendor/bin/tailwind-cs-fixer --dry-run

# Show differences
vendor/bin/tailwind-cs-fixer --diff

# Specify file extensions
vendor/bin/tailwind-cs-fixer --extensions=html,twig,php

# Combine options
vendor/bin/tailwind-cs-fixer templates/ --dry-run --diff
```

## Current limitations

- Does not sort classes in complex Twig conditions
- Order is not configurable (follows official Tailwind order)

## Issues and feature requests

Please report issues and request features at https://github.com/iq2i/tailwind-cs-fixer/issues.

## License

This bundle is under the MIT license. For the whole copyright, see
the [LICENSE](LICENSE) file distributed with this source code.
