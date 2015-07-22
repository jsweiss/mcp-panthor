<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling;

use ErrorException;
use Exception;
use QL\HttpProblem\HttpProblem;
use QL\HttpProblem\HttpProblemException;
use QL\Panthor\Exception\NotFoundException;
use QL\Panthor\Exception\RequestException;
use QL\Panthor\TemplateInterface;
use Slim\Http\Response;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * Handle all errors and exceptions as HttpProblem
 *
 * This configurator outputs generic messages for errors. For internal APIs, or debug modes, you may choose to add
 * more context to the HttpProblem.
 */
class APIExceptionConfigurator extends ExceptionConfigurator
{
    const ERR_INTERNAL = 'Internal Server Error';
    const ERR_NOT_FOUND = 'Not Found';

    /**
     * {@inheritdoc}
     */
    protected function registerHandlers()
    {
        $this->register([$this, 'handleNotFoundException']);
        $this->register([$this, 'handleRequestException']);
        $this->register([$this, 'handleHttpProblemException']);
        $this->register([$this, 'handleSuperFatalException']);
        $this->register([$this, 'handleBaseException']);
    }

    /**
     * {@inheritdoc}
     */
    public function handleBaseException(Exception $exception)
    {
        $this->handleHttpProblemException(HttpProblemException::build(500, self::ERR_INTERNAL));
    }

    /**
     * {@inheritdoc}
     */
    public function handleHttpProblemException(HttpProblemException $exception)
    {
        $this->renderProblemResponse($exception->problem());
    }

    /**
     * {@inheritdoc}
     */
    public function handleNotFoundException(NotFoundException $exception)
    {
        $this->handleHttpProblemException(HttpProblemException::build(404, self::ERR_NOT_FOUND));
    }

    /**
     * @param RequestException $exception
     *
     * @return void
     */
    public function handleRequestException(RequestException $exception)
    {
        $this->handleHttpProblemException(HttpProblemException::build($exception->getCode(), $exception->getMessage()));
    }

    /**
     * {@inheritdoc}
     */
    public function handleSuperFatalException(FatalErrorException $exception)
    {
        $this->renderProblemResponse(new HttpProblem(500, self::ERR_INTERNAL), true);
    }
}
