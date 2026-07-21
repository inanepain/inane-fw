# Project Guidelines: Inane Framework (inane-fw)

This document provides essential information for developers working on the Inane Framework project.

---

## 1. General Language Guide
- Do use British English spelling with ~ise~, not Oxford spelling with ~ize~.
- Use British English for all generated text, comments, documentation, commit messages and user-facing content.
- Maintain these conventions consistently across all responses.


## 2. Build and Configuration

### Environment Requirements
- **PHP**: Version 8.5 or higher is required (as specified in `composer.json`).
- **Git**: Required for managing the project and its submodules.

### Project Setup
The project relies heavily on git submodules for its internal libraries (located in `lib/inanepain/`).

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd inane-fw
   ```

2. **Initialize Submodules**:
   ```bash
   git submodule update --init --recursive
   ```

3. **Track Development Branch**:
   The submodules should typically track the `develop` branch.
   ```bash
   # Update the tracked branch for all submodules
   for d in lib/inanepain/*; do git submodule set-branch -b develop $d; done

   # Fetch the latest commits
   git submodule update --remote --recursive
   ```

4. **Composer Dependencies**:
   ```bash
   composer install
   ```

### Build Scripts
The project uses `just` (via `justfile`) and Composer scripts for common tasks:
- **Build Documentation**:
  - `just build`: Builds both CHANGELOG and README.
  - `composer run build-win`: Windows-specific build for documentation.
- **Stylesheets**:
  - `just css`: Compiles SASS to CSS.

---

## 3. Testing Information

### Running Tests
Currently, the project doesn’t use a centralised testing framework like PHPUnit. Testing is primarily done through manual scripts or playground files.

- **PHP Tests**: Located in various directories (e.g., `lib/inanepain/cli/test.php`). Run them directly using the PHP CLI (ensure PHP 8.5+).
- **JavaScript Tests**: Located in `public/js/inane/playground/`. These are typically ES modules (`.mjs`) used for frontend testing.

### Adding New Tests
To add a new test, create a standalone PHP script in a relevant directory (or a temporary `tests/` directory if preferred). Ensure you require the composer autoloader.

### Example Test
The following script demonstrates how to test the `Inane\Stdlib\Thing\Toggle` class:

```php
<?php
// test_example.php
require_once 'vendor/autoload.php';

use Inane\Stdlib\Thing\Toggle;

// Initialise toggle with initial state true (will be inverted to false internally, then toggled on first call)
// The Toggle class in this project has a property hook for 'toggle'
$toggle = new Toggle(true);

echo "Toggle 1: " . ($toggle->toggle ? 'true' : 'false') . "\n"; // false
echo "Toggle 2: " . ($toggle->toggle ? 'true' : 'false') . "\n"; // true

// Using with custom values
$valueToggle = new Toggle(true, true, "ON", "OFF");
echo "Value toggle 1: " . $valueToggle() . "\n"; // ON (since it toggles to true)
echo "Value toggle 2: " . $valueToggle() . "\n"; // OFF
```

Execute with:
```bash
php test_example.php
```

---

## 4. Additional Development Information

### Code Style and Standards
- **Strict Typing**: Use `declare(strict_types=1);` in all PHP files.
- **PHP 8.5+ Features**: Utilise modern PHP features like Property Hooks, as seen in the `Toggle` class.
- **Naming Conventions**:
  - Namespaces follow PSR-4.
  - Core libraries are under the `Inane\` namespace.
  - Skeleton application logic is under the `Knot\` namespace.
- **PHP Doc**:
    - Line between the last @param and @return.
    - Line between @return and @throws.

### Project Structure
- `config/`: Global configuration.
- `data/`: Storage for SQLite databases and DDL.
- `lib/inanepain/`: Internal libraries managed as submodules.
- `module/`: Primary application source code.
- `public/`: Web root.
- `source/`: Development area for new libraries, documentation sources (`doc/`), and styles (`style/`).
- `src/`: Core application logic (Namespace `Knot\`).

### Useful Commands
- **Check PHP version**: `php -v` (Ensure it's 8.5+)
- **Build Readme**: `composer run build-readme-win` (on Windows)
