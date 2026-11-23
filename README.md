# Data transfer objects (fork illimitar, PHP 7.3)

Fork de [spatie/data-transfer-object](https://github.com/spatie/data-transfer-object) 3.9.1 que roda no PHP 7.3.33.
O pacote continua com o nome `spatie/data-transfer-object`; use o branch `illi` (`dev-illi`).

O original usa recursos do PHP 8 (atributos e propriedades tipadas). Neste fork as propriedades publicas ficam
**sem tipo** e a configuracao vai em listas estaticas da propria classe. Todas as listas sao opcionais.

| Lista estatica | Substitui | Formato |
| --- | --- | --- |
| `$__mapFrom` | `#[MapFrom]` | `'propriedade' => 'chave.de.origem'` (aceita ponto, ex.: `@attributes.versao`) |
| `$__mapTo` | `#[MapTo]` | `'propriedade' => 'nome na saida'` (usado por `all()` e `toArray()`) |
| `$__castWith` | `#[CastWith]` | `'propriedade' => ['caster' => Classe::class, 'args' => [valores]]` |
| `$__propertyTypes` | tipo da propriedade | `'propriedade' => '?OutroDto'` ou `'string'` |
| `$__validators` | atributos `Validator` | `'propriedade' => [Validador::class]` |
| `$__strict` | `#[Strict]` | `true` recusa chaves que nao sao propriedades |

## Exemplo

```php
use Spatie\DataTransferObject\DataTransferObject;

class Endereco extends DataTransferObject
{
    public static $__mapFrom = ['rua' => 'xLgr'];

    public $rua = null;
    public $cep = null;
}

class Pessoa extends DataTransferObject
{
    public static $__mapFrom = ['nome' => 'xNome', 'endereco' => 'end'];

    // '?Endereco' sem barra: classe da mesma namespace de quem declara a propriedade
    public static $__propertyTypes = ['endereco' => '?Endereco'];

    public $nome = null;
    public $endereco = null;
}

$pessoa = new Pessoa(['xNome' => 'Ana', 'end' => ['xLgr' => 'Rua A', 'cep' => '60000000']]);

$pessoa->nome;          // 'Ana'
$pessoa->endereco->rua; // 'Rua A' (objeto Endereco criado sozinho)
$pessoa->toArray();     // ['nome' => 'Ana', 'endereco' => ['rua' => 'Rua A', 'cep' => '60000000']]
```

## Regras

- O construtor recebe **um array** (nao ha argumentos nomeados no PHP 7.3). Vale o mesmo para `clone(['nome' => 'x'])`.
- A chave de origem vem de `$__mapFrom`; se nao existir, vale o nome da propriedade; se tambem nao existir, o valor padrao.
- Valor `null` nunca passa por caster.
- `$__castWith`: o caster e criado com `new Caster($tipos, ...$args)`. `$tipos` sao os nomes de `$__propertyTypes`
  e `$args` entram na ordem do array (as chaves sao ignoradas).
- Sem caster, um tipo de `$__propertyTypes` que seja um DTO vira objeto quando o valor e um array.
  Os outros tipos (`string`, `int`, enums etc.) **nao** sao convertidos.
- Validador (`Validator`) precisa ter construtor sem argumentos, pois a lista guarda so o nome da classe.

## Metodos

`arrayOf`, `all`, `only`, `except`, `clone` e `toArray` seguem o original.

## Teste

```bash
composer install
vendor/bin/phpunit
```

Licenca MIT, do projeto original (Spatie).
