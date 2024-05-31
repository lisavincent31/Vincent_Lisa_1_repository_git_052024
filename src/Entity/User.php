<?php 
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getClient', 'getUsers'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getClient', 'getUsers'])]
    #[Assert\NotBlank(message: "Le prénom de votre utilisateur est obligatoire.")]
    #[Assert\Length(min:3, max:255, minMessage: "Le prénom doit contenir au moins {{limit}} caractères", maxMessage: "Le prénom ne peut pas faire plus de {{limit}} catactères")]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getClient', 'getUsers'])]
    #[Assert\NotBlank(message: "Le nom de votre utilisateur est obligatoire.")]
    #[Assert\Length(min:3, max:255, minMessage: "Le nom doit contenir au moins {{limit}} caractères", maxMessage: "Le nom ne peut pas faire plus de {{limit}} catactères")]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getClient', 'getUsers'])]
    #[Assert\Email(message:"L'email n'est pas au bon format.")]
    private ?string $email = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }
}
