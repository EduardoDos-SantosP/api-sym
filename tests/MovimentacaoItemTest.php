<?php

namespace App\Tests;

use App\Entity\Movimentacao;
use App\Entity\Model;
use App\Entity\MovimentacaoItem;
use TypeError;

class MovimentacaoItemTest extends EntityBoTest
{
    protected static function bootEntities(): void
    {
        parent::bootEntities();
        self::$testEntities = [MovimentacaoItem::class];
    }

    public function store(): int
    {
        (new MovimentacaoItemTest())->testCrud();

        $model = new MovimentacaoItem();
        static::assertInstanceOf(Model::class, $model);
        foreach ($model::getProperties() as $prop) {
            if (!$prop->setter) continue;
            $testValue = match ($prop->type) {
                'int', 'float' => 10,
                'string' => 'Valor teste para ' . $prop->property,
                'bool' => false,
                'array' => [],
                default => null
            };
            try {
                [$model, $prop->setter]($testValue);
            } catch (TypeError) {
            }
        }
        $model->setMovimentacao(new Movimentacao());
        $this->bo->store($model);
        static::assertGreaterThan(0, $model->getId());
        foreach ($model::getProperties() as $prop) {
            if ($prop->type === 'string')
                [$model, $prop->setter]([$model, $prop->getter]() . ' Alterado');
            else if (in_array($prop->type, ['int', 'float']))
                [$model, $prop->setter]([$model, $prop->getter]() + 1);
        }
        $this->bo->store($model);
        static::assertGreaterThan(0, $model->getId());
        return $model->getId();
    }

    public function testCrud(): void
    {
        self::data()->qtd = $this->fetchCountEntities();

        $id = $this->store();

        static::assertEquals(
            self::data()->qtd + 1,
            $this->fetchCountEntities()
        );

        $model = $this->fetchById($id);

        /*$this->delete($model);

        static::assertEquals(
            self::data()->qtd,
            $this->fetchCountEntities()
        );*/
    }
}