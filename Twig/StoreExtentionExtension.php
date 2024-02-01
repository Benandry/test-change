<?php

namespace App\Twig;

use App\Entity\Stores;
use Twig\Extension\AbstractExtension;
use Psr\Container\ContainerInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class StoreExtentionExtension extends AbstractExtension
{
    private $container;

    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
    }
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('store_name', [$this, 'getStoreName']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('store_name', [$this, 'getStoreName']),
            new TwigFunction('host_name', [$this, 'getHostName']),
        ];
    }

    public function getStoreName($id)
    {
        $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneById($id);
        $storeName="";
        if($storeId) {
            $storeName = $storeId->getName();
        }
        return $storeName;
    }
    public function getHostName($id)
    {
        return $this->container->getParameter('hostname_' . strtolower($this->getStoreName($id)));
    }
}
