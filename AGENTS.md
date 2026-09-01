# Code Style

- **Strict Typing**: Use `declare(strict_types=1);` in all PHP files.
- DO use British -ise instead of -ize.

## PHP Doc

- Line between the last @param and @return.
- Line between @return and @throws.
- Always add @throws.

## Language
- Do use British English spelling with -ise, not (-ize) Oxford spelling.

## Naming Conventions
- Class names should be in StudlyCaps.
- Method names should be in camelCase.
- Variable names should be in camelCase.

## Code Organisation
- Use namespaces to organise code.
- Keep files focused on a single responsibility.
- Use meaningful and descriptive names for files and directories.
- Avoid deep nesting of directories.
- Avoid long file names.
- Use short and concise file names.

## PHP

- DO prefer clean code.
- DO prefer type-safe code.

- You're an expert PHP 8.5 developer.
- Prefer readonly properties.
- Prefer enums, attributes, constructor promotion.
- Prefer union/intersection types.
- Prefer first-class callables.
- Avoid deprecated syntax.

- The project is primarily written in PHP version 8.5.
- Code should be compatible with PHP 8.5 features.
- Code should target PHP 8.5 features.

- DO Check for any debugging code that should not be released to production.
- Do be concise in method and parameter descriptions.

### PHPDoc

- DO Space/line between the last @param and the @return tags.
- Do not return example code, do not use @author or @version or @since tags.
- DO NOT generate example usage.
- DO NOT generate usage example.
- DO NOT use HTML tags such as <p>, <lu>, <li>.
- DO NOT generate documentation for type member properties.
- DO Write PHPDoc.
- DO Add @throws tags to PHPDoc when applicable.
- DO Space/line between the @return and @throws tags.
- DO keep an empty line between @return and @throws tags.
- DO be concise in method and parameter descriptions.
