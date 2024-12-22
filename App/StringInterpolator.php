<?php


namespace Codegenhub\App;

use Jawira\CaseConverter\Convert;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\TwigFilter;

class StringInterpolator implements InterpolatorInterface
{
    public function interpolate(string $template, array $item, array $additional): string
    {
        $loader = new ArrayLoader([
            'index' => $template
        ]);
        $twig = new Environment($loader, [
            'autoescape' => false
        ]);
        $twig->addFilter(new TwigFilter('rtrimS', [$this, 'rtrimS']));
        $twig->addFilter(new TwigFilter('camelCase', [$this, 'convertToCamelCase']));
        $twig->addFilter(new TwigFilter('CamelCase', [$this, 'convertToPascalCase']));
        $twig->addFilter(new TwigFilter('kebab', [$this, 'convertToKebabCase']));
        $twig->addFilter(new TwigFilter('snake', [$this, 'convertToSnakeCase']));
        $twig->addFilter(new TwigFilter('macro', [$this, 'convertToMacroCase']));
        return $twig->render('index', [
            'item' => $item,
            'additional' => $additional,
            'date' => '190426_115552'
        ]);
    }

    public function rtrimS($string): string
    {
        return rtrim($string, 's');
    }


    public function convertToCamelCase($string): string
    {
        $converter = new Convert($string);
        return $converter->toCamel();
    }

    public function convertToPascalCase($string): string
    {
        $converter = new Convert($string);
        return $converter->toPascal();
    }

    public function convertToKebabCase($string): string
    {
        $converter = new Convert($string);
        return $converter->toKebab();
    }

    public function convertToSnakeCase($string): string
    {
        $converter = new Convert($string);
        return $converter->toSnake();
    }

    public function convertToMacroCase($string): string
    {
        $converter = new Convert($string);
        return $converter->toMacro();
    }
}
