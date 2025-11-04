<?php
namespace netvod\model;

class User
{
    public int $id;
    public string $email;
    public string $password;
    public ?string $nom;
    public ?string $prenom;
}
