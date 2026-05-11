<?php

namespace App\Dto;

class RegisterPlayerInput
{
    public string $email = '';

    public string $password = '';

    public string $firstName = '';

    public string $lastName = '';

    public array $questionnaire = [];
}
