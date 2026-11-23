<?php

namespace Spatie\DataTransferObject;

use ReflectionClass;
use ReflectionProperty;
use Spatie\DataTransferObject\Casters\DataTransferObjectCaster;
use Spatie\DataTransferObject\Exceptions\InvalidCasterClass;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Spatie\DataTransferObject\Exceptions\ValidationException;

/**
 * Versao PHP 7.3 do spatie/data-transfer-object 3.9.1.
 *
 * No lugar dos atributos do PHP 8, cada DTO declara listas estaticas (todas opcionais):
 * $__mapFrom, $__mapTo, $__castWith, $__propertyTypes, $__validators e $__strict.
 * As propriedades publicas ficam sem tipo.
 */
abstract class DataTransferObject
{
    const TIPOS_SIMPLES = [
        'string', 'int', 'integer', 'float', 'double', 'bool', 'boolean', 'array',
        'mixed', 'object', 'iterable', 'callable', 'null', 'self', 'parent', 'static',
    ];

    /** @var array propriedade => chave de origem do valor (aceita "a.b") */
    public static $__mapFrom = [];

    /** @var array propriedade => nome na saida de all() e toArray() */
    public static $__mapTo = [];

    /** @var array propriedade => ['caster' => Classe, 'args' => [valores posicionais]] */
    public static $__castWith = [];

    /** @var array propriedade => tipo (ex.: '?InfDpsData', 'string', 'A|B') */
    public static $__propertyTypes = [];

    /** @var array propriedade => Validator ou lista de Validator (objeto ou nome da classe) */
    public static $__validators = [];

    /** @var bool recusa chaves que nao sao propriedades */
    public static $__strict = false;

    /** @var array */
    protected $exceptKeys = [];

    /** @var array */
    protected $onlyKeys = [];

    public function __construct(...$args)
    {
        if (is_array($args[0] ?? null)) {
            $args = $args[0];
        }

        foreach ($this->propriedadesPublicas() as $propriedade) {
            $nome = $propriedade->getName();
            $origem = isset(static::$__mapFrom[$nome]) ? static::$__mapFrom[$nome] : $nome;

            $valor = Arr::get($args, $origem, Arr::get($args, $nome, $this->{$nome}));
            $this->{$nome} = $this->converterValor($propriedade, $valor);

            $args = Arr::forget(Arr::forget($args, $origem), $nome);
        }

        if (static::$__strict && count($args)) {
            throw UnknownProperties::new(static::class, array_keys($args));
        }

        $this->validar();
    }

    public static function arrayOf(array $arrayOfParameters): array
    {
        return array_map(function ($parameters) {
            return new static($parameters);
        }, $arrayOfParameters);
    }

    public function all(): array
    {
        $data = [];

        foreach ($this->propriedadesPublicas() as $propriedade) {
            $nome = $propriedade->getName();
            $saida = isset(static::$__mapTo[$nome]) ? static::$__mapTo[$nome] : $nome;

            $data[$saida] = $this->{$nome};
        }

        return $data;
    }

    public function only(string ...$keys)
    {
        $dataTransferObject = clone $this;

        $dataTransferObject->onlyKeys = array_merge($this->onlyKeys, $keys);

        return $dataTransferObject;
    }

    public function except(string ...$keys)
    {
        $dataTransferObject = clone $this;

        $dataTransferObject->exceptKeys = array_merge($this->exceptKeys, $keys);

        return $dataTransferObject;
    }

    /**
     * Copia o objeto trocando alguns valores: $dto->clone(['nome' => 'x']).
     */
    public function clone(...$args)
    {
        if (is_array($args[0] ?? null)) {
            $args = $args[0];
        }

        return new static(array_merge($this->toArray(), $args));
    }

    public function toArray(): array
    {
        if (count($this->onlyKeys)) {
            $array = Arr::only($this->all(), $this->onlyKeys);
        } else {
            $array = Arr::except($this->all(), $this->exceptKeys);
        }

        return $this->parseArray($array);
    }

    protected function parseArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if ($value instanceof DataTransferObject) {
                $array[$key] = $value->toArray();

                continue;
            }

            if (! is_array($value)) {
                continue;
            }

            $array[$key] = $this->parseArray($value);
        }

        return $array;
    }

    /**
     * @return ReflectionProperty[]
     */
    private function propriedadesPublicas(): array
    {
        $propriedades = [];

        foreach ((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $propriedade) {
            if (! $propriedade->isStatic()) {
                $propriedades[] = $propriedade;
            }
        }

        return $propriedades;
    }

    /**
     * Aplica o caster de $__castWith ou, sem ele, cria o DTO aninhado indicado em $__propertyTypes.
     */
    private function converterValor(ReflectionProperty $propriedade, $valor)
    {
        if ($valor === null) {
            return null;
        }

        $nome = $propriedade->getName();
        $tipos = $this->tiposPropriedade($nome);

        if (isset(static::$__castWith[$nome])) {
            $config = static::$__castWith[$nome];
            $classe = $config['caster'];

            if (! is_subclass_of($classe, Caster::class)) {
                throw new InvalidCasterClass($classe);
            }

            $argumentos = isset($config['args']) ? array_values($config['args']) : [];

            return (new $classe($tipos, ...$argumentos))->cast($valor);
        }

        if (! is_array($valor) && ! is_object($valor)) {
            return $valor;
        }

        $namespace = $propriedade->getDeclaringClass()->getNamespaceName();

        foreach ($tipos as $tipo) {
            $classe = $this->resolverClasseDto($tipo, $namespace);

            if ($classe !== null) {
                return (new DataTransferObjectCaster([$classe]))->cast($valor);
            }
        }

        return $valor;
    }

    private function tiposPropriedade(string $nome): array
    {
        if (! isset(static::$__propertyTypes[$nome])) {
            return [];
        }

        $tipos = [];

        foreach (explode('|', static::$__propertyTypes[$nome]) as $tipo) {
            $tipo = ltrim(trim($tipo), '?');

            if ($tipo !== '') {
                $tipos[] = $tipo;
            }
        }

        return $tipos;
    }

    /**
     * Nome sem barra e' da mesma namespace da classe que declara a propriedade.
     */
    private function resolverClasseDto(string $tipo, string $namespace): ?string
    {
        if (in_array(strtolower($tipo), self::TIPOS_SIMPLES, true)) {
            return null;
        }

        $classe = strpos($tipo, '\\') === false ? $namespace . '\\' . $tipo : $tipo;
        $classe = ltrim($classe, '\\');

        if (class_exists($classe) && is_subclass_of($classe, self::class)) {
            return $classe;
        }

        return null;
    }

    private function validar(): void
    {
        if (! static::$__validators) {
            return;
        }

        $erros = [];

        foreach (static::$__validators as $nome => $validadores) {
            $validadores = is_array($validadores) ? $validadores : [$validadores];

            foreach ($validadores as $validador) {
                if (is_string($validador)) {
                    $validador = new $validador();
                }

                $resultado = $validador->validate($this->{$nome});

                if (! $resultado->isValid) {
                    $erros[$nome][] = $resultado;
                }
            }
        }

        if (count($erros)) {
            throw new ValidationException($this, $erros);
        }
    }
}
