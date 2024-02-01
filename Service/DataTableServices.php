<?php

namespace App\Service;

use App\Entity\Stores;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class DataTableServices
{
    private $container;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }
    private function getStoreName($id)
    {
        $storeId = $this->container->get('doctrine')->getRepository(Stores::class)->findOneById($id);
        $storeName = "";
        if ($storeId) {
            $storeName = $storeId->getName();
        }
        return $storeName;
    }
    private function getHostName($id)
    {
        return $this->container->getParameter('hostname_' . strtolower($this->getStoreName($id)));
    }


    private function loopArrayRequest(array $request)
    {
        return  array_map(function ($element) {
            $data = $element;
            $data['hostName'] = $this->getHostName($data["store"]);
            return $data;
        }, $request);
    }
    public function dataTableConfig($request, $reponseQuery)
    {
        $draw = $request->get('draw');
        return [
            'draw' => $draw,
            'recordsTotal' => count($reponseQuery[1]),
            'recordsFiltered' => count($reponseQuery[1]),
            'data' => $this->loopArrayRequest($reponseQuery[0]),
            'dataSrc' =>  $reponseQuery[1]
        ];
    }

    /**
     * Get request via dataTables API
     *
     * @param Request $request
     * @return array
     */
    public function getRequestDataTable(Request $request): array
    {
        return [
            'except' => $request->get('except'),
            'start' => $request->query->get('start'),
            'length' => $request->query->get('length'),
            'searchValue' => $request->get('search') ? $request->get('search')['value'] : '',
            'minDate' => $request->query->get('minDate'),
            'maxDate' => $request->query->get('maxDate'),
            'asiaSite' => $request->get('asiaSite'),
            'typeStore' => $request->get('typeStore'),
        ];
    }
}
