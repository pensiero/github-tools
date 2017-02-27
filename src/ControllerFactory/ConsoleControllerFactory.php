<?php
namespace GithubTools\ControllerFactory;

use GithubTools\Controller\ConsoleController as Controller;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConsoleControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        $parentLocator = $controllerManager->getServiceLocator();

        /** @var array $config */
        $config = $parentLocator->get('config');

        return new Controller($config);
    }
}