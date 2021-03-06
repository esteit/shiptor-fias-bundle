<?php
namespace Shiptor\Bundle\FiasBundle\Controller\Api;

use Moriony\RpcServer\HandlerProvider\MappedContainerHandlerProvider;
use Moriony\RpcServer\Server\RpcServer;
use Moriony\RpcServer\Subscriber\GetParamAuthenticationSubscriber;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shiptor\Bundle\FiasBundle\AbstractController;
use Shiptor\Bundle\FiasBundle\Subscriber\RpcServer\ExceptionLogSubscriber;
use Shiptor\Bundle\FiasBundle\Subscriber\RpcServer\JsonRpcLogSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Moriony\RpcServer\Protocol\JsonRpcProtocol;

/**
 * Class FiasController
 * @package Shiptor\Bundle\FiasBundle\Controller\Api
 */
class FiasController extends AbstractController
{
    /**
     * @Route("/v1", name="api-system-v1")
     * @param Request $request
     * @return Response
     */
    public function v1Action(Request $request)
    {
        /** @var JsonRpcProtocol $protocol */
        $protocol = $this->container->get('rpc_server.json_rpc_protocol');
        $server = new RpcServer($protocol);
        $server->getEventDispatcher()->addSubscriber(new JsonRpcLogSubscriber($this->getDoctrine()));
        $server->getEventDispatcher()->addSubscriber(new ExceptionLogSubscriber($this->getLogger()));
        $server->getEventDispatcher()->addSubscriber(new GetParamAuthenticationSubscriber($this->container->getParameter('fias_api.key')));

        $server->addHandlerProvider(new MappedContainerHandlerProvider($this->container, [
            'map' => [
                'getActualAddresses' => [
                    'service' => 'shiptor_fias.service.fias_api',
                    'method' => 'getActualAddresses',
                ],
                'getDataByPostalCode' => [
                    'service' => 'shiptor_fias.service.fias_api',
                    'method' => 'getDataByPostalCode',
                ],
                'getActualPlainCode' => [
                    'service' => 'shiptor_fias.service.fias_api',
                    'method' => 'getActualPlainCode',
                ],
                'getAddressByFias' => [
                    'service' => 'shiptor_fias.service.fias_api',
                    'method' => 'getAddressByFias',
                ],
                'getParentByCode' => [
                    'service' => 'shiptor_fias.service.fias_api',
                    'method' => 'getParentByCode',
                ],
                'getLastUpdateDate' => [
                    'service' => 'shiptor_fias.service.fias_api',
                    'method' => 'getLastUpdateDate',
                ],
                'getPreviousCodes' => [
                    'service' => 'shiptor_fias.service.fias_api',
                    'method' => 'getPreviousCodes',
                ],
            ],
        ]));

        return $server->handleRequest($request);
    }
}
