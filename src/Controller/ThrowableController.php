<?php

namespace App\Controller;

use App\Annotation\Routing\NotRouted;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use function Symfony\Component\String\b;

#[NotRouted]
class ThrowableController extends Controller
{
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

        return $this->json($this->uncapsuleThrowable($e));
    }

    public function __invoke(Request $request): Response
    {
        return $this->index($request);
    }

    private function uncapsuleThrowable(Throwable $e): array
    {
        return collect((new ReflectionClass($e))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->mapWithKeys(fn(ReflectionMethod $m) => ($n = b($m->name))
                ->equalsTo($n = $n->trimPrefix('get'))
                ? [0 => 0] : [(string)$n->camel() => $e->{"get$n"}()]
            )->filter()->all();
    }
}