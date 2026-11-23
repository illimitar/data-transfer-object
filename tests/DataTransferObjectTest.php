<?php

namespace Spatie\DataTransferObject\Tests;

use Spatie\DataTransferObject\Exceptions\InvalidCasterClass;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Spatie\DataTransferObject\Exceptions\ValidationException;
use Spatie\DataTransferObject\Tests\Dummy\Endereco;
use Spatie\DataTransferObject\Tests\Dummy\Estrito;
use Spatie\DataTransferObject\Tests\Dummy\Pessoa;
use Spatie\DataTransferObject\Tests\Dummy\Validado;
use Spatie\DataTransferObject\DataTransferObject;

class DataTransferObjectTest extends TestCase
{
    public function test_usa_o_nome_da_propriedade()
    {
        $pessoa = new Pessoa(['nome' => 'Ana', 'idade' => 30]);

        $this->assertSame('Ana', $pessoa->nome);
        $this->assertSame(30, $pessoa->idade);
    }

    public function test_map_from_le_a_chave_de_origem()
    {
        $pessoa = new Pessoa(['xNome' => 'Ana']);

        $this->assertSame('Ana', $pessoa->nome);
    }

    public function test_map_from_aceita_ponto_e_chave_com_arroba()
    {
        $pessoa = new Pessoa(['@attributes' => ['versao' => '1.01']]);

        $this->assertSame('1.01', $pessoa->versao);
    }

    public function test_valor_padrao_quando_a_chave_nao_vem()
    {
        $pessoa = new Pessoa([]);

        $this->assertNull($pessoa->nome);
        $this->assertSame([], $pessoa->filiais);
    }

    public function test_cria_dto_aninhado_a_partir_de_array()
    {
        $pessoa = new Pessoa(['end' => ['xLgr' => 'Rua A', 'cep' => '60000000']]);

        $this->assertInstanceOf(Endereco::class, $pessoa->endereco);
        $this->assertSame('Rua A', $pessoa->endereco->rua);
        $this->assertSame('60000000', $pessoa->endereco->cep);
    }

    public function test_mantem_dto_aninhado_ja_criado()
    {
        $endereco = new Endereco(['cep' => '1']);
        $pessoa = new Pessoa(['endereco' => $endereco]);

        $this->assertSame($endereco, $pessoa->endereco);
    }

    public function test_caster_recebe_tipos_e_argumentos_posicionais()
    {
        $pessoa = new Pessoa(['tipo' => 'abc']);

        $this->assertSame('ABC!', $pessoa->tipo);
    }

    public function test_caster_nao_roda_com_valor_nulo()
    {
        $pessoa = new Pessoa(['tipo' => null]);

        $this->assertNull($pessoa->tipo);
    }

    public function test_array_caster_cria_lista_de_dtos()
    {
        $pessoa = new Pessoa(['filiais' => [['cep' => '1'], ['cep' => '2']]]);

        $this->assertCount(2, $pessoa->filiais);
        $this->assertInstanceOf(Endereco::class, $pessoa->filiais[1]);
        $this->assertSame('2', $pessoa->filiais[1]->cep);
    }

    public function test_caster_invalido_gera_erro()
    {
        $this->expectException(InvalidCasterClass::class);

        (new class(['x' => 1]) extends DataTransferObject {
            public static $__castWith = ['x' => ['caster' => \stdClass::class]];

            public $x = null;
        });
    }

    public function test_strict_recusa_chave_desconhecida()
    {
        $this->expectException(UnknownProperties::class);

        new Estrito(['nome' => 'Ana', 'extra' => 1]);
    }

    public function test_sem_strict_ignora_chave_desconhecida()
    {
        $pessoa = new Pessoa(['nome' => 'Ana', 'extra' => 1]);

        $this->assertSame('Ana', $pessoa->nome);
    }

    public function test_validador_aceita_valor_valido()
    {
        $this->assertSame(5, (new Validado(['valor' => 5]))->valor);
    }

    public function test_validador_recusa_valor_invalido()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Deve ser maior que zero.');

        new Validado(['valor' => 0]);
    }

    public function test_to_array_usa_map_to_e_converte_aninhados()
    {
        $pessoa = new Pessoa(['xNome' => 'Ana', 'end' => ['xLgr' => 'Rua A']]);

        $array = $pessoa->toArray();

        $this->assertSame('Ana', $array['name']);
        $this->assertSame('Rua A', $array['endereco']['rua']);
        $this->assertArrayNotHasKey('nome', $array);
    }

    public function test_only_e_except()
    {
        $pessoa = new Pessoa(['nome' => 'Ana', 'idade' => 30]);

        $this->assertSame(['idade' => 30], $pessoa->only('idade')->toArray());
        $this->assertArrayNotHasKey('idade', $pessoa->except('idade')->toArray());
        $this->assertSame(30, $pessoa->idade);
    }

    public function test_clone_troca_valores()
    {
        $endereco = new Endereco(['cep' => '1']);

        $copia = $endereco->clone(['cep' => '2']);

        $this->assertSame('2', $copia->cep);
        $this->assertSame('1', $endereco->cep);
    }

    public function test_array_of()
    {
        $enderecos = Endereco::arrayOf([['cep' => '1'], ['cep' => '2']]);

        $this->assertCount(2, $enderecos);
        $this->assertSame('2', $enderecos[1]->cep);
    }
}
