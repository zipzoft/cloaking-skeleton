# Cloaking Skeleton

A flexible and extensible PHP-based visitor validation system that allows you to implement cloaking functionality based on various criteria such as IP address and referrer sources.

## Features

- Modular validator system
- Built-in validators for:
  - Thailand IP addresses
  - Google referrer validation
- Session management
- HTML response handling
- PSR-4 compliant autoloading
- Modern PHP 8.1+ support

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

1. Clone this repository:
```bash
git clone https://github.com/zipzoft/cloaking-skeleton.git
```

2. Install dependencies:
```bash
composer install
```

### Adding Custom Validators

You can create your own validators by implementing the appropriate validator interface and adding them to the validator factory.

## Project Structure

```
├── assets/           # Static assets
├── screens/         # Screen templates
|   ├── main.html    # Main screen
|   └── fake.html    # Fake screen
├── src/             # Source code
│   ├── Validators/  # Validation rules
│   └── ...
├── vendor/          # Composer dependencies
├── composer.json    # Composer configuration
└── index.php        # Application entry point
```

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Author

[Zipzoft](https://github.com/zipzoft)
