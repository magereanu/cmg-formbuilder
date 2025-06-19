# CodeMagster.com FormBuilder

**Version:** 1.0 Beta  
**Author:** [CodeMagster.com](https://codemagster.com)  
**License:** MIT  
**Status:** 🚧 Beta

A lightweight, extensible PHP FormBuilder class using Bootstrap 5.3 markup. Ideal for creating dynamic forms with validation, CSRF protection, file uploads, and JSON schema import/export.

---

## ✨ Features

- 📦 Add fields: input, textarea, select, checkbox, radio, file
- 🔄 Nested field names (e.g., `user[profile][email]`)
- ✅ Required field validation (including files)
- 🔐 CSRF token handling
- 📝 Field-level rules (email, url, minlength)
- 🔄 Import/export form schemas as JSON
- 📤 File upload validation
- 💡 Inline custom HTML blocks
- 🧪 Debug mode (prints POST, FILES, and errors)
- 📄 Markdown form documentation generator

---

## 📂 Folder Structure

```
project/
├── index.php
└── src/
    └── FormBuilder.php
```

---

## 🚀 Quick Start

### 1. Include the class

```php
require_once 'src/FormBuilder.php';

use CMG\FormBuilder\FormBuilder;
```

### 2. Create and render the form

```php
$form = new FormBuilder();
$form->setMethod('POST')
     ->setAction('')
     ->setFormClass('needs-validation')
     ->enableCsrf()
     ->addField('email', 'email', 'Email Address', ['required' => true])
     ->addField('cv', 'file', 'Upload CV', ['required' => true]);

$form->handleSubmit(); // Optional callback hook
$form->render();       // Or renderFieldRaw() for manual layout
```

---

## 🔐 CSRF Protection

Enable CSRF with:

```php
$form->enableCsrf();
```

And validate after submission:

```php
if (!$form->validateCsrf($_POST)) {
    die('Invalid CSRF token');
}
```

---

## 🧪 Debugging

```php
$form->enableDebug();
$form->debugOutput(); // Dumps POST, FILES, ERRORS
```

---

## 📤 File Upload Support

Use nested names like `documents[cv]`. The builder correctly parses and validates `$_FILES` with nested arrays.

---

## 🧩 JSON Schema

### Export

```php
$json = $form->exportSchema();
file_put_contents('form-schema.json', $json);
```

### Import

```php
$json = file_get_contents('form-schema.json');
$form->importSchema($json);
```

---

## 📄 Generate Markdown Docs

```php
echo $form->exportMarkdownDoc();
```

---

## ✅ License

MIT © CodeMagster.com
