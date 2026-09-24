<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\Validator;

/**
 * Contrôleur de base : rendu de vues, réponses JSON, redirections et validation.
 */
abstract class Controller
{
    /** @param array<string, mixed> $data */
    protected function view(string $template, array $data = [], ?string $layout = 'layouts.public'): Response
    {
        $view = View::make($template, $this->shared($data));

        if ($layout !== null) {
            $view->layout($layout);
        }

        return Response::html($view->render());
    }

    /** @param array<string, mixed> $data */
    protected function console(string $template, array $data = []): Response
    {
        return $this->view($template, $data, 'layouts.console');
    }

    /** @param array<string, mixed> $data */
    protected function admin(string $template, array $data = []): Response
    {
        return $this->view($template, $data, 'layouts.admin');
    }

    /** @param array<string, mixed> $payload */
    protected function json(array $payload, int $status = 200): Response
    {
        return Response::json($payload, $status);
    }

    protected function ok(string $message, array $extra = []): Response
    {
        return $this->json(array_merge(['success' => true, 'message' => $message], $extra));
    }

    protected function fail(string $message, array $errors = [], int $status = 422): Response
    {
        return $this->json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }

    /**
     * Réponse uniforme pour les actions console : JSON si AJAX, redirection sinon.
     *
     * @param array{success:bool,message:string,errors?:array<string,list<string>>,redirect?:string} $result
     */
    protected function respond(Request $request, array $result, string $fallbackRedirect, $formInput = null): Response
    {
        if ($request->wantsJson()) {
            return $this->json($result, $result['success'] ? 200 : 422);
        }

        if (!$result['success'] && is_array($formInput)) {
            Session::flashInput($formInput);
        }

        Session::flash($result['success'] ? 'success' : 'error', $result['message']);

        return Response::redirect($result['redirect'] ?? $fallbackRedirect);
    }

    protected function redirect(string $to, ?string $message = null, string $type = 'success'): Response
    {
        if ($message !== null) {
            Session::flash($type, $message);
        }

        return Response::redirect($to);
    }

    protected function back(Request $request, ?string $message = null, string $type = 'success'): Response
    {
        return $this->redirect($request->header('referer') ?: '/', $message, $type);
    }

    /** @param array<string, string> $rules @param array<string, string> $labels */
    protected function validate(Request $request, array $rules, array $labels = []): Validator
    {
        return new Validator($request->all(), $rules, $labels);
    }

    /**
     * Pagination simple basée sur la requête.
     *
     * @return array{page:int,perPage:int,offset:int,pages:int,total:int}
     */
    protected function paginate(Request $request, int $total, int $perPage = 20): array
    {
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max($request->integer('page', 1), 1), $pages);

        return [
            'page'    => $page,
            'perPage' => $perPage,
            'offset'  => ($page - 1) * $perPage,
            'pages'   => $pages,
            'total'   => $total,
        ];
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function shared(array $data): array
    {
        return array_merge([
            'currentPath' => (string) ($_SERVER['REQUEST_URI'] ?? '/'),
            'flashes'     => Session::pullFlashes(),
        ], $data);
    }
}
