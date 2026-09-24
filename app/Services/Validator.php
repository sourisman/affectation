<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Validation serveur : toutes les données clientes sont revérifiées côté PHP,
 * la validation frontend n'étant qu'un confort d'usage.
 */
final class Validator
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    /** @var array<string, mixed> */
    private array $data = [];

    /**
     * @param array<string, mixed> $input
     * @param array<string, string> $rules  ex. ['email' => 'required|email|max:190']
     * @param array<string, string> $labels libellés lisibles pour les messages
     */
    public function __construct(array $input, array $rules, private array $labels = [])
    {
        foreach ($rules as $field => $ruleSet) {
            $value = $input[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;

            foreach (explode('|', $ruleSet) as $rule) {
                $this->apply($field, $value, trim($rule));
            }

            $this->data[$field] = $value;
        }
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return !$this->fails();
    }

    /** @return array<string, list<string>> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        foreach ($this->errors as $messages) {
            return $messages[0];
        }

        return 'Données invalides.';
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        return $this->data;
    }

    public function value(string $field, mixed $default = null): mixed
    {
        return $this->data[$field] ?? $default;
    }

    private function apply(string $field, mixed $value, string $rule): void
    {
        $label = $this->labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
        [$name, $parameter] = array_pad(explode(':', $rule, 2), 2, null);
        $isEmpty = $value === null || $value === '' || $value === [];

        if ($name !== 'required' && $name !== 'nullable' && $isEmpty) {
            return; // champ optionnel vide : aucune autre règle appliquée
        }

        switch ($name) {
            case 'required':
                if ($isEmpty) {
                    $this->addError($field, "Le champ « {$label} » est obligatoire.");
                }
                break;

            case 'nullable':
                break;

            case 'email':
                if (!filter_var((string) $value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "L'adresse email saisie n'est pas valide.");
                }
                break;

            case 'phone':
                if (!preg_match('/^[+0-9 ().\-]{6,30}$/', (string) $value)) {
                    $this->addError($field, "Le numéro de téléphone n'est pas valide.");
                }
                break;

            case 'min':
                if (mb_strlen((string) $value) < (int) $parameter) {
                    $this->addError($field, "Le champ « {$label} » doit contenir au moins {$parameter} caractères.");
                }
                break;

            case 'max':
                if (mb_strlen((string) $value) > (int) $parameter) {
                    $this->addError($field, "Le champ « {$label} » ne peut dépasser {$parameter} caractères.");
                }
                break;

            case 'length':
                if (mb_strlen((string) $value) !== (int) $parameter) {
                    $this->addError($field, "Le champ « {$label} » doit contenir exactement {$parameter} caractères.");
                }
                break;

            case 'numeric':
                if (!is_numeric((string) $value)) {
                    $this->addError($field, "Le champ « {$label} » doit être numérique.");
                }
                break;

            case 'integer':
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "Le champ « {$label} » doit être un nombre entier.");
                }
                break;

            case 'date':
                if (strtotime((string) $value) === false) {
                    $this->addError($field, "La date du champ « {$label} » n'est pas valide.");
                }
                break;

            case 'in':
                $allowed = explode(',', (string) $parameter);
                if (!in_array((string) $value, $allowed, true)) {
                    $this->addError($field, "La valeur du champ « {$label} » n'est pas autorisée.");
                }
                break;

            case 'same':
                if (($this->data[$parameter] ?? null) !== $value) {
                    $this->addError($field, "La confirmation du champ « {$label} » ne correspond pas.");
                }
                break;

            case 'strong_password':
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{10,}$/', (string) $value)) {
                    $this->addError($field, "Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule et un chiffre.");
                }
                break;

            case 'alpha_dash':
                if (!preg_match('/^[A-Za-z0-9_\-]+$/', (string) $value)) {
                    $this->addError($field, "Le champ « {$label} » ne peut contenir que des lettres, chiffres, tirets et underscores.");
                }
                break;

            case 'regex':
                if ($parameter !== null && @preg_match('/' . $parameter . '/', (string) $value) !== 1) {
                    $this->addError($field, "Le format du champ « {$label} » est invalide.");
                }
                break;

            case 'unique':
                // unique:ModelClass,column  → vérifie l'unicité en base
                [$class, $column] = array_pad(explode(',', (string) $parameter, 2), 2, 'id');
                if (class_exists($class) && (new $class())->exists($column, $value)) {
                    $this->addError($field, "Cette valeur du champ « {$label} » est déjà utilisée.");
                }
                break;

            case 'between_dates':
                // between_dates:otherField  → value doit être >= data[otherField]
                $other = $this->data[$parameter] ?? null;
                if ($other !== null && strtotime((string) $value) < strtotime((string) $other)) {
                    $this->addError($field, "La date « {$label} » ne peut être antérieure à la date d'affectation.");
                }
                break;

            case 'different':
                if ((string) ($this->data[$parameter] ?? '') === (string) $value) {
                    $this->addError($field, "Le champ « {$label} » doit être différent du champ d'origine.");
                }
                break;

            case 'url':
                if (!filter_var((string) $value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, "L'URL du champ « {$label} » n'est pas valide.");
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
