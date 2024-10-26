<?php


namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Company;
use App\Entity\ReservationStatus;
use App\Event\ReservationEvent;
use App\Service\MercurePublisher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ReservationProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface  $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface  $removeProcessor,
        private MercurePublisher    $mercurePublisher, private Security $security,
        private NormalizerInterface $normalizer, private EntityManagerInterface $manager, private EventDispatcherInterface $eventDispatcher

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
        $statuses = $this->manager->getRepository(ReservationStatus::class)->findAll();
        $confirmed = $this->getStatus($statuses, "confirmed");
        $completed = $this->getStatus($statuses, "completed");
        $draft = $this->getStatus($statuses, "draft");
        $canceled = $this->getStatus($statuses, "canceled");

        $previousData = $context['previous_data'] ?? null;

        if ($operation instanceof Post) {
            $this->eventDispatcher->dispatch(new ReservationEvent($data), ReservationEvent::RESERVATION_CUSTOMER_CONFIRM);
        }
        if ($previousData) {
            //When customer has confirmed
            if ($data->getStatus() !== $confirmed && $this->security->getUser() === $data->getCustomer && $previousData->getStatus() !== $data->getStatus()) {
                $this->eventDispatcher->dispatch(new ReservationEvent($data), ReservationEvent::RESERVATION_CUSTOMER_HAS_CONFIRMED);
            } //When customer has cancelled
            else if ($data->getStatus() !== $canceled && $this->security->getUser() === $data->getCustomer && $previousData->getStatus() !== $data->getStatus()) {
                $this->eventDispatcher->dispatch(new ReservationEvent($data), ReservationEvent::RESERVATION_CUSTOMER_HAS_CANCELED);
            }
        }

        $type = $operation instanceof Post ? MercurePublisher::OPERATION_NEW : MercurePublisher::OPERATION_UPDATE;

        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        try {
            $serializedData = $this->normalizer->normalize(["type" => $type, "data" => $data], null, ['groups' => Company::READ]);
            $this->mercurePublisher->publishUpdate($serializedData, Company::MERCURE_TOPIC);
        } catch (\Exception $exception) {
            return $result;
        }

        return $result;

    }

    public function getStatus(array $statuses, string $name): ?ReservationStatus
    {
        $results = array_filter($statuses, function ($item) use ($name) {
            if ($item instanceof ReservationStatus && $item->getStatus() === $name) {
                return true;
            }
            return false;
        });
        return empty($results) ? null : $results[0];

    }
}
