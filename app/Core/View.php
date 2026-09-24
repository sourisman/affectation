<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Moteur de vues PHP : rendu de layouts, composants, sections et échappement.
 */
final class View
{
    /** @var array<string, mixed> */
    private static array $shared = [];

    private static string $viewPath = '';

    /** @var array<string, string> */
    private array $sections = [];

    /** @var array<string, string> */
    private array $stacks = [];

    private ?string $layout = null;

    /** @var array<string, mixed> */
    private array $data = [];

    /** @param array<string, mixed> $data */
    public function __construct(private readonly string $template, array $data = [])
    {
        $this->data = array_merge(self::$shared, $data);
    }

    public static function configure(string $viewPath): void
    {
        self::$viewPath = rtrim($viewPath, '/');
    }

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    /** @param array<string, mixed> $data */
    public static function make(string $template, array $data = []): self
    {
        return new self($template, $data);
    }

    /** @param array<string, mixed> $data */
    public static function exists(string $template): bool
    {
        return is_readable(self::resolve($template));
    }

    /** @param array<string, mixed> $data */
    public function with(string $key, mixed $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function layout(string $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    public function section(string $name, string $content): void
    {
        $this->sections[$name] = $content;
    }

    public function startSection(string $name): void
    {
        ob_start();
        $this->sections[$name] = '';
        $this->data['_section_open'] = $name;
    }

    public function endSection(): void
    {
        $name = $this->data['_section_open'] ?? null;

        if ($name !== null) {
            $this->sections[$name] = (string) ob_get_clean();
            unset($this->data['_section_open']);
        }
    }

    public function push(string $stack, string $content): void
    {
        $this->stacks[$stack] = ($this->stacks[$stack] ?? '') . $content;
    }

    public function startPush(string $stack): void
    {
        ob_start();
        $this->data['_push_open'] = $stack;
    }

    public function endPush(): void
    {
        $stack = $this->data['_push_open'] ?? null;

        if ($stack !== null) {
            $this->stacks[$stack] = ($this->stacks[$stack] ?? '') . (string) ob_get_clean();
            unset($this->data['_push_open']);
        }
    }

    public function yieldContent(string $section, string $default = ''): string
    {
        return $this->sections[$section] ?? $default;
    }

    public function stack(string $stack): string
    {
        return $this->stacks[$stack] ?? '';
    }

    public function render(): string
    {
        $content = $this->renderTemplate($this->template);

        if ($this->layout === null) {
            return $content;
        }

        $layoutTemplate = new self($this->layout, $this->data);
        $layoutTemplate->sections = $this->sections;
        $layoutTemplate->stacks = $this->stacks;
        $layoutTemplate->sections['content'] = $content;

        return $layoutTemplate->renderTemplate($this->layout);
    }

    /** @param array<string, mixed> $data */
    private function renderTemplate(string $template): string
    {
        $file = self::resolve($template);

        if (!is_readable($file)) {
            throw new RuntimeException("Vue introuvable : {$template}");
        }

        extract($this->data, EXTR_SKIP);
        $__view = $this;

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }

    private static function resolve(string $template): string
    {
        return self::$viewPath . '/' . str_replace('.', '/', $template) . '.php';
    }

    public function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function toString(): string
    {
        return $this->render();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
