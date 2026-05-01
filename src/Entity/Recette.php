<?php

namespace App\Entity;

use App\Repository\RecetteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: RecetteRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Recette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(min: 5, minMessage: 'Le titre doit avoir au moins 5 caractères.')]
    private ?string $titre = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(min: 30, minMessage: 'La description doit avoir au moins 30 caractères.')]
    private ?string $description = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Les instructions sont obligatoires.')]
    private ?string $instructions = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Range(min: 1, notInRangeMessage: 'Le temps de préparation doit être au moins 1 minute.')]
    private ?int $tempsPreparation = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $tempsCuisson = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(
        choices: ['facile', 'moyen', 'difficile'],
        message: 'La difficulté doit être : facile, moyen ou difficile.'
    )]
    private ?string $difficulte = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Range(
        min: 1,
        max: 50,
        notInRangeMessage: 'Le nombre de personnes doit être entre 1 et 50.'
    )]
    private ?int $nbPersonnes = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $publiee = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\ManyToOne(inversedBy: 'recettes')]
    private ?CategorieRecette $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'recettes')]
    private ?User $auteur = null;

    #[ORM\ManyToMany(targetEntity: TagRecette::class, inversedBy: 'recettes')]
    private Collection $tags;

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: Ingredient::class, orphanRemoval: true)]
    private Collection $ingredients;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->ingredients = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setDateCreationValue(): void
    {
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getInstructions(): ?string { return $this->instructions; }
    public function setInstructions(string $instructions): static { $this->instructions = $instructions; return $this; }

    public function getTempsPreparation(): ?int { return $this->tempsPreparation; }
    public function setTempsPreparation(int $tempsPreparation): static { $this->tempsPreparation = $tempsPreparation; return $this; }

    public function getTempsCuisson(): ?int { return $this->tempsCuisson; }
    public function setTempsCuisson(?int $tempsCuisson): static { $this->tempsCuisson = $tempsCuisson; return $this; }

    public function getDifficulte(): ?string { return $this->difficulte; }
    public function setDifficulte(string $difficulte): static { $this->difficulte = $difficulte; return $this; }

    public function getNbPersonnes(): ?int { return $this->nbPersonnes; }
    public function setNbPersonnes(int $nbPersonnes): static { $this->nbPersonnes = $nbPersonnes; return $this; }

    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(\DateTimeInterface $dateCreation): static { $this->dateCreation = $dateCreation; return $this; }

    public function isPubliee(): ?bool { return $this->publiee; }
    public function setPubliee(bool $publiee): static { $this->publiee = $publiee; return $this; }

    public function getImageName(): ?string { return $this->imageName; }
    public function setImageName(?string $imageName): static { $this->imageName = $imageName; return $this; }

    public function getCategorie(): ?CategorieRecette { return $this->categorie; }
    public function setCategorie(?CategorieRecette $categorie): static { $this->categorie = $categorie; return $this; }

    public function getAuteur(): ?User { return $this->auteur; }
    public function setAuteur(?User $auteur): static { $this->auteur = $auteur; return $this; }

    public function getTags(): Collection { return $this->tags; }
    public function addTag(TagRecette $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        return $this;
    }
    public function removeTag(TagRecette $tag): static
    {
        $this->tags->removeElement($tag);
        return $this;
    }

    public function getIngredients(): Collection { return $this->ingredients; }
    public function addIngredient(Ingredient $ingredient): static
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
            $ingredient->setRecette($this);
        }
        return $this;
    }
    public function removeIngredient(Ingredient $ingredient): static
    {
        if ($this->ingredients->removeElement($ingredient)) {
            if ($ingredient->getRecette() === $this) {
                $ingredient->setRecette(null);
            }
        }
        return $this;
    }
}