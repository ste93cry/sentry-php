<?php

declare(strict_types=1);

namespace Sentry\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Sentry\Integration\FrameContextifierIntegration;
use Sentry\Options;
use Sentry\Serializer\RepresentationSerializer;
use Sentry\Stacktrace;

final class FrameContextifierIntegrationTest extends TestCase
{
    /**
     * @dataProvider invokeDataProvider
     */
    public function testInvoke(string $fixture, int $lineNumber, ?int $contextLines, int $preContextCount, int $postContextCount): void
    {
        $integration = new FrameContextifierIntegration($contextLines);
        $integration->setupOnce();

        $fileContent = explode("\n", $this->getFixture($fixture));
        $options = new Options([]);
        $stacktrace = new Stacktrace(
            $options,
            null,
            new RepresentationSerializer($options)
        );



        $stacktrace->addFrame($this->getFixturePath($fixture), $lineNumber, ['function' => '[unknown]']);

        $frames = $stacktrace->getFrames();

        $this->assertCount(1, $frames);
        $this->assertCount($preContextCount, $frames[0]->getPreContext());
        $this->assertCount($postContextCount, $frames[0]->getPostContext());

        for ($i = 0; $i < $preContextCount; ++$i) {
            $this->assertEquals(rtrim($fileContent[$i + ($lineNumber - $preContextCount - 1)]), $frames[0]->getPreContext()[$i]);
        }

        $this->assertEquals(rtrim($fileContent[$lineNumber - 1]), $frames[0]->getContextLine());

        for ($i = 0; $i < $postContextCount; ++$i) {
            $this->assertEquals(rtrim($fileContent[$i + $lineNumber]), $frames[0]->getPostContext()[$i]);
        }
    }

    public function invokeDataProvider(): \Generator
    {
        yield 'short file' => [
            'code/ShortFile.php',
            3,
            2,
            2,
            2
        ];

        yield 'long file with default context' => [
            'code/LongFile.php',
            8,
            null,
            5,
            5
        ];

        yield 'long file with specified context' => [
            'code/LongFile.php',
            8,
            2,
            2,
            2
        ];

        yield 'short file with no context' => [
            'code/ShortFile.php',
            3,
            0,
            0,
            0
        ];

        yield 'long file near end of file' => [
            'code/LongFile.php',
            11,
            5,
            5,
            2
        ];

        yield 'long file near beginning of file' => [
            'code/LongFile.php',
            3,
            5,
            2,
            5
        ];
    }
}
