<?php


namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Company;
use App\Service\MercurePublisher;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CompanyProcessor implements ProcessorInterface
{
    public function __construct(#[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
                                private ProcessorInterface       $persistProcessor,
                                #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
                                private ProcessorInterface       $removeProcessor,
                                private MercurePublisher         $mercurePublisher,
                                private NormalizerInterface      $normalizer,
                                private EventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($operation instanceof DeleteOperationInterface) {
            try {
                $serializedData = $this->normalizer->normalize(["type" => MercurePublisher::OPERATION_DELETE, "data" => $data], null, ['groups' => Company::READ]);
                $this->mercurePublisher->publishUpdate($serializedData, Company::MERCURE_TOPIC);
            } catch (\Exception $exception) {

            }
            return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
        }

        $type = $operation instanceof Post ? MercurePublisher::OPERATION_NEW : MercurePublisher::OPERATION_UPDATE;

        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        /*
        if($operation instanceof Patch) {
            if($result->getStatus===){
                $this->eventDispatcher->dispatch(new ReservationEvent($result),ReservationEvent::RESERVATION_CUSTOMER_CONFIRM);

            }
        }
        */
        try {
            $serializedData = $this->normalizer->normalize(["type" => $type, "data" => $data], null, ['groups' => Company::READ]);
            $this->mercurePublisher->publishUpdate($serializedData, Company::MERCURE_TOPIC);
        } catch (\Exception $exception) {
            return $result;
        }


        return $result;
    }
}
