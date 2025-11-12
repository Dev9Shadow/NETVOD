<?php
namespace netvod\entity;

class Comment
{
    public ?int $id = null;
    public ?int $id_user = null;
    public ?int $id_serie = null;
    public ?int $note = null;
    public ?string $contenu = null;
    public ?string $created_at = null;
    
    public ?string $user_nom = null;
    public ?string $user_prenom = null;
}