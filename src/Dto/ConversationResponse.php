<?php

namespace App\Dto;

class ConversationResponse
{
    public string $id;
    public ?string $name;
    public string $type;
    public array $members = [];
    public ?string $lastMessage = null;
    public ?string $lastMessageTime = null;
    public string $createdAt;
    public string $updatedAt;
}
