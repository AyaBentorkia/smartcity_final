<?php

namespace App\Services;

use App\Events\ConversationStarted;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MunicipalAdmin;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class MessagingService
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private MessageRepositoryInterface      $messageRepository,
        private NotificationService             $notificationService,
    ) {}

    public function getMyConversations(int $userId, string $role): Collection
    {
        if ($role === 'citizen') {
            return $this->conversationRepository->getByCitizenId($userId);
        }
        return $this->conversationRepository->getByMunicipalAdminId($userId);
    }

    public function getConversation(int $conversationId, int $userId): Conversation
    {
        $conversation = $this->conversationRepository->findById($conversationId);

        if (!$conversation) {
            throw new \Exception('Conversation non trouvée', 404);
        }

        if ($conversation->citizen_id !== $userId && $conversation->municipal_admin_id !== $userId) {
            throw new \Exception('Non autorisé', 403);
        }

        $this->messageRepository->markAsRead($conversationId, $userId);

        return $conversation;
    }

    public function startConversation(int $citizenId, int $adminId, ?int $incidentId, string $firstMessage): Conversation
    {
        $admin = MunicipalAdmin::where('user_id', $adminId)->first();

        if (!$admin) {
            throw new \Exception('Responsable municipal non trouvé', 404);
        }

        $existing = $this->conversationRepository->findExisting($citizenId, $adminId, $incidentId);

        if ($existing) {
            $this->sendMessage($existing->id, $citizenId, $firstMessage);
            return $existing;
        }

        $conversation = $this->conversationRepository->create([
            'citizen_id'         => $citizenId,
            'municipal_admin_id' => $adminId,
            'incident_id'        => $incidentId,
            'status'             => 'open',
            'last_message_at'    => now(),
        ]);

        $message = $this->messageRepository->create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $citizenId,
            'content'         => $firstMessage,
            'is_read'         => false,
        ]);

        // ✅ Broadcast temps réel
        broadcast(new ConversationStarted($conversation->load(['citizen', 'incident'])))->toOthers();
        broadcast(new MessageSent($message->load('sender')))->toOthers();

        $this->notificationService->notify(
            userId:  $adminId,
            title:   'Nouveau message',
            content: 'Un citoyen vous a envoyé un message',
            type:    'new_message',
        );

        Log::info('Conversation started', ['conversation_id' => $conversation->id]);

        return $conversation->load(['citizen', 'municipalAdmin', 'incident']);
    }

    public function sendMessage(int $conversationId, int $senderId, string $content): Message
    {
        $conversation = $this->conversationRepository->findById($conversationId);

        if (!$conversation) {
            throw new \Exception('Conversation non trouvée', 404);
        }

        if ($conversation->citizen_id !== $senderId && $conversation->municipal_admin_id !== $senderId) {
            throw new \Exception('Non autorisé', 403);
        }

        if ($conversation->status === 'closed') {
            throw new \Exception('Cette conversation est fermée', 400);
        }

        $message = $this->messageRepository->create([
            'conversation_id' => $conversationId,
            'sender_id'       => $senderId,
            'content'         => $content,
            'is_read'         => false,
        ]);

        $this->conversationRepository->update($conversation, [
            'last_message_at' => now(),
        ]);

        //  Broadcast temps réel
        broadcast(new MessageSent($message->load('sender')))->toOthers();

        $recipientId = $senderId === $conversation->citizen_id
            ? $conversation->municipal_admin_id
            : $conversation->citizen_id;

        $this->notificationService->notify(
            userId:  $recipientId,
            title:   'Nouveau message',
            content: 'Vous avez reçu un nouveau message',
            type:    'new_message',
        );

        Log::info('Message sent', ['conversation_id' => $conversationId]);

        return $message->load('sender');
    }

    public function closeConversation(int $conversationId, int $adminId): Conversation
    {
        $conversation = $this->conversationRepository->findById($conversationId);

        if (!$conversation) {
            throw new \Exception('Conversation non trouvée', 404);
        }

        if ($conversation->municipal_admin_id !== $adminId) {
            throw new \Exception('Non autorisé', 403);
        }

        if ($conversation->status === 'closed') {
            throw new \Exception('Conversation déjà fermée', 400);
        }

        $closed = $this->conversationRepository->update($conversation, ['status' => 'closed']);

        $this->notificationService->notify(
            userId:  $conversation->citizen_id,
            title:   'Conversation fermée',
            content: 'Votre conversation a été clôturée',
            type:    'conversation_closed',
        );

        Log::info('Conversation closed', ['conversation_id' => $conversationId]);

        return $closed;
    }

    public function deleteMessage(int $messageId, int $userId): void
    {
        $message = $this->messageRepository->findById($messageId);

        if (!$message) {
            throw new \Exception('Message non trouvé', 404);
        }

        if ($message->sender_id !== $userId) {
            throw new \Exception('Non autorisé', 403);
        }

        $this->messageRepository->delete($message);

        Log::info('Message deleted', ['message_id' => $messageId]);
    }
}