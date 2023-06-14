<?php

namespace App\Controller;

use App\Annotation\Routing\EntityArgProvider;
use App\Entity\Contabil;
use App\Entity\Model;
use App\Enum\EnumArgProviderMode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ContabilController extends EntityController
{
	public function index(): JsonResponse
	{
		$conta = new Contabil();
		$conta->setNome('teste');
		return $this->json($conta);
	}
	
	public function all(): JsonResponse
	{
		return $this->json($this->getBo()->all());
	}
	
	public function new(Contabil $contabil): Response
	{
		$this->getBo()->store($contabil);
		
		return $this->json($contabil);
	}
	
	public function byId(
		#[EntityArgProvider(classToDeserialize: Contabil::class)]
		Model $model
	): JsonResponse {
		return parent::byId($model);
	}
	
	public function delete(
		#[EntityArgProvider(EnumArgProviderMode::Query, classToDeserialize: Contabil::class)]
		Model $model
	): JsonResponse {
		return parent::delete($model);
	}
}
