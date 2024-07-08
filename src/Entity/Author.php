<?php

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['author:read', 'author:write', 'book:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['author:read', 'author:write', 'book:read'])]
    private ?string $first_name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank, Assert\Length(min: 3,  minMessage: 'Your last name must be at least {{ limit }} characters long',)]
    #[Groups(['author:read', 'author:write', 'book:read'])]
    private ?string $last_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['author:read', 'author:write', 'book:read'])]
    private ?string $sur_name = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\ManyToMany(targetEntity: Book::class, mappedBy: 'authors')]
    #[Groups(['author:read', 'author:write'])]
    private Collection $books;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getSurName(): ?string
    {
        return $this->sur_name;
    }

    public function setSurName(?string $sur_name): static
    {
        $this->sur_name = $sur_name;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): static
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
            $book->addAuthor($this);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->books->removeElement($book)) {
            $book->removeAuthor($this);
        }

        return $this;
    }
}
