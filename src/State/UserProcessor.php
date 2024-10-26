<?php


namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Event\UserEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface          $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface          $removeProcessor,
        private UserPasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface    $dispatcher
    )
    {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($operation instanceof DeleteOperationInterface) {
            return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
        }

        if ($data->getPlainPassword()) {
            $data->setPassword($this->passwordHasher->hashPassword($data, $data->getPlainPassword()));
        }
        if(!$data->getPassword()) {
            $data->setPassword($this->passwordHasher->hashPassword($data, "test"));
        }


        $previousData = $context['previous_data'] ?? null;

        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        // It is important to dispatch event with result
        if (!$previousData) {
            $userEvent = new UserEvent($result);
            try {
                $this->dispatcher->dispatch($userEvent, UserEvent::CONFIRM_EMAIL);
            } catch (\Exception $e) {
            }
        }

        return $result;
    }
}
