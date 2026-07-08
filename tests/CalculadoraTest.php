<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
use App\Calculadora;
class CalculadoraTest extends TestCase
{
public function testDeveSomarDoisNumerosCorretamente()
{
// 1. Cenário (Arrange)
$calculadora = new Calculadora();
// 2. Ação (Act)
$resultado = $calculadora->somar(10, 5);
// 3. Validação (Assert)
$this->assertEquals(15, $resultado);
}
}
