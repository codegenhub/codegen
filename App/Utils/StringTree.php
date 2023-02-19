<?php

namespace Codegenhub\App\Utils;

class StringTree
{
    public function __construct(private string $rootValue, private array $children = [])
    {
    }

    public function isTerminal(): bool
    {
        return empty($this->children);
    }

    public function getValue(): string
    {
        return $this->rootValue;
    }

    public function getChildTrees(...$keys): array
    {
        $result = [];
        foreach ($this->children as $key => $values) {
            if (!in_array($key, $keys) && !empty($keys)) {
                continue;
            }
            $result[$key] = new self($key, $values);
        }

        return $result;
    }

    public function getChildTree(string $key): self
    {
        $children = $this->getChildTrees($key);

        return reset($children) ?? throw new \Exception(sprintf('Child tree with key %s not found.', $key));
    }
}
