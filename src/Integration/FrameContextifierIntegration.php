<?php

declare(strict_types=1);

namespace Sentry\Integration;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sentry\Event;
use Sentry\SentrySdk;
use Sentry\Stacktrace;
use Sentry\State\Scope;

/**
 * This integration reads excerpts of code around the line that originated an
 * error.
 *
 * @author Stefano Arlandini <sarlandini@alice.it>
 */
final class FrameContextifierIntegration implements IntegrationInterface
{
    /**
     * @var int The maximum number of lines of code to read
     */
    private $maxLinesToFetch;

    /**
     * @var LoggerInterface A PSR-3 logger
     */
    private $logger;

    /**
     * Creates a new instance of this integration.
     *
     * @param int $maxLinesToFetch The maximum number of lines of code to read
     * @param LoggerInterface|null $logger A PSR-3 logger
     */
    public function __construct(int $maxLinesToFetch = 5, ?LoggerInterface $logger = null)
    {
        if ($maxLinesToFetch <= 0) {
            throw new \InvalidArgumentException(sprintf('The value of the $maxLinesToFetch must be greater than 0. Got: "%d".', $maxLinesToFetch));
        }

        $this->maxLinesToFetch = $maxLinesToFetch;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function setupOnce(): void
    {
        Scope::addGlobalEventProcessor(static function (Event $event): ?Event {
            $currentHub = SentrySdk::getCurrentHub();
            $client = $currentHub->getClient();
            $integration = $client->getIntegration(self::class);

            if (null === $integration) {
                return $event;
            }

            if (null !== $event->getStacktrace()) {
                $integration->addContextToStacktraceFrames($event->getStacktrace());
            }

            foreach ($event->getExceptions() as $exception) {
                if (!isset($exception['stacktrace'])) {
                    continue;
                }

                $integration->addContextToStacktraceFrames($exception['stacktrace']);
            }

            return $event;
        });
    }

    /**
     * Contextifies the frames of the given stacktrace.
     *
     * @param Stacktrace $stacktrace The stacktrace object
     */
    private function addContextToStacktraceFrames(Stacktrace $stacktrace): void
    {
        foreach ($stacktrace->getFrames() as $frame) {
            if ($frame->isInternal()) {
                continue;
            }

            $sourceCodeExcerpt = $this->getSourceCodeExcerpt($frame->getFile(), $frame->getLine());

            if (isset($sourceCodeExcerpt['pre_context'])) {
                $frame->setPreContext($sourceCodeExcerpt['pre_context']);
            }

            if (isset($sourceCodeExcerpt['context_line'])) {
                $frame->setContextLine($sourceCodeExcerpt['context_line']);
            }

            if (isset($sourceCodeExcerpt['post_context'])) {
                $frame->setPostContext($sourceCodeExcerpt['post_context']);
            }
        }
    }

    /**
     * Gets an excerpt of the source code around a given line.
     *
     * @param string $path            The file path
     * @param int    $lineNumber      The line to centre about
     *
     * @return array<string, string|string[]>
     *
     * @psalm-return array{
     *     pre_context?: string[],
     *     context_line?: string,
     *     post_context?: string[]
     * }
     */
    private function getSourceCodeExcerpt(string $path, int $lineNumber): array
    {
        if (@!is_readable($path) || !is_file($path)) {
            return [];
        }

        $frame = [
            'pre_context' => [],
            'context_line' => '',
            'post_context' => [],
        ];

        $target = max(0, ($lineNumber - ($this->maxLinesToFetch + 1)));
        $currentLineNumber = $target + 1;

        try {
            $file = new \SplFileObject($path);
            $file->seek($target);

            while (!$file->eof()) {
                /** @var string $line */
                $line = $file->current();
                $line = rtrim($line, "\r\n");

                if ($currentLineNumber == $lineNumber) {
                    $frame['context_line'] = $line;
                } elseif ($currentLineNumber < $lineNumber) {
                    $frame['pre_context'][] = $line;
                } elseif ($currentLineNumber > $lineNumber) {
                    $frame['post_context'][] = $line;
                }

                ++$currentLineNumber;

                if ($currentLineNumber > $lineNumber + $this->maxLinesToFetch) {
                    break;
                }

                $file->next();
            }
        } catch (\Throwable $exception) {
            $this->logger->warning(sprintf('Failed to get the source code excerpt for the file "%s".', $path));
        }

        return $frame;
    }
}
