<?php

namespace CMG\FormBuilder;

class FormBuilder
{
    private array $elements = [];
    private string $method = 'POST';
    private string $action = '';
    private string $formId = 'cmg-form';
    private string $formClass = 'needs-validation';
    private array $formAttributes = [];
    private array $requiredFields = [];
    private array $errors = [];
    private bool $hasFile = false;
    private string $submitLabel = 'Submit';
    private bool $showRequiredAsterisk = false;

    public function setSubmitLabel(string $label): self
{
    $this->submitLabel = $label;
    return $this;
}

    public function setMethod(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function setId(string $formId): self
    {
        $this->formId = $formId;
        return $this;
    }

    public function setFormClass(string $class): self
    {
        $this->formClass = $class;
        return $this;
    }

    public function setFormAttributes(array $attributes): self
    {
        $this->formAttributes = $attributes;
        return $this;
    }

    public function startDiv(string $class = ''): self
    {
        $this->elements[] = ['type' => 'start_div', 'class' => $class];
        return $this;
    }

    public function endDiv(): self
    {
        $this->elements[] = ['type' => 'end_div'];
        return $this;
    }

    public function enableAsterisk(bool $enable = true): self
{
    $this->showRequiredAsterisk = $enable;
    return $this;
}

    public function addField(string $name, string $type, string $label = '', array $attributes = [], array $options = []): self
    {
        if (isset($attributes['required'])) {
            $this->requiredFields[$name] = [
                'label' => $label ?: $name,
                'type' => $type
            ];
        }

        if ($type === 'file') {
            $this->hasFile = true;
        }

        $this->elements[] = compact('name', 'type', 'label', 'attributes', 'options');
        return $this;
    }



    


    public function render(): void
    {
        $formAttributes = $this->renderAttributes($this->formAttributes);
        $enctype = $this->hasFile ? 'enctype="multipart/form-data"' : '';

        echo "<form id=\"{$this->formId}\" action=\"{$this->action}\" method=\"{$this->method}\" class=\"{$this->formClass}\" {$enctype} {$formAttributes}>";

        foreach ($this->elements as $el) {
            switch ($el['type'] ?? 'input') {
                case 'start_div':
                    $class = htmlspecialchars($el['class']);
                    echo "<div class=\"$class\">";
                    break;

                case 'end_div':
                    echo "</div>";
                    break;
                case 'html':
                    echo $el['html'];
                    break;
                case 'csrf':
                    $name = htmlspecialchars($el['name']);
                    $value = htmlspecialchars($el['value']);
                    echo "<input type=\"hidden\" name=\"$name\" value=\"$value\">";
                    break;

                default:
                    $this->renderField($el);
                    break;
            }
        }

        echo '<button type="submit" class="btn btn-primary">'.$this->submitLabel.'</button>';
        echo '</form>';
    }
    private function renderField(array $field): void
    {
        if (empty($field['name'])) {
            return; // Skip rendering if field name is missing
        }
        $rawName = $field['name'];
        $name = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
        $type = htmlspecialchars($field['type']);
        $label = htmlspecialchars($field['label']);
        $cleanAttributes = $field['attributes'] ?? [];
        $errorClass = isset($this->errors[$rawName]) ? ' is-invalid' : '';
        $feedback = $this->errors[$rawName] ?? '';
        $inputId = str_replace(['[', ']'], '_', $rawName); // Safe ID

        echo "<div class=\"mb-3\">";
      if ($label && !in_array($type, ['checkbox', 'radio'])) {
    $asterisk = ($this->showRequiredAsterisk && isset($this->requiredFields[$rawName])) ? ' <span class="text-danger">*</span>' : '';
    echo "<label for=\"$inputId\" class=\"form-label\">$label$asterisk</label>";
}

        switch ($type) {
            case 'textarea':
                $value = $this->getValueFromArray($_POST, $rawName) ?? '';
                echo "<textarea name=\"$name\" id=\"$inputId\" class=\"form-control{$errorClass}\" " .
                    $this->renderAttributes($cleanAttributes) . ">" . htmlspecialchars($value) . "</textarea>";
                break;

            case 'select':
                $selectedValue = $cleanAttributes['value'] ?? $this->getValueFromArray($_POST, $rawName);
                echo "<select name=\"$name\" id=\"$inputId\" class=\"form-select{$errorClass}\" " .
                    $this->renderAttributes($cleanAttributes) . ">";
                foreach ($field['options'] as $value => $text) {
                    $isSelected = ($selectedValue == $value || (is_array($selectedValue) && in_array($value, $selectedValue))) ? 'selected' : '';
                    echo "<option value=\"" . htmlspecialchars($value) . "\" $isSelected>" . htmlspecialchars($text) . "</option>";
                }
                echo "</select>";
                break;

            case 'checkbox':
            case 'radio':
                $currentValue = $cleanAttributes['value'] ?? 'on';
                $postValue = $this->getValueFromArray($_POST, $rawName);
                $isChecked = false;

                if (
                    (isset($cleanAttributes['checked']) && $cleanAttributes['checked'] == true) ||
                    ($postValue == $currentValue || (is_array($postValue) && in_array($currentValue, $postValue)))
                ) {
                    $isChecked = true;
                }

                unset($cleanAttributes['checked']);
                $checkedHtml = $isChecked ? 'checked' : '';

                echo "<div class=\"form-check\">";
                echo "<input type=\"$type\" id=\"$inputId\" name=\"$name\" class=\"form-check-input{$errorClass}\" value=\"" . htmlspecialchars($currentValue) . "\" " .
                    $this->renderAttributes($cleanAttributes) . " $checkedHtml>";
                echo "<label for=\"$inputId\" class=\"form-check-label\">$label</label>";
                echo "</div>";
                break;

            case 'file':
                echo "<input type=\"file\" name=\"$name\" id=\"$inputId\" class=\"form-control{$errorClass}\" " .
                    $this->renderAttributes($cleanAttributes) . " />";
                break;

            default:
                $value = $cleanAttributes['value'] ?? $this->getValueFromArray($_POST, $rawName) ?? '';
                echo "<input type=\"$type\" name=\"$name\" id=\"$inputId\" class=\"form-control{$errorClass}\" value=\"" .
                    htmlspecialchars($value) . "\" " . $this->renderAttributes($cleanAttributes) . " />";
        }

        if ($feedback) {
            echo "<div class=\"invalid-feedback\">$feedback</div>";
        }

        echo "</div>";
    }

    public function renderFieldByName(string $fieldName): void
{
    foreach ($this->elements as $el) {
        if (($el['name'] ?? '') === $fieldName) {
            $this->renderField($el);
            return;
        }
    }

    echo "<!-- Field '$fieldName' not found -->";
}

public function renderFieldRaw(string $name, string $type, string $label = '', array $attributes = [], array $options = []): void
{
    $field = [
        'name' => $name,
        'type' => $type,
        'label' => $label,
        'attributes' => $attributes,
        'options' => $options
    ];

    $this->renderField($field);
}

    private function renderAttributes(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= htmlspecialchars($key) . '="' . htmlspecialchars($value) . '" ';
        }
        return trim($html);
    }
    private function getFileFromArray(array $files, string $name): ?array
    {
        if (strpos($name, '[') === false) {
            return isset($files[$name]) ? $files[$name] : null;
        }

        $segments = preg_split('/[\[\]]+/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $file = [];

        foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $attr) {
            $value = $files[$segments[0]][$attr] ?? null;

            if ($value === null) {
                return null;
            }

            foreach (array_slice($segments, 1) as $segment) {
                if (!isset($value[$segment])) {
                    $value = null;
                    break;
                }
                $value = $value[$segment];
            }

            $file[$attr] = $value;
        }

        // Ensure it's a valid file structure
        return isset($file['error']) ? $file : null;
    }

    private function getValueFromArray(array $source, string $name)
    {
        if (strpos($name, '[') === false) {
            return $source[$name] ?? null;
        }

        // Convert "field[key1][key2]" into a nested array lookup
        $segments = preg_split('/[\[\]]+/', $name, -1, PREG_SPLIT_NO_EMPTY);

        $value = $source;
        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return $value;
    }
    public function validate(array $postData): array
    {
        $errors = [];

        foreach ($this->requiredFields as $name => $meta) {
            $labelText = $meta['label'] ?: ucfirst(str_replace('_', ' ', $name));
            $type = $meta['type'] ?? 'text';

            // Check if it's a file field based on type
            if ($type === 'file') {

                $fileInput = $this->getFileFromArray($_FILES, $name);

                if (is_array($fileInput) && array_key_exists('error', $fileInput)) {
                    if (is_array($fileInput['error'])) {
                        $hasUpload = false;
                        foreach ($fileInput['error'] as $error) {
                            if ($error !== UPLOAD_ERR_NO_FILE) {
                                $hasUpload = true;
                                break;
                            }
                        }
                        if (!$hasUpload) {
                            $errors[$name] = "$labelText is required.";
                        }
                    } else {
                        if ($fileInput['error'] === UPLOAD_ERR_NO_FILE) {
                            $errors[$name] = "$labelText is required.";
                        }
                    }

                    continue;
                }
            }

            // For all other types, use post data
            $value = $this->getValueFromArray($postData, $name);

            if (is_array($value)) {
                $nonEmpty = array_filter($value, fn($v) => trim((string)$v) !== '');
                if (empty($nonEmpty)) {
                    $errors[$name] = "$labelText is required.";
                }
            } else {
                if (trim((string)$value) === '') {
                    $errors[$name] = "$labelText is required.";
                }
            }
        }

        $this->errors = $errors;
        return $errors;
    }


    public function addHtml(string $html): self
    {
        $this->elements[] = ['type' => 'html', 'html' => $html];
        return $this;
    }


    public function enableCsrf(): self
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Generate CSRF token if not already set
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        $token = $_SESSION['_csrf_token'];

        $this->elements[] = [
            'type' => 'csrf',
            'name' => '_csrf_token',
            'value' => $token
        ];

        return $this;
    }

    public function validateCsrf(array $postData): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $submitted = $postData['_csrf_token'] ?? '';
        $valid = $_SESSION['_csrf_token'] ?? '';

        return hash_equals($valid, $submitted);
    }


    // --- JSON SCHEMA SUPPORT ---

    public function exportSchema(): string
    {
        return json_encode([
            'method' => $this->method,
            'action' => $this->action,
            'id' => $this->formId,
            'class' => $this->formClass,
            'attributes' => $this->formAttributes,
            'elements' => $this->elements
        ], JSON_PRETTY_PRINT);
    }

    public function importSchema(string $json): self
    {
        $data = json_decode($json, true);

        $this->method = $data['method'] ?? 'POST';
        $this->action = $data['action'] ?? '';
        $this->formId = $data['id'] ?? 'cmg-form';
        $this->formClass = $data['class'] ?? 'needs-validation';
        $this->formAttributes = $data['attributes'] ?? [];
        $this->elements = $data['elements'] ?? [];

        // Rebuild required fields
        foreach ($this->elements as $el) {
            if (isset($el['attributes']['required'])) {
                $this->requiredFields[$el['name']] = [
                    'label' => $el['label'] ?? $el['name'],
                    'type' => $el['type'] ?? 'input'
                ];
            }
            if (($el['type'] ?? '') === 'file') {
                $this->hasFile = true;
            }
        }

        return $this;
    }


    // === Feature 1: Field-Level Validation Rules ===
    private function validateFieldRule(string $rule, $value): bool
    {
        switch ($rule) {
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            case 'minlength':
                return strlen($value) >= 3;
            default:
                return true;
        }
    }

    // === Feature 2: Client-Side Hints are included via attributes ===

    // === Feature 3: Help Text / Descriptions ===
    private function renderDescription(?string $description): void
    {
        if ($description) {
            echo "<div class='form-text'>" . htmlspecialchars($description) . "</div>";
        }
    }

    // === Feature 4: Fieldsets ===
    public function startFieldset(string $legend = ''): self
    {
        $this->elements[] = ['type' => 'start_fieldset', 'legend' => $legend];
        return $this;
    }

    public function endFieldset(): self
    {
        $this->elements[] = ['type' => 'end_fieldset'];
        return $this;
    }

    // === Feature 5: Custom Rendering Callback ===
    private $renderCallback = null;
    public function setRenderCallback(callable $callback): self
    {
        $this->renderCallback = $callback;
        return $this;
    }

    // === Feature 6: Sanitization ===
    public function sanitize(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $clean[$key] = $this->sanitize($val);
            } else {
                $clean[$key] = htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
            }
        }
        return $clean;
    }

    // === Feature 7: Submission Hook ===
    private $submitCallback = null;
    public function onSubmit(callable $callback): self
    {
        $this->submitCallback = $callback;
        return $this;
    }

    public function handleSubmit(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === $this->method) {
            $errors = $this->validate($_POST);
            if (empty($errors) && $this->submitCallback) {
                call_user_func($this->submitCallback, $_POST, $_FILES);
            }
        }
    }

    // === Feature 8: Debug Mode ===
    private $debugMode = false;
    public function enableDebug(): self
    {
        $this->debugMode = true;
        return $this;
    }

    public function debugOutput(): void
    {
        if ($this->debugMode) {
            echo "<pre>POST: " . print_r($_POST, true) . "</pre>";
            echo "<pre>FILES: " . print_r($_FILES, true) . "</pre>";
            echo "<pre>ERRORS: " . print_r($this->errors, true) . "</pre>";
        }
    }

    // === Feature 9: Preview Renderer ===
    public function preview(): void
    {
        echo "<form class='{$this->formClass}'><fieldset>";
        foreach ($this->elements as $el) {
            if ($el['type'] === 'input' && !empty($el['label'])) {
                echo "<div class='mb-2'><strong>" . htmlspecialchars($el['label']) . "</strong></div>";
            }
        }
        echo "</fieldset></form>";
    }

    // === Feature 10: Markdown Doc Export ===
    public function exportMarkdownDoc(): string
    {
        $doc = "# Form Documentation\n\n";
        foreach ($this->elements as $el) {
            if (in_array($el['type'] ?? 'input', ['input', 'textarea', 'select', 'checkbox', 'radio'])) {
                $doc .= "### " . ($el['label'] ?? $el['name']) . "\n";
                $doc .= "- Name: `" . $el['name'] . "`\n";
                $doc .= "- Type: `" . $el['type'] . "`\n";
                if (!empty($el['attributes']['required'])) {
                    $doc .= "- Required: Yes\n";
                }
                $doc .= "\n";
            }
        }
        return $doc;
    }
}
