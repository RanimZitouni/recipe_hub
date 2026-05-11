<?php

namespace App\Event;

use App\Entity\Recette;
use Symfony\Contracts\EventDispatcher\Event;

class RecipeCreatedEvent extends Event
{
    public function __construct(
        private Recette $recette
    ) {}

    public function getRecette(): Recette
    {
        return $this->recette;
    }
}