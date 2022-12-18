<?php

namespace App\Controller;

use App\Annotation\Routing\NotRouted;
use RuntimeException;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use function Symfony\Component\String\b;

#[NotRouted]
class ThrowableController extends EntityController
{
	public function __invoke(Request $request): Response
	{
		return $this->index($request);
	}
	
	public function index(Request $request): Response
	{
		/** @var $e Throwable */
		if (!$e = $request->get('exception'))
			throw new RuntimeException("O recurso 'exception'não foi econtrado!");
		
		if (is_a($e, NotFoundHttpException::class)) {
			$matches = b($e->getMessage())->match('"[A-Z]+ ([A-z\:\/\d\.]+)"');
			if ($matches && ($route = $matches[1]) && !($newRoute = b($route)->lower())->equalsTo(b($route)))
				return $this->redirect($newRoute);
		}
		
		if ($request->headers->get('sec-fetch-mode') !== 'navigate')
			return $this->json([get_class($e) => $this->uncapsuleObj($e)]);
		
		return new Response((new HtmlErrorRenderer())->render($e)->getAsString());
	}
}