<?php
namespace AA\PhotoforyouBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 * @ORM\Table(name="aa_photo")
 * @ORM\Entity(repositoryClass="AA\PhotoforyouBundle\Repository\PhotoRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Photo
{
    public function __construct()
    {
        $this->dateCreation = new \Datetime();
        $this->categories = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=3)
     *
     * @var string
     */
    private $nom;

    /**
     * @Vich\UploadableField(mapping="product_image", fileNameProperty="imageName")
     * @Assert\Image(
     *     mimeTypesMessage = "La photographie doit être au format JPEG",
     *     minWidth = 2400,
     *     minHeight = 1600,
     *     maxSize = "30M"
     * )
     * 
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    private $imageName;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\Column(name="acheteur", type="string", nullable=true)
     */
    private $acheteur = null;

    /**
     * @ORM\Column(name="vendeur", type="string", nullable=true)
     */
    private $vendeur = null;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     * @Assert\Length(min=50)
     */
    private $contenu;

    /**
     * @ORM\Column(name="prix", type="integer")
     */
    private $prix;

    /**
     * @ORM\Column(name="nb_vues", type="integer")
     */
    private $nbVues = 0;

    /**
     * @ORM\Column(name="date_creation", type="datetime")
     * @Assert\DateTime()
     *
     * @var \DateTime
     */
    private $dateCreation;

    /**
     * @ORM\ManyToMany(targetEntity="AA\PhotoforyouBundle\Entity\Categorie", cascade={"persist"})
     * @ORM\JoinTable(name="aa_photo_categorie")
     */
    private $categories;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set imageName
     *
     * @param string $imageName
     *
     * @return Photo
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * Get imageName
     *
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Photo
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set contenu
     *
     * @param string $contenu
     *
     * @return Photo
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu
     *
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set prix
     *
     * @param integer $prix
     *
     * @return Photo
     */
    public function setPrix($prix)
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * Get prix
     *
     * @return integer
     */
    public function getPrix()
    {
        return $this->prix;
    }

    /**
     * Set nbVues
     *
     * @param integer $nbVues
     *
     * @return Photo
     */
    public function setNbVues($nbVues)
    {
        $this->nbVues = $nbVues;

        return $this;
    }

    /**
     * Get nbVues
     *
     * @return integer
     */
    public function getNbVues()
    {
        return $this->nbVues;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|UploadedFile $image
     */
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImage(EmbeddedFile $image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Photo
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    public function upNbVues()
    {
        $this->nbVues = $this->nbVues + 1;
    }

    /**
     * Set vendeur
     *
     * @param string $vendeur
     *
     * @return Photo
     */
    public function setVendeur($vendeur)
    {
        $this->vendeur = $vendeur;

        return $this;
    }

    /**
     * Get vendeur
     *
     * @return string
     */
    public function getVendeur()
    {
        return $this->vendeur;
    }

    /**
     * Set acheteur
     *
     * @param string $acheteur
     *
     * @return Photo
     */
    public function setAcheteur($acheteur)
    {
        $this->acheteur = $acheteur;

        return $this;
    }

    /**
     * Get acheteur
     *
     * @return string
     */
    public function getAcheteur()
    {
        return $this->acheteur;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Photo
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Add categorie
     *
     * @param \AA\PhotoforyouBundle\Entity\Categorie $categorie
     *
     * @return Photo
     */
    public function addCategorie(\AA\PhotoforyouBundle\Entity\Categorie $categorie)
    {
        $this->categories[] = $categorie;

        return $this;
    }

    /**
     * Remove categorie
     *
     * @param \AA\PhotoforyouBundle\Entity\Categorie $categorie
     */
    public function removeCategorie(\AA\PhotoforyouBundle\Entity\Categorie $categorie)
    {
        $this->categories->removeElement($categorie);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }
}
