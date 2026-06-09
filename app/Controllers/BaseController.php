<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected IncomingRequest|CLIRequest $request;

    protected array $helpers = ['url', 'form', 'html'];

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
    }

    /**
     * Render view dengan layout sidebar
     */
    protected function render(string $view, array $data = []): string
    {
        $data['session'] = session();
        return view('layouts/main', array_merge($data, ['contentView' => $view]));
    }
}
