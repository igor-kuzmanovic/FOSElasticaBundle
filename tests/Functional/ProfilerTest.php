<?php

declare(strict_types=1);

/*
 * This file is part of the FOSElasticaBundle package.
 *
 * (c) FriendsOfSymfony <https://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\ElasticaBundle\Tests\Functional;

use FOS\ElasticaBundle\DataCollector\ElasticaDataCollector;
use FOS\ElasticaBundle\Logger\ElasticaLogger;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Extension\HttpKernelExtension;
use Symfony\Bridge\Twig\Extension\HttpKernelRuntime;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\Group('functional')]
final class ProfilerTest extends WebTestCase
{
    private ElasticaLogger $logger;

    private Environment $twig;

    private ElasticaDataCollector $collector;

    protected function setUp(): void
    {
        $this->logger = new ElasticaLogger($this->createStub(LoggerInterface::class), true);
        $this->collector = new ElasticaDataCollector($this->logger);

        $twigLoaderFilesystem = new FilesystemLoader(__DIR__.'/../../src/Resources/views/Collector');
        $twigLoaderFilesystem->addPath(__DIR__.'/../../vendor/symfony/web-profiler-bundle/Resources/views', 'WebProfiler');
        $this->twig = new Environment($twigLoaderFilesystem, ['debug' => true, 'strict_variables' => true]);

        $urlGeneratorMock = $this->createMock(UrlGeneratorInterface::class);
        $fragmentHandlerMock = $this->createMock(FragmentHandler::class);
        $loaderMock = $this->createMock(RuntimeLoaderInterface::class);

        $this->twig->addExtension(new RoutingExtension($urlGeneratorMock));
        $this->twig->addExtension(new HttpKernelExtension());

        $urlGeneratorMock->method('generate')->willReturn('');
        $fragmentHandlerMock->method('render')->willReturn('');
        $loaderMock->method('load')->willReturn(new HttpKernelRuntime($fragmentHandlerMock));

        $this->twig->addRuntimeLoader($loaderMock);
    }

    /**
     * @param array<string, mixed>|string $query
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('queryProvider')]
    public function testRender(array|string $query): void
    {
        $connection = [
            'host' => 'localhost',
            'port' => '9200',
            'transport' => 'http',
        ];
        $this->logger->logQuery('index/_search', 'GET', $query, 1, $connection);
        $this->collector->collect($request = new Request(), new Response());

        $output = $this->twig->render('elastica.html.twig', [
            'request' => $request,
            'collector' => $this->collector,
            'queries' => $this->logger->getQueries(),
            'profile_type' => 'request',
        ]);

        $output = str_replace('&quot;', '"', $output);

        $this->assertStringContainsString('{"query":{"match_all":', $output);
        $this->assertStringContainsString('index/_search', $output);
        $this->assertStringContainsString('localhost:9200', $output);
    }

    /**
     * @return \Iterator<int<0, max>, array{(array<string, mixed> | string)}>
     */
    public static function queryProvider(): \Iterator
    {
        yield [json_decode('{"query":{"match_all":{}}}', true)];
        yield ['{"query":{"match_all":{}}}'];
    }
}
