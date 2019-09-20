<?php
namespace Networkteam\SentryClient\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

class SentryCommandController extends CommandController
{
    /**
     * Test the sentry client
     */
    public function testCommand()
    {
        throw new \Exception('Testing the Sentry client', 1568995699);
    }
}
