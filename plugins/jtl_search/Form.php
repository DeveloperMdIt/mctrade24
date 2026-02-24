<?php

namespace Plugin\jtl_search;

/**
 * Class Form
 * @package Plugin\jtl_search
 */
class Form
{
    /**
     * @var array<string, string>
     */
    private array $formData = [];

    /**
     * @var array
     */
    private array $elements = [];

    /**
     * @var array
     */
    private array $rules = [];

    /**
     * @var array
     */
    private array $errors = [];

    /**
     * Form constructor.
     * @param string $formName
     * @param string $formMethod
     */
    public function __construct(string $formName, string $formMethod)
    {
        if (
            \strlen($formName) > 0
            && !\strpos($formName, ' ')
            && (\strtolower($formMethod) === 'post' || \strtolower($formMethod) === 'get')
        ) {
            $this->formData['name']   = $formName;
            $this->formData['method'] = $formMethod;
        }
    }

    /**
     * @param string $name
     * @param string $type
     * @param string $label
     * @param array  $options
     * @return bool
     */
    public function addElement(string $name, string $type, string $label = '', array $options = []): bool
    {
        if (\strlen($name) > 0 && !\strpos($name, ' ')) {
            if (\strlen($type) > 0) {
                if (isset($this->elements[$name])) {
                    return false;
                }

                if (!\is_array($options)) {
                    $options = [];
                }
                $this->elements[$name] = [
                    'name'     => $name,
                    'type'     => $type,
                    'label'    => $label,
                    'cOpt_arr' => $options
                ];
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @param string     $elementName
     * @param string     $message
     * @param string     $rule
     * @param mixed|null $optionalParam
     */
    public function addRule(string $elementName, string $message, string $rule, $optionalParam = null): void
    {
        $rules = new FormRules();
        if (
            \strlen($elementName) > 0
            && isset($this->elements[$elementName])
            && \method_exists($rules, $rule)
        ) {
            if (isset($this->rules[$elementName])) {
                $this->rules[$elementName][] = [
                    'rule'      => $rule,
                    'message'   => $message,
                    'xOptParam' => $optionalParam
                ];
            } else {
                $this->rules[$elementName] = [
                    [
                        'rule'      => $rule,
                        'message'   => $message,
                        'xOptParam' => $optionalParam
                    ]
                ];
            }
        }
    }

    /**
     * @return string
     */
    public function getFormStartHTML(): string
    {
        $res = '';
        if (
            isset($this->formData['name'], $this->formData['method'])
            && \strlen($this->formData['name']) > 0
            && \strlen($this->formData['method'])
        ) {
            $res = '<form name="' . $this->formData['name'] . '" method="' . $this->formData['method'] . '">';
        }

        return $res;
    }

    /**
     * @return string
     */
    public function getHiddenElements(): string
    {
        $res = '';
        foreach ($this->elements as $elem) {
            if (\strtolower($elem['type']) !== 'hidden') {
                continue;
            }
            $res .= '<input type="' . $elem['type'] . '" name="' . $elem['name'] . '"';
            foreach ($elem['cOpt_arr'] as $key => $value) {
                if (\is_numeric($value) || \is_string($value)) {
                    $res .= ' ' . $key . '="' . $value . '"';
                }
            }
            $res .= ' />';
        }

        return $res;
    }

    /**
     * @return string
     */
    public function getFormEndHTML(): string
    {
        return '</form>';
    }

    /**
     * @param string $elementName
     * @return string
     */
    public function getElementHTML($elementName): string
    {
        if (isset($this->elements[$elementName])) {
            switch (\strtolower($this->elements[$elementName]['type'])) {
                case 'textarea':
                    $res          = '<' . \strtolower($this->elements[$elementName]['type'])
                        . ' name="' . $elementName . '"';
                    $defaultvalue = '';
                    foreach ($this->elements[$elementName]['cOpt_arr'] as $key => $value) {
                        if (\strtolower($key) === 'value') {
                            $defaultvalue = $value;
                        } elseif (\is_numeric($value) || \is_string($value)) {
                            $res .= ' ' . $key . '="' . $value . '"';
                        }
                    }
                    $res .= '>' . $defaultvalue . '</' .
                        \strtolower($this->elements[$elementName]['type']) . '>';

                    return $res;
                case 'text':
                case 'submit':
                    $res = '<input type="' . \strtolower($this->elements[$elementName]['type'])
                        . '" name="' . $elementName . '"';
                    foreach ($this->elements[$elementName]['cOpt_arr'] as $key => $value) {
                        if (\is_numeric($value) || \is_string($value)) {
                            $res .= ' ' . $key . '="' . $value . '"';
                        }
                    }
                    $res .= ' />';

                    return $res;
                default:
                    break;
            }
        }

        return '';
    }

    /**
     * @param string $elementName
     * @return string
     */
    public function getLabelHTML($elementName): string
    {
        return isset($this->elements[$elementName])
            ? '<label for="' . $elementName . '">' . $this->elements[$elementName]['label'] . '</label>'
            : '';
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $valid = true;
        $rules = new FormRules();
        foreach ($this->rules as $name => $rule) {
            foreach ($rule as $data) {
                if (!$rules->{$data['rule']}($_POST[$name], $data['xOptParam'])) {
                    if (isset($this->errors[$name])) {
                        $this->errors[$name][] = $data['message'];
                    } else {
                        $this->errors[$name] = [$data['message']];
                    }
                    $valid = false;
                }
            }
        }

        return $valid;
    }

    /**
     * @param string $error
     */
    public function setError(string $error): void
    {
        if (\strlen($error) > 0) {
            if (isset($this->errors['error'])) {
                $this->errors['error'][] = $error;
            } else {
                $this->errors['error'] = [$error];
            }
        }
    }

    /**
     * @param string|null $elementName
     * @return array
     */
    public function getErrorMessages(?string $elementName = null): array
    {
        $res = [];
        if (isset($elementName, $this->errors[$elementName])) {
            $res = $this->errors[$elementName];
        } else {
            foreach ($this->errors as $errors) {
                foreach ($errors as $error) {
                    $res[] = $error;
                }
            }
        }

        return $res;
    }
}
