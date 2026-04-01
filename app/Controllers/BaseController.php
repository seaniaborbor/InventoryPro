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
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation.
     *
     * @var array
     */
    protected $helpers = ['permission', 'url', 'form', 'currency'];

    /**
     * Session instance
     *
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * Request instance
     *
     * @var IncomingRequest|CLIRequest
     */
    protected $request;

    /**
     * Response instance
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Set properties
        $this->session = \Config\Services::session();
        $this->request = $request;
        $this->response = $response;
        $this->logger = $logger;
        
        // Load helpers
        helper('permission');
        helper('currency');
    }
}