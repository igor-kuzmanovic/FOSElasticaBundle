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

namespace FOS\ElasticaBundle\Tests\Unit\Command;

use FOS\ElasticaBundle\Command\ResetCommand;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\Resetter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @internal
 */
final class ResetCommandTest extends TestCase
{
    private ResetCommand $command;

    /**
     * @var Resetter|\PHPUnit\Framework\MockObject\MockObject
     */
    private \PHPUnit\Framework\MockObject\MockObject $resetter;

    /**
     * @var IndexManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private \PHPUnit\Framework\MockObject\MockObject $indexManager;

    protected function setUp(): void
    {
        $this->resetter = $this->createMock(Resetter::class);
        $this->indexManager = $this->createMock(IndexManager::class);

        $this->command = new ResetCommand($this->indexManager, $this->resetter);
    }

    public function testResetAllIndexes(): void
    {
        $this->indexManager
            ->method('getAllIndexes')
            ->willReturn(['index1' => true, 'index2' => true])
        ;

        $matcher = $this->exactly(2);
        $this->resetter->expects($matcher)
            ->method('resetIndex')
            ->willReturnCallback(function (string $indexName) use ($matcher): void {
                match ($matcher->numberOfInvocations()) {
                    1 => $this->assertSame('index1', $indexName),
                    2 => $this->assertSame('index2', $indexName),
                };
            })
        ;

        $this->command->run(
            new ArrayInput([]),
            new NullOutput()
        );
    }

    public function testResetIndex(): void
    {
        $this->indexManager->expects($this->never())
            ->method('getAllIndexes')
        ;

        $this->resetter->expects($this->once())
            ->method('resetIndex')
            ->with('index1')
        ;

        $this->command->run(
            new ArrayInput(['--index' => 'index1']),
            new NullOutput()
        );
    }
}
