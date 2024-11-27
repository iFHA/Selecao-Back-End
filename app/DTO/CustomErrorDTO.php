<?php
namespace App\DTO;

class CustomErrorDTO {
    public function __construct(public string $message)
    {
    }
}