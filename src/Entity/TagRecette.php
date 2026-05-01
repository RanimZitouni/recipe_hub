<?php

namespace App\Entity;

use App\Repository\TagRecetteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: TagRecetteRepository::class)]
#[UniqueEntity(fields: ['nom'], message: 'Ce tag existe déjà.')]
class TagRecette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $nom = null;

    #[ORM\Column(length: 7)]
    #[Assert\NotBlank(message: 'La couleur est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^#[0-9A-Fa-f]{6}$/',
        message: 'La couleur doit être un code hexadécimal valide (ex: #FF5733).'
    )]
    private ?string $couleur = null;

    #[ORM\ManyToMany(mappedBy: 'tags', targetEntity: Recette::class)]
    private Collection $recettes;

    public function __construct()
    {
        $this->recettes = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getCouleur(): ?string { return $this->couleur; }
    public function setCouleur(string $couleur): static { $this->couleur = $couleur; return $this; }

    public function getRecettes(): Collection { return $this->recettes; }
}