<?php

declare(strict_types=1);

namespace App\Support;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class Container
{
    private array $bindings = [];

    private array $instances = [];

    public function bind(string $id, callable|string $concrete, bool $singleton = false): void
    {
        $this->bindings[$id] = ['concrete' => $concrete, 'singleton' => $singleton];
        unset($this->instances[$id]);
    }

    public function singleton(string $id, callable|string $concrete): void
    {
        $this->bind($id, $concrete, true);
    }

    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id])
            || isset($this->bindings[$id])
            || class_exists($id);
    }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->bindings[$id])) {
            $binding = $this->bindings[$id];
            $concrete = $binding['concrete'];

            $object = is_string($concrete) ? $this->build($concrete) : $concrete($this);

            if (!is_object($object)) {
                throw new RuntimeException(sprintf('O binding "%s" não retornou um objeto.', $id));
            }

            if ($binding['singleton']) {
                $this->instances[$id] = $object;
            }

            return $object;
        }

        return $this->build($id);
    }

    public function build(string $class): object
    {
        if (!class_exists($class)) {
            throw new RuntimeException(sprintf('Classe "%s" não encontrada.', $class));
        }

        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException(sprintf('A classe "%s" não pode ser instanciada.', $class));
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $arguments[] = $this->get($type->getName());
                continue;
            }

            if ($this->has($parameter->getName())) {
                $arguments[] = $this->get($parameter->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            if ($parameter->allowsNull()) {
                $arguments[] = null;
                continue;
            }

            throw new RuntimeException(sprintf(
                'Não foi possível resolver o parâmetro "%s" de "%s".',
                $parameter->getName(),
                $class
            ));
        }

        return $reflection->newInstanceArgs($arguments);
    }
}
