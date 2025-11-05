<?php
namespace netvod\entity;

class User
{
    public ?int $id = null;
    public ?string $email = null;
    public ?string $password_hash = null;
    public ?string $nom = null;
    public ?string $prenom = null;
}