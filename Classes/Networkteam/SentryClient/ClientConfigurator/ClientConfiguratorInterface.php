<?php
namespace Networkteam\SentryClient\ClientConfigurator;

interface ClientConfiguratorInterface
{
    /**
     * @param \Raven_Client $client
     */
    public function configureClient(\Raven_Client $client);
}
