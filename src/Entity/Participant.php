<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticipantRepository")
 */
class Participant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $mail;

    /**
     * @ORM\Column(type="boolean")
     */
    private $administrateur;

    /**
     * @ORM\Column(type="boolean")
     */
    private $actif;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sortie", mappedBy="organisateur")
     */
    private $sorties;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Sortie", mappedBy="estInscrit")
     */
    private $sortiesOuJeSuisInscrit;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="participants")
     */
    private $site;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->sortiesOuJeSuisInscrit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): self
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties[] = $sorty;
            $sorty->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): self
    {
        if ($this->sorties->contains($sorty)) {
            $this->sorties->removeElement($sorty);
            // set the owning side to null (unless already changed)
            if ($sorty->getOrganisateur() === $this) {
                $sorty->setOrganisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getSortiesOuJeSuisInscrit(): Collection
    {
        return $this->sortiesOuJeSuisInscrit;
    }

    public function addSortiesOuJeSuisInscrit(Sortie $sortiesOuJeSuisInscrit): self
    {
        if (!$this->sortiesOuJeSuisInscrit->contains($sortiesOuJeSuisInscrit)) {
            $this->sortiesOuJeSuisInscrit[] = $sortiesOuJeSuisInscrit;
            $sortiesOuJeSuisInscrit->addEstInscrit($this);
        }

        return $this;
    }

    public function removeSortiesOuJeSuisInscrit(Sortie $sortiesOuJeSuisInscrit): self
    {
        if ($this->sortiesOuJeSuisInscrit->contains($sortiesOuJeSuisInscrit)) {
            $this->sortiesOuJeSuisInscrit->removeElement($sortiesOuJeSuisInscrit);
            $sortiesOuJeSuisInscrit->removeEstInscrit($this);
        }

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }
}
