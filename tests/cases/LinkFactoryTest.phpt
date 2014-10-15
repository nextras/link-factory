<?php

namespace Nextras\Application;

use Nette\Application\Request;
use Nette\Application\Routers\Route;
use Nette\Http\Url;
use Tester;
use Tester\Assert;
use Mockery;

require __DIR__ . '/../bootstrap.php';


class LinkFactoryTest extends Tester\TestCase
{

	public function testLink()
	{
		$this->assertLink(
			'Foo:bar', ['a' => 'b'],
			'Foo', ['a' => 'b', 'action' => 'bar'],
			'/basepath/foo/bar?a=b'
		);

		$this->assertLink(
			'//Foo:bar#anchor', ['a' => 'b'],
			'Foo', ['a' => 'b', 'action' => 'bar'],
			'http://example.com/basepath/foo/bar?a=b#anchor'
		);

		$this->assertLink(
			'Admin:Dashboard:default', [],
			'Admin:Dashboard', ['action' => 'default'],
			'/basepath/admin.dashboard/default'
		);
	}


	public function assertLink($dest, $destParams, $requestPresenter, $requestParams, $finalUrl)
	{
		$url = new Url('http://example.com/basepath/');

		$realRouter = new Route('<presenter>/<action>');

		$router = Mockery::mock('Nette\Application\IRouter')
			->shouldReceive('constructUrl')->with(
				Mockery::on(function (Request $appRequest) use ($requestPresenter, $requestParams) {
					Assert::same($requestPresenter, $appRequest->getPresenterName());
					Assert::same($requestParams, $appRequest->getParameters());
					Assert::same('GET', $appRequest->getMethod());
					Assert::same([], $appRequest->getPost());
					Assert::same([], $appRequest->getFiles());
					return TRUE;
				}),
				$url
			)
			->andReturnUsing([$realRouter, 'constructUrl'])
			->getMock();

		$request = Mockery::mock('Nette\Http\IRequest')
			->shouldReceive('getUrl')
			->andReturn($url)
			->getMock();

		$factory = new LinkFactory($router, $request);
		Assert::same($finalUrl, $factory->link($dest, $destParams));
	}

}

$test = new LinkFactoryTest;
$test->run();
