<?php

namespace App\Dto;

class MessageResponse
{
    public string $id;
    public string $content;
    public string $senderName;
    public string $senderId;
    public string $conversationId;
    public string $createdAt;
    public ?string $editedAt = null;
}
