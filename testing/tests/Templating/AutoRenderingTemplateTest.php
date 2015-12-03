<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\Templating;

use Mockery;
use PHPUnit_Framework_TestCase;
use Slim\Http\Response;
use Twig_Environment;
use Twig_Template;

class AutoRenderingTemplateTest extends PHPUnit_Framework_TestCase
{
    public function testRenderingWithoutResponseActsSameAsLazyTwig()
    {
        $environment = Mockery::mock(Twig_Environment::CLASS, [
            'loadTemplate' => Mockery::mock(Twig_Template::CLASS, ['render' => 'test-rendered'])
        ]);

        $template = new AutoRenderingTemplate($environment, null, 'path/to/template/file');

        $rendered = $template->render([
            'param1' => 'abcd',
            'param2' => '1234',
        ]);

        $this->assertSame('test-rendered', $rendered);
    }

    public function testRenderingWithResponseSetsBody()
    {
        $environment = Mockery::mock(Twig_Environment::CLASS, [
            'loadTemplate' => Mockery::mock(Twig_Template::CLASS, ['render' => 'test-rendered'])
        ]);

        $response = Mockery::mock(Response::CLASS);
        $response
            ->shouldReceive('setBody')
            ->with('test-rendered')
            ->once();

        $template = new AutoRenderingTemplate($environment, null, 'path/to/template/file');
        $template->setResponse($response);

        $rendered = $template->render([
            'param1' => 'abcd',
            'param2' => '1234',
        ]);

        $this->assertSame('test-rendered', $rendered);
    }
}
